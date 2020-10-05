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
 * DecayPulse
 *
 * Calculates the continuous Signal\Packet stream for an envelope defined by an exponential decay curve.
 */
class DecayPulse implements Envelope\IGenerator {

    use Signal\TContextIndexAware;

    const A_MAPS = [
        self::S_NOTE_MAP_SPEED => true,
        self::S_NOTE_MAP_LEVEL => true
    ];

    private Signal\Control\Packet $oOutputPacket;

    /** @var Map\Note\IMIDINumber[] $aNoteMaps - keyed by use case */
    private array $aNoteMaps   = [];

    private float
        $fInitial,          // User supplied initial level. Used value depends on key scaling (if any)
        $fHalfLife,         // User supplied half-life. Used value depends on key scaling (if any)
        $fCurrent,
        $fDecayPerSample,   // Calculated decay, per sample.
        $fTimeScale  = 1.0,
        $fLevelScale = 1.0
    ;

    private int
        $iSamplePosition = 0,
        $iNoteNumber     = Map\Note\IMIDINumber::CENTRE_REFERENCE
    ;

    /**
     * Constructor
     *
     * @param float                     $fInitial
     * @param float                     $fHalfLife (in seconds)
     * @param Map\Note\IMIDINumber|null $oNoteMapSpeed (optional)
     * @param Map\Note\IMIDINumber|null $oNoteMapLevel (optional)
     */
    public function __construct(
        float                $fInitial,
        float                $fHalfLife,
        Map\Note\IMIDINumber $oNoteMapSpeed  = null,
        Map\Note\IMIDINumber $oNoteMapLevel  = null
    ) {
        $this->fInitial       = $fInitial;
        $this->fHalfLife      = $fHalfLife;
        $this->oOutputPacket  = new Signal\Control\Packet();
        if ($oNoteMapSpeed) {
            $this->aNoteMaps[self::S_NOTE_MAP_SPEED] = $oNoteMapSpeed;
        }
        if ($oNoteMapLevel) {
            $this->aNoteMaps[self::S_NOTE_MAP_LEVEL] = $oNoteMapLevel;
        }
        $this->reset();
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
    public function reset() : Signal\Control\IStream {
        $this->iSamplePosition = 0;
        $this->recalculate();
        return $this;
    }

    /**
     * Emit the next signal Packet.
     *
     * @return Signal\Control\Packet
     */
    public function emit(?int $iIndex = null) : Signal\Control\Packet {
        if ($this->useLast($iIndex)) {
            return $this->oOutputPacket;
        }

        $oValues = $this->oOutputPacket->getValues();
        foreach ($oValues as $i => $fValue) {
            $this->fCurrent *= $this->fDecayPerSample;
            $oValues[$i] = $this->fCurrent;
            ++$this->iSamplePosition;
        }

        return $this->oOutputPacket;
    }

    /**
     * @inheritdoc
     *
     * @see Map\Note\IMIDINumberAware
     */
    public function getNoteNumberMapUseCases() : array {
        return array_keys(self::A_MAPS);
    }

    /**
     * @inheritdoc
     *
     * @see Map\Note\IMIDINumberAware
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
     * @see Map\Note\IMIDINumberAware
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
     * @see Map\Note\IMIDINumberAware
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
     * @see Map\Note\IMIDINumberAware
     */
    public function setNoteName(string $sNote) : self {
        // Just use the first Note Map, if any, to convert the note name.
        foreach ($this->aNoteMaps as $oNoteMap) {
            return $this->setNoteNumber($oNoteMap->getNoteNumber($sNote));
        }
        return $this;
    }

    /**
     * Recalculate the internal values
     */
    private function recalculate() {

        // First the easiest calculation which is the initial level to use.
        $this->fCurrent = $this->fInitial * $this->fLevelScale;

        // Calculate the effective half life in samples.
        // This is the sample rate * half life * key scaling factor
        $iHalfLifeInSamples = (int)(Signal\Context::get()->getProcessRate() * $this->fHalfLife * $this->fTimeScale);

        // Now calculate the required decay per sample required to reach half intensity after that many samples.
        $this->fDecayPerSample = 0.5 * 2 ** (($iHalfLifeInSamples - 1) / $iHalfLifeInSamples);

            dprintf(
                "%s() Initial Level: %f, Half Life (samples): %d, Decay Per Sample: %f\n",
                __METHOD__,
                $this->fCurrent,
                $iHalfLifeInSamples,
                $this->fDecayPerSample
            );
    }

}

