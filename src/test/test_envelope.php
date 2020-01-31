<?php

namespace ABadCafe\Synth;

//include_once 'profiling.php';

require_once '../Map.php';
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


$oSpeedKeyScaling = new Map\Note\TwelveToneEqualTemperament(
    1.0, 0.25, true
);

$oLevelKeyScaling = new Map\Note\TwelveToneEqualTemperament(
    0.75, 0.125, true
);

$oEnvelopeGenerator = new Envelope\Generator\LinearInterpolated(
    $oShape,
    $oSpeedKeyScaling,
    $oLevelKeyScaling
);

$oEnvelopeGenerator->setNoteName('A1');
$oEnvelopeGenerator->setNoteName('A2');
$oEnvelopeGenerator->setNoteName('A3');
$oEnvelopeGenerator->setNoteName('A4');
$oEnvelopeGenerator->setNoteName('A5');
$oEnvelopeGenerator->setNoteName('A6');
$oEnvelopeGenerator->setNoteName('A7');

$oEnvelopeGenerator->getNoteNumberMap(Envelope\IGenerator::S_NOTE_MAP_SPEED)->debug();

// $oOutput = new Output\Wav;
//
// $oOutput->open('output/test_envelope.wav');
//
// do {
//     $oOutput->write($oEnvelopeGenerator->emit());
// } while ($oEnvelopeGenerator->getPosition() < ($iOneSecond * 5));
// $oOutput->close();
