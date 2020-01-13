<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Generator\IGenerator;
use ABadCafe\Synth\Signal\Packet;

/**
 * ILimits
 *
 * Defines limits for oscillators data.
 */
interface ILimits {
    const
        // Sample rate
        MIN_RATE = 11025,
        MAX_RATE = 192000,
        DEF_RATE = 44100,

        // Frequency
        MIN_FREQ = 55.0,
        MAX_FREQ = 3520.0,
        DEF_FREQ = 440.0
    ;
}

interface IOutputOnly {
    public function emit(int $iLength);
}

/**
 * Base class for Oscillator implementations
 */
abstract class Base {
    protected
        /** @var IGenerator $oGenerator */
        $oGenerator,

        /** @var int $iSampleRate */
        $iSampleRate,

        /** @var int $iSamplePosition */
        $iSamplePosition = 0,

        /** @var float $fFrequency */
        $fFrequency,

        /** @var float $fScaleVal */
        $fScaleVal
    ;

    /**
     * Constructor. Set default sample rate and frequency here. Sample rate is immutable once set.
     *
     * @param int   $iSampleRate
     * @param float $fFrequency
     */
    public function __construct(
        IGenerator $oGenerator,
        int        $iSampleRate = ILimits::DEF_RATE,
        float      $fFrequency  = ILimits::DEF_FREQ
    ) {
        $this->oGenerator  = $oGenerator;
        $this->iSampleRate = $this->clamp($iSampleRate, ILimits::MIN_RATE, ILimits::MAX_RATE);
        $this->setFrequency($fFrequency);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return sprintf(
            "%s [%s freq:%.3fHz rate:%dHz, pos:%d]",
            static::class,
            get_class($this->oGenerator),
            $this->fFrequency,
            $this->iSampleRate,
            $this->iSamplePosition
        );
    }

    /**
     * Get the oscillator sample rate, in Hz.
     *
     * @return int
     */
    public function getSampleRate() : int {
        return $this->iSampleRate;
    }

    /**
     * Get the oscillator sample position, which is the total number of samples generated since
     * instantiation or the last call to reset().
     *
     * @return int
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * Get the oscillator signal frequency
     *
     * @return int
     */
    public function getFrequency() : float {
        return $this->fFrequency;
    }

    /**
     * Reset the duty cycle
     *
     * @param  float $fFrequency
     * @return self
     */
    public function reset() : self {
        $this->iSamplePosition = 0;
        return $this;
    }

    /**
     * Set the oscillator signal frequency
     *
     * @param  float $fFrequency
     * @return self
     */
    public function setFrequency(float $fFrequency) : self {
        $this->fFrequency = $this->clamp($fFrequency, ILimits::MIN_FREQ, ILimits::MAX_FREQ);
        $this->fScaleVal  = $this->oGenerator->getPeriod() * $this->fFrequency / (float)$this->iSampleRate;
        return $this;
    }

    /**
     * Clamp some numeric vale between a minimum and maximum
     *
     * @param  float|int $mValue
     * @param  float|int $mMin
     * @param  float|int $mMax
     * @return float|int
     */
    protected function clamp($mValue, $mMin, $mMax) {
        return max(min($mValue, $mMax), $mMin);
    }
}

require_once 'oscillator/Basic.php';
