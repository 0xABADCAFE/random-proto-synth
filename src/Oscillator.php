<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Generator\IGenerator;


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

/**
 * Interface for simple output only oscillators
 */
interface IOutputOnly {
    public function emit() : Packet;
}

/**
 * Interface for simple output only oscillators
 */
interface ISingleInput {
    public function emit(Packet $oInput) : Packet;
}

/**
 * Base class for Oscillator implementations
 */
abstract class Base {
    protected
        /** @var IGenerator $oGenerator */
        $oGenerator,

        /** @var Paclet */
        $oGeneratorInput,

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
     * @param IGenerator $oGenerator
     * @param float      $fFrequency
     */
    public function __construct(
        IGenerator $oGenerator,
        float      $fFrequency  = ILimits::F_DEF_FREQ
    ) {
        $this->oGenerator      = $oGenerator;
        $this->oGeneratorInput = new Packet();
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
            Context::get()->getProcessRate(),
            $this->iSamplePosition
        );
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
        $this->fFrequency = $this->clamp($fFrequency, ILimits::F_MIN_FREQ, ILimits::F_MAX_FREQ);
        $this->fScaleVal  = $this->oGenerator->getPeriod() * $this->fFrequency / (float)Context::get()->getProcessRate();
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
