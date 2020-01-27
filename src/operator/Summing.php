<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;
use ABadCafe\Synth\Envelope\IGenerator as IEnvelopeGenerator;
use ABadCafe\Synth\Utility\IEnumeratedInstance;
use ABadCafe\Synth\Utility\TEnumeratedInstance;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Summing
 *
 * Basic summing output implementation of IOperator. Acts as a fixed mixer.
 */
class Summing extends Base implements IProcessor {

    /** @var IOperator[] $aOperators */
    private $aOperators = [];

    /** @var float[] $aLevels */
    private $aLevels    = [];

    /** @var int $iPosotion */
    private $iPosition  = 0;

    public function __construct() {
        $this->oLastPacket = new Packet();
        $this->assignInstanceID();
    }

    /**
     * @inheritdoc
     * @see IOperator
     *
     * The InputKind parameter is ignored, all inputs are treated as E_SIGNAL
     */
    public function attachInput(IOperator $oOperator, float $fLevel, InputKind $oKind = null) : IOperator {
        return $this->attachSignalInput($oOperator, $fLevel);
    }

    /**
     * @inheritdoc
     * @see IProcessor
     */
    public function attachSignalInput(IOperator $oOperator, float $fLevel) : IProcessor {
        $iInstanceID = $oOperator->getInstanceID();
        $this->aOperators[$iInstanceID] = $oOperator;
        $this->aLevels[$iInstanceID]    = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function reset() : IStream {
        $this->iPosition = 0;
        $this->oLastPacket->fillWith(0);
        return $this;
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function getPosition() : int {
        return $this->iPosition;
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function emitPacketForIndex(int $iPacketIndex) : Packet {
        $this->iPosition += Context::get()->getPacketLength();
        if ($iPacketIndex == $this->iPacketIndex) {
            return $this->oLastPacket;
        }

        $this->oLastPacket->fillWith(0);
        foreach ($this->aOperators as $iInstanceID => $oOperator) {
            $this->oLastPacket->accumulate($oOperator->emitPacketForIndex($iPacketIndex), $this->aLevels[$iInstanceID]);
        }
        $this->iPacketIndex = $iPacketIndex;
        return $this->oLastPacket;;
    }
}
