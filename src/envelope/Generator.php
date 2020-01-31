<?php

namespace ABadCafe\Synth\Envelope\Generator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Envelope\IShape;
use ABadCafe\Synth\Envelope\IGenerator;

use ABadCafe\Synth\Map\Note\IMIDINumber      as IMIDINoteMap;
use ABadCafe\Synth\Map\Note\Invariant        as InvariantNoteMap;
use ABadCafe\Synth\Map\Note\IMIDINumberAware as IMIDINoteMapAware;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Generator
 *
 * Calculates the continuous signal packet stream for an envelope defined by a given IShape
 */
class LinearInterpolated implements IGenerator {

    const A_MAPS = [
        self::S_NOTE_MAP_SPEED => true,
        self::S_NOTE_MAP_LEVEL => true
    ];

    private
        /** @var IShape $oShape : input IShape */
        $oShape          = null,

        /** @var IMIDINoteMap[] $aNoteMaps - keyed by use case */
        $aNoteMaps       = [],

        /** @var int $iNoteNumber */
        $iNoteNumber     = IMIDINoteMap::CENTRE_REFERENCE,

        /** @var float $fTimeScale */
        $fTimeScale      = 1.0,

        /** @var float $fAmplitude Scale */
        $fLevelScale = 1.0,

        /** @var Packet $oOutputPacket : Buffer for signal */
        $oOutputPacket   = null,

        /** @var Packet $oFinalPacket : Fixed packet filled with the final envelope value */
        $oFinalPacket    = null,

        /** @var {int, float}[] $aProcessPoints : Envelope IShape points, converted into Sample Position => Level pairs */
        $aProcessPoints  = [],

        /** @var int[] $aProcessPoints : Indexes to the Process Points array, keyed by the Sample Position they start at  */
        $aProcessIndexes = [],

        /** @var int $iSamplePosition : Current Sample Position */
        $iSamplePosition = 0,

        /** @var int $iLastPosition : Used to early out and return the fixed packet */
        $iLastPosition   = 0,

        /** @var float $fGradient : Current Interpolant Gradient */
        $fGradient       = 0,

        /** @var float $fYOffset : Current Interpolant Y Offset */
        $fYOffset        = 0,

        /** @var int $iXOffset : Current Interpolant X Offset */
        $iXOffset        = 0
    ;

