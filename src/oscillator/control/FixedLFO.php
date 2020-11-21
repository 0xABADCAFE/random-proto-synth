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
 * Control
 */
namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use \SPLFixedArray;
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class FixedLFO extends Base {

    use Signal\TContextIndexAware;

    protected float
        $fFrequency        = ILimits::F_DEF_FREQ, // The base frequency
        $fCurrentFrequency = ILimits::F_DEF_FREQ, // The present instantaneous frequency considering any pitch control
        $fScaleVal         = 0.0,
        $fDepth            = 0.5
    ;

    /**
     * Constructor.
     *
     * @param Signal\IWaveform $oWaveform
     * @param float             $fFrequency
     * @param float             $fDepth;
     */
    public function __construct(
        Signal\IWaveform $oWaveform,
        float             $fFrequency  = ILimits::F_DEF_FREQ,
        float             $fDepth      = 0.5
    ) {
        parent::__construct($oWaveform);
        $this->setFrequency($fFrequency);
        $this->fPhaseCorrection = 0;
        $this->fDepth       = $fDepth;
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * @inheritDoc
     */
    public function getFrequency() : float {
        return $this->fFrequency;
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iSamplePosition      = 0;
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
        $this->fScaleVal  = $this->oWaveform->getPeriod() * $this->fFrequency * Signal\Context::get()->getSamplePeriod();
        $this->fCurrentFreqency = $this->fFrequency;
        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param  float $fDepth
     * @return self
     */
    public function setDepth(float $fDepth) : self {
        $this->fDepth = $fDepth;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Control\Packet {
        if ($this->useLast($iIndex)) {
            return $this->oLastOutput;
        }

        $oValues = $this->oWaveformInput->getValues();

        // Basic linear intervals, there is no phase adjustment
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition++;
        }

        $oOutput = $this->oWaveform->map($this->oWaveformInput);
        $oOutput->scaleBy($this->fDepth);
        $this->oLastOutput = $oOutput;
        return $oOutput;
    }
}
