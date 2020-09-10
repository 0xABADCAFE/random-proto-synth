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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$aOperatorList = [
    'mod1' => new Operator\UnmodulatedOscillator(
        // Wave function
        new Oscillator\Audio\Simple(
            new Signal\Generator\Sine()
        ),

        // Frequency ratio
        1987.0/440.0,

        // Detune
        0.0,

        // Amplitude Control
        new Envelope\Generator\LinearInterpolated(
            new Envelope\Shape(
                1,  // Initial Level
                [
                    [ 1/2, 0.5],
                    [ 1/4, 0.5],
                    [ 1/8, 0.5],
                    [ 1/16, 0.5],
                    [ 1/32, 0.5],
                    [ 0, 5]
                ]
            )
        )
    ),

    'mod2' => new Operator\UnmodulatedOscillator(
        // Wave function
        new Oscillator\Audio\Simple(
            new Signal\Generator\Sine(),
            3
        )
    ),

    'carrier' => new Operator\ModulatableOscillator(
        // Wave function
        new Oscillator\Audio\Simple(
            new Signal\Generator\Sine()
        ),

        // Frequency ratio
        1.0,

        // Detune
        0.0,

        // Amplitude Control
        new Envelope\Generator\LinearInterpolated(
            new Envelope\Shape(
                1, // Initial Level
                [  // Level / Time Pairs
                    [ 1/2, 1],
                    [ 1/4, 1],
                    [ 1/8, 1],
                    [ 1/16, 1],
                    [ 0, 1]
                ]
            )
        )
    ),

    'out' => new Operator\PCMOutput(new Output\Play)
];

$aModulationMatrix = [
    'out' => [
        'carrier' => ['audio' => 1.0]
    ],
    'carrier' => [
        'mod1' => ['phase' => 0.5],
        'mod2' => ['phase' => 0.2]
    ],
];

$oPatch = new Patch\Module($aOperatorList, $aModulationMatrix);
