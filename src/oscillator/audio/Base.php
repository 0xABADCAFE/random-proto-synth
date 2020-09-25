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

namespace ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Signal;
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base class for Oscillator implementations
 */
abstract class Base implements IOscillator {

    const F_INV_TWELVE = 1.0/12.0;

    protected Signal\IGenerator $oGenerator;
    protected Signal\Audio\Packet
        $oGeneratorInput,
        $oLastOutput
    ;
    protected int $iSamplePosition = 0;

    protected float
        $fFrequency        = ILimits::F_DEF_FREQ, // The base frequency
        $fCurrentFrequency = ILimits::F_DEF_FREQ, // The present instantaneous frequency considering any pitch control
        $fPhaseCorrection  = 0.0,                 // The accumulated phase difference as a result of pitch control */
        $fScaleVal         = 0.0,

        $fSoftening        = 0.1,
        $fLastOne          = 0.0,
        $fLastTwo          = 0.0
    ;

    protected ?\SPLFixedArray
        $oPhaseShift = null,
        $oPitchShift = null
    ;


    /**
     * Constructor.
     *
     * @param Signal\IGenerator $oGenerator
     * @param float             $fFrequency
     * @param float             $fPhase;
     */
    public function __construct(
        Signal\IGenerator $oGenerator,
        float             $fFrequency  = ILimits::F_DEF_FREQ,
        float             $fPhase      = 0.0
    ) {
        $this->oGenerator       = $oGenerator;
        $this->oGeneratorInput  = new Signal\Audio\Packet();
        $this->oLastOutput      = new Signal\Audio\Packet();
        $this->setFrequency($fFrequency);
        $this->fPhaseCorrection = $oGenerator->getPeriod() * $fPhase;
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
            Signal\Context::get()->getProcessRate(),
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
        $this->iSamplePosition  = 0;
        $this->oPhaseShift      = null;
        $this->oPitchShift      = null;
        $this->fCurrentFreqency = $this->fFrequency;
        $this->fPhaseCorrection = 0;
        return $this;
    }

    /**
     * Set the oscillator signal frequency
     *
     * @param  float $fFrequency
     * @return self
     */
    public function setFrequency(float $fFrequency) : self {
        $this->fFrequency = clamp($fFrequency, ILimits::F_MIN_FREQ, ILimits::F_MAX_FREQ);
        $this->fScaleVal  = $this->oGenerator->getPeriod() * $this->fFrequency * Signal\Context::get()->getSamplePeriod();
        $this->fCurrentFreqency = $this->fFrequency;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPitchModulation(Signal\Control\Packet $oPitch = null) : self {
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
    public function setPhaseModulation(Signal\Audio\Packet $oPhase = null) : self {
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

    protected function postProcess() {
        if ($this->fSoftening > 0.0) {
            $oSampleValues = $this->oLastOutput->getValues();
            $fScaleIn  = 0.5 * $this->fSoftening;
            $fScaleOut = 1.0 / (1.0 + $this->fSoftening);
            foreach ($oSampleValues as $i => $fCurrentSample) {
                $fLastAverage      = $fScaleIn * ($this->fLastOne + $this->fLastTwo);
                $fCurrentSample    = $fScaleOut * ($fCurrentSample + $fLastAverage);
                $oSampleValues[$i] = $fCurrentSample;
                $this->fLastTwo = $this->fLastOne;
                $this->fLastOne = $fCurrentSample;
            }
        }
    }
}
