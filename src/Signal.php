<?php

/**
 * Signal
 */
namespace ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
        I_DEF_PACKET_LENGTH = 128,
        I_MAX_PACKET_LENGTH = 1024
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IChannelMode
 *
 */
interface IChannelMode {
    const
        I_CHAN_MONO   = 1,
        I_CHAN_STEREO = 2
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IStream
 *
 * Interface for components that generate a continuous stream of signal data, such as oscillators and envelope
 * generators.
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IPanLaw
 *
 * Interface for panning law implementations that convert a stream of panning values into a stream of left/right
 * amplitude.
 */
interface IPanLaw {

    /**
     * Convert a monophonic Packet of normalised panning values into a stereo Packet of amplitude values for the
     * left and right channels.
     *
     * @param  MonoPacket $oPanPacket - pan values from -1.0 (left) to 0.0 (centre) to 1.0 (right)
     * @return StereoPacket
     */
    public function map(MonoPacket $oPanPacket) : StereoPacket;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IGenerator
 *
 * Main signal generator interface. Function generators generate a basic waveform, with a time-independent duty
 * cycle of 0.0 - 1.0. Values outside this range will have their integer part ignored.
 */
interface IGenerator {

    /**
     * Returns the period of this function, i.e. the numeric interval after which it's output cycles.
     *
     * @return float
     */
    public function getPeriod() : float;

    /**
     * Calculate a Packets worth of output values for a Packets worth of input values
     *
     * @param Packet $oInput
     * @return Packet
     *
     */
    public function map(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IFilter
 *
 * Main signal filter interface. The filter cutoff is normalised such that the range 0.0 - 1.0 covers the full frequency
 * range.
 */
interface IFilter {
    const
        F_MIN_CUTOFF = 0.001,
        F_DEF_CUTOFF = 0.5,
        F_MAX_CUTOFF = 1.0
    ;

    /**
     * Reset the filter, re-initialising all internal state.
     *
     * @return self.
     */
    public function reset() : self;

    /**
     * Set the cutoff. Uses a normalied scale in which 1.0 is the highest stable setting
     * supported by the filter.
     *
     * @param  float $fCutoff - 0 < $fCutoff <= 1.0
     * @return self
     */
    public function setCutoff(float $fCutoff) : self;

    /**
     * Get the cutoff. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getCutoff() : float;

    /**
     * Filter a Packet
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function filter(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'signal/Context.php';
require_once 'signal/Packet.php';
require_once 'signal/PanLaw.php';
require_once 'signal/Generator.php';
require_once 'signal/Filter.php';

require_once 'signal/packet/TPacket.php';
require_once 'signal/packet/Audio.php';
require_once 'signal/packet/Control.php';
