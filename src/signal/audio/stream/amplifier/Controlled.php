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
 * Amplifier\Controlled
 *
 * Implements a simple Amplifier for an input Signal\Audio\IStream, controlled by some Control\IStream
 */
class Controlled extends Base {

    private Signal\Control\IStream $oControlStream;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream   $oInputStream   - audio source
     * @param Signal\Control\IStream $oControlStream - control source
     */
    public function __construct(Signal\Audio\IStream $oInputStream, Signal\Control\IStream $oControlStream) {
        parent::__construct($oInputStream);
        $this->oInputStream   = $oInputStream;
        $this->oControlStream = $oControlStream;
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        parent::__reset();
        $this->oControlStream->reset();
        return $this;
    }

    /**
     * @overridden
     */
    protected function emitNew() : Signal\Audio\Packet {
        $this->oLastOutputPacket->copyFrom($this->oInputStream->emit($this->iLastIndex));
        return $this->oLastOutputPacket->levelControl($this->oControlStream->emit($this->iLastIndex));
    }
}

