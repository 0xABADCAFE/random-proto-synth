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

namespace ABadCafe\Synth\Signal\Waveform;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Sine - single frequency
 *
 * Maps input values to a sine wave output.
 */
class Sine extends Primitive {

    const F_PERIOD = 2.0 * M_PI;

    private float
        $fBiasLevel,
        $fScaleLevel
    ;

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return self::F_PERIOD;
    }

    /**
     * @inheritdoc
     */
    public function map(Signal\IPacket $oInput) : Signal\IPacket {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        if ($this->oShaper) {
            foreach ($oValues as $i => $fTime) {
                $fTime = $this->oShaper->modifyInput($fTime);
                $oValues[$i] = $this->oShaper->modifyOutput(($this->fScaleLevel * sin($fTime)) + $this->fBiasLevel);
            }
        } else {
            foreach ($oValues as $i => $fTime) {
                $oValues[$i] = ($this->fScaleLevel * sin($fTime)) + $this->fBiasLevel;
            }
        }
        return $oOutput;
    }

    /**
     * @overridden
     */
    protected function init() {
        $this->fBiasLevel  = 0.5*($this->fMaxLevel + $this->fMinLevel);
        $this->fScaleLevel = 0.5*($this->fMaxLevel - $this->fMinLevel);
    }
}
