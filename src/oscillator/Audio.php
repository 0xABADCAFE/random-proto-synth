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
 * Audio
 */
namespace ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ILimits
 *
 * Defines limits for oscillators data.
 */
interface ILimits {

    const
        /**
         * Frequency Range
         */
        F_MIN_FREQ = 1.0/60.0,
        F_DEF_FREQ = 440.0,      // A4 : Standard Concert Pitch
        F_MAX_FREQ = 14080.0     // A9
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOscillator
 *
 * Interface for Audio Oscillators
 */
interface IOscillator extends Oscillator\IOscillator, Signal\Audio\IStream {

    /**
     * Set a pitch shift, per sample to be applied to the basic frequency. This is applied in subsequent calls to emit().
     * The intent here is to support pitch envelope generators. However, as this is applied per sample point, we can use
     * it to do some extreme FM effects too.
     *
     * The Packet data points are interpreted as octaves to shift by, for example, -1 is an octave down, +1 is an octave up.
     *
     * @param  Signal\Control\Packet $oPitch
     * @return self
     */
    public function setPitchModulation(Signal\Control\Packet $oPitch = null) : self;

    /**
     * Set a phase moulation, per sample, to be applied to the basic waveform. This is applied in subseuent calls to emit().
     * The intent here is to allow Phase Modulation based FM synthesis.
     *
     * The packet data points are interpreted as duty cycle values to shift by. For example, -1 is one full period behind, +1 is
     * one full period ahead.
     *
     * @param Signal\Audio\Packet $oPitch
     * @return self
     */
    public function setPhaseModulation(Signal\Audio\Packet $oPhase = null) : self;

}

require_once 'audio/Base.php';
require_once 'audio/Simple.php';
require_once 'audio/Super.php';
require_once 'audio/Morphing.php';
require_once 'audio/Factory.php';

