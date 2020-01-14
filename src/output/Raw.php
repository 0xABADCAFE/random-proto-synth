<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal\Packet;

abstract class Raw implements IPCMOutput {

    protected $rOutput = null;

    public function __destruct() {
        $this->close();
    }

    public function open(string $sPath) {
        if (
            $this->rOutput ||
            !($this->rOutput = fopen($sPath, 'wb'))
        ) {
            throw new IOException();
        }
    }

    public function close() {
        if ($this->rOutput) {
            fclose($this->rOutput);
            $this->rOutput = null;
        }
    }
}

class Raw16BitLittle extends Raw {

    const
        I_MIN_LEVEL = -32767,
        I_MAX_LEVEL = 32767
    ;

    public function write(Packet $oPacket) {
        $aSamples = $oPacket->getValues();
        $aOutput  = [];
        foreach ($aSamples as $fSample) {
            $aOutput[] = min(
                max(
                    (int)($fSample * self::I_MAX_LEVEL),
                    self::I_MIN_LEVEL
                ),
                self::I_MAX_LEVEL
            );
        }
        fwrite($this->rOutput, pack('v*', ...$aOutput));
    }
}
