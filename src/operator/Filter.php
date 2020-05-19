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
use ABadCafe\Synth\Map;

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

    private Signal\Audio\IFilter $oFilter;

    private ?Signal\Control\IStream
        $oCutoffControl    = null,
        $oResonanceControl = null
    ;

    private array
        /** @var IOperator[] $aOperators */
        $aOperators = [],

        /** @var float[] $aLevels */
        $aLevels    = [],

        $aNoteMapForwards = []
    ;

    private int $iPosition  = 0;

    /**
     * Constructor
     *
     * @param Signal\Audop\IFilter   $oFilter
     * @param Signal\Control\IStream $oCutoffControl    (optional)
     * @param Signal\Control\IStream $oResonanceControl (optional)
     */
    public function __construct(
        Signal\Audio\IFilter $oFilter,
        Signal\Control\IStream $oCutoffControl    = null,
        Signal\Control\IStream $oResonanceControl = null
    ) {
        $this->oFilter           = $oFilter;
        $this->oLastPacket       = new Signal\Audio\Packet();
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
    public function reset() : Signal\Audio\IStream {
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
    public function emitPacketForIndex(int $iPacketIndex) : Signal\Audio\Packet {
        $this->iPosition += Signal\Context::get()->getPacketLength();
        if ($iPacketIndex == $this->iPacketIndex) {
            return $this->oLastPacket;
        }

        $this->oLastPacket->fillWith(0);
        foreach ($this->aOperators as $iInstanceID => $oOperator) {
            $this->oLastPacket->accumulate($oOperator->emitPacketForIndex($iPacketIndex), $this->aLevels[$iInstanceID]);
        }

        if ($this->oCutoffControl && $this->oFilter instanceof Signal\Audio\Filter\ICutoffControlled) {
            $this->oFilter->setCutoffControl($this->oCutoffControl->emit());
        }
        if ($this->oResonanceControl && $this->oFilter instanceof Signal\Audio\Filter\IResonanceControlled) {
            $this->oFilter->setResonanceControl($this->oResonanceControl->emit());
        }

        $this->oLastPacket = $this->oFilter->filter($this->oLastPacket);

        $this->iPacketIndex = $iPacketIndex;
        return $this->oLastPacket;
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
    public function getNoteNumberMap(string $sUseCase) : Map\Note\IMIDINumber {
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
    public function setNoteNumber(int $iNote) : self {
        if ($this->oCutoffControl instanceof Map\Note\IMIDINumberAware) {
            $this->oCutoffControl->setNoteNumber($iNote);
        }
        if ($this->oResonanceControl instanceof Map\Note\IMIDINumberAware) {
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
    public function setNoteName(string $sNote) : self {
        if ($this->oCutoffControl instanceof Map\Note\IMIDINumberAware) {
            $this->oCutoffControl->setNoteName($sNote);
        }
        if ($this->oResonanceControl instanceof Map\Note\IMIDINumberAware) {
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
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : self {
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
        if ($this->oCutoffControl instanceof Map\Note\IMIDINumberAware) {
            $aCutoffCases = $this->oCutoffControl->getNoteNumberMapUseCases();
            foreach ($aCutoffCases as $sCutoffUseCase) {
                $this->aNoteMapForwards[self::S_CUTOFF_PREFIX . $sCutoffUseCase] = (object)[
                    'oControl' => $this->oCutoffControl,
                    'sUseCase' => $sCutoffUseCase
                ];
            }
        }
        if ($this->oResonanceControl instanceof Map\Note\IMIDINumberAware) {
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
