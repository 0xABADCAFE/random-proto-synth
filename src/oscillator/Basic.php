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
