<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use AbadCafe\Synth\Output\IPCMOutput;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////s

/**
 * Basic summing output implementation of IOutputOperator
 */
class Output implements IOutputOperator {

    /** @var IOperator[] $aOperators */
    private $aOperators = [];

    /** @var float[] $aLevels */
    private $aLevels    = [];

    /** @var int $iPosotion */
    private $iPosition  = 0;

    /**
     * @inheritdoc
     */
    public function attachOperator(IOperator $oOperator, float $fLevel) : IOutputOperator {
        $iInstanceID = $oOperator->getInstanceID();
        $this->aOperators[$iInstanceID] = $oOperator;
        $this->aLevels[$iInstanceID]    = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function emit() : Packet {
        $oPacket = new Packet();
        foreach ($this->aOperators as $iInstanceID => $oOperator) {
            $oPacket->accumulate($oOperator->emit(), $this->aLevels[$iInstanceID]);
        }
        $this->iPosition += Context::get()->getPacketLength();
        return $oPacket;
    }

    /**
     * @inheritdoc
     */
    public function reset() : IStream {
        $this->iPosition = 0;
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->iPosition;
    }

}

/**
 * Extension of the basic Output that uses an IPCMOutput stream for writing
 */
class PCMOutput extends Output {

    /** @var IPCMOutput $oOutput */
    private $oPCMOutput;

    /**
     * Constructor, inject the desired file output type
     *
     * @param IPCMOutput $oPCMOutput
     */
    public function __construct(IPCMOutput $oPCMOutput) {
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
     */
    public function emit() : Packet {
        $oPacket = parent::emit();
        $this->oPCMOutput->write($oPacket);
        return $oPacket;
    }
}
