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

namespace ABadCafe\Synth\Operator;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Map;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * UnmodulatedOscillator
 *
 * Basic output only oscillator. Accepts pitch and amplitude control streams but not signal
 * modulation.
 */
class UnmodulatedOscillator extends Base implements ISource {

    const
        S_ROOT_NOTE        = 'root_note',
        S_AMPLITUDE_PREFIX = 'amplitude_',
        S_PITCH_PREFIX     = 'pitch_'
    ;

    protected Audio\IOscillator $oOscillator;

    protected float
        $fFrequencyRatio,
        $fDetune
    ;

    protected ?Signal\Control\IStream
        $oAmplitudeControl,
        $oPitchControl
    ;

    /** @var Map\Note\IMIDINumber */
    protected Map\Note\IMIDINumber $oRootNoteMap;

    protected array
        /** @var [] $aNoteMapForwards */
        $aNoteMapForwards = [],

        /** @var string[] $aNoteMapUseCases */
        $aNoteMapUseCases = []
    ;

    /**
     * Constructo
     *
     * @param Audio\IOscillator           $oOscillator       : Waveform generator to use    (required)
     * @param float                       $fFrequencyRatio   : Multiple of root note
     * @param float                       $fDetune           : Frequency adjustment
     * @param Signal\Control\IStream|null $oAmplitudeControl : Amplitude Envelope Generator (optional)
     * @param Signal\Control\IStream|null $oPitchControl     : Pitch Envelope Generator     (optional)
     * @param Map\Note\IMIDINumber|null   $oRootNoteMap      : Basic notemap for pitch
     */
    public function __construct(
        Audio\IOscillator       $oOscillator,
        float                   $fFrequencyRatio   = 1.0,
        float                   $fDetune           = 0.0,
        Signal\Control\IStream  $oAmplitudeControl = null,
        Signal\Control\IStream  $oPitchControl     = null,
        Map\Note\IMIDINumber    $oRootNoteMap      = null
    ) {
        $this->oOscillator       = $oOscillator;
        $this->fFrequencyRatio   = $fFrequencyRatio;
        $this->fDetune           = $fDetune;
        $this->oAmplitudeControl = $oAmplitudeControl;
        $this->oPitchControl     = $oPitchControl;
        $this->oRootNoteMap      = $oRootNoteMap ?: Map\Note\TwelveToneEqualTemperament::getStandardNoteMap();
        $this->configureNoteMapBehaviours();
        $this->assignInstanceID();
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->oOscillator->getPosition();
    }

