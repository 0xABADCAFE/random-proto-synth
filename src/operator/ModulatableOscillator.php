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
use ABadCafe\Synth\Oscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Modulatable
 *
 * Simple modulatable Operator implementation. Supports E_AMPLITUDE and E_PHASE modulation inputs.
 */
class ModulatableOscillator extends UnmodulatedOscillator implements IAmplitudeModulated, IPhaseModulated {

    protected array
        /** @var IOperator[] $aModulators - keyed by instance ID */
        $aModulators = [],

        /** @var float[] $aPhaseModulationIndex - keyed by instance ID */
        $aPhaseModulationIndex = [],

        /** @var float[] $aAmplidudeModulationIndex - keyed by instance ID */
        $aAmplitudeModulationIndex = []
    ;

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->oOscillator->getPosition();
    }

    /**
     * @inheritdoc
     *
     * This Operator only accepts modulating inputs. An E_SIGNAL input will be interpreted E_AMPLITUDE.
     */
    public function attachInput(
        IOperator $oOperator,
        float     $fLevel,
        InputKind $oKind = null
    ) : IOperator {
        $iKind = $oKind ? $oKind->getValue() : InputKind::E_PHASE;
        switch ($oKind->getValue()) {
            case InputKind::E_SIGNAL;
            case InputKind::E_AMPLITUDE:
                return $this->attachAmplitudeModulatorInput($oOperator, $fLevel);
            case InputKind::E_PHASE:
                return $this->attachPhaseModulatorInput($oOperator, $fLevel);
        }
        return $this;
    }

    /**
     * @inheritdoc
     * @see IAmplitudeModulated
     */
    public function attachAmplitudeModulatorInput(IOperator $oOperator, float $fLevel) : self {
        $this->aModulators[$oOperator->iInstanceID]               = $oOperator;
        $this->aAmplitudeModulationIndex[$oOperator->iInstanceID] = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     * @see IPhaseModulated
     */
    public function attachPhaseModulatorInput(IOperator $oOperator, float $fLevel) : self {
        $this->aModulators[$oOperator->iInstanceID]           = $oOperator;
        $this->aPhaseModulationIndex[$oOperator->iInstanceID] = $fLevel;
        return $this;
    }

    /**
     * This is called whenever the emit() is invoked and useLast() returns false. This ensures we don;t repeatedly call stuff
     * in an unnecessary way.
     *
     * @inheritDoc
     */
    protected function emitNew() : Signal\Audio\Packet {

        $oPhaseAccumulator     = empty($this->aPhaseModulationIndex)     ? null : new Signal\Audio\Packet();
        $oAmplitudeAccumulator = empty($this->aAmplitudeModulationIndex) ? null : new Signal\Audio\Packet();
        foreach ($this->aModulators as $iInstanceID => $oOperator) {
            $oPacket = $oOperator->emit($this->iLastIndex);
            if (isset($this->aPhaseModulationIndex[$iInstanceID])) {
                $oPhaseAccumulator->accumulate($oPacket, $this->aPhaseModulationIndex[$iInstanceID]);
            }
            if (isset($this->aAmplitudeModulationIndex[$iInstanceID])) {
                $oAmplitudeAccumulator->accumulate($oPacket, $this->aAmplitudeModulationIndex[$iInstanceID]);
            }
        }

        // Apply any phase modulation
        if ($oPhaseAccumulator) {
            $this->oOscillator->setPhaseModulation($oPhaseAccumulator);
        }

        // Apply any pitch control
        if ($this->oPitchControl) {
            $this->oOscillator->setPitchModulation($this->oPitchControl->emit($this->iLastIndex));
        }

        // If we have a volume control, get the amplifier output. Otherwise, the raw oscillator
        if ($this->oAmplifier) {
            $this->oLastPacket = $this->oAmplifier->emit($this->iLastIndex);
        } else {
            $this->oLastPacket = $this->oOscillator->emit($this->iLastIndex);
        }

        // Apply any amplitude modulation
        if ($oAmplitudeAccumulator) {
            $this->oLastPacket->modulateWith($oAmplitudeAccumulator);
        }

        return $this->oLastPacket;
    }

}
