<?php

namespace ABadCafe\Synth;

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Output.php';

$iOneSecond = Signal\Context::get()->getProcessRate();

$oOscillator = new Oscillator\Simple(
    new Signal\Generator\Sine(),
    220
);

$oEnvelopeGenerator = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
    	    1, [
    	    	   [-1.0, 0.5],
    	    	   [-1.0, 0.5]
    	    ]
    )
);

$oOutput = new Output\Wav;

$oOutput->open('output/test_penv.wav');

do {
   $oOscillator->setPitchModulation($oEnvelopeGenerator->emit());
   $oOutput->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);

$oOutput->close();
