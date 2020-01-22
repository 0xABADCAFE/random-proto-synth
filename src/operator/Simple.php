<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;
use ABadCafe\Synth\Envelope\IGenerator as IEnvelopeGenerator;
use ABadCafe\Synth\Utility\IEnumeratedInstance;
use ABadCafe\Synth\Utility\TEnumeratedInstance;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Simple implements IOperator, IEnumeratedInstance {

    use TEnumeratedInstance;

    protected
        /** @var IOscillator $oOscillator */
        $oOscillator,

        /** @var IEnvelopeGenerator $oEnvelope */
        $oEnvelope,

        /** @var Packet $oLastPacket */
        $oLastPacket,

        /** @var IOperator[] $aModulators - keyed by instance ID */
        $aModulators               = [],

        /** @var float[] $aPhaseModulationIndex - keyed by instance ID */
        $aPhaseModulationIndex     = [],

        /** @var float[] $aAmplidudeModulationIndex - keyed by instance ID */
        $aAmplitudeModulationIndex = [],

        /** @var int $iPacketIndex */
        $iPacketIndex              = 0
    ;

    public function __construct(
        IOscillator        $oOscillator,
        IEnvelopeGenerator $oEnvelope
    ) {
        $this->oOscillator = $oOscillator;
        $this->oEnvelope   = $oEnvelope;
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
        $this->oEnvelope->reset();
        $this->iPacketIndex = 0;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachAmplitudeModulator(
        IOperator $oOperator,
        float $fIndex
    ) : IOperator {
        $this->aModulators[$oOperator->iInstanceID] = $oOperator;
        $this->aAmplitudeModulationIndex[$oOperator->iInstanceID] = $fIndex;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachPhaseModulator(
        IOperator $oOperator,
        float $fIndex
    ) : IOperator {
        $this->aModulators[$oOperator->iInstanceID] = $oOperator;
        $this->aPhaseModulationIndex[$oOperator->iInstanceID] = $fIndex;
        return $this;
    }

    public function dump() {

    }

    /**
     * @inheritdoc
     */
    public function emit() : Packet {
        return $this->emitPacketForIndex($this->iPacketIndex + 1);
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
        $oOscillatorPacket  = $oPhaseAccumulator     ? $this->oOscillator->emitPhaseModulated($oPhaseAccumulator) : $this->oOscillator->emit();
        $oEnvelopePacket    = $oAmplitudeAccumulator ? $this->oEnvelope->emit()->modulateWith($oPhaseAccumulator) : $this->oEnvelope->emit();
        $this->oLastPacket  = $oOscillatorPacket->modulateWith($this->oEnvelope->emit());
        $this->iPacketIndex = $iPacketIndex;
        return $this->oLastPacket;
    }
}
