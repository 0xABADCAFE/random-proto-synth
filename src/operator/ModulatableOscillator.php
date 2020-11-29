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
use ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Modulatable
 *
 * Simple modulatable Operator implementation. Supports E_AMPLITUDE and E_PHASE modulation inputs.
 */
class ModulatableOscillator extends UnmodulatedOscillator implements IAmplitudeModulated, IPhaseModulated {

    protected Signal\Audio\Stream\Mixer\Fixed $oPhaseMixer;
    protected Signal\Audio\Stream\Mixer\Fixed $oAmplitudeMixer;

    /**
     * @overridden
     */
    public function __construct(
        Audio\IOscillator       $oOscillator,
        float                   $fFrequencyRatio   = 1.0,
        float                   $fDetune           = 0.0,
        Signal\Control\IStream  $oAmplitudeControl = null,
        Signal\Control\IStream  $oPitchControl     = null,
        Map\Note\IMIDINumber    $oRootNoteMap      = null
    ) {
        parent::__construct(
            $oOscillator,
            $fFrequencyRatio,
            $fDetune,
            $oAmplitudeControl,
            $oPitchControl,
            $oRootNoteMap
        );
        $this->oPhaseMixer     = new Signal\Audio\Stream\Mixer\Fixed();
        $this->oAmplitudeMixer = new Signal\Audio\Stream\Mixer\Fixed();
    }

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
        $this->oAmplitudeMixer->addStream(
            (string)$oOperator->iInstanceID,
            $oOperator,
            $fLevel
        );
        return $this;
    }

    /**
     * @inheritdoc
     * @see IPhaseModulated
     */
    public function attachPhaseModulatorInput(IOperator $oOperator, float $fLevel) : self {
        $this->oPhaseMixer->addStream(
            (string)$oOperator->iInstanceID,
            $oOperator,
            $fLevel
        );
        return $this;
    }

    /**
     * This is called whenever the emit() is invoked and useLast() returns false. This ensures we don;t repeatedly call stuff
     * in an unnecessary way.
     *
     * @inheritDoc
     */
    protected function emitNew() : Signal\Audio\Packet {

        $oPhaseAccumulator = $this->oPhaseMixer->isSilent() ? null : $this->oPhaseMixer->emit($this->iLastIndex);

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

        $oAmplitudeAccumulator = $this->oAmplitudeMixer->isSilent() ? null : $this->oAmplitudeMixer->emit($this->iLastIndex);

        // Apply any amplitude modulation
        if ($oAmplitudeAccumulator) {
            $this->oLastPacket->modulateWith($oAmplitudeAccumulator);
        }

        return $this->oLastPacket;
    }

}
