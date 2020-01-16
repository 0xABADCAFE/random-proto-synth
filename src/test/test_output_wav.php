<?php

namespace ABadCafe\Synth;
require_once '../Signal.php';
require_once '../Oscillator.php';
require_once '../Output.php';

$oOutput = new Output\Wav;

$oOutput->open('test.wav');
$oOutput->write(new Signal\Packet());
$oOutput->close();
