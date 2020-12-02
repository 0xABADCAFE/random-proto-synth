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
 * Mixer\Fixed
 *
 * Implements a simple fixed level mixer for a set of Audio\IStreams, each with their own level.
 */
class Fixed implements Signal\Audio\Stream\IMixer {

    use Signal\Audio\TStreamIndexed;

    private int    $iPosition = 0;
    private array  $aStreams  = [];
    private array  $aLevels   = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->oLastOutputPacket = new Signal\Audio\Packet();
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
    public function hasInput() : bool {
        return false === empty($this->aStreams);
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iPosition  = 0;
        $this->iLastIndex = 0;
        $this->oLastOutputPacket->fillWith(0);
//         foreach ($this->aStreams as $oStream) {
//             $oStream->reset();
//         }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Signal\Audio\Packet {
        if (empty($this->aLevels) || $this->useLast($iIndex)) {
            return $this->oLastOutputPacket;
        }
        return $this->emitNew();
    }

    /**
     * @inheritDoc
     */
    public function addInput(string $sName, Signal\Audio\IStream $oStream, float $fLevel) : self {
        $this->aStreams[$sName] = $oStream;
        $this->aLevels[$sName]  = $fLevel;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeInput(string $sName) : self {
        unset($this->aStreams[$sName]);
        unset($this->aLevels[$sName]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInputs() : array {
        return $this->aStreams;
    }

    /**
     * @return Packet
     */
    private function emitNew() : Signal\Audio\Packet {
        $this->iPosition += Signal\Context::get()->getPacketLength();
        $this->oLastOutputPacket->fillWith(0.0);
        foreach ($this->aStreams as $i => $oStream) {
            $this->oLastOutputPacket->accumulate(
                $oStream->emit($this->iLastIndex),
                $this->aLevels[$i]
            );
        }
        return $this->oLastOutputPacket;
    }
}
