<?php

namespace ABadCafe\Synth;

require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Output.php';

$iMaxSamples = 5 * Signal\Context::get()->getProcessRate();


$oOscillator = new Oscillator\Morphing(
    new Signal\Generator\Square(),
    new Signal\Generator\SawDown(),
    new Signal\Generator\Sine(-0.5, 0.5),
    55,
    2.001,
    1
);


$oFreqEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
    	    0.5, [
    	        [0.99, 0.05],
    	        [0.1, 0.2],
    	    	   [0.01, 2],
    	    	   [1, 1],
    	    	   [0.05, 1]
    	    ]
    )
);

$oResEnvelope = new Envelope\Generator\LinearInterpolated(
    new Envelope\Shape(
    	    2, [
    	        [3.8, 2]
    	    ]
    )
);


$oOutput = new Output\Wav;

$oOutput->open('output/test_filter.wav');

$oFilter = new Signal\Filter\ResonantLowPass;

do {
   $oOutput->write(
       $oFilter->filter(
           $oOscillator->emit(),
           $oFreqEnvelope->emit(),
           $oResEnvelope->emit()
       )
   );
} while ($oOscillator->getPosition() < $iMaxSamples);

$oOutput->close();
