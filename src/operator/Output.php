<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Output\IPCMOutput;

use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * PCMOutput
 *
 * Extension of the basic Summing operator that pushes to an IPCMOutput stream.
 */
class PCMOutput extends Summing implements IOutput {

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
     * Render to file. Does not reset the operator so that subsequent calls continue to render from the last generated packet.
     *
     * @param  float  $fSeconds
     * @return IOutput
     * @throws IOException
     */
    public function render(float $fSeconds) : IOutput {
        $iMaxSamples = $this->getPosition() + (int)$fSeconds * Context::get()->getProcessRate();
        $fStart = microtime(true);
        do {
            $this->emit();
        } while ($this->getPosition() < $iMaxSamples);
        $fElapsed = microtime(true) - $fStart;
        dprintf(
            "Generated %.3f seconds in %.3f seconds [%.3fx realtime]\n",
            $fSeconds,
            $fElapsed,
            $fSeconds / $fElapsed
        );
        return $this;
    }

    /**
     * Open the output file
     *
     * @param  string $sPath
     * @return self   fluent
     * @throws IOException
     */
    public function open(string $sPath) : self {
        $this->oPCMOutput->open($sPath);
        return $this;
    }

    /**
     * Close the output file
     *
     * @return self fluent
     */
    public function close() : self {
        $this->oPCMOutput->close();
        return $this;
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function emit() : Signal\IPacket {
        $oPacket = parent::emit();
        $this->oPCMOutput->write($oPacket);
        return $oPacket;
    }
}
