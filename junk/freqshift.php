<?php

class Sine {

    private $fPhase = 0;
    private $fTimeScale = M_PI / 128;
    private $iPosition = 0;
    private $fBaseFreq = 1;
    private $fCurrFreq = 1;
    private $fPeriod   = 2.0*M_PI;

    public function generate(array $aFreqScale) : array {
        $aResult = [];
        foreach ($aFreqScale as $fFreqScale) {
            $fTime        = $this->iPosition * $this->fTimeScale;
            $fCurrFreq    = $this->fBaseFreq * $fFreqScale;
            $aResult[$this->iPosition++] = (int)(32767*sin($this->fCurrFreq*$fTime + $this->fPhase));
            $this->fPhase += ($this->fCurrFreq - $fCurrFreq)*$fTime;
            $this->fCurrFreq = $fCurrFreq;
        }
        return $aResult;
    }
}

const I_SIZE = 32768;

if ($rOutput = fopen('out.raw', 'wb')) {
    $oSine   = new Sine();
    $aScale  = array_fill(0, I_SIZE, 1);
    $aScale  = array_merge($aScale, range(1, 2, 1/I_SIZE), array_fill(0, I_SIZE-1, 2));
    $aOutput = $oSine->generate($aScale);
    fwrite($rOutput, pack('v*', ...$aOutput));
    fclose($rOutput);
}
