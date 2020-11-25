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
 * FixedMixer
 *
 * Implements a simple fixed level mixer for a set of Audio\IStreams, each with their own level.
 */
class FixedMixer implements Signal\Audio\IStream {

    use Signal\TContextIndexAware;

    private int    $iPosition = 0;
    private array  $aStreams  = [];
    private array  $aLevels   = [];
    private Signal\Audio\Packet $oLastPacket;

    /**
     * Constructor
     */
    public function __construct() {
        $this->oLastPacket = new Signal\Audio\Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->iPosition;
    }

    /**
     * Returns true if the mixer has no inputs to mix.
     */
    public function isSilent() : bool {
        return empty($this->aStreams);
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iPosition  = 0;
        $this->iLastIndex = 0;
        $this->oLastPacket->fillWith(0);
        foreach ($this->aStreams as $oStream) {
            $oStream->reset();
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Audio\Packet {
        $this->iPosition += Signal\Context::get()->getPacketLength();
        if (empty($this->aLevels) || $this->useLast($iIndex)) {
            return $this->oLastPacket;
        }
        return $this->emitNew();
    }

    /**
     * Adds a named stream, overwriting any existing stream of the same name,
     *
     * @param  string  $sName
     * @param  IStream $oStream
     * @param  float   $fLevel
     * @return self
     */
    public function addStream(string $sName, Signal\Audio\IStream $oStream, float $fLevel) : self {
        $this->aStreams[$sName] = $oStream;
        $this->aLevels[$sName]  = $fLevel;
        return $this;
    }

    /**
     * Remove a named stream
     *
     * @return self
     */
    public function removeStream(string $sName) : self {
        unset($this->aStreams[$sName]);
        unset($this->aLevels[$sName]);
        return $this;
    }

    /**
     * @return Packet
     */
    private function emitNew() : Signal\Audio\Packet {
        $this->oLastPacket->fillWith(0.0);
        foreach ($this->aStreams as $i => $oStream) {
            $this->oLastPacket->accumulate(
                $oStream->emit($this->iLastIndex),
                $this->aLevels[$i]
            );
        }
        return $this->oLastPacket;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Amplifier
 *
 * Implements a simple Amplifier for an input Signal\Audio\IStream, controlled by some Control\IStream
 */
class Amplifier implements Signal\Audio\IStream {

    use Signal\TContextIndexAware;

    private Signal\Audio\Packet    $oLastPacket;
    private Signal\Audio\IStream   $oInput;
    private Signal\Control\IStream $oControl;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream   $oInput   - audio source
     * @param Signal\Control\IStream $oControl - control source
     */
    public function __construct(Signal\Audio\IStream $oInput, Signal\Control\IStream $oControl) {
        $this->oInput      = $oInput;
        $this->oControl    = $oControl;
        $this->oLastPacket = new Signal\Audio\Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->oInput->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iLastIndex = 0;
        $this->oLastPacket->fillWith(0);
        $this->oInput->reset();
        $this->oControl->reset();
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
        $this->oLastPacket->copyFrom($this->oInput->emit($this->iLastIndex));
        $this->oLastPacket->levelControl($this->oControl->emit($this->iLastIndex));
        return $this->oLastPacket;
    }
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
