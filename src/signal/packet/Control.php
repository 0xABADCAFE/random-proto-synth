<?php

namespace ABadCafe\Synth\Signal\Control;
use ABadCafe\Synth\Signal\TPacket;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Control Packet
 */
class Packet {
    use TPacket;

    /**
     * Constructor
     */
    public function __construct() {
        // just for testing
        $this->oValues = self::initEmptyValues(1);
    }
}

/**
 * Control IStream
 *
 * Interface for generators of Control Packets
 */
interface IStream {

    /**
     * Get the current stream position
     *
     * @return int
     */
    public function getPosition() : int;

    /**
     * Reset the stream
     */
    public function reset() : self;

    /**
     * Get the next control packet
     */
    public function emit() : Packet;
}
