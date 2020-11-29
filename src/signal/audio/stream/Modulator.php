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
class Modulator implements Signal\Audio\IStream {

    use Signal\TContextIndexAware;

    private int                  $iPosition = 0;
    private Signal\Audio\Packet  $oLastPacket;
    private Signal\Audio\IStream $oInput1;
    private Signal\Audio\IStream $oInput2;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInput1 - audio stream 1
     * @param Signal\Audio\IStream $oInput2 - audio stream 2
     */
    public function __construct(Signal\Audio\IStream $oInput1, Signal\Audio\IStream $oInput2) {
        $this->oInput1     = $oInput1;
        $this->oInput2     = $oInput2;
        $this->oLastPacket = new Signal\Audio\Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->oInput1->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iLastIndex = 0;
        $this->oLastPacket->fillWith(0);
        $this->oInput1->reset();
        $this->oInput2->reset();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Audio\Packet {
        if ($this->useLast($iIndex)) {
            return $this->oLastPacket;
        }
        return $this->emitNew();
    }

    /**
     * @return Audio\Packet
     */
    private function emitNew() : Signal\Audio\Packet {
        $this->oLastPacket->copyFrom($this->oInput1->emit($this->iLastIndex));
        $this->oLastPacket->modulateWith($this->oInput2->emit($this->iLastIndex));
        return $this->oLastPacket;
    }
}
