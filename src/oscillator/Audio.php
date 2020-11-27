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
     * Sets a Control stream input for pitch modulation. This is intended for things like LFO, Pitch Envelopes etc.
     * The values in the control stream are interpreted as multiples of the base frequency of the oscillator. Use
     * Signal\Control\Stream\SemitonesToMultiplier to convert output of any signal source defined in semitones for
     * use here.
     *
     * Passing null clears any existing modulator.
     *
     * @param  Signal\Control\IStream|null $oPitchModulator
     * @return self
     */
    public function setPitchModulator(?Signal\Control\IStream $oPitchModulator) : self;

    /**
     * Sets an audio stream input for phase modulation. This is intended for FM synthesis purposes where output(s)
     * from some other audio oscillator(s) modulate the phase of the oscillator but not it's fundamental frequency.
     *
     * @param  Signal\Audio\IStream|null $oPhaseModulator
     * @return self
     */
    public function setPhaseModulator(?Signal\Audio\IStream $oPhaseModulator) : self;

    /**
     * Gets the current instantaneous frequency of the oscillator, which may be very different than what is
     * returned by getFrequency() due to the impact of any pitch modulation.
     */
    public function getCurrentFrequency() : float;
}

