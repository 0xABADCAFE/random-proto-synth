<?php

/**
 *      _/_/_/  _/_/_/  _/      _/  _/_/_/      _/_/_/
 *   _/          _/    _/_/  _/_/  _/    _/  _/
 *  _/  _/_/    _/    _/  _/  _/  _/_/_/      _/_/
 * _/    _/    _/    _/      _/  _/              _/
 *  _/_/_/  _/_/_/  _/      _/  _/        _/_/_/
 *
 *  - Grossly Impractical Modular PHP Synthesiser -
 *
 */

declare(strict_types = 1);

namespace ABadCafe\Synth\Envelope\Generator;
use ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Map;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Generator
 *
 * Calculates the continuous Signal\Packet stream for an envelope defined by a given Envelope\IShape
 */
class LinearInterpolated implements Envelope\IGenerator {

    const A_MAPS = [
        self::S_NOTE_MAP_SPEED => true,
        self::S_NOTE_MAP_LEVEL => true
    ];

    private Envelope\IShape $oShape;

    private Signal\Control\Packet
        $oOutputPacket, // Buffer for control signal
        $oFinalPacket   // Fixed packet filled with the final envelope value
    ;

    /** @var Map\Note\IMIDINumber[] $aNoteMaps - keyed by use case */
    private array $aNoteMaps = [];

    private int   $iNoteNumber = Map\Note\IMIDINumber::CENTRE_REFERENCE;

    private float
        $fTimeScale  = 1.0,
        $fLevelScale = 1.0
    ;

    private array
        /** @var {int, float}[] $aProcessPoints : Envelope points, converted into Sample Position => Level pairs */
        $aProcessPoints  = [],

        /** @var int[] $aProcessPoints : Indexes to the Process Points array, keyed by the Sample Position they start at  */
        $aProcessIndexes = []
    ;

    private int
        $iSamplePosition = 0, // Current Sample Position
        $iLastPosition   = 0    // Used to early out and return the fixed packet
    ;

    private float
        $fGradient = 0, // Current Interpolant Gradient
        $fYOffset  = 0  //  Current Interpolant Y Offset
    ;

    private int   $iXOffset = 0; //  Current Interpolant X Offset

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
    public function setShape(Envelope\IShape $oShape) : self {
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
     * @return Signal\Control\IStream
     */
    public function reset() : self {
        $this->iSamplePosition = 0;
        $this->recalculate();
        return $this;
    }

    /**
     * Emit the next signal Packet.
     *
     * @return Packet
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
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : self {
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
    public function setNoteNumber(int $iNote) : self {
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
    public function setNoteName(string $sNote) : self {
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
