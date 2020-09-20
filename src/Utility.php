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

/**
 * Utility
 */
namespace ABadCafe\Synth\Utility;

/**
 * Basic variadic debug print (to STDERR)
 *
 * @param string $sTemplate
 */
function dprintf(string $sTemplate, ...$aVarArgs) {
    fprintf(STDERR, $sTemplate, ...$aVarArgs);
}

/**
 * Clamp some numeric vale between a minimum and maximum
 *
 * @param  float|int $mValue
 * @param  float|int $mMin
 * @param  float|int $mMax
 * @return float|int
 */
function clamp($mValue, $mMin, $mMax) {
    return max(min($mValue, $mMax), $mMin);
}

require_once 'utility/Enum.php';
require_once 'utility/EnumeratedInstance.php';
require_once 'utility/Set.php';
require_once 'utility/Singleton.php';
require_once 'utility/Factory.php';
