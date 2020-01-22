<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for Operators. Operators combine Oscillators and Envelopes along with the notion of input modulators.
 */
interface IOperator extends IStream {

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

/**
 * Interface for output Operators. These handle the terminating carrier operators and sum their outputs.
 */
interface IOutputOperator extends IStream {
    public function attachOperator(IOperator $oOperator, float $fLevel) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'Utility.php';
require_once 'operator/Simple.php';
require_once 'operator/Output.php';

