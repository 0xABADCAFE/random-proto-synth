<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Modulatable
 *
 * Simple modulatable Operator implementation. Supports E_AMPLITUDE and E_PHASE modulation inputs.
 */
class ModulatableOscillator extends Base implements IAmplitudeModulated, IPhaseModulated {

    protected
        /** @var IOscillator $oOscillator */
        $oOscillator,

        /** @var IStream $oAmplitudeControl */
        $oAmplitudeControl,

        /** @var IStream $oPitchControl */
        $oPitchControl,

        /** @var IOperator[] $aModulators - keyed by instance ID */
        $aModulators               = [],

        /** @var float[] $aPhaseModulationIndex - keyed by instance ID */
        $aPhaseModulationIndex     = [],

        /** @var float[] $aAmplidudeModulationIndex - keyed by instance ID */
        $aAmplitudeModulationIndex = []
    ;

    /**
     * Constructor
     *
     * @param IOscillator  $oOscillator       : Waveform generator to use   (required)
     * @param IStream|null $oAmplitudeControl : Amplitude Envelope Generator (optional)
     * @param IStream|null $oPitchControl     : Pitch Envelope Generator     (optional)
     */
    public function __construct(
        IOscillator $oOscillator,
        IStream     $oAmplitudeControl  = null,
        IStream     $oPitchControl      = null
    ) {
        $this->oOscillator       = $oOscillator;
        $this->oAmplitudeControl = $oAmplitudeControl;
        $this->oPitchControl     = $oPitchControl;
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
        $iKind = $oKind ? $oKind->getValue() : InputKind::E_PHASE;
        switch ($oKind->getValue()) {
            case InputKind::E_SIGNAL;
            case InputKind::E_AMPLITUDE:
                return $this->attachAmplitudeModulatorInput($oOperator, $fLevel);
            case InputKind::E_PHASE:
                return $this->attachPhaseModulatorInput($oOperator, $fLevel);;
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @see IAmplitudeModulated
     */
    public function attachAmplitudeModulatorInput(IOperator $oOperator, float $fLevel) : IAmplitudeModulated {
        $this->aModulators[$oOperator->iInstanceID]               = $oOperator;
        $this->aAmplitudeModulationIndex[$oOperator->iInstanceID] = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     * @see IPhaseModulated
     */
    public function attachPhaseModulatorInput(IOperator $oOperator, float $fLevel) : IPhaseModulated {
        $this->aModulators[$oOperator->iInstanceID]           = $oOperator;
        $this->aPhaseModulationIndex[$oOperator->iInstanceID] = $fLevel;
        return $this;
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

        $oPhaseAccumulator     = empty($this->aPhaseModulationIndex)     ? null : new Packet();
        $oAmplitudeAccumulator = empty($this->aAmplitudeModulationIndex) ? null : new Packet();
        foreach ($this->aModulators as $iInstanceID => $oOperator) {
            $oPacket = $oOperator->emitPacketForIndex($iPacketIndex);
            if (isset($this->aPhaseModulationIndex[$iInstanceID])) {
                $oPhaseAccumulator->accumulate($oPacket, $this->aPhaseModulationIndex[$iInstanceID]);
            }
            if (isset($this->aAmplitudeModulationIndex[$iInstanceID])) {
                $oAmplitudeAccumulator->accumulate($oPacket, $this->aAmplitudeModulationIndex[$iInstanceID]);
            }
        }

        // Apply any phase modulation
        if ($oPhaseAccumulator) {
            $this->oOscillator->setPhaseModulation($oPhaseAccumulator);
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

        // Apply any amplitude modulation
        if ($oAmplitudeAccumulator) {
            $oOscillatorPacket->modulateWith($oAmplitudeAccumulator);
        }

        $this->oLastPacket        = $oOscillatorPacket;
        $this->iPacketIndex       = $iPacketIndex;
        return $this->oLastPacket;
    }
}