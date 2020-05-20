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

namespace ABadCafe\Synth\Output;
use ABadCafe\Synth\Signal;
use function ABadCafe\Synth\Utility\clamp;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Raw
 *
 * Base class for raw output
 */
class Play implements IPCMOutput, Signal\IChannelMode {

    const
        I_MIN_LEVEL = -32767,
        I_MAX_LEVEL = 32767,
        I_BUFFER    = 256
    ;

    /**
     * @param resource $rOutput
     */
    private $rOutput = null;

    private array
        $aPipeDescriptors  = [
            0 => ['pipe', 'r'],
            1 => ['file', '/dev/null', 'a'],
            2 => ['file', '/dev/null', 'a']
        ],
        $aPipes = []
    ;

    private int   $iChannelMode;

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
            Signal\Context::get()->getProcessRate(),
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
            $this->aPipes  = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function write(Signal\IPacket $oPacket) {
        $aOutput = $oPacket
            ->quantise(self::I_MAX_LEVEL, self::I_MIN_LEVEL, self::I_MAX_LEVEL)
            ->toArray();
        fwrite($this->aPipes[0], pack('v*', ...$aOutput));
    }
}


