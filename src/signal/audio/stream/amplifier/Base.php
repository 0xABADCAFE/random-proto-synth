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
abstract class Base extends Signal\Audio\Stream\BaseSingleStreamProcessor implements Signal\Audio\Stream\IAmplifier {

    use Signal\Audio\TStreamIndexed;

    protected Signal\Audio\Packet $oLastOutputPacket;

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInputStream - audio source
     * @param float                $fLevel
     */
    public function __construct(?Signal\Audio\IStream $oInputStream) {
        $this->oInputStream = $oInputStream;
        $this->oLastOutputPacket = new Signal\Audio\Packet();
    }

    public function setInputStream(Signal\Audio\IStream $oInputStream) : self {
        $this->oInputStream = $oInputStream;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iLastIndex = 0;
        $this->iPosition  = 0;
        $this->oLastOutputPacket->fillWith(0.0);
        //$this->oInputStream->reset();
        return $this;
    }

}

