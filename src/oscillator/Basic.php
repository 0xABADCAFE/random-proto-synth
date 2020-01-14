<?php

namespace ABadCafe\Synth\Oscillator;

use ABadCafe\Synth\Signal\Packet;

/**
 * Basic
 */
class Basic extends Base implements IOutputOnly {

    /**
     * @inheritdoc
     */
    public function emit(int $iLength) {
        $aSamples = [];
        while ($iLength-- > 0) {
            $aSamples[] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map(new Packet($aSamples));
    }
}

class AM extends Base {

    public function emit(Packet $oAmplitudeModulator) {
        $iLength  = $oAmplitudeModulator->count();
        $aSamples = [];
        while ($iLength-- > 0) {
            $aSamples[] = $this->fScaleVal * $this->iSamplePosition++;
        }
        return $this->oGenerator->map(new Packet($aSamples))->multiply($oAmplitudeModulator);
    }
}


class FM extends Base {

    public function emit(Packet $oPhaseModulator) {
        $fPhaseSize = $this->oGenerator->getPeriod();
        $aModulator = $oPhaseModulator->getValues();
        $aSamples = [];
        foreach ($aModulator as $fPhaseShift) {
            $aSamples[] = ($this->fScaleVal * $this->iSamplePosition++) + ($fPhaseShift * $fPhaseSize);
        }
        return $this->oGenerator->map(new Packet($aSamples));
    }
}
