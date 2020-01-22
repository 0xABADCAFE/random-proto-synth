<?php

namespace ABadCafe\Synth\Signal;

/**
 * ILimits
 *
 * Defines limits for signal data.
 */
interface ILimits {
    const
        /**
         * Level Limits
         */
        F_MIN_LEVEL_NO_CLIP = -1.0,
        F_MAX_LEVEL_NO_CLIP = 1.0,
        F_P2P_LEVEL_NO_CLIP = self::F_MAX_LEVEL_NO_CLIP - self::F_MIN_LEVEL_NO_CLIP,

        /**
         * Process Rate Limits
         */
        I_MIN_PROCESS_RATE  = 11025,
        I_MAX_PROCESS_RATE  = 192000,
        I_DEF_PROCESS_RATE  = 44100,

        /**
         * Packet Length Limits
         */
        I_MIN_PACKET_LENGTH = 8,
        I_DEF_PACKET_LENGTH = 64,
        I_MAX_PACKET_LENGTH = 256
    ;
}

/**
 * IStream
 *
 * Interface for components that generate a continuous stream of signal data,
 * such as oscillators and envelope generators.
 *
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
     * Emit a Packet
     */
    public function emit() : Packet;
}

require_once 'signal/Context.php';
require_once 'signal/Packet.php';
require_once 'signal/Generator.php';
