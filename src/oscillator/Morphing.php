<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Generator\IGenerator;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\ILimits as SignalLimits;

/**
 * Morphing Oscillator. Two Generators, second at a fixed frequency ratio to the first, mixed using a third Generator as an LFO.
 */
 class Morphing extends Simple {

    const
        F_MIN_RATIO = 0.125,
        F_MAX_RATIO = 8.0
    ;

    protected
        $oSecondaryGenerator,
        $oSecondaryInput,
        $oMixingGenerator,
        $oMixingInput,
        $fSecondaryRatio,
        $fMixingFrequency,
        $fSecondaryScaleVal,
        $fMixingScaleVal
    ;

    public function __construct(
        IGenerator $oPrimaryGenerator,
        IGenerator $oSecondaryGenerator,
        IGenerator $oMixingGenerator,
        float      $fPrimaryFrequency,
        float      $fSecondaryRatio,
        float      $fMixingFrequency
    ) {
        $this->fMixingFrequency    = $this->clamp($fMixingFrequency, ILimits::F_MIN_FREQ, ILimits::F_MAX_FREQ);
        $this->fSecondaryRatio     = $this->clamp($fSecondaryRatio, self::F_MIN_RATIO, self::F_MAX_RATIO);
        $this->oSecondaryGenerator = $oSecondaryGenerator;
        $this->oMixingGenerator    = $oMixingGenerator;
        $this->oSecondaryInput     = new Packet();
        $this->oMixingInput        = new Packet();
        parent::__construct($oPrimaryGenerator, $fPrimaryFrequency);
    }

    public function setFrequency(float $fFrequency) : IOscillator {
        parent::setFrequency($fFrequency);
        $fRate = 1.0 / (float)Context::get()->getProcessRate();
        $this->fSecondaryScaleVal = $this->oSecondaryGenerator->getPeriod() * $this->fFrequency * $this->fSecondaryRatio * $fRate;
        $this->fMixingScaleVal    = $this->oMixingGenerator->getPeriod()    * $this->fMixingFrequency * $fRate;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @todo - Reimplement to apply pitch and phase modulation as in the Simple oscillator.
     */
    public function emit() : Packet {
        $oValues          = $this->oGeneratorInput->getValues();
        $oSecondaryValues = $this->oSecondaryInput->getValues();
        $oMixingValues    = $this->oMixingInput->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i] = $this->fScaleVal * $this->iSamplePosition;
            $oSecondaryValues[$i] = $this->fSecondaryScaleVal * $this->iSamplePosition;
            $oMixingValues[$i]    = $this->fMixingScaleVal    * $this->iSamplePosition++;
        }
        $oOutputPacket    = $this->oGenerator->map($this->oGeneratorInput);
        $oSecondaryValues = $this->oSecondaryGenerator->map($this->oSecondaryInput)->getValues();
        $oMixingValues    = $this->oMixingGenerator->map($this->oMixingInput)->getValues();
        $oOutputValues    = $oOutputPacket->getValues();
        foreach ($oOutputValues as $i => $fPrimary) {
            $fMixValue = 0.5 * ($oMixingValues[$i] + SignalLimits::F_MAX_LEVEL_NO_CLIP);
            $oOutputValues[$i] = ($fPrimary * $fMixValue) + ((1.0 - $fMixValue)*$oSecondaryValues[$i]);
        }
        return $oOutputPacket;
    }

    /**
     * @inheritdoc
     *
     * @todo - remove. See emit()
     */
    public function emitPhaseModulated(Packet $oPhase) : Packet {
        $oValues             = $this->oGeneratorInput->getValues();
        $oSecondaryValues    = $this->oSecondaryInput->getValues();
        $oMixingValues       = $this->oMixingInput->getValues();
        $fPhaseSize          = $this->oGenerator->getPeriod();
        $fSecondaryPhaseSize = $this->oSecondaryGenerator->getPeriod();
        $oModulator          = $oPhase->getValues();
        foreach ($oValues as $i => $fValue) {
            $oValues[$i]          = ($this->fScaleVal          * $this->iSamplePosition) + ($fPhaseSize * $oModulator[$i]);
            $oSecondaryValues[$i] = ($this->fSecondaryScaleVal * $this->iSamplePosition) + ($fSecondaryPhaseSize * $oModulator[$i]);
            $oMixingValues[$i]    = $this->fMixingScaleVal     * $this->iSamplePosition++;
        }
        $oOutputPacket    = $this->oGenerator->map($this->oGeneratorInput);
        $oSecondaryValues = $this->oSecondaryGenerator->map($this->oSecondaryInput)->getValues();
        $oMixingValues    = $this->oMixingGenerator->map($this->oMixingInput)->getValues();
        $oOutputValues    = $oOutputPacket->getValues();
        foreach ($oOutputValues as $i => $fPrimary) {
            $fMixValue = 0.5 * ($oMixingValues[$i] + SignalLimits::F_MAX_LEVEL_NO_CLIP);
            $oOutputValues[$i] = ($fPrimary * $fMixValue) + ((1.0 - $fMixValue)*$oSecondaryValues[$i]);
        }
        return $oOutputPacket;
    }
 }