    /**
     * Constructor
     *
     * Accepts the basic envelope shape and a pair of optional note maps that are used to scale the speed and level of the envelope points
     * depending on the note number.
     *
     * @param IShape            $oShape
     * @param IMIDINoteMap|null $oNoteMapSpeed (optional)
     * @param IMIDINoteMap|null $oNoteMapLevel (optional)
     */
    public function __construct(
        IShape       $oShape,
        IMIDINoteMap $oNoteMapSpeed = null,
        IMIDINoteMap $oNoteMapLevel = null
    ) {
        $this->oShape        = $oShape;
        $this->oOutputPacket = new Packet();
        $this->oFinalPacket  = new Packet();
        if ($oNoteMapSpeed) {
            $this->aNoteMaps[self::S_NOTE_MAP_SPEED] = $oNoteMapSpeed;
        }
        if ($oNoteMapLevel) {
            $this->aNoteMaps[self::S_NOTE_MAP_LEVEL] = $oNoteMapLevel;
        }
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function getShape() : IShape {
        return $this->oShape;
    }

    /**
     * @inheritdoc
     */
    public function setShape(IShape $oShape) : IGenerator {
        $this->oShape = $oShape;
        $this->reset();
        return $this;
    }

    /**
     * Get the oscillator sample position, which is the total number of samples generated since
     * instantiation or the last call to reset().
     *
     * @return int
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * Reset the envelope. This resets the sample output position and re-evaluates the IShape in case of any changes.
     *
     * @return IStream
     */
    public function reset() : IStream {
        $this->iSamplePosition = 0;
        $this->recalculate();
        return $this;
    }

    /**
     * Emit the next signal Packet.
     *
     * @return Packet
     */
    public function emit() : Packet {
        $iLength = Context::get()->getPacketLength();

        // If we are at the end of the envelope, just return the final packet
        if ($this->iSamplePosition >= $this->iLastPosition) {
            $this->iSamplePosition += $iLength;
            return clone $this->oFinalPacket;
        }

        $oValues = $this->oOutputPacket->getValues();

        for ($i = 0; $i < $iLength; $i++) {
            // If the sample position hits a process index position, we need to recalculate our interpolants
            if (isset($this->aProcessIndexes[$this->iSamplePosition])) {
                $this->updateInterpolants();
            }
            $oValues[$i] = $this->fYOffset + (++$this->iSamplePosition - $this->iXOffset)*$this->fGradient;
        }
        return $this->oOutputPacket;
    }

    /**
     * @inheritdoc
     */
    public function setNoteNumberMap(IMIDINoteMap $oNoteMap, string $sUseCase = null) : IMIDINoteMapAware {
        if (isset(self::A_MAPS[$sUseCase])) {
            $this->aNoteMaps[$sUseCase] = $oNoteMap;
            $this->recalculate();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNoteNumberMap(string $sUseCase = null) : IMIDINoteMap {
        if (null !== $sUseCase && isset($this->aNoteMaps[$sUseCase])) {
            return $this->aNoteMaps[$sUseCase];
        }
        // Fulfil the interface requirements by returning the invariant note map
        return InvariantNoteMap::get();
    }

    /**
     * @inheritdoc
     */
    public function setNoteNumber(int $iNote) : IMIDINoteMapAware {
        // If the note number has changed, use the key scale map to obtain the time scaling to use for that note
        if ($iNote != $this->iNoteNumber) {
            $this->fTimeScale = isset($this->aNoteMaps[self::S_NOTE_MAP_SPEED]) ?
                $this->aNoteMaps[self::S_NOTE_MAP_SPEED]->mapByte($iNote) :
                1.0;

            $this->fLevelScale = isset($this->aNoteMaps[self::S_NOTE_MAP_LEVEL]) ?
                $this->aNoteMaps[self::S_NOTE_MAP_LEVEL]->mapByte($iNote) :
                1.0;

            $this->iNoteNumber = $iNote;

            fprintf(
                STDERR,
                "%s() Set Note #%d : TScale %.3f, LScale %.3f\n",
                __METHOD__,
                $iNote,
                $this->fTimeScale,
                $this->fLevelScale
            );

            $this->recalculate();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setNoteName(string $sNote) : IMIDINoteMapAware {
        // Just use the first Note Map, if any, to convert the note name.
        foreach ($this->aNoteMaps as $oNoteMap) {
            return $this->setNoteNumber($oNoteMap->getNoteNumber($sNote));
        }
        return $this;
    }

    /**
     * Recalculate the internal process points
     */
    private function recalculate() {
        $this->aProcessPoints  = [];
        $iProcessRate = Context::get()->getProcessRate();
        $fTimeTotal   = 0.0;
        $i = 0;
        foreach ($this->oShape->getAll() as $aPoint) {
            $fTimeTotal += $aPoint[1] * $this->fTimeScale;
            $iPosition = (int)($fTimeTotal * $iProcessRate);
            $this->aProcessIndexes[$iPosition] = $i;
            $this->aProcessPoints[$i++] = (object)[
                'iStart' => $iPosition,
                'fLevel' => $aPoint[0] * $this->fLevelScale
            ];
        }
        $oLastPoint = end($this->aProcessPoints);

        // Pad on the last point again with a slight time offset. This ensures the interpolant code is always acting between a pair
        // of points and avoids wandering off the end of the array.
        $this->aProcessPoints[$i] = (object)[
            'iStart' => $oLastPoint->iStart + 16,
            'fLevel' => $oLastPoint->fLevel
        ];

        $this->iLastPosition = $oLastPoint->iStart;
        $this->oFinalPacket->fillWith($oLastPoint->fLevel);
    }

    /**
     * Calculate the interpolants for the current phase of the envelope
     */
    private function updateInterpolants() {
        $iIndex  = $this->aProcessIndexes[$this->iSamplePosition];
        $oPointA = $this->aProcessPoints[$iIndex];
        $oPointB = $this->aProcessPoints[$iIndex + 1];
        $this->fGradient = ($oPointB->fLevel - $oPointA->fLevel) / (float)($oPointB->iStart - $oPointA->iStart);
        $this->fYOffset  = $oPointA->fLevel;
        $this->iXOffset  = $oPointA->iStart;
    }
}
