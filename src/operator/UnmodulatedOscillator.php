<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;

use ABadCafe\Synth\Map\Note\IMIDINumber      as IMIDINoteMap;
use ABadCafe\Synth\Map\Note\Invariant        as InvariantNoteMap;
use ABadCafe\Synth\Map\Note\IMIDINumberAware as IMIDINoteMapAware;
use ABadCafe\Synth\Map\Note\TwelveToneEqualTemperament;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * UnmodulatedOscillator
 *
 * Basic output only oscillator. Ignores all input. Intended for use as LFO, etc.
 */
class UnmodulatedOscillator extends Base {

    const
        S_ROOT_NOTE        = 'root_note',
        S_AMPLITUDE_PREFIX = 'amplitude_',
        S_PITCH_PREFIX     = 'pitch_'
    ;

    protected
        /** @var IOscillator $oOscillator */
        $oOscillator,

        /** @var IStream $oAmplitudeControl */
        $oAmplitudeControl,

        /** @var IStream $oPitchControl */
        $oPitchControl,

        /** @var IMIDINoteMap */
        $oRootNoteMap,

        /** @var [] */
        $aNoteMapForwards = [],

        /** @var string[] $aNoteMapUseCases */
        $aNoteMapUseCases = []
    ;

    /**
     * Constructor
     *
     * @param IOscillator       $oOscillator       : Waveform generator to use    (required)
     * @param IStream|null      $oAmplitudeControl : Amplitude Envelope Generator (optional)
     * @param IStream|null      $oPitchControl     : Pitch Envelope Generator     (optional)
     * @param IMIDINoteMap|null $oRootNoteMap      : Basic notemap for pitch
     */
    public function __construct(
        IOscillator  $oOscillator,
        IStream      $oAmplitudeControl = null,
        IStream      $oPitchControl     = null,
        IMIDINoteMap $oRootNoteMap      = null
    ) {
        $this->oOscillator       = $oOscillator;
        $this->oAmplitudeControl = $oAmplitudeControl;
        $this->oPitchControl     = $oPitchControl;
        $this->oRootNoteMap      = $oRootNoteMap ?: TwelveToneEqualTemperament::getStandardNoteMap();
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
    public function reset() : IStream {
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
    public function setNoteNumberMap(IMIDINoteMap $oNoteMap, string $sUseCase) : IMIDINoteMapAware {
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
    public function getNoteNumberMap(string $sUseCase) : IMIDINoteMap {
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
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : IMIDINoteMapAware {
        $this->oOscillator->setFrequency($this->oRootNoteMap->mapByte($iNote));
        if ($this->oAmplitudeControl instanceof IMIDINoteMapAware) {
            $this->oAmplitudeControl->setNoteNumber($iNote);
        }
        if ($this->oPitchControl instanceof IMIDINoteMapAware) {
            $this->oPitchControl->setNoteNumber($iNote);
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteName(string $sNote) : IMIDINoteMapAware {
        return $this->setNoteNumber($this->oRootNoteMap->getNoteNumber($sNote));
    }

    /**
     * Emit a Packet for a given input Index. This is used to ensure that we don't end up repeatedly asking an Operator for subsequent Packets as a consequence
     * of it being a modulator twice in the overall algorithm lattice.
     *
     * @param  int
     * @return Packet
     */
    protected function emitPacketForIndex(int $iPacketIndex) : Packet {
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
