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
class Prototype implements IOscillator {

    use Signal\TContextIndexAware;

    protected Signal\IWaveform $oWaveform;
    protected Signal\Audio\Packet
        $oWaveformInput,
        $oLastOutput
    ;

    protected ?Signal\Control\IStream $oPitchControl = null;

    protected int $iSamplePosition = 0;

    protected float
        $fFrequency        = ILimits::F_DEF_FREQ, // The base frequency
        $fCurrentFrequency = ILimits::F_DEF_FREQ, // The present instantaneous frequency considering any pitch control
        $fPhaseCorrection  = 0.0,                 // The accumulated phase difference as a result of pitch control */
        $fScaleVal         = 0.0
    ;

    protected ?\SPLFixedArray
        $oPhaseShift = null
    ;

    /**
     * Constructor.
     *
     * @param Signal\IWaveform            $oWaveform
     * @param Signal\Control\IStream|null $oPitchControl = null,
     * @param float                       $fFrequency
     * @param float                       $fPhase;
     */
    public function __construct(
        Signal\IWaveform        $oWaveform,
        ?Signal\Control\IStream $oPitchControl = null,
        float                   $fFrequency    = ILimits::F_DEF_FREQ,
        float                   $fPhase        = 0.0
    ) {
        $this->oWaveform        = $oWaveform;
        $this->oPitchControl    = $oPitchControl;
        $this->oWaveformInput   = new Signal\Audio\Packet();
        $this->oLastOutput      = new Signal\Audio\Packet();
        $this->setFrequency($fFrequency);
        $this->fPhaseCorrection = $oWaveform->getPeriod() * $fPhase;
    }

    /**
     * @inheritdoc
     */
    public function setPitchModulation(Signal\Control\Packet $oPitch = null) : self {
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function setPhaseModulation(Signal\Audio\Packet $oPhase = null) : self {
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return sprintf(
            "%s [%s freq:%.3fHz rate:%dHz, pos:%d]",
            static::class,
            get_class($this->oWaveform),
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
        $this->fScaleVal  = $this->oWaveform->getPeriod() * $this->fFrequency * Signal\Context::get()->getSamplePeriod();
        $this->fCurrentFreqency = $this->fFrequency;
        return $this;
    }

    //////////////////////////////////////////////////////////////////

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Audio\Packet {

        if ($this->useLast($iIndex)) {
            return $this->oLastOutput;
        }

        $oValues = $this->oWaveformInput->getValues();

        if ($this->oPitchControl) {
            $oPitchShift = clone $this->oPitchControl->emit($this->iLastIndex)->getValues();

            // Every sample point has a new frequency, we must also correct the phase for every sample point.
            // The phase correction is accumulated, which is equivalent to integrating over the time step.
            $fTimeStep     = Signal\Context::get()->getSamplePeriod() * $this->oWaveform->getPeriod();
            foreach ($oPitchShift as $i => $fNextFrequencyMultiplier) {
                $fNextFrequency          = $this->fFrequency * $fNextFrequencyMultiplier;
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

        $this->oLastOutput = $this->oWaveform->map($this->oWaveformInput);
        return $this->oLastOutput;
    }
}
