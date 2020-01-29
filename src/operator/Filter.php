<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Filter\IFilter;
use ABadCafe\Synth\Signal\Filter\IResonant;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ControlledFilter
 *
 * Basic summing output implementation of IOperator. Acts as a fixed mixer.
 */
class ControlledFilter extends Base implements IProcessor {

    /** @var IFilter $oFilter */
    private $oFilter;

    /** @var IStream $oCutoffControl */
    private $oCutoffControl    = null;

    /** @var IStream $oResonanceControl */
    private $oResonanceControl = null;

    /** @var IOperator[] $aOperators */
    private $aOperators = [];

    /** @var float[] $aLevels */
    private $aLevels    = [];

    /** @var int $iPosotion */
    private $iPosition  = 0;

    /**
     * Constructor
     *
     * @param IFilter $oFilter
     * @param IStream $oCutoffControl    (optional)
     * @param IStream $oResonanceControl (optional)
     */
    public function __construct(
        IFilter $oFilter,
        IStream $oCutoffControl    = null,
        IStream $oResonanceControl = null
    ) {
        $this->oFilter           = $oFilter;
        $this->oLastPacket       = new Packet();
        $this->oCutoffControl    = $oCutoffControl;
        $this->oResonanceControl = $oResonanceControl;
        $this->assignInstanceID();
    }

    /**
     * @inheritdoc
     * @see IOperator
     *
     * The InputKind is ignored. All inputs to Filter are signal inputs.
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
        $this->oFilter->reset();
        if ($this->oCutoffControl) {
            $this->oCutoffControl->reset();
        }
        if ($this->oResonanceControl) {
            $this->oResonanceControl->reset();
        }
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

        if ($this->oCutoffControl) {
            $this->oFilter->setCutoffControl($this->oCutoffControl->emit());
        }
        if ($this->oResonanceControl && $this->oFilter instanceof IResonant) {
            $this->oFilter->setResonanceControl($this->oResonanceControl->emit());
        }

        $this->oLastPacket = $this->oFilter->filter($this->oLastPacket);

        $this->iPacketIndex = $iPacketIndex;
        return $this->oLastPacket;;
    }
}
