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
 * BaseSingleStreamProcessor
 *
 * @implements ISingleStreamProcessor
 *
 * Simple common base implementation for ISingleStreamProcessor implementors (not mandatory). Since we can't rely on
 * having an input stream set, also provides it's own stream positon counter.
 */
abstract class BaseSingleStreamProcessor implements ISingleStreamProcessor {

    protected ?Signal\Audio\IStream $oInputStream = null;
    protected int $iPosition = 0;

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->iPosition;
    }

    /**
     * @inheritDoc
     */
    public function hasInput() : bool {
        return null !== $this->oInputStream;
    }

    /**
     * @inheritDoc
     */
    public function setInput(Signal\Audio\IStream $oInputStream) : self {
        $this->oInputStream = $oInputStream;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInput() : ?Signal\Audio\IStream {
        return $this->oInputStream;
    }
}
