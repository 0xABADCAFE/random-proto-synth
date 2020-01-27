<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal\IStream;
use ABadCafe\Synth\Utility\TEnum;

require_once 'Utility.php';

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Type safe enumeration for Operator Input types.
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
 * Basic interface for Linkable Operators.
 */
interface IOperator extends IStream {
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

require_once 'operator/Base.php';
require_once 'operator/Summing.php';
require_once 'operator/ModulatedOscillator.php';
require_once 'operator/Filter.php';
require_once 'operator/Output.php';

