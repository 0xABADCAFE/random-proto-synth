<?php

namespace ABadCafe\Synth\Signal\Filter;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use \SPLFixedArray;

/**
 * Query limits for basic filters
 */
interface IFilterLimits {
    public function getMinF() : float;
    public function getMaxF() : float;
}

/**
 * Query limits for resonance
 */
interface IResonanceLimits {
    public function getMinQ() : float;
    public function getMaxQ() : float;
}

/**
 * Control interface for fully controllable
 * resonant filters.
 */
interface IFullyAutomatedResonantFilter {
    public function reset();
    public function filter(
        Packet $oInput,
        Packet $oCutoff,
        Packet $oEnv
    ) : Packet;
}


/**
 * Resonant Low Pass filter
 *
 * Based on https://www.musicdsp.org/en/latest/Filters/26-moog-vcf-variation-2.html
 */
class ResonantLowPass implements IFilterLimits, IResonanceLimits, IFullyAutomatedResonantFilter {

    private
        $fIn1,  $fIn2,  $fIn3,  $fIn4,
        $fOut1, $fOut2, $fOut3, $fOut4
    ;
    
    public function __construct() {
        $this->reset();
    }

    /**
     * @inheritdoc
     */    
    public function getMinF() : float {
        return 0.01;
    }

    /**
     * @inheritdoc
     */
    public function getMaxF() : float {
        return 1.0;
    }

    /**
     * @inheritdoc
     */
    public function getMinQ() : float {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getMaxQ() : float {
        return 4.0;
    }

    /**
     * @inheritdoc
     */
    public function reset() {
        $this->fIn1  = 0;
        $this->fIn2  = 0;
        $this->fIn3  = 0;
        $this->fIn4  = 0;
        $this->fOut1 = 0;
        $this->fOut2 = 0;
        $this->fOut3 = 0;
        $this->fOut4 = 0;        
    }

    /**
     * @inheritdoc
     */
    public function filter(Packet $oInput, Packet $oCutoffEnv, Packet $oResEnv) : Packet {
        $oOutput    = clone $oInput;
        $oSamples   = $oOutput->getValues();
        $oCutoff    = $oCutoffEnv->getValues();
        $oResonance = $oResEnv->getValues();
        foreach ($oSamples as $i => $fInput) {
            $f  = $oCutoff[$i]    * 1.16;
            $fb = $oResonance[$i] * (1.0 - 0.15 * $f * $f);

            $fInput      -= $this->fOut4 * $fb;
            $fInput      *= 0.35013 * ($f * $f * $f * $f);
            $this->fOut1  = $fInput + 0.3 * $this->fIn1 + (1 - $f) * $this->fOut1; // Pole 1
            $this->fIn1   = $fInput;
            $this->fOut2  = $this->fOut1 + 0.3 * $this->fIn2 + (1 - $f) * $this->fOut2;  // Pole 2
            $this->fIn2   = $this->fOut1;
            $this->fOut3  = $this->fOut2 + 0.3 * $this->fIn3 + (1 - $f) * $this->fOut3;  // Pole 3
            $this->fIn3   = $this->fOut2;
            $this->fOut4  = $this->fOut3 + 0.3 * $this->fIn4 + (1 - $f) * $this->fOut4;  // Pole 4
            $this->fIn4   = $this->fOut3;
            $oSamples[$i] = $this->fOut4;
        }
        return $oOutput;
    }
}
