<?php

namespace ABadCafe\Synth\Signal\Audio;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Audio Packet (Mono)
 */
class MonoPacket extends Signal\BasePacket {
    use Signal\TPacketOperations;

    public function toStereo() : StereoPacket {

    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(Signal\IChannelMode::I_CHAN_MONO);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Audio Packet (Stereo)
 */
class StereoPacket extends Signal\BasePacket {
    use Signal\TPacketOperations;

    public function toMono() : MonoPacket {

    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(Signal\IChannelMode::I_CHAN_STEREO);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Control IStream
 *
 * Interface for generators of Control Packets
 */
interface IMonoStream {

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
    public function emit() : MonoPacket;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Control IStream
 *
 * Interface for generators of Control Packets
 */
interface IStereoStream {

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
    public function emit() : StereoPacket;
}
