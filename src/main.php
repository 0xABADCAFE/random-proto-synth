<?php

require_once 'FunctionGenerator.php';

$oGenerator = new Synth\FunctionGenerator\DC();
echo "Testing ", $oGenerator, "\n";
$oGenerator->setLevel(0.25);
print_r($oGenerator->generate(32));

$oGenerator = new Synth\FunctionGenerator\Noise();
echo "Testing ", $oGenerator, "\n";
print_r($oGenerator->generate(32));

$oGenerator = new Synth\FunctionGenerator\Sine();
echo "Testing ", $oGenerator, "\n";
print_r($oGenerator->generate(32));

