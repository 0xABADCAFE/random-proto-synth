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

namespace ABadCafe\Synth\Envelope\Generator;
use ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Utility;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KeyedSet
 *
 * Type safe associative collection for envelopes
 */
class KeyedSet implements \Countable {
    use Utility\TSet;

    /**
     * @param  string              $sKey
     * @param  Envelope\IGenerator $oItem
     * @return self
     */
    public function add(string $sKey, Envelope\IGenerator $oItem) : self {
        $this->aItems[$sKey] = $oItem;
        return $this;
    }
}
