<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Oscillator\IOscillator;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Fixed
 *
 * Basic output only oscillator. Ignores all input. Intended for use as LFO, etc.
 */
class FixedOscillator extends Base {

    protected
        /** @var IOscillator $oOscillator */
        $oOscillator,

        /** @var IStream $oAmplitudeControl */
        $oAmplitudeControl,

        /** @var IStream $oPitchControl */
        $oPitchControl
    ;

    /**
     * Constructor
     *
     * @param IOscillator  $oOscillator       : Waveform generator to use   (required)
     * @param IStream|null $oAmplitudeControl : Amplitude Envelope Generator (optional)
     * @param IStream|null $oPitchControl     : Pitch Envelope Generator     (optional)
     */
    public function __construct(
        IOscillator $oOscillator,
        IStream     $oAmplitudeControl  = null,
        IStream     $oPitchControl      = null
    ) {
        $this->oOscillator       = $oOscillator;
        $this->oAmplitudeControl = $oAmplitudeControl;
        $this->oPitchControl     = $oPitchControl;
        $this->assignInstanceID();
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->oOscillator->getPosition();
    }

    /**
     * @inheritdoc
     */
    public function reset() : IStream {
        $this->oOscillator->reset();
        if ($this->oAmplitudeControl) {
            $this->oAmplitudeControl->reset();
        }
        if ($this->oPitchControl) {
            $this->oPitchControl->reset();
        }
        $this->iPacketIndex = 0;
        return $this;
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
        return $this;
    }

    /**
     * Emit a Packet for a given input Index. This is used to ensure that we don't end up repeatedly asking an Operator for subsequent Packets as a consequence
     * of it being a modulator twice in the overall algorithm lattice.
     *
     * @param  int
     * @return Packet
     */
    protected function emitPacketForIndex(int $iPacketIndex) : Packet {

        if ($iPacketIndex == $this->iPacketIndex) {
            return $this->oLastPacket;
        }

        // Apply any pitch control
        if ($this->oPitchControl) {
            $this->oOscillator->setPitchModulation($this->oPitchControl->emit());
        }

        // Get the raw oscillator output
        $oOscillatorPacket = $this->oOscillator->emit();

        // Apply any amplitude control
        if ($this->oAmplitudeControl) {
            $oOscillatorPacket->modulateWith($this->oAmplitudeControl->emit());
        }

        $this->oLastPacket        = $oOscillatorPacket;
        $this->iPacketIndex       = $iPacketIndex;
        return $this->oLastPacket;
    }
}
