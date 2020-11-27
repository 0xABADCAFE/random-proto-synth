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
use \InvalidArgumentException;
use \RangeException;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Super Oscillator. Single Waveform with any number of tunable harmonics
 */
class Super extends Simple {

    use Signal\TContextIndexAware;

    protected array
        /** @var float[] $aHarmonics */
        $aHarmonics        = [],

        /** @var float[] $aIntensities*/
        $aIntensities      = [],

        /** @var float[] $aInitPhases */
        $aInitPhases       = [],

        /** @var float[] $aPhaseCorrections */
        $aPhaseCorrections = []
    ;

    /**
     * Requires a generator and a Harmonic Stack which is an array of [float Harmonic, float Intensity, float Phase]
     * triplets.
     *   - Harmonic is the specific multiple of the base frequency, e.g. 2.0 is one octave above
     *   - Intensity is the ampliude level for harmonic, 1.0 is full volume
     *   - Phase is the initial phase offset, 1.0 is one full duty cycle behind
     *
     * It is presumed, but not required that the first entry in the array will be for the first harmonic.
     *
     * For this Oscillator to be useful, at least two harmonics need to be present. For this reason an exception is
     * thrown if the harmonic array has fewer than two entries.
     *
     * @param Signal\IWaveform $oWaveform
     * @param float[3][]        $aHarmonicStack
     * @param float             $fFrequency
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        Signal\IWaveform $oWaveform,
        array            $aHarmonicStack,
        float            $fFrequency  = ILimits::F_DEF_FREQ
    ) {
        parent::__construct(
            $oWaveform,
            $fFrequency,
            0.0
        );
        $this->initHarmonicStack($aHarmonicStack);
    }

    /**
     * @inheritDoc
     */
    protected function emitNew() : Signal\Audio\Packet {
        $iSamplePosition = 0;
        $oInputValues    = $this->oWaveformInput->getValues();
        $oOutputValues   = $this->oLastOutput->fillWith(0);
        $oPhaseShift     = $this->oPhaseModulator ?
            $this->oPhaseModulator
                ->emit($this->iLastIndex)
                ->getValues()
            : null;

        if ($this->oPitchModulator) {
            // We have something modulating our basic pitch. This gets complicated...
            $oPitchShift = $this->oPitchModulator
                ->emit($this->iLastIndex)
                ->getValues();

            // Process each harmonic term
            foreach ($this->aHarmonics as $iHarmonicID => $fHarmonic) {

                // Every sample point has a new frequency, but we can't just use the instantaneous Waveform value for
                // that as it would be the value that the function has if it was always at that frequency.
                // Therefore we must also correct the phase for every sample point too. The phase correction is
                // accumulated, which is equivalent to integrating over the time step.

                $iSamplePosition   = $this->iSamplePosition;
                $fCurrentFrequency = $this->fCurrentFrequency;

                $fPhaseCorrection  = &$this->aPhaseCorrections[$iHarmonicID];
                foreach ($oPitchShift as $i => $fNextFrequencyMultiplier) {
                    $fNextFrequency    = $this->fFrequency * $fNextFrequencyMultiplier;
                    $fTime             = $this->fTimeStep * $iSamplePosition++;
                    $oInputValues[$i]  = ($fHarmonic * $fCurrentFrequency * $fTime) + $fPhaseCorrection;
                    $fPhaseCorrection += $fTime * $fHarmonic * ($fCurrentFrequency - $fNextFrequency);
                    $fCurrentFrequency = $fNextFrequency;
                }

                // Apply any phase shift
                if ($oPhaseShift) {
                    foreach ($oPhaseShift as $i => $fPhase) {
                        $oInputValues[$i] += $this->fWaveformPeriod * $fPhase;
                    }
                }
                $oOutputValues->accumulate(
                    $this->oWaveform->map($this->oWaveformInput),
                    $this->aIntensities[$iHarmonicID]
                );
            }
            $this->fCurrentFrequency = $fCurrentFrequency;

        } else {
            foreach ($this->aHarmonics as $iHarmonicID => $fHarmonic) {
                $iSamplePosition = $this->iSamplePosition;
                $fScaleVal       = $this->fScaleVal * $fHarmonic;
                foreach ($oInputValues as $i => $fValue) {
                    $oInputValues[$i] = ($fScaleVal * $iSamplePosition++) + $this->aPhaseCorrections[$iHarmonicID];
                }

                // Apply any phase shift
                if ($oPhaseShift) {
                    foreach ($oPhaseShift as $i => $fPhase) {
                        $oInputValues[$i] += $this->fWaveformPeriod * $fPhase;
                    }
                }
                $oOutputValues->accumulate(
                    $this->oWaveform->map($this->oWaveformInput),
                    $this->aIntensities[$iHarmonicID]
                );
            }
        }

        $this->iSamplePosition = $iSamplePosition;
        return $this->oLastOutput;
    }

    /**
     * @overriden
     */
    public function reset() : self {
        parent::reset();
        $this->aPhaseCorrections = $this->aInitPhases;
        return $this;
    }

    /**
     * Initialise the harmonic stack.
     */
    private function initHarmonicStack(array $aHarmonicStack) {
        if (count($aHarmonicStack) < 2) {
            throw new InvalidArgumentException('Harmonic Stack must have at least 2 entries');
        }

        foreach ($aHarmonicStack as $iHarmonicID => $aHarmonic) {
            if (!is_array($aHarmonic) || count($aHarmonic) != 3) {
                throw new InvalidArgumentException('Invalid parameter count for Harmonic #' . $iHarmonicID);
            }
            if ($aHarmonic[0] <= 0) {
                throw new RangeException('Invalid multiplier for Harmonic ' . $iHarmonicID);
            }
        }

        $this->aHarmonics   = array_column($aHarmonicStack, 0);
        $this->aIntensities = array_column($aHarmonicStack, 1);
        $this->aInitPhases  = array_column($aHarmonicStack, 2);
        $fPeriod = $this->oWaveform->getPeriod();
        foreach ($this->aInitPhases as $i => $fPhaseNormalised) {
            $this->aInitPhases[$i] = $fPeriod * $fPhaseNormalised;
        }
        $this->aPhaseCorrections = $this->aInitPhases;
    }
}
