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
 * Interface for simple output only oscillators, for example basic wave generators or LFO implementations.
 */
interface IOutputOnly {

    /**
     * Emit the next signal packet.
     *
     * @return Packet
     */
    public function emit() : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for single input oscillators. The input modulates some property of the waveform, for example amplitude or phase.
 */
interface ISingleInput {

    /**
     * Emit the next signal packet.
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function emit(Packet $oInput) : Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for dual input oscillators. Generally one input will modulate phase and the other amplitude.
 */
interface IDualInput {

    /**
     * Emit the next signal packet.
     *
     * @param  Packet $oInput1
     * @param  Packet $oInput2
     * @return Packet
     */
    public function emit(Packet $oInput1, Packet $oInput2) : Packet;
}

require_once 'oscillator/Basic.php';
