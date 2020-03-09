<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal;

/**
 * Raw
 *
 * Base class for raw output
 */
abstract class Raw implements IPCMOutput {

    /**
     * @param resource $rOutput
     */
    protected $rOutput = null;

    public function __destruct() {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function open(string $sPath) {
        if (
            $this->rOutput ||
            !($this->rOutput = fopen($sPath, 'wb'))
        ) {
            throw new IOException();
        }
    }

    /**
     * @inheritdoc
     */
    public function close() {
        if ($this->rOutput) {
            fclose($this->rOutput);
            $this->rOutput = null;
        }
    }
}

/**
 * Raw16BitLittle
 *
 * Raw stream of 16-bit signed samples, LSB order
 */
class Raw16BitLittle extends Raw {

    const
        I_MIN_LEVEL = -32767,
        I_MAX_LEVEL = 32767
    ;

    /**
     * @inheritdoc
     */
    public function write(Signal\IPacket $oPacket) {
        $aOutput = $oPacket
            ->quantise(self::I_MAX_LEVEL, self::I_MIN_LEVEL, self::I_MAX_LEVEL)
            ->toArray();
        fwrite($this->rOutput, pack('v*', ...$aOutput));
    }
}
