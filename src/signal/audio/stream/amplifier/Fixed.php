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

    protected float $fFixedLevel;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInputStream - audio source
     * @param float                $fFixedLevel
     */
    public function __construct(Signal\Audio\IStream $oInputStream, float $fFixedLevel) {
        parent::__construct($oInputStream);
        $this->fFixedLevel = $fFixedLevel;
    }

    /**
     * @return float
     */
    public function getLevel() : float {
        return $this->fFixedLevel;
    }

    /**
     * @param  float $fFixedLevel
     * @return self
     */
    public function setLevel(float $fFixedLevel) : self {
        $this->fFixedLevel = $fFixedLevel;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isSilent() : bool {
        return abs($this->fFixedLevel) <= 1e-6;
    }

    /**
     * @overridden
     */
    protected function emitNew() : Signal\Audio\Packet {
        $this->oLastOutputPacket->copyFrom($this->oInputStream->emit($this->iLastIndex));
        return $this->oLastOutputPacket->scaleBy($this->fFixedLevel);
    }
}
