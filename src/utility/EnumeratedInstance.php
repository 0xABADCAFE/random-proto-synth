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
 * IEnumeratedInstance
 *
 * Interface for entities that have a unique runtime enumerated instance ID
 */
interface IEnumeratedInstance {

    /**
     * @return int
     */
    public function getInstanceID() : int;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * TEnumeratedInstance
 *
 * Common mixin for implementors of the IEnumeratedInstance interface
 */
trait TEnumeratedInstance {

    private static int $iNextInstanceID = 0;
    protected int $iInstanceID = 0;

    /**
     * Perform the ID assignment. Should be called at the end of construction whithin the incorporating class.
     */
    protected function assignInstanceID() {
        $this->iInstanceID = ++self::$iNextInstanceID;
        dprintf(
            "%s assigned instance %d\n",
            get_class($this),
            $this->iInstanceID
        );
    }

    /**
     * Get the instance ID associated with this entity
     */
    public function getInstanceID() : int {
        return $this->iInstanceID;
    }
}
