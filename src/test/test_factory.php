<?php

/**
 *      _/_/_/  _/_/_/  _/      _/  _/_/_/      _/_/_/
 *   _/          _/    _/_/  _/_/  _/    _/  _/
 *  _/  _/_/    _/    _/  _/  _/  _/_/_/      _/_/
 * _/    _/    _/    _/      _/  _/              _/
 *  _/_/_/  _/_/_/  _/      _/  _/        _/_/_/
 *
 *  - Grossly Impractical Modular PHP Synthesiser -
 *
 */

declare(strict_types = 1);

namespace ABadCafe\Synth;

require_once '../Synth.php';

const S_EXAMPLE_1 = '
{
    "type":"lfo",
    "rate":15.0,
    "depth":0.66,
    "generator":{
        "type":"sine",
        "min":-0.75,
        "max":0.75
    }
}';

$oDefinition = json_decode(S_EXAMPLE_1);
$oOscillator = Oscillator\Control\Factory::get()->createFrom($oDefinition);
echo $oOscillator, "\n";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_EXAMPLE_2 = '
{
    "type":"simple",
    "freq":440.0,
    "phase":0.0,
    "generator":{
        "type":"triangle",
        "min":-0.75,
        "max":0.75
    }
}';

$oDefinition = json_decode(S_EXAMPLE_2);
$oOscillator = Oscillator\Audio\Factory::get()->createFrom($oDefinition);
echo $oOscillator, "\n";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_EXAMPLE_3 = '
{
    "type":"super",
    "freq":440.0,
    "stack":[
        [1.0, 0.5, 0.0],
        [2.0, 0.25, 0.0],
        [3.0, 0.25, 0.0]
    ],
    "generator":{
        "type":"sawdown",
        "min":-0.75,
        "max":0.75
    }
}';

$oDefinition = json_decode(S_EXAMPLE_3);
$oOscillator = Oscillator\Audio\Factory::get()->createFrom($oDefinition);
echo $oOscillator, "\n";
