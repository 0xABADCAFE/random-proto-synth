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

namespace ABadCafe\Synth\Operator;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Summing
 *
 * Basic summing output implementation of IOperator. Acts as a fixed mixer.
 */
class Summing extends Base implements IOperator, IProcessor {

    private array
        /** @var IOperator[] $aOperators */
        $aOperators = [],

        /** @var float[] $aLevels */
        $aLevels    = []
    ;

    private int $iPosition  = 0;

    public function __construct() {
        $this->oLastPacket = new Signal\Audio\Packet();
        $this->assignInstanceID();
    }

    /**
     * @inheritdoc
     * @see IOperator
     *
     * The InputKind parameter is ignored, all inputs are treated as E_SIGNAL
     */
    public function attachInput(IOperator $oOperator, float $fLevel, InputKind $oKind = null) : self {
        return $this->attachSignalInput($oOperator, $fLevel);
    }

    /**
     * @inheritdoc
     * @see IProcessor
     */
    public function attachSignalInput(IOperator $oOperator, float $fLevel) : self {
        $iInstanceID = $oOperator->getInstanceID();
        $this->aOperators[$iInstanceID] = $oOperator;
        $this->aLevels[$iInstanceID]    = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     * @see IStream
     */
    public function reset() : self {
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
    public function emitNew() : Signal\Audio\Packet {
        $this->iPosition += Signal\Context::get()->getPacketLength();
        $this->oLastPacket->fillWith(0);
        foreach ($this->aOperators as $iInstanceID => $oOperator) {
            $this->oLastPacket->accumulate($oOperator->emit($this->iLastIndex), $this->aLevels[$iInstanceID]);
        }
        return $this->oLastPacket;
    }
}
