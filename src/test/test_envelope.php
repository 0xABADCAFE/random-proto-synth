<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Envelope.php';
require_once '../Output.php';

error_reporting(-1);

$iOneSecond = Signal\Context::get()->getProcessRate();

$oShape = new Envelope\Shape;
$oShape
    ->initial(0)            // Initial Level
    ->append(0.75, 0.1)     // Full Level after 0.5 seconds
    ->append(1.0,  0.2)
    ->append(0.5, 0.5)    // 75%  Level after another 0.5 seconds
    ->append(0, 4);

$oEnvelopeGenerator = new Envelope\Generator\LinearInterpolated($oShape);


$oOutput = new Output\Wav;

$oOutput->open('output/test_envelope.wav');

do {
    $oOutput->write($oEnvelopeGenerator->emit());
} while ($oEnvelopeGenerator->getPosition() < ($iOneSecond * 5));
$oOutput->close();
