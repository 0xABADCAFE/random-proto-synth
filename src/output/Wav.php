<?php

namespace ABadCafe\Synth\Output;

use ABadCafe\Synth\Signal\Packet;

class Wav implements IPCMOutput {

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

    const S_HEADER_PACK = 'C4 V C4 C4 V v v V V v v C4 V';

    protected
        $rOutput = null
    ;

    public function __construct() {

    }

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
        $this->reserveHeader();
    }

    public function close() {
        if ($this->rOutput) {
            $this->writeHeader();
            fclose($this->rOutput);
            $this->rOutput = null;
        }
    }

    public function write(Packet $oPacket) {
        $aOutput = $oPacket
             ->quantize(self::I_MAX_LEVEL, self::I_MIN_LEVEL, self::I_MAX_LEVEL)
            ->toArray();
        fwrite($this->rOutput, pack('v*', ...$aOutput));
    }

    private function reserveHeader() {
        // Todo - write the first 44 bytes to be
        // replaced by the header
    }

    private function writeHeader() {
        $aHeader   = self::M_HEADER;
        $iFileSize = ftell($this->rOutput);
        $aHeader['iChunkSize']     = $iFileSize - 8;
        $aHeader['iSubChunk2Size'] = $iFileSize - 40;

        // Todo - other properties
        rewind($this->rOutput);
        fwrite(
            $this->rOutput,
            pack(self::S_HEADER_PACK, ...$aHeader)
        );
    }
}

