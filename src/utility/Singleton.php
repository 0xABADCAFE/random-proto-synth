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
 * TSingleton singleton trait
 */
trait TSingleton {

    /**
     * @var self|null $oInstance
     */
    private static ?self $oInstance = null;

    /**
     * Get the instance
     *
     * @return self
     */
    public static function get() : self {
        if (null === self::$oInstance) {
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }

    /**
     * Surrogate constructor, called from the private constructor here.
     */
    protected abstract function singletonInitialise();

    /**
     * Prevent external construction
     */
    private function __construct() {
        $this->singletonInitialise();
    }
}
