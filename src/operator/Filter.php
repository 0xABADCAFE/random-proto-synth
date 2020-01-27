<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Filter\IFilter;
use ABadCafe\Synth\Signal\Filter\IResonant;
use ABadCafe\Synth\Envelope\IGenerator as IEnvelopeGenerator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Summing
 *
 * Basic summing output implementation of IOperator. Acts as a fixed mixer.
 */
class EnvelopedFilter extends Base implements IProcessor {

    /** @var IFilter $oFilter */
    private $oFilter;

    /** @var IEnvelopeGenerator $oCutoffEnvelope */
    private $oCutoffEnvelope    = null;

    /** @var IEnvelopeGenerator $oResonanceEnvelope */
    private $oResonanceEnvelope = null;

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
     * @param IEnvelopeGenerator $oCutoffEnvelope    (optional)
     * @param IEnvelopeGenerator $oResonanceEnvelope (optional)s
     */
    public function __construct(
        IFilter $oFilter,
        IEnvelopeGenerator $oCutoffEnvelope    = null,
        IEnvelopeGenerator $oResonanceEnvelope = null
    ) {
        $this->oFilter            = $oFilter;
        $this->oLastPacket        = new Packet();
        $this->oCutoffEnvelope    = $oCutoffEnvelope;
        $this->oResonanceEnvelope = $oResonanceEnvelope;
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
        if ($this->oCutoffEnvelope) {
            $this->oCutoffEnvelope->reset();
        }
        if ($this->oResonanceEnvelope) {
            $this->oResonanceEnvelope->reset();
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

        if ($this->oCutoffEnvelope) {
            $this->oFilter->setCutoffControl($this->oCutoffEnvelope->emit());
        }
        if ($this->oResonanceEnvelope && $this->oFilter instanceof IResonant) {
            $this->oFilter->setResonanceControl($this->oResonanceEnvelope->emit());
        }

        $this->oLastPacket = $this->oFilter->filter($this->oLastPacket);

        $this->iPacketIndex = $iPacketIndex;
        return $this->oLastPacket;;
    }
}
