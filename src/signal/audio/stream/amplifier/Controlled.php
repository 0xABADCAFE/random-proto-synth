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

    private Signal\Control\IStream $oControl;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream   $oInput   - audio source
     * @param Signal\Control\IStream $oControl - control source
     */
    public function __construct(Signal\Audio\IStream $oInput, Signal\Control\IStream $oControl) {
        parent::__construct($oInput);
        $this->oInput      = $oInput;
        $this->oControl    = $oControl;
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        parent::__reset();
        $this->oControl->reset();
        return $this;
    }

    /**
     * @overridden
     */
    protected function emitNew() : Signal\Audio\Packet {
        $this->oLastOutputPacket->copyFrom($this->oInput->emit($this->iLastIndex));
        return $this->oLastOutputPacket->levelControl($this->oControl->emit($this->iLastIndex));
    }
}

