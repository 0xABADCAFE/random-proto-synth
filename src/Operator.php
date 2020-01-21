<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;
use ABadCafe\Synth\Envelope\Generator\IGenerator as IEnvelopeGenerator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for Operators. Operators combine Oscillators and Envelopes along with the notion of input modulators.
 */
interface IOperator {

    /**
     * Attach an Operator as an amplitude modulator. The overall strength of the modulation is controlled by the index
     *
     * @param  IOperator $oOperator
     * @param  float     $fIndex
     * @return IOperator
     */
    public function attachAmplitudeModulator(self $oOperator, float $fIndex) : self;

    /**
     * Attach an Operator as a phase modulator. The overall strength of the modulation is controlled by the index
     *
     * @param  IOperator $oOperator
     * @param  float     $fIndex
     * @return IOperator
     */
    public function attachPhaseModulator(self $oOperator, float $fIndex) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Standard implements IOperator, IStream {

    protected
        $iInstance,
        $oOscillator,
        $oEnvelope,
        $aModulators               = [],
        $aPhaseModulationIndex     = [],
        $aAmplitudeModulationIndex = []
    ;

    protected static $iNextInstance = 0;

    public function __construct(
        IOscillator        $oOscillator,
        IEnvelopeGenerator $oEnvelope
    ) {
        $this->oOscillator = $oOscillator;
        $this->oEnvelope   = $oEnvelope;
        $this->iInstance   = self::$iNextInstance++;
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
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachAmplitudeModulator(
        IOperator $oOperator,
        float $fIndex
    ) : IOperator {
        $this->aModulators[$oOperator->iInstance] = $oOperator;
        $this->aAmplitudeModulationIndex[$oOperator->iInstance] = $fIndex;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachPhaseModulator(
        IOperator $oOperator,
        float $fIndex
    ) : IOperator {
        $this->aModulators[$oOperator->iInstance] = $oOperator;
        $this->aPhaseModulationIndex[$oOperator->iInstance] = $fIndex;    
        return $this;
    }


    public function emit() : Packet {
        $oPhaseAccumulator     = new Packet();
        $oAmplitudeAccumulator = new Packet();
        foreach ($this->aModulators as $iInstance => $oOperator) {
            $oPacket = $oOperator->emit();
        }
    }
}
