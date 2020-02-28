<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\IChannelMode;

use function ABadCafe\Synth\Utility\clamp;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Raw
 *
 * Base class for raw output
 */
class Play implements IPCMOutput, IChannelMode {

    const
        I_MIN_LEVEL = -32767,
        I_MAX_LEVEL = 32767,
        I_BUFFER    = 256
    ;

    /**
     * @param resource $rOutput
     */
    private $rOutput = null;

    private $aPipeDescriptors  = [
        0 => ['pipe', 'r'],
        1 => ['file', '/dev/null', 'a'],
        2 => ['file', '/dev/null', 'a']
    ];

    private $aPipes;

    private $iChannelMode;

    public function __construct(int $iChannelMode = self::I_CHAN_MONO) {
        $this->iChannelMode = clamp($iChannelMode, self::I_CHAN_MONO, self::I_CHAN_STEREO);
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function open(string $sPath) {
        $sCommand = sprintf(
            'play -t raw -b 16 -c %d -e signed --endian=little -r %d --buffer %d -',
            $this->iChannelMode,
            Context::get()->getProcessRate(),
            self::I_BUFFER
        );

        if (
            $this->rOutput ||
            !($this->rOutput = proc_open($sCommand, $this->aPipeDescriptors, $this->aPipes))
        ) {
            throw new IOException();
        } else {
            dprintf("SOX: %s\n", $sCommand);
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
