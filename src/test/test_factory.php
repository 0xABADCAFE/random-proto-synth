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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Map
const S_EXAMPLE_4 = '
{
    "type":"12tone_scaled",
    "center":0.75,
    "scale":1.25,
    "invert":false
}';

$oDefinition = json_decode(S_EXAMPLE_4);
$oMap = Map\Note\Factory::get()->createFrom($oDefinition);
print_r($oMap);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Envelope
const S_EXAMPLE_5 = '
{
    "type":"custom",
    "shape":{
        "initial":0.0,
        "points":[
            [1.0, 0.1],
            [0.75, 1.0],
            [0.25, 5.0],
            [0.0, 10]
        ]
    },
    "keyscale_speed":{
        "type":"12tone_scaled",
        "center":0.75,
        "scale":1.25,
        "invert":false
    },
    "keyscale_level":{
        "type":"12tone_scaled",
        "center":1.0,
        "scale":1.0,
        "invert":true
    }
}';

$oDefinition = json_decode(S_EXAMPLE_5);
$oEnvelope = Envelope\Factory::get()->createFrom($oDefinition);
print_r($oEnvelope);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// Filter 1
const S_EXAMPLE_6 = '
{
    "type":"lowpass",
    "cutoff":2.0,
    "resonance":3.5
}';

$oDefinition = json_decode(S_EXAMPLE_6);
$oFilter = Signal\Audio\Filter\Factory::get()->createFrom($oDefinition);
print_r($oFilter);

// Filter 2
const S_EXAMPLE_7 = '
{
    "type":"karlsen",
    "mode":"notch",
    "cutoff":1.0,
    "resonance":3.5
}';

$oDefinition = json_decode(S_EXAMPLE_7);
$oFilter = Signal\Audio\Filter\Factory::get()->createFrom($oDefinition);
print_r($oFilter);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// Filter 1
const S_EXAMPLE_8 = '
{
    "type":"play"
}';

$oDefinition = json_decode(S_EXAMPLE_8);
$oOutput = Output\Factory::get()->createFrom($oDefinition);
print_r($oOutput);
