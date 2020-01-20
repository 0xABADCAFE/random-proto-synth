<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Generator\IGenerator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



interface IOperator {

    public function attachPhaseModulator(self $oOperator, float $fIndex) : self;

}
