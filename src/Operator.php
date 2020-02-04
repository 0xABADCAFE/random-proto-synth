<?php

/**
 * Operator
 */
namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Oscillator\IOscillator;
use ABadCafe\Synth\Utility\TEnum;
use ABadCafe\Synth\Map\Note\IMIDINumber      as IMIDINoteMap;
use ABadCafe\Synth\Map\Note\IMIDINumberAware as IMIDINoteMapAware;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * InputKind
 *
 * Type safe enumeration for Operator Input types. Intended for use in patch file import process.
 */
final class InputKind {

    use TEnum;

    const
        E_SIGNAL    = 0,
        E_AMPLITUDE = 1,
        E_PHASE     = 2
    ;

    /**
     * @inheritdoc
     */
    protected function defineAllowedValues() : array {
        return [self::E_SIGNAL, self::E_AMPLITUDE, self::E_PHASE];
    }

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOperator
 *
 * Basic interface for linkable Operators. Operators combine Oscillators and control sources to sculpt sounds.
 */
interface IOperator extends IStream, IMIDINoteMapAware {

    /**
     * Generic input attachment: Attaches another Operator as an input. Some Operators may support more than one
     * kind of input. This can be provied by the optional last parameter.
     *
     * @param  self           $oOperator
     * @param  float          $fLevel
     * @param  InputKind|null $oKind
     * @return self
     */
    public function attachInput(self $oOperator, float $fLevel, InputKind $oKind = null) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IProcessor
 *
 * Tag interface for general signal processing operators, e.g. summing outputs or filters.
 */
interface IProcessor {

    /**
     * Add a signal input. The output of the attached input is used as a signal source.
     * The level parameter ajusts the signal power, with 1.0 being no change.
     *
     * @param  IOperator $oOperator
     * @param  float     $fLevel
     * @return self
     */
    public function attachSignalInput(IOperator $oOperator, float $fLevel) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IAmplitudeModulated
 *
 * Tag interface for operators that allow another operator's output to be used as an Amplitude Modulation source.
 */
interface IAmplitudeModulated {

    /**
     * Add an Amplitude Modulator input. The level specifies the modulation index.
     *
     * @param  IOperator $oOperator
     * @param  float     $fLevel
     * @return self
     */
    public function attachAmplitudeModulatorInput(IOperator $oOperator, float $fLevel) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IPhaseModulated
 *
 * Tag interface for operators that allow another operator's output to be used as a Phase Modulation source.
 */
interface IPhaseModulated {

    /**
     * Add a Phase Modulator input. The level specifies the modulation index
     *
     * @param  IOperator $oOperator
     * @param  float     $fLevel
     * @return self
     */
    public function attachPhaseModulatorInput(IOperator $oOperator, float $fLevel) : self;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ISource
 *
 * Tag interface for operators that generate a signal, e.g. via an oscillator
 */
interface ISource {

    /**
     * Get the principle oscillator of the source signal
     *
     * @return IOscillator
     */
    public function getOscillator() : IOscillator;

    /**
     * Get the frequency ratio for the operator. This is a multiple of the frequency of the root
     * note.
     *
     * @return float 
     */
    public function getFrequencyRatio() : float;
    
    /**
     * Get the detune amount for the operator. This is a fixed offset in Hz.
     *
     * @return float 
     */
    public function getDetune() : float;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOutput
 *
 * Tag interface for operators that render to output
 */
interface IOutput extends IProcessor {

    /**
     * Render audio. The time period requested will be converted into the nearest number of Packet lengths.
     *
     * @param  float $fSeconds
     * @return self  fluent
     */
    public function render(float $fSeconds) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'operator/Base.php';
require_once 'operator/Summing.php';
require_once 'operator/UnmodulatedOscillator.php';
require_once 'operator/ModulatableOscillator.php';
require_once 'operator/Filter.php';
require_once 'operator/Output.php';

