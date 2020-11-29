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

namespace ABadCafe\Synth\Signal\Audio\Stream;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Modulator
 *
 * Performs amplitude modulation of two Signal\Audio\IStream
 */
class Modulator implements Processor {

    use Signal\Audio\TStreamIndexed;

    private int                  $iPosition = 0;
    private Signal\Audio\IStream $oInputStream1;
    private Signal\Audio\IStream $oInputStream2;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInputStream1 - audio stream 1
     * @param Signal\Audio\IStream $oInputStream2 - audio stream 2
     */
    public function __construct(Signal\Audio\IStream $oInputStream1, Signal\Audio\IStream $oInputStream2) {
        $this->oInputStream1           = $oInputStream1;
        $this->oInputStream2           = $oInputStream2;
        $this->oLastOutputPacket = new Signal\Audio\Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->oInputStream1->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iLastIndex = 0;
        $this->oLastOutputPacket->fillWith(0);
        $this->oInputStream1->reset();
        $this->oInputStream2->reset();
        return $this;
    }


    /**
     * @return Signal\Audio\Packet
     */
    protected function emitNew() : Signal\Audio\Packet {
        $this->oLastOutputPacket->copyFrom($this->oInputStream1->emit($this->iLastIndex));
        $this->oLastOutputPacket->modulateWith($this->oInputStream2->emit($this->iLastIndex));
        return $this->oLastOutputPacket;
    }
}
