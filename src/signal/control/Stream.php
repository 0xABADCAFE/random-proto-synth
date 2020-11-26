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

namespace ABadCafe\Synth\Signal\Control\Stream;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FixedMixer
 *
 * Implements a simple fixed level mixer for a set of Control\IStreams, each with their own level.
 */
class FixedMixer implements Signal\Control\IStream {

    use Signal\TContextIndexAware;

    private int    $iPosition = 0;
    private array  $aStreams  = [];
    private array  $aLevels   = [];
    private Signal\Control\Packet $oLastPacket;

    /**
     * Constructor
     */
    public function __construct() {
        $this->oLastPacket = new Signal\Control\Packet();
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
    public function emit(?int $iIndex = null) : Signal\Control\Packet {
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
    public function addStream(string $sName, Signal\Control\IStream $oStream, float $fLevel) : self {
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
    private function emitNew() : Signal\Control\Packet {
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
 * SemitonesToScale
 *
 * Converts a Control Stream expressed as pitch control in semitones to a frequency multiplier.
 */
class SemitonesToScale implements Signal\Control\IStream {

    const F_INV_TWELVE = 1.0/12.0;

    use Signal\TContextIndexAware;

    private Signal\Control\IStream $oInput;
    private Signal\Control\Packet  $oLastPacket;

    /**
     * @param Signal\Control\IStream $oInput
     */
    public function __construct(Signal\Control\IStream $oInput) {
        $this->oInput      = $oInput;
        $this->oLastPacket = new Signal\Control\Packet();
        $this->oLastPacket->fillWith(1.0);
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
        $this->oLastPacket->fillWith(1.0);
        $this->oInput->reset();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Control\Packet {
        if ($this->useLast($iIndex)) {
            return $this->oLastPacket;
        }
        return $this->emitNew();
    }

    /**
     * @return Signal\Control\Packet
     */
    private function emitNew() : Signal\Control\Packet {
        $oSemitoneValues   = $this->oInput->emit($this->iLastIndex)->getValues();
        $oMultiplierValues = $this->oLastPacket->getValues();
        foreach ($oSemitoneValues as $i => $fSemitone) {
            $oMultiplierValues[$i] = 2.0 ** ($fSemitone * self::F_INV_TWELVE);
        }
        return $this->oLastPacket;
    }
}
