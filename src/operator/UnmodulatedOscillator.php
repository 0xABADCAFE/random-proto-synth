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

    protected ?Signal\Control\IStream $oAmplitudeControl;
    protected ?signal\Control\IStream $oPitchControl;

    protected ?Signal\Audio\Stream\Amplifier $oAmplifier;

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

        // If an Amplitude Control is provide, create an Amplifier from it
        if ($oAmplitudeControl) {
            $this->oAmplifier = new Signal\Audio\Stream\Amplifier(
                $this->oOscillator,
                $oAmplitudeControl
            );
        }
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
        if ($this->oAmplifier) {
            $this->oAmplifier->reset();
        } else {
            $this->oOscillator->reset();
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
     * @inheritDoc
     */
    protected function emitNew() : Signal\Audio\Packet {

        // Apply any pitch control
        if ($this->oPitchControl) {
            $this->oOscillator->setPitchModulation($this->oPitchControl->emit($this->iLastIndex));
        }

        // If we have a volume control, get the amplifier output. Otherwise, the raw oscillator
        if ($this->oAmplifier) {
            $this->oLastPacket = $this->oAmplifier->emit($this->iLastIndex);
        } else {
            $this->oLastPacket = $this->oOscillator->emit($this->iLastIndex);
        }

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
