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
 * Signal
 */
namespace ABadCafe\Synth\Signal;
use \SPLFixedArray;

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////

interface IPacket {

   /**
    * Obtain the raw floating point values in the Packet
    *
    * @return SPLFixedArray
    */
   public function getValues() : SPLFixedArray;

   /**
    * Obtain an integer quantised set of values in the Packet
    *
    * @param  int $iScaleValue
    * @param  int $iMinValue
    * @param  int $iMaxValue
    * @return SPLFixedArray
    */
   public function quantise(int $iScaleValue, int $iMinValue, int $iMaxValue) : SPLFixedArray;

   /**
    * Set all values in the Packet to a fixed value.
    *
    * @param  float $fValue
    * @return self
    */
   public function fillWith(float $fValue) : self;

   /**
    * Add a fixed value to every value in the Packet
    *
    * @param  float $fValue
    * @return self
    */
   public function biasBy(float $fValue) : self;

   /**
    * Add a fixed value to every value in the Packet
    *
    * @param  float $fValue
    * @return self
    */
   public function scaleBy(float $fValue) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
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
    public function emit() : IPacket;
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
    public function map(IPacket $oInput) : IPacket;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'signal/Context.php';
require_once 'signal/Packet.php';
require_once 'signal/Control.php';
require_once 'signal/Audio.php';
require_once 'signal/Generator.php';
