<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\IChannelMode;

use function ABadCafe\Synth\Utility\clamp;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Wav
 *
 * Minimal implementation of the RIFF Wave standard for linear PCM
 */
class Wav implements IPCMOutput, IChannelMode {

    const
        I_DEF_RATE_SIGNAL_DEFAULT = 0,
        I_DEF_RESOLUTION_BITS     = 16,
        I_HEADER_SIZE             = 44
    ;

    const M_HEADER = [
        'sChunkID'       => 'RIFF', //  0: 4
        'iChunkSize'     => -1,     //  4: 4  (file - 8)
        'sFormat'        => 'WAVE', //  8: 4  Format ID
        'sSubChunk1ID'   => 'fmt ', // 12: 4
        'iSubChunk1Size' => 16,     // 16: 4
        'iAudioFormat'   => 1,      // 20: 2
        'iNumChannels'   => 1,      // 22: 2
        'iSampleRate'    => -1,     // 24: 4
        'iByteRate'      => -1,     // 28: 4
        'iBlockAlign'    => -1,     // 32: 2
        'iBitsPerSample' => 16,     // 34: 2
        'sSubChunk2ID'   => 'data', // 36: 4
        'iSubChunk2Size' => -1,     // 40: 4
    ];

    const S_HEADER_PACK = 'a4Va4a4VvvVVvva4V';

    protected
        $rOutput = null,
        $iSampleRate,
        $iBitsPerSample,
        $iNumChannels,
        $iQuantize
    ;

    /**
     * Constructor
     *
     * @param int $iSampleRate    (defaults to the Signal Process Rate
     * @param int $iBitsPerSample (defaults to 16)
     * @param int $iChannelMiode  (defaults to mono)
     */
    public function __construct(
        int $iSampleRate    = self::I_DEF_RATE_SIGNAL_DEFAULT,
        int $iBitsPerSample = self::I_DEF_RESOLUTION_BITS,
        int $iChannelMiode  = self::I_CHAN_MONO
    ) {
        $this->iSampleRate    = $iSampleRate != self::I_DEF_RATE_SIGNAL_DEFAULT ?: Context::get()->getProcessRate();
        $this->iBitsPerSample = $iBitsPerSample;
        $this->iNumChannels   = clamp($iChannelMode, self::I_CHAN_MONO, self::I_CHAN_STEREO);
        $this->iQuantize      = (1 << ($this->iBitsPerSample - 1)) - 1;
    }

    /**
     * Destructor, ensures output is closed
     */
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
        dprintf(
            "%s opened %s for output, using %d channel(s) at %d Hz, %d bits\n",
            self::class,
            $sPath,
            $this->iNumChannels,
            $this->iSampleRate,
            $this->iBitsPerSample
        );
        $this->reserveHeader();
    }

    /**
     * @inheritdoc
     */
    public function close() {
        if ($this->rOutput) {
            $this->writeHeader();
            fclose($this->rOutput);
            $this->rOutput = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function write(Packet $oPacket) {
        $aOutput = $oPacket
            ->quantize($this->iQuantize, -$this->iQuantize, $this->iQuantize)
            ->toArray();
        fwrite($this->rOutput, pack('v*', ...$aOutput));
    }

    /**
     * Reserve the header storage on opening the file
     */
    private function reserveHeader() {
        fwrite($this->rOutput, str_repeat('-', self::I_HEADER_SIZE));
    }

    /**
     * Rewinds and writes the header on closing the file
     */
    private function writeHeader() {
        $aHeader     = self::M_HEADER;
        $iFileSize   = ftell($this->rOutput);
        $iBlockAlign = ($this->iNumChannels * $this->iBitsPerSample) >> 3;

        $aHeader['iChunkSize']     = $iFileSize - 8;
        $aHeader['iSubChunk2Size'] = $iFileSize - self::I_HEADER_SIZE;
        $aHeader['iNumChannels']   = $this->iNumChannels;
        $aHeader['iSampleRate']    = $this->iSampleRate;
        $aHeader['iByteRate']      = $this->iSampleRate * $iBlockAlign;
        $aHeader['iBlockAlign']    = $iBlockAlign;
        $aHeader['iBitsPerSample'] = $this->iBitsPerSample;

        // Todo - other properties
        rewind($this->rOutput);
        fwrite(
            $this->rOutput,
            pack(self::S_HEADER_PACK, ...array_values($aHeader))
        );
    }
}
