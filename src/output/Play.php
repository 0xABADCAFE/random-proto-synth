<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Context;

/**
 * Raw
 *
 * Base class for raw output
 */
class Play implements IPCMOutput {

    const
        I_MIN_LEVEL = -32767,
        I_MAX_LEVEL = 32767
    ;

    /**
     * @param resource $rOutput
     */
    protected $rOutput = null;

    protected $aPipeDescriptors  = [
        0 => ['pipe', 'r'],
        1 => ['file', '/dev/null', 'a'],
        2 => ['file', '/dev/null', 'a']
    ];

    protected $aPipes;

    public function __destruct() {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function open(string $sPath) {
        $sCommand = sprintf(
            'play -t raw -b 16 -c 1 -e signed --endian=little -r %d -',
            Context::get()->getProcessRate()
        );

        if (
            $this->rOutput ||
            !($this->rOutput = proc_open($sCommand, $this->aPipeDescriptors, $this->aPipes))
        ) {
            throw new IOException();
        }
    }

    /**
     * @inheritdoc
     */
    public function close() {
        if ($this->rOutput) {
            proc_close($this->rOutput);
            foreach ($this->aPipes as $rPipe) {
                if (is_resource($rPipe)) {
                    fclose($rPipe);
                }
            }
            $this->rOutput = null;
            $this->aPipes  = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function write(Packet $oPacket) {
        $aOutput = $oPacket
            ->quantize(self::I_MAX_LEVEL, self::I_MIN_LEVEL, self::I_MAX_LEVEL)
            ->toArray();
        fwrite($this->aPipes[0], pack('v*', ...$aOutput));
    }
}


