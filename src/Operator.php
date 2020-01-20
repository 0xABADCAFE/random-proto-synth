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

    public function attachPhaseModulator(self $oOperator, float $fIndex) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Standard implements IOperator, IStream {

    protected
        $oOscillator,
        $oEnvelope
    ;

    public function __construct(
        IOscillator        $oOscillator,
        IEnvelopeGenerator $oEnvelope
    ) {
        $this->oOscillator = $oOscillator;
        $this->oEnvelope   = $oEnvelope;
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
}
