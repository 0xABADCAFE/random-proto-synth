<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Signal\Filter\IFilter;
use ABadCafe\Synth\Signal\Filter\IResonant;

use ABadCafe\Synth\Map\Note\IMIDINumber      as IMIDINoteMap;
use ABadCafe\Synth\Map\Note\Invariant        as InvariantNoteMap;
use ABadCafe\Synth\Map\Note\IMIDINumberAware as IMIDINoteMapAware;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ControlledFilter
 *
 * Basic summing output implementation of IOperator. Acts as a fixed mixer.
 */
class ControlledFilter extends Base implements IProcessor {

    const
        S_CUTOFF_PREFIX    = 'cutoff_',
        S_RESONANCE_PREFIX = 'resonance_'
    ;

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

    private $aNoteMapForwards = [];

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
        $this->configureNoteMapBehaviours();
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

    /**
     * @inheritdoc
     */
    public function getNoteNumberMapUseCases() : array {
        return array_keys($this->aNoteMapForwards);
    }


    /**
     * @inheritdoc
     *
     * Return the whichever note map use case has been mapped to either the cutoff or resonance controls.
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMap(string $sUseCase) : IMIDINoteMap {
        if (isset($this->aNoteMapForwards[$sUseCase])) {
            $oForwards = $this->aNoteMapForwards[$sUseCase];
            return $oForwards->oControl->getNoteNumberMap(
                $oForwards->sUseCase
            );
        }
        return parent::getNoteNumberMap($sUseCase);
    }

    /**
     * @inheritdoc
     *
     * Pass the note number to any mapped input controls
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : IMIDINoteMapAware {
        if ($this->oCutoffControl instanceof IMIDINoteMapAware) {
            $this->oCutoffControl->setNoteNumber($iNote);
        }
        if ($this->oResonanceControl instanceof IMIDINoteMapAware) {
            $this->oResonanceControl->setNoteNumber($iNote);
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * Pass the note map to the appropriate mapped input controls
     *
     * @see IMIDINumberAware
     */
    public function setNoteName(string $sNote) : IMIDINoteMapAware {
        if ($this->oCutoffControl instanceof IMIDINoteMapAware) {
            $this->oCutoffControl->setNoteName($sNote);
        }
        if ($this->oResonanceControl instanceof IMIDINoteMapAware) {
            $this->oResonanceControl->setNoteName($sNote);
        }
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumberMap(IMIDINoteMap $oNoteMap, string $sUseCase) : IMIDINoteMapAware {
        if (isset($this->aNoteMapForwards[$sUseCase])) {
            $oForwards = $this->aNoteMapForwards[$sUseCase];
            $oForwards->oControl->setNoteNumberMap($oNoteMap, $oForwards->sUseCase);
        }
        return $this;
    }

    /**
     * Builds the list of note map use cases. We take the filter cutoff and resonance control inputs and if they
     * support note maps, we extract them and aggregate them here. This means the filter operator supports the
     * complete set of note maps that each of it's input controls supports. We prefix the use case to ensure that
     * there is no overlap between them.
     */
    private function configureNoteMapBehaviours() {
        $this->aNoteMapForwards = [];
        if ($this->oCutoffControl instanceof IMIDINoteMapAware) {
            $aCutoffCases = $this->oCutoffControl->getNoteNumberMapUseCases();
            foreach ($aCutoffCases as $sCutoffUseCase) {
                $this->aNoteMapForwards[self::S_CUTOFF_PREFIX . $sCutoffUseCase] = (object)[
                    'oControl' => $this->oCutoffControl,
                    'sUseCase' => $sCutoffUseCase
                ];
            }
        }
        if ($this->oResonanceControl instanceof IMIDINoteMapAware) {
            $aResonanceCases = $this->oResonanceControl->getNoteNumberMapUseCases();
            foreach ($aResonanceCases as $sResonanceUseCase) {
                $this->aNoteMapForwards[self::S_RESONANCE_PREFIX . $sResonanceUseCase] = (object)[
                    'oControl' => $this->oResonanceControl,
                    'sUseCase' => $sResonanceUseCase
                ];
            }
        }
    }
}
