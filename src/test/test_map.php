<?php

namespace ABadCafe\Synth;

require_once '../Utility.php';

//print_r(Map\Note\TwelveToneMap::A_NOTE_NAMES);

$oMap = new Map\Note\TwelveToneEqualTemperament;

echo $oMap->mapNote('A4'), "\n";
