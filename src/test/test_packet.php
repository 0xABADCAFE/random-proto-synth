<?php

namespace ABadCafe\Synth;

//require 'profiling.php';

require_once '../Signal.php';

const I_ITERATIONS = 100000;

$oPacket1 = new Signal\Packet();
$oPacket2 = new Signal\Packet();

echo "Testing Packet::sumWith() over ", I_ITERATIONS, " iterations...";
$fTime = microtime(true);
for ($i = 0; $i < I_ITERATIONS; $i++) {
    $oPacket1->sumWith($oPacket2);
}
$fTime = microtime(true) - $fTime;
echo " Took ", $fTime, " seconds\n";

echo "Testing Packet::modulateWith() over ", I_ITERATIONS, " iterations...";
$fTime = microtime(true);
for ($i = 0; $i < I_ITERATIONS; $i++) {
    $oPacket1->modulateWith($oPacket2);
}
$fTime = microtime(true) - $fTime;
echo " Took ", $fTime, " seconds\n";

echo "Testing Packet::accumulate() over ", I_ITERATIONS, " iterations...";
$fTime = microtime(true);
for ($i = 0; $i < I_ITERATIONS; $i++) {
    $oPacket1->accumulate($oPacket2, 0.5);
}
$fTime = microtime(true) - $fTime;
echo " Took ", $fTime, " seconds\n";
