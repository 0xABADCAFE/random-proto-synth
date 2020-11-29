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

namespace ABadCafe\Synth\Signal\Audio\Stream\Mixer;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FixedMixer
 *
 * Implements a simple fixed level mixer for a set of Audio\IStreams, each with their own level.
 */
class Mixer implements Signal\Audio\Stream\IMixer {

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
