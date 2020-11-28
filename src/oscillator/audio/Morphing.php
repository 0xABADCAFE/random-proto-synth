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
 * Morphing Oscillator. Two Waveforms, second at a fixed frequency ratio to the first, mixed using a third Waveform as an LFO.
 */
class Morphing extends Simple {

    use Signal\TContextIndexAware;

    const
        F_MIN_RATIO = 0.125,
        F_MAX_RATIO = 8.0
    ;

    protected Signal\IWaveform
        $oSecondaryWaveform,
        $oMixingWaveform
    ;

    protected Signal\Audio\Packet
        $oSecondaryInput,
        $oMixingInput
    ;

    protected float
        $fSecondaryPeriod,
        $fSecondaryRatio,
        $fSecondaryPhaseCorrection,
        $fMixingFrequency,
        $fSecondaryScaleVal,
        $fMixingScaleVal
    ;

    public function __construct(
        Signal\IWaveform $oPrimaryWaveform,
        Signal\IWaveform $oSecondaryWaveform,
        Signal\IWaveform $oMixingWaveform,
        float            $fPrimaryFrequency,
        float            $fSecondaryRatio,
        float            $fMixingFrequency
    ) {
        $this->oSecondaryWaveform = $oSecondaryWaveform;
        $this->oMixingWaveform    = $oMixingWaveform;
        $this->fMixingFrequency   = clamp($fMixingFrequency, ILimits::F_MIN_FREQ, ILimits::F_MAX_FREQ);
        $this->fSecondaryRatio    = clamp($fSecondaryRatio, self::F_MIN_RATIO, self::F_MAX_RATIO);
        $this->fSecondaryPeriod   = $oSecondaryWaveform->getPeriod();
        $this->oSecondaryInput    = new Signal\Audio\Packet();
        $this->oMixingInput       = new Signal\Audio\Packet();
        parent::__construct($oPrimaryWaveform, $fPrimaryFrequency);
    }

    /**
     * @inheritDoc
     */
    public function setFrequency(float $fFrequency) : self {
        parent::setFrequency($fFrequency);
        $fSamplePeriod = Signal\Context::get()->getSamplePeriod();
        $this->fSecondaryScaleVal = $this->oSecondaryWaveform->getPeriod() * $this->fFrequency * $this->fSecondaryRatio * $fSamplePeriod;
        $this->fMixingScaleVal    = $this->oMixingWaveform->getPeriod()    * $this->fMixingFrequency * $fSamplePeriod;
        return $this;
    }

    /**
     * @inheritDoc
     *
     * @todo - Reimplement to apply pitch and phase modulation as in the Simple oscillator.
     */
    protected function emitNew() : Signal\Audio\Packet {
        $oPrimaryValues   = $this->oWaveformInput->getValues();
        $oSecondaryValues = $this->oSecondaryInput->getValues();
        $oMixingValues    = $this->oMixingInput->getValues();
        $oPhaseShift      = $this->oPhaseModulator ?
            $this->oPhaseModulator
                ->emit($this->iLastIndex)
                ->getValues()
            : null;

        if ($this->oPitchModulator) {
            // We have something modulating our basic pitch. This gets complicated...
            $oPitchShift = $this->oPitchModulator
                ->emit($this->iLastIndex)
                ->getValues();

            // Every sample point has a new frequency, but we can't just use the instantaneous Waveform value for
            // that as it would be the value that the function has if it was always at that frequency.
            // Therefore we must also correct the phase for every sample point too. The phase correction is
            // accumulated, which is equivalent to integrating over the time step.

            // TODO

        } else {
            foreach ($oPrimaryValues as $i => $fValue) {
                $oPrimaryValues[$i]   = $this->fScaleVal          * $this->iSamplePosition;
                $oSecondaryValues[$i] = $this->fSecondaryScaleVal * $this->iSamplePosition;
                $oMixingValues[$i]    = $this->fMixingScaleVal    * $this->iSamplePosition++;
            }
        }

        if ($oPhaseShift) {
            foreach ($oPhaseShift as $i => $fValue) {
                $oPrimaryValues[$i]   += $this->fWaveformPeriod  * $fValue;
                $oSecondaryValues[$i] += $this->fSecondaryPeriod * $fValue;
            }
        }

        // And now blend the outputs
        $oOutputPacket    = $this->oWaveform->map($this->oWaveformInput);
        $oSecondaryValues = $this->oSecondaryWaveform->map($this->oSecondaryInput)->getValues();
        $oMixingValues    = $this->oMixingWaveform->map($this->oMixingInput)->getValues();
        $oOutputValues    = $oOutputPacket->getValues();
        foreach ($oOutputValues as $i => $fPrimary) {
            $fMixValue = 0.5 * ($oMixingValues[$i] + Signal\ILimits::F_MAX_LEVEL_NO_CLIP);
            $oOutputValues[$i] = ($fPrimary * $fMixValue) + ((1.0 - $fMixValue) * $oSecondaryValues[$i]);
        }
        return $this->oLastOutput = $oOutputPacket;
    }

    /**
     * @inheritdoc
     *
     * @todo - remove. See emit()
     */
//     public function emitPhaseModulated(Signal\Packet $oPhase) : Signal\Packet {
//         $oValues             = $this->oWaveformInput->getValues();
//         $oSecondaryValues    = $this->oSecondaryInput->getValues();
//         $oMixingValues       = $this->oMixingInput->getValues();
//         $fPhaseSize          = $this->oWaveform->getPeriod();
//         $fSecondaryPhaseSize = $this->oSecondaryWaveform->getPeriod();
//         $oModulator          = $oPhase->getValues();
//         foreach ($oValues as $i => $fValue) {
//             $oValues[$i]          = ($this->fScaleVal          * $this->iSamplePosition) + ($fPhaseSize * $oModulator[$i]);
//             $oSecondaryValues[$i] = ($this->fSecondaryScaleVal * $this->iSamplePosition) + ($fSecondaryPhaseSize * $oModulator[$i]);
//             $oMixingValues[$i]    = $this->fMixingScaleVal     * $this->iSamplePosition++;
//         }
//         $oOutputPacket    = $this->oWaveform->map($this->oWaveformInput);
//         $oSecondaryValues = $this->oSecondaryWaveform->map($this->oSecondaryInput)->getValues();
//         $oMixingValues    = $this->oMixingWaveform->map($this->oMixingInput)->getValues();
//         $oOutputValues    = $oOutputPacket->getValues();
//         foreach ($oOutputValues as $i => $fPrimary) {
//             $fMixValue = 0.5 * ($oMixingValues[$i] + SignalLimits::F_MAX_LEVEL_NO_CLIP);
//             $oOutputValues[$i] = ($fPrimary * $fMixValue) + ((1.0 - $fMixValue)*$oSecondaryValues[$i]);
//         }
//         return $oOutputPacket;
//     }
 }
