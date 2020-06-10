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
namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use \SPLFixedArray;
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class LFO implements IOscillator {

    protected Signal\IGenerator     $oGenerator;
    protected Signal\Control\Packet $oGeneratorInput;
    protected int                   $iSamplePosition = 0;

    protected float
        $fFrequency        = ILimits::F_DEF_FREQ, // The base frequency
        $fCurrentFrequency = ILimits::F_DEF_FREQ, // The present instantaneous frequency considering any pitch control
        $fPhaseCorrection  = 0.0,                 // The accumulated phase difference as a result of pitch control */
        $fScaleVal         = 0.0,
        $fIntensity        = 0.5
    ;

    protected ?SPLFixedArray         $oFrequencyShift      = null;
    protected ?Signal\Control\Packet $oIntensityModulation = null;


    /**
     * Constructor.
     *
     * @param Signal\IGenerator $oGenerator
     * @param float             $fFrequency
     * @param float             $fIntensity;
     */
    public function __construct(
        Signal\IGenerator $oGenerator,
        float             $fFrequency  = ILimits::F_DEF_FREQ,
        float             $fIntensity  = 0.5
    ) {
        $this->oGenerator       = $oGenerator;
        $this->oGeneratorInput  = new Signal\Control\Packet();
        $this->setFrequency($fFrequency);
        $this->fPhaseCorrection = 0;
        $this->fIntensity       = $fIntensity;
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * @inheritdoc
     */
    public function getFrequency() : float {
        return $this->fFrequency;
    }

    /**
     * @inheritdoc
     */
    public function reset() : self {
        $this->iSamplePosition      = 0;
        $this->oFrequencyShift      = null;
        $this->oIntensityModulation = null;
        $this->fCurrentFreqency     = $this->fFrequency;
        $this->fPhaseCorrection     = 0;
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
     * @param  Signal\Control\Packet $oRate
     * @return self
     */
    public function setRateModulation(Signal\Control\Packet $oRate = null) : self {
        if ($oRate) {
            $this->oFrequencyShift = clone $oRate->getValues();
        } else {
            $this->oFrequencyShift = null;
        }
        return $this;
    }

    /**
     * @param  Signal\Control\Packet $oIntensity
     * @return self
     */
    public function setIntensityModulation(Signal\Control\Packet $oIntensity = null) : self {
        if ($oIntensity) {
            $this->oIntensityModulation = clone $oIntensity->getValues();
        } else {
            $this->oIntensityModulation = null;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Control\Packet {

        $oValues = $this->oGeneratorInput->getValues();

        if ($this->oFrequencyShift) {
            // Every sample point has a new frequency, we must also correct the phase for every sample point.
            // The phase correction is accumulated, which is equivalent to integrating over the time step.
            $fTimeStep     = Signal\Context::get()->getSamplePeriod() * $this->oGenerator->getPeriod();
            foreach ($this->oFrequencyShift as $i => $fNextFrequency) {
                $fTime                   = $fTimeStep * $this->iSamplePosition++;
                $oValues[$i]             = ($this->fCurrentFrequency * $fTime) + $this->fPhaseCorrection;
                $this->fPhaseCorrection += $fTime * ($this->fCurrentFrequency - $fNextFrequency);
                $this->fCurrentFrequency = $fNextFrequency;
            }
        } else {
            // Basic linear intervals, there is no phase adjustment
            foreach ($oValues as $i => $fValue) {
                $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
            }
        }

        $oOutput = $this->oGenerator->map($this->oGeneratorInput);
        if ($this->oIntensityModulation) {
            $oOutput->modulateWith($this->oIntensityModulation);
        } else {
            $oOutput->scaleBy($this->fIntensity);
        }
        return $oOutput;
    }
}
