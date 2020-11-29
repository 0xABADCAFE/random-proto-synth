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
 * Amplifier\Base
 *
 * Common base fpr IAmplifier implementations
 */
abstract class Base implements Signal\Audio\Stream\IAmplifier {

    use Signal\TContextIndexAware;

    protected Signal\Audio\Packet  $oLastPacket;
    protected Signal\Audio\IStream $oInput;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInput - audio source
     * @param float                $fLevel
     */
    public function __construct(Signal\Audio\IStream $oInput) {
        $this->oInput      = $oInput;
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
     * @return Signal\Audio\Packet
     */
    protected abstract function emitNew() : Signal\Audio\Packet;

}

