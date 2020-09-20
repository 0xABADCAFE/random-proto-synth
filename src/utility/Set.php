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

/**
 * TSet
 *
 * Mixin to allow the definition of collections of a given type.
 */
trait TSet {

    /**
     * @var mixed[] $aItems
     */
    private array $aItems = [];

    /**
     * @return mixed[]
     */
    public function getAll() : array {
        return $this->aItems;
    }

    /**
     * @param  string $sKey
     * @return bool
     */
    public function has(string $sKey) : bool {
        return isset($this->aItems[$sKey]);
    }

    /**
     * @param  string $sKey
     * @return mixed|null
     */
    public function get(string $sKey) {
        return $this->aItems[$sKey] ?? null;
    }

    /**
     * @return int
     */
    public function count() : int {
        return count($this->aItems);
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool {
        return empty($this->aItems);
    }
}
