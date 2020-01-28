<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use AbadCafe\Synth\Output\IPCMOutput;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PCMOutput
 *
 * Extension of the basic Summing operator that pushes to an IPCMOutput stream.
 */
class PCMOutput extends Summing {

    /** @var IPCMOutput $oOutput */
    private $oPCMOutput;

    /**
     * Constructor, inject the desired file output type
     *
     * @param IPCMOutput $oPCMOutput
     */
    public function __construct(IPCMOutput $oPCMOutput) {
        parent::__construct();
        $this->oPCMOutput = $oPCMOutput;
    }

    /**
     * Open the output file
     *
     * @param  string $sPath
     * @throws IOException
     */
    public function open(string $sPath) {
        $this->oPCMOutput->open($sPath);
    }

    /**
     * Close the output file
     */
    public function close() {
        $this->oPCMOutput->close();
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function emit() : Packet {
        $oPacket = parent::emit();
        $this->oPCMOutput->write($oPacket);
        return $oPacket;
    }
}
