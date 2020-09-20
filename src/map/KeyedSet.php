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

namespace ABadCafe\Synth\Map;
use ABadCafe\Synth\Utility;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * KeyedSet
 *
 * Type safe associative collection for MIDI Byte Maps
 */
class KeyedSet implements \Countable {
    use Utility\TSet;

    /**
     * @param  string   $sKey
     * @param  MIDIByte $oItem
     * @return self
     */
    public function add(string $sKey, MIDIByte $oItem) : self {
        $this->aItems[$sKey] = $oItem;
        return $this;
    }
}

