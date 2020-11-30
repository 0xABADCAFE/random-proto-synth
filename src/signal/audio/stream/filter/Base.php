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

namespace ABadCafe\Synth\Signal\Audio\Stream\Filter;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Filter\Base
 *
 * Common base for IFilter implementations
 */
abstract class Base implements Signal\Audio\Stream\IFilter {

    use Signal\Audio\TStreamIndexed;

    /** Input audio stream */
    protected Signal\Audio\IStream $oInputStream;

    /** Optional control streams for cutoff and resonance */
    protected ?Signal\Control\IStream
        $oCutoffControl,
        $oResonanceControl
    ;

    /** Fixed parameters for when there are no control streams */
    protected float
        $fFixedCutoff,
        $fFixedResonance
    ;

    /** Selected filter function, depends on which parameters are fixed and varying */
    protected $cFilterFunction;

    /** Set of possible filter functions */
    protected static $aFilterFunctionNames = [
        0 => 'processFixedCFixedQ',
        1 => 'processVaryingCFixedQ',
        2 => 'processFixedCVaryingQ',
        3 => 'processVaryingCVaryingQ',
    ];

    /**
     * Constructor
     *
     * @param Signal\Audio\IStream $oInput - audio source
     */
    public function __construct(
        Signal\Audio\IStream    $oInputStream,
        ?Signal\Control\IStream $oCutoffControl    = null,
        ?Signal\Control\IStream $oResonanceControl = null,
        float $fFixedCutoff                        = self::F_DEF_CUTOFF,
        float $fFixedResonance                     = self::F_DEF_RESONANCE
    ) {
        $this->oInputStream      = $oInputStream;
        $this->oLastOutputPacket = new Signal\Audio\Packet();
        $this->oCutoffControl    = $oCutoffControl;
        $this->oResonanceControl = $oResonanceControl;
        $this->fFixedCutoff      = $fFixedCutoff;
        $this->fFixedResonance   = $fFixedResonance;
        $this->chooseFilterFunction();
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int {
        return $this->oInputStream->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function reset() : self {
        $this->iLastIndex = 0;
        $this->oLastOutputPacket->fillWith(0);
        $this->oInput->reset();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFixedCutoff(float $fCutoff) : self {
        $this->fFixedCutoff = max($fCutoff, self:: F_MIN_CUTOFF);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFixedCutoff() : float {
        return $this->fFixedCutoff;
    }

    /**
     * @inheritDoc
     */
    public function setFixedResonance(float $fResonance) : self {
        $this->fFixedResonance = max($fResonance, self:: F_MIN_RESONANCE);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFixedResonance() : float {
        return $this->fFixedResonance;
    }

    /**
     * @inheritDoc
     */
    public function setCutoffControl(?Signal\Control\IStream $oCutoffControl) : self {
        $this->oCutoffControl = $oCutoffControl;
        $this->chooseFilterFunction();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCutoffControl() : ?Signal\Control\IStream  {
        return $this->oCutoffControl;
    }

    /**
     * @inheritDoc
     */
    public function setResonanceControl(?Signal\Control\IStream $oResonanceControl) : self {
        $this->oResonanceControl = $oResonanceControl;
        $this->chooseFilterFunction();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResonanceControl() : ?Signal\Control\IStream {
        return $this->oResonanceControl;
    }

    /**
     * Determines which filter function to use based on the current configuration
     */
    protected function chooseFilterFunction() {
        $iFunctionIndex = ($this->oCutoffControl ? 1 : 0) | ($this->oResonanceControl ? 2 : 0);
        $this->cFilterFunction = [$this, self::$aFilterFunctionNames[$iFunctionIndex]];
    }

    protected function emitNew() : Signal\Audio\Packet {
        $cFilterFunction = $this->cFilterFunction;
        $cFilterFunction();
        return $this->oLastOutputPacket;
    }

    /**
     * Specific method for fixed C and fixed Q
     */
    protected abstract function processFixedCFixedQ();

    /**
     * Specific method for varying C and fixed Q
     */
    protected abstract function processVaryingCFixedQ();

    /**
     * Specific method for fixed C and varying Q
     */
    protected abstract function processFixedCVaryingQ();

    /**
     * Specific method for varying C and varying Q
     */
    protected abstract function processVaryingCVaryingQ();
}

