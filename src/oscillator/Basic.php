<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Generator\IGenerator;
use ABadCafe\Synth\Signal\Packet;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for Oscillator implementations
 */
abstract class Base {
    protected
        /** @var IGenerator $oGenerator */
        $oGenerator,

        /** @var Packet $oGeneratorInput */
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Basic
 */
class Basic extends Base implements IOutputOnly {

    /**
     * @inheritdoc
     */
    public function emit() : Packet {
        $oValues = $this->oGeneratorInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * AmplitudeModulated
 */
class AmplitudeModulated extends Base implements ISingleInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oAmplitude) : Packet {
        $oValues = $this->oGeneratorInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map($this->oGeneratorInput)->modulateWith($oAmplitude);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PhaseModulated
 */
class PhaseModulated extends Base implements ISingleInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oPhase) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PhaseAndAmplitudeModulated
 */
class PhaseAndAmplitudeModulated extends Base implements IDualInput {

    /**
     * @inheritdoc
     */
    public function emit(Packet $oPhase, Packet $oAmplitude) : Packet {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $oValues    = $this->oGeneratorInput->getValues();
        $oModulator = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseSize * $oModulator[$i]);
        }
        return $this->oGenerator->map($this->oGeneratorInput)->modulateWith($oAmplitude);
    }
}
