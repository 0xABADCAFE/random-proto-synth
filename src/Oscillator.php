<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Generator\IGenerator;

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
        F_MAX_FREQ = 3520.0,
        F_DEF_FREQ = 440.0
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for Oscillators
 */
interface IOscillator {
    /**
     * Get the oscillator frequency in Hz
     *
     * @return float
     */
    public function getFrequency() : float;

    /**
     * Set a new frequency for the oscillator, in Hz
     *
     * @param  float $fFrequecny
     * @return self
     */
    public function setFrequency(float $fFrequency) : self;

    /**
     * Generate a Packet of signal, applying the input Packet of phase modulation to the
     * internal generator.
     *
     * @param  Packet $iPhase
     * @return Packet
     */
    public function emitPhaseModulated(Packet $iPhase) : Packet;
}

require_once 'oscillator/Base.php';
require_once 'oscillator/Simple.php';
require_once 'oscillator/Morphing.php';

