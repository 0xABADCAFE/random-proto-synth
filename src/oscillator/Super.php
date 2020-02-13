<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\IGenerator;
use ABadCafe\Synth\Signal\Packet;
use \InvalidArgumentException;
use \RangeException;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Super Oscillator. Single Generator with any number of tunable harmonics
 */
class Super extends Simple {

    protected
        /** @var float[] $aHarmonics */
        $aHarmonics   = [],

        /** @var float[] $aIntensities*/
        $aIntensities = [],

        /** @var float[] $aInitPhases */
        $aInitPhases = [],

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
     * @param IGenerator $oGenerator
     * @param float[3][] $aHarmonicStack
     * @param float      $fFrequency
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        IGenerator $oGenerator,
        array      $aHarmonicStack,
        float      $fFrequency  = ILimits::F_DEF_FREQ
    ) {
        $this->oGenerator      = $oGenerator;
        $this->oGeneratorInput = new Packet();
        $this->setFrequency($fFrequency);
        $this->initHarmonicStack($aHarmonicStack);
    }

    /**
     * @inheritdoc
     */
    public function emit() : Packet {
        $oOutput = new Packet();
        $oValues = $this->oGeneratorInput->getValues();
        $iSamplePosition = 0;

        // Handle pitch control
        if ($this->oPitchShift) {
            $fCyclePeriod      = $this->oGenerator->getPeriod();
            $fSamplePeriod     = Context::get()->getSamplePeriod();
            $fCurrentFrequency = $this->fCurrentFrequency;
            // Process each harmonic term
            foreach ($this->aHarmonics as $iHarmonicID => $fHarmonic) {
                // Every sample point has a new frequency, we must also correct the phase for every sample point.
                // The phase correction is accumulated, which is equivalent to integrating over the time step.
                $iSamplePosition   = $this->iSamplePosition;
                $fCurrentFrequency = $this->fCurrentFrequency;
                foreach ($this->oPitchShift as $i => $fNextFrequency) {
                    $fTime                                  = $fCyclePeriod * $fSamplePeriod * $iSamplePosition++;
                    $oValues[$i]                            = ($fHarmonic * $fCurrentFrequency * $fTime) + $this->aPhaseCorrections[$iHarmonicID];
                    $this->aPhaseCorrections[$iHarmonicID] += $fTime * $fHarmonic * ($fCurrentFrequency - $fNextFrequency);
                    $fCurrentFrequency                      = $fNextFrequency;
                }

                // Apply any phase shift
                if ($this->oPhaseShift) {
                    foreach ($this->oPhaseShift as $i => $fPhase) {
                        $oValues[$i] += $fPhase;
                    }
                }
                $oOutput->accumulate(
                    $this->oGenerator->map($this->oGeneratorInput),
                    $this->aIntensities[$iHarmonicID]
                );
            }
            $this->fCurrentFrequency = $fCurrentFrequency;
        } else {
            // Process each harmonic term
            foreach ($this->aHarmonics as $iHarmonicID => $fHarmonic) {
                $iSamplePosition = $this->iSamplePosition;
                $fScaleVal       = $this->fScaleVal * $fHarmonic;
                foreach ($oValues as $i => $fValue) {
                    $oValues[$i] = ($fScaleVal * $iSamplePosition++) + $this->aPhaseCorrections[$iHarmonicID];
                }

                // Apply any phase shift
                if ($this->oPhaseShift) {
                    foreach ($this->oPhaseShift as $i => $fPhase) {
                        $oValues[$i] += $fPhase;
                    }
                }
                $oOutput->accumulate(
                    $this->oGenerator->map($this->oGeneratorInput),
                    $this->aIntensities[$iHarmonicID]
                );
            }
        }
        $this->iSamplePosition = $iSamplePosition;
        return $oOutput;
    }

    /**
     * @overriden
     */
    public function reset() : IStream {
        parent::reset();
        $this->aPhaseCorrections = $this->aInitPhases;
        return $this;
    }

    /**
     * Initialise the harmonic stack.
     */
    private function initHarmonicStack(array $aHarmonicStack) {
        if (count($aHarmonicStack) < 2) {
            throw new InvaliArgumentException();
        }

        foreach ($aHarmonicStack as $iHarmonicID => $aHarmonic) {
            if (!is_array($aHarmonic) || count($aHarmonic) != 3) {
                throw new InvalidArgumentException('Invalid parameter count for Harmonic #' . $iHarmonicID);
            }
            if ($aHarmonic[0] <= 0) {
                throw new RangeException('Invalid multiplier for Harmonic ' . $iHarmonicID);
            }
        }

        $this->aHarmonics        = array_column($aHarmonicStack, 0);
        $this->aIntensities      = array_column($aHarmonicStack, 1);
        $this->aInitPhases       = array_column($aHarmonicStack, 2);
        $fPeriod = $this->oGenerator->getPeriod();
        foreach ($this->aInitPhases as $i => $fPhaseNormalised) {
            $this->aInitPhases[$i] = $fPeriod * $fPhaseNormalised;
        }
        $this->aPhaseCorrections   = $this->aInitPhases;
    }
}
