<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;
use ABadCafe\Synth\Envelope\IGenerator as IEnvelopeGenerator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ModulatedOscillator
 *
 * Simple modulatable Operator implementation. Supports E_AMPLITUDE and E_PHASE modulation inputs.
 */
class ModulatedOscillator extends Base implements IAmplitudeModulated, IPhaseModulated {

    protected
        /** @var IOscillator $oOscillator */
        $oOscillator,

        /** @var IEnvelopeGenerator $oAmplitudeEnvelope */
        $oAmplitudeEnvelope,

        /** @var IEnvelopeGenerator $oPitchEnvelope */
        $oPitchEnvelope,

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
     * @param IOscillator             $oOscillator        : Waveform generator to use    (required)
     * @param IEnvelopeGenerator      $oAmplitudeEnvelope : Amplitude Envelope Generator (required)
     * @param IEnvelopeGenerator|null $oPitchEnvelope     : Pitch Envelope Generator     (optional)
     */
    public function __construct(
        IOscillator        $oOscillator,
        IEnvelopeGenerator $oAmplitudeEnvelope,
        IEnvelopeGenerator $oPitchEnvelope = null
    ) {
        $this->oOscillator        = $oOscillator;
        $this->oAmplitudeEnvelope = $oAmplitudeEnvelope;
        $this->oPitchEnvelope     = $oPitchEnvelope;
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
        $this->oAmplitudeEnvelope->reset();
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

        if ($oPhaseAccumulator) {
            $this->oOscillator->setPhaseModulation($oPhaseAccumulator);
        }

        if ($this->oPitchEnvelope) {
            $this->oOscillator->setPitchModulation($this->oPitchEnvelope->emit());
        }

        $oOscillatorPacket        = $this->oOscillator->emit();
        $oAmplitudeEnvelopePacket = $oAmplitudeAccumulator ?
            $this->oAmplitudeEnvelope->emit()->modulateWith($oAmplitudeAccumulator) :
            $this->oAmplitudeEnvelope->emit()
        ;
        $this->oLastPacket        = $oOscillatorPacket->modulateWith($this->oAmplitudeEnvelope->emit());
        $this->iPacketIndex       = $iPacketIndex;
        return $this->oLastPacket;
    }
}
