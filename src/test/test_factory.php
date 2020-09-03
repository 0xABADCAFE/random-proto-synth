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

const S_EXAMPLE = '
{
    "type":"fixed",
    "generator":{
        "type":"sine",
        "min":-0.75,
        "max":0.75
    }
}';

$oDefinition = json_decode(S_EXAMPLE);

$oOscillator = Oscillator\Control\Factory::get()->createFrom($oDefinition);

print_r($oOscillator);
