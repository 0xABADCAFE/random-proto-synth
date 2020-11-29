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
 * IMixer
 *
 * Top level interface for audio stream mixers
 */
interface IMixer extends Signal\Audio\IStream {

    /**
     * Add a named input stream to the mix. If a stream already exists with then given name, it will be replaced.
     *
     * @param  string               $sName
     * @param  Signal\Audio\IStream $oStream
     * @param  float                $fInitialLevel
     * @return self
     */
    public function addStream(string $sName, Signal\Audio\IStream $oStream, float $fInitialLevel) : self;

    /**
     * Removes a named input stream. No errors are raised if the named stream does not exist.
     *
     * @param  string $sName
     * @return self
     */
    public function removeStream(string $sName) : self;

    /**
     * Returns true if the mixer has no active inputs
     *
     * @return bool
     */
    public function isSilent() : bool;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IAmplifier
 *
 * Tag interface for Amplifiers
 */
interface IAmplifier extends Signal\Audio\IStream {

}

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