    /**
     * @inheritdoc
     */
    public function reset() : Signal\Audio\IStream {
        $this->oOscillator->reset();
        if ($this->oAmplitudeControl) {
            $this->oAmplitudeControl->reset();
        }
        if ($this->oPitchControl) {
            $this->oPitchControl->reset();
        }
        $this->iPacketIndex = 0;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This Operator only accepts modulating inputs. An E_SIGNAL input will be interpreted E_AMPLITUDE.
     */
    public function attachInput(
        IOperator $oOperator,
        float     $fLevel,
        InputKind $oKind = null
    ) : IOperator {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMapUseCases() : array {
        return $this->aNoteMapUseCases;
    }

    /**
     * @inheritdoc
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : self {
        if (isset($this->aNoteMapForwards[$sUseCase])) {
            $oEntity = $this->aNoteMapForwards[$sUseCase];
            $oEntity->oControl->setNoteNumberMap(
                $oNoteMap,
                $oEntity->sUseCase
            );
        } else if ($sUseCase === self::S_ROOT_NOTE) {
            $this->oRootNoteMap = $oNoteMap;
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMap(string $sUseCase) : Map\Note\IMIDINumber {
        if (isset($this->aNoteMapForwards[$sUseCase])) {
            $oEntity = $this->aNoteMapForwards[$sUseCase];
            return $oEntity->oControl->getNoteNumberMap(
                $oEntity->sUseCase
            );
        } else if ($sUseCase === self::S_ROOT_NOTE) {
            return $this->oRootNoteMap;
        }
        return parent::getNoteNumberMap();
    }

    /**
     * @inheritdoc
     * @see IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : self {
        $fFrequency = $this->fDetune + $this->fFrequencyRatio * $this->oRootNoteMap->mapByte($iNote);
        $this->oOscillator->setFrequency($fFrequency);
        if ($this->oAmplitudeControl instanceof Map\Note\IMIDINumberAware) {
            $this->oAmplitudeControl->setNoteNumber($iNote);
        }
        if ($this->oPitchControl instanceof IMIDMap\Note\IMIDINumberAwareINoteMapAware) {
            $this->oPitchControl->setNoteNumber($iNote);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @see IMIDINumberAware
     */
    public function setNoteName(string $sNote) : self {
        return $this->setNoteNumber($this->oRootNoteMap->getNoteNumber($sNote));
    }

    /**
     * Get the principle oscillator of the source signal
     *
     * @return Audio\IOscillator
     */
    public function getOscillator() : Audio\IOscillator {
        return $this->oOscillator;
    }

    /**
     * Get the frequency ratio for the operator. This is a multiple of the frequency of the root
     * note.
     *
     * @return float
     */
    public function getFrequencyRatio() : float {
        return $this->fFrequencyRatio;
    }

    /**
     * Get the detune amount for the operator. This is a fixed offset in Hz.
     *
     * @return float
     */
    public function getDetune() : float {
        return $this->fDetune;
    }

    /**
     * Emit a Packet for a given input Index. This is used to ensure that we don't end up repeatedly asking an Operator for subsequent Packets as a consequence
     * of it being a modulator twice in the overall algorithm lattice.
     *
     * @param  int
     * @return Signal\Audio\Packet
     */
    protected function emitPacketForIndex(int $iPacketIndex) : Signal\Audio\Packet {
        if ($iPacketIndex == $this->iPacketIndex) {
            return $this->oLastPacket;
        }

        // Apply any pitch control
        if ($this->oPitchControl) {
            $this->oOscillator->setPitchModulation($this->oPitchControl->emit());
        }

        // Get the raw oscillator output
        $oOscillatorPacket = $this->oOscillator->emit();

        // Apply any amplitude control
        if ($this->oAmplitudeControl) {
            $oOscillatorPacket->modulateWith($this->oAmplitudeControl->emit());
        }

        $this->oLastPacket        = $oOscillatorPacket;
        $this->iPacketIndex       = $iPacketIndex;
        return $this->oLastPacket;
    }

    /**
     * Builds the list of note map use cases. We take the filter cutoff and resonance control inputs and if they
     * support note maps, we extract them and aggregate them here. This means the filter operator supports the
     * complete set of note maps that each of it's input controls supports. We prefix the use case to ensure that
     * there is no overlap between them.
     */
    protected function configureNoteMapBehaviours() {
        $this->oNoteMapForwards = [];
        if ($this->oAmplitudeControl instanceof IMIDINoteMapAware) {
            foreach ($this->oAmplitudeControl->getNoteNumberMapUseCases() as $sAmplitudeUseCase) {
                $this->aNoteMapForwards[self::S_AMPLITUDE_PREFIX . $sAmplitudeUseCase] = (object)[
                    'oControl' => $this->oAmplitudeControl,
                    'sUseCase' => $sAmplitudeUseCase
                ];
            }
        }
        if ($this->oPitchControl instanceof IMIDINoteMapAware) {
            foreach ($this->oPitchControl->getNoteNumberMapUseCases() as $sPitchUseCase) {
                $this->aNoteMapForwards[self::S_PITCH_PREFIX . $sPitchUseCase] = (object)[
                    'oControl' => $this->oPitchControl,
                    'sUseCase' => $sPitchUseCase
                ];
            }
        }
        $this->aNoteMapUseCases = array_merge([self::S_ROOT_NOTE], array_keys($this->aNoteMapForwards));
    }
}
