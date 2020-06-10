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
 * Control
 */
namespace ABadCafe\Synth\Signal\Control;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Concrete control signal Packet
 *
 */
class Packet implements Signal\IPacket {

   use Signal\TPacketImplementation;
}

/**
 * Interface for control stream generators.
 *
 */
interface IStream extends Signal\IStream {

    /**
     * Reset the stream
     *
     * @inheritDoc
     */
    public function reset() : self; // Covariant return

    /**
     * Emit a Packet
     *
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Packet; // Covariant return
}
