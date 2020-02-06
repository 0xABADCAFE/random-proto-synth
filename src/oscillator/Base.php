<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\IGenerator;
use ABadCafe\Synth\Signal\Packet;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for Oscillator implementations
 */
abstract class Base implements IOscillator {

    const F_INV_TWELVE = 1.0/12.0;

    protected
        /** @var IGenerator $oGenerator */
        $oGenerator,

        /** @var Packet $oGeneratorInput */
        $oGeneratorInput,

        /** @var int $iSamplePosition */
        $iSamplePosition = 0,

        /** @var float $fFrequency - The base frequency */
        $fFrequency,

        /** @var float $fCurrentFequency - The present instantaneous frequency considering any pitch control */
        $fCurrentFrequency,

        /** @var float $fPhaseCorrection - The accumulated phase difference as a result of pitch control */
        $fPhaseCorrection,

        /** @var float $fScaleVal */
        $fScaleVal,

        /** @var SPLFixedArray $oPhaseShift */
        $oPhaseShift,

        /** @var SPLFixedArray $oPitchShift */
        $oPitchShift
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
     * @return self
     */
    public function reset() : IStream {
        $this->iSamplePosition  = 0;
        $this->oPhaseShift      = null;
        $this->oPitchShift      = null;
        $this->fCurrentFreqency = $this->fFrequency;
        $this->fPhaseAdjustment = 0;
        return $this;
    }

    /**
     * Set the oscillator signal frequency
     *
     * @param  float $fFrequency
     * @return self
     */
    public function setFrequency(float $fFrequency) : IOscillator {
        $this->fFrequency = $this->clamp($fFrequency, ILimits::F_MIN_FREQ, ILimits::F_MAX_FREQ);
        $this->fScaleVal  = $this->oGenerator->getPeriod() * $this->fFrequency * Context::get()->getSamplePeriod();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPitchModulation(Packet $oPitch = null) : IOscillator {
        if ($oPitch) {
            // Convert the linear semitone based shifts into absolute multiples of the base frequency
            $this->oPitchShift = clone $oPitch->getValues();
            foreach ($this->oPitchShift as $i => $fValue) {
                $this->oPitchShift[$i] = $this->fFrequency * (2 ** ($fValue * self::F_INV_TWELVE));
            }
        } else {
            $this->oPitchShift = null;
        }
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function setPhaseModulation(Packet $oPhase = null) : IOscillator {
        if ($oPhase) {
            $fPhaseSize = $this->oGenerator->getPeriod();
            $this->oPhaseShift = clone $oPhase->getValues();
            foreach ($this->oPhaseShift as $i => $fValue) {
                $this->oPhaseShift[$i] = $fValue * $fPhaseSize;
            }
        } else {
            $this->oPhaseShift = null;
        }
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
