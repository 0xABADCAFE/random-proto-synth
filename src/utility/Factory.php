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

namespace ABadCafe\Synth\Utility;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IFactory
 */
interface IFactory {

    /**
     * This is the main method. Implementors shall use covariance to constrain their return types. The input is a basic
     * structure that comes from deseralizing an input textual definition.
     *
     * @param  object $oDefinition
     * @return object
     *
     */
    public function createFrom(object $oDefinition) : object;
}
