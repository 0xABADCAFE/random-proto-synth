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

namespace ABadCafe\Synth\Signal\Audio\Stream\Amplifier;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Amplifier\Fixed
 *
 * Implements a simple Amplifier for an input Signal\Audio\IStream, controlled by some Control\IStream
 */
class Fixed extends Base {

    protected float $fLevel;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInput - audio source
     * @param float                $fLevel
     */
    public function __construct(Signal\Audio\IStream $oInput, float $fLevel) {
        parent::__construct($oInput);
        $this->fLevel = $fLevel;
    }

    /**
     * @return float
     */
    public function getLevel() : float {
        return $this->fLevel;
    }

    /**
     * @param  float $fLevel
     * @return self
     */
    public function setLevel(float $fLevel) : self {
        $this->fLevel = $fLevel;
        return $this;
    }

    /**
     * @overridden
     */
    protected function emitNew() : Signal\Audio\Packet {
        $this->oLastOutputPacket->copyFrom($this->oInput->emit($this->iLastIndex));
        return $this->oLastOutputPacket->scaleBy($this->fLevel);
    }
}
