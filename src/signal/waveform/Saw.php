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
 * SawUp - series of frequencies
 *
 * Maps input values to a upwards sawtooth output.
 */
class SawUp extends Shape {

    const F_PERIOD  = 1.0;

    protected float
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
                $oValues[$i] = $this->oShaper->modifyOutput($this->fScaleLevel * ($fTime - floor($fTime)) + $this->fMinLevel);
            }
        } else {
            foreach ($oValues as $i => $fTime) {
                $oValues[$i] = $this->fScaleLevel * ($fTime - floor($fTime)) + $this->fMinLevel;
            }
        }
        return $oOutput;
    }

    /**
     * @overriden
     */
    protected function init() {
        $this->fScaleLevel = $this->fMaxLevel - $this->fMinLevel;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * SawDown - series of frequencies
 *
 * Maps input values to a downwards sawtooth output.
 */
class SawDown extends SawUp {

    /**
     * @inheritdoc
     */
    public function map(Signal\IPacket $oInput) : Signal\IPacket {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        if ($this->oShaper) {
            foreach ($oValues as $i => $fTime) {
                $fTime = $this->oShaper->modifyInput($fTime);
                $oValues[$i] = $this->oShaper->modifyOutput($this->fScaleLevel * (ceil($fTime) - $fTime) + $this->fMinLevel);
            }
        } else {
            foreach ($oValues as $i => $fTime) {
                $oValues[$i] = $this->fScaleLevel * (ceil($fTime) - $fTime) + $this->fMinLevel;
            }
        }
        return $oOutput;
    }
}

