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
 * Square - series of frequencies
 *
 * Maps input values to a square output.
 */
class Square extends Base {

    const F_PERIOD = 2.0;

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
            foreach ($oValues as $i => $fValue) {
                $fValue = $this->oShaper->modifyInput($fValue);
                $oValues[$i] = $this->oShaper->modifyOutput(floor($fValue) & 1 ? $this->fMinLevel : $this->fMaxLevel);
            }
        } else {
            foreach ($oValues as $i => $fValue) {
                $oValues[$i] = floor($fValue) & 1 ? $this->fMinLevel : $this->fMaxLevel;
            }
        }
        return $oOutput;
    }
}
