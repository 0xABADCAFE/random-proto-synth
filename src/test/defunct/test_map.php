<?php

namespace ABadCafe\Synth;

require_once '../Map.php';

$oMap = new Map\Note\TwelveToneEqualTemperament(440, 1);

echo $oMap->mapNote('A4'), "\n";

print_r($oMap);
