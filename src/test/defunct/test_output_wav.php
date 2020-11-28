<?php

namespace ABadCafe\Synth;

require_once '../Synth.php';

$oOutput = new Output\Wav;

$oOutput->open('test.wav');
$oOutput->write(new Signal\Packet());
$oOutput->close();
