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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class ControlledLFO extends Base {

    use Signal\TContextIndexAware;

    protected Signal\IWaveform $oWaveform;

    protected Signal\Control\IStream
        $oRateControl,
        $oDepthControl
    ;

    protected Signal\Control\Packet
        $oWaveformInput,
        $oLastOutput
    ;
    protected int $iSamplePosition = 0;

    protected float
        $fCurrentFrequency = ILimits::F_DEF_FREQ, // The present instantaneous frequency considering any pitch control
        $fPhaseCorrection  = 0.0                  // The accumulated phase difference as a result of pitch control
    ;


    /**
     * Constructor.
     *
     * @param Signal\IWaveform $oWaveform
     * @param float            $fFrequency
     * @param float            $fDepth;
     */
    public function __construct(
        Signal\IWaveform       $oWaveform,
        Signal\Control\IStream $oRateControl,
        Signal\Control\IStream $oDepthControl
    ) {
        $this->oWaveform        = $oWaveform;
        $this->oRateControl     = $oRateControl;
        $this->oDepthControl    = $oDepthControl;
        $this->oWaveformInput   = new Signal\Control\Packet();
        $this->oLastOutput      = new Signal\Control\Packet();
        $this->fPhaseCorrection = 0;
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
        return $this->fCurrentFrequency;
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iSamplePosition      = 0;
        $this->fCurrentFreqency     = ILimits::F_DEF_FREQ;
        $this->fPhaseCorrection     = 0;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFrequency(float $fFrequency) : self {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDepth(float $fDepth) : self {
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
        $oShifts = $this->oRateControl->emit($iIndex);

        $fTimeStep     = Signal\Context::get()->getSamplePeriod() * $this->oWaveform->getPeriod();
        foreach ($oShifts->getValues() as $i => $fNextFrequency) {
            $fTime                   = $fTimeStep * $this->iSamplePosition++;
            $oValues[$i]             = ($this->fCurrentFrequency * $fTime) + $this->fPhaseCorrection;
            $this->fPhaseCorrection += $fTime * ($this->fCurrentFrequency - $fNextFrequency);
            $this->fCurrentFrequency = $fNextFrequency;
        }

        $oOutput = $this->oWaveform->map($this->oWaveformInput);
        $oOutput->modulateWith($this->oDepthControl->emit($iIndex));
        $this->oLastOutput = $oOutput;
        return $oOutput;
    }
}
