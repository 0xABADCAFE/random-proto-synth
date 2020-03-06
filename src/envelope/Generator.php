<?php

namespace ABadCafe\Synth\Envelope\Generator;

use ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Map;

use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Generator
 *
 * Calculates the continuous mono signal packet stream for an envelope defined by a given Envelope\IShape
 */
class LinearInterpolated implements Envelope\IGenerator {

    const A_MAPS = [
        self::S_NOTE_MAP_SPEED => true,
        self::S_NOTE_MAP_LEVEL => true
    ];

    private
        /** @var Envelope\IShape $oShape : input envelope shape */
        $oShape          = null,

        /** @var Map\Note\IMIDINumber[] $aNoteMaps - keyed by use case */
        $aNoteMaps       = [],

        /** @var int $iNoteNumber */
        $iNoteNumber     = Map\Note\IMIDINumber::CENTRE_REFERENCE,

        /** @var float $fTimeScale */
        $fTimeScale      = 1.0,

        /** @var float $fAmplitude Scale */
        $fLevelScale     = 1.0,

        /** @var Signal\Control\Packet $oOutputPacket : Buffer for signal */
        $oOutputPacket   = null,

        /** @var Signal\Control\Packet $oFinalPacket : Fixed packet filled with the final envelope value */
        $oFinalPacket    = null,

        /** @var {int, float}[] $aProcessPoints : Envelope points, converted into Sample Position => Level pairs */
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
     * @param Envelope\IShape           $oShape
     * @param Map\Note\IMIDINumber|null $oNoteMapSpeed (optional)
     * @param Map\Note\IMIDINumber|null $oNoteMapLevel (optional)
     */
    public function __construct(
        Envelope\IShape      $oShape,
        Map\Note\IMIDINumber $oNoteMapSpeed = null,
        Map\Note\IMIDINumber $oNoteMapLevel = null
    ) {
        $this->oShape        = $oShape;
        $this->oOutputPacket = new Signal\Control\Packet();
        $this->oFinalPacket  = new Signal\Control\Packet();
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
    public function getShape() : Envelope\IShape {
        return $this->oShape;
    }

    /**
     * @inheritdoc
     */
    public function setShape(Envelope\IShape $oShape) : Envelope\IGenerator {
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
     * Reset the envelope. This resets the sample output position and re-evaluates the Envelope\IShape in case of any changes.
     *
     * @return Signal\Control\IStream
     */
    public function reset() : Signal\Control\IStream {
        $this->iSamplePosition = 0;
        $this->recalculate();
        return $this;
    }

    /**
     * Emit the next signal Signal\Control\Packet.
     *
     * @return Signal\Control\Packet
     */
    public function emit() : Signal\Control\Packet {
        $iLength = Signal\Context::get()->getPacketLength();

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
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMapUseCases() : array {
        return array_keys(self::A_MAPS);
    }

    /**
     * @inheritdoc
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : Map\Note\IMIDINumberAware {
        if (isset(self::A_MAPS[$sUseCase])) {
            $this->aNoteMaps[$sUseCase] = $oNoteMap;
            $this->recalculate();
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMap(string $sUseCase) : Map\Note\IMIDINumber {
        if (null !== $sUseCase && isset($this->aNoteMaps[$sUseCase])) {
            return $this->aNoteMaps[$sUseCase];
        }
        // Fulfil the interface requirements by returning the invariant note map
        return Map\Note\InvariantNoteMap::get();
    }

    /**
     * @inheritdoc
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : Map\Note\IMIDINumberAware {
        // If the note number has changed, use the key scale map to obtain the time scaling to use for that note
        if ($iNote != $this->iNoteNumber) {
            $this->fTimeScale = isset($this->aNoteMaps[self::S_NOTE_MAP_SPEED]) ?
                $this->aNoteMaps[self::S_NOTE_MAP_SPEED]->mapByte($iNote) :
                1.0;

            $this->fLevelScale = isset($this->aNoteMaps[self::S_NOTE_MAP_LEVEL]) ?
                $this->aNoteMaps[self::S_NOTE_MAP_LEVEL]->mapByte($iNote) :
                1.0;

            $this->iNoteNumber = $iNote;

            dprintf(
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
     *
     * @see IMIDINumberAware
     */
    public function setNoteName(string $sNote) : Map\Note\IMIDINumberAware {
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
        $iProcessRate = Signal\Context::get()->getProcessRate();
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
