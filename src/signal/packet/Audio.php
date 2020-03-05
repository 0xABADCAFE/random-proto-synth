<?php

namespace ABadCafe\Synth\Signal\Audio;
use ABadCafe\Synth\Signal\IChannelMode;
use ABadCafe\Synth\Signal\TPacket;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Audio Packet (Mono)
 */
class MonoPacket {
    use TPacket;

    public function toStereo() : StereoPacket {

    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(IChannelMode::I_CHAN_MONO);
    }
}

/**
 * Audio Packet (Stereo)
 */
class StereoPacket {
    use TPacket;

    public function toMono() : MonoPacket {

    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(IChannelMode::I_CHAN_STEREO);
    }
}

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
