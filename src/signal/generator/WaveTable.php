<?php

namespace ABadCafe\Synth\Signal\Generator;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IGenerator;
use function ABadCafe\Synth\Utility\clamp;

use \SPLFixedArray;

/**
 * WaveTable
 *
 * Use a short lookup table as a wave period. The table must be a power of two in length. To enforce this,
 * construction takes the exponent size, which must be an integer between 2 and 8.
 */
class WaveTable implements IGenerator {

    const
        I_MIN_SIZE_EXP = 2,
        I_MAX_SIZE_EXP = 8
    ;


    private
        $oTable  = null,
        $fPeriod = 0,
        $iMask   = 0
    ;

    /**
     *
     */
    public function __construct(int $iSizeExp) {
        $iSize         = 1 << (int)clamp($iSizeExp, self::I_MIN_SIZE_EXP, self::I_MAX_SIZE_EXP);
        $this->oTable  = new SPLFixedArray($iSize);
        $this->iMask   = $iSize - 1;
        $this->fPeriod = (float)$iSize;
    }

    public function getTable() : SPLFixedArray {
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
    public function map(Packet $oInput) : Packet {
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
