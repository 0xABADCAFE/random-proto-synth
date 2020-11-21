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
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * WaveTable
 *
 * Use a short lookup table as a wave period. The table must be a power of two in length. To enforce this,
 * construction takes the exponent size, which must be an integer between 2 and 8.
 */
class WaveTable implements Signal\IWaveform {

    const
        I_MIN_SIZE_EXP = 2,
        I_MAX_SIZE_EXP = 8
    ;

    private ?SPLFixedArray $oTable  = null;
    private float          $fPeriod = 0;
    private int            $iMask   = 0;

    /**
     * @param int $iSizeExp
     */
    public function __construct(int $iSizeExp) {
        $iSize         = 1 << (int)clamp($iSizeExp, self::I_MIN_SIZE_EXP, self::I_MAX_SIZE_EXP);
        $this->oTable  = new SPLFixedArray($iSize);
        $this->iMask   = $iSize - 1;
        $this->fPeriod = (float)$iSize;
    }

    /**
     * @return SPLFixedArray
     */
    public function getData() : SPLFixedArray {
        return $this->oTable;
    }

    /**
     * @inheritdoc
     */
    public function getPeriod() : float {
        return $this->fPeriod;
    }

    /**
     * @inheritdoc
     */
    public function map(Signal\IPacket $oInput) : Signal\IPacket {
        $oOutput = clone $oInput;
        $oValues = $oOutput->getValues();
        foreach ($oValues as $i => $fValue) {
            $iIndex1     = (int)floor($fValue);
            $fInterp     = $fValue - $iIndex1;
            $iIndex1    &= $this->iMask;
            $iIndex2     = ($iIndex1 + 1) & $this->iMask;
            $oValues[$i] = ((1.0 - $fInterp) * $this->oTable[$iIndex1]) + ($fInterp * $this->oTable[$iIndex2]);
        }
        return $oOutput;
    }
}
