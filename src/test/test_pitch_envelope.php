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
    	    0.0,              // Start at 0
    	    [
    	    	   [ 0.0, 0.125], // Level for 0.125 seconds
    	    	   [ 1.0, 0.125], // Rise +1 octaves in 0.125 seconds,
    	    	   [ 1.0, 0.125], // Level for 0.125 seconds
    	    	   [ 0.0, 0.125], // Fall to 0.0 in 0,125 seconds
    	    	   [ 0.0, 0.125], // Level for 0.125 seconds
    	    	   [-1.0, 0.125], // Fall -1 octaves in 0.125 seconds
    	    	   [-1.0, 0.125], // Level for 0.125 seconds
    	    	   [ 0.0, 0.125]
        ]
    )
);

$oOutputWave = new Output\Wav;
$oOutputEnv  = new Output\Wav;
$oOutputWave->open('output/test_penv_wave.wav');
$oOutputEnv->open('output/test_penv_env.wav');

do {
   $oEnvPacket = $oEnvelopeGenerator->emit();
   $oOutputEnv->write($oEnvPacket);
   $oOscillator->setPitchModulation($oEnvPacket);
   $oOutputWave->write($oOscillator->emit());
} while ($oOscillator->getPosition() < $iOneSecond);

$oOutputEnv->close();
$oOutputWave->close();
