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

/**
 * Control
 */
namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use \SPLFixedArray;
use function ABadCafe\Synth\Utility\clamp;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

abstract class Base implements IOscillator {

    protected Signal\IGenerator $oGenerator;
    protected Signal\Control\Packet
        $oGeneratorInput,
        $oLastOutput
    ;
    protected int   $iSamplePosition = 0;
    protected float $fPhaseCorrection = 0;

    /**
     * Constructor.
     *
     * @param Signal\IGenerator $oGenerator
     */
    public function __construct(Signal\IGenerator $oGenerator) {
        $this->oGenerator       = $oGenerator;
        $this->oGeneratorInput  = new Signal\Control\Packet();
        $this->oLastOutput      = new Signal\Control\Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return sprintf(
            "%s [%s]",
            static::class,
            get_class($this->oGenerator),
        );
    }
}
