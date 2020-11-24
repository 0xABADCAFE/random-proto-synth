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

/**
 * Audio
 */
namespace ABadCafe\Synth\Signal\Audio;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Concrete Audio Packet
 *
 */
class Packet implements Signal\IPacket {

    use Signal\TPacketImplementation;

    public function levelControl(Signal\Control\Packet $oLevel) : self {
        $oLevelValues = $oLevel->getValues();
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $oLevelValues[$i];
        }
        return $this;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Interface for audio stream generators.
 *
 */
interface IStream extends Signal\IStream {

    /**
     * Reset the stream
     *
     * @inheritDoc
     */
    public function reset() : self; // Covariant return

    /**
     * Emit a Packet
     *
     * @inheritDoc
     */
    public function emit(?int $iIndex = null) : Packet; // Covariant return
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FixedMixer
 *
 * Implements a fixed level mixer for a set IStreams, each with their own level.
 */
class FixedMixer implements IStream {

    use Signal\TContextIndexAware;

    private int    $iPosition = 0;
    private array  $aStreams  = [];
    private array  $aLevels   = [];
    private Packet $oLastPacket;

    /**
     * Constructor
     */
    public function __construct() {
        $this->oLastPacket = new Packet();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->iPosition;
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
    public function emit(?int $iIndex = null) : Packet {
        if (empty($this->aLevels) || $this->useLast($iIndex)) {
            return $this->oLastPacket;
        }
        return $this->emitNew();
    }

    /**
     * @param  IStream $oStream
     * @param  float   $fLevel
     * @return self
     */
    public function add(IStream $oStream, float $fLevel) : self {
        if (abs($fLevel) > 0.0) {
            $this->aStreams[] = $oStream;
            $this->aLevels[]  = $fLevel;
        }
        return $this;
    }

    /**
     * @return Packet
     */
    private function emitNew() : Packet {
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
 * IFilter
 *
 * Main audio signal filter interface. The filter cutoff is normalised such that the range 0.0 - 1.0 covers the full
 * frequency range.
 */
interface IFilter {
    const
        F_MIN_CUTOFF = 0.001,
        F_DEF_CUTOFF = 0.5,
        F_MAX_CUTOFF = 1.0
    ;

    /**
     * Reset the filter, re-initialising all internal state.
     *
     * @return self.
     */
    public function reset() : self;

    /**
     * Set the cutoff. Uses a normalied scale in which 1.0 is the highest stable setting
     * supported by the filter.
     *
     * @param  float $fCutoff - 0 < $fCutoff <= 1.0
     * @return self
     */
    public function setCutoff(float $fCutoff) : self;

    /**
     * Get the cutoff. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getCutoff() : float;

    /**
     * Filter a Packet
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function filter(Packet $oInput) : Packet;
}
