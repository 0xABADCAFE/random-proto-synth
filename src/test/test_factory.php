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

$oFactory = Signal\Generator\Factory::get();

$oDescription = (object)[
    'type' => 'Sine',
    'min'  => -0.75,
    'max'  => 0.75,
];

$oGenerator = $oFactory->create($oDescription);

print_r($oGenerator);

$oDescription = (object)[
    'type' => 'table',
    'data'  => [-1, 0, 1, 0],
];

$oGenerator = $oFactory->create($oDescription);

print_r($oGenerator);
