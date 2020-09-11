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

require_once '../Utility.php';

/**
 *
 */
class TestSingleton {

    use Utility\TSingleton;

    protected function singletonInitialise() {
        echo "Initialised\n";
    }
}

$oInstance1 = TestSingleton::get();
$oInstance2 = TestSingleton::get();

var_dump($oInstance1 === $oInstance2);
