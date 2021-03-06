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

namespace ABadCafe\Synth\Signal\Audio\Filter;
use ABadCafe\Synth\Signal;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * ICutoffControlled
 *
 * Basic filter interface
 */
interface ICutoffControlled extends Signal\Audio\IFilter {

    /**
     * Set a control packet for the cutoff. This allows the control of the filter from other signal sources.
     * The values in the packet will be applied on every call to filter() until a new control Packet is set
     * or the existing one is cleared by setting to null. When a control packet is set, it overries whatever
     * the default cutoff has been set to.
     *
     * @param Packet|null $oCutoff
     */
    public function setCutoffControl(Signal\Control\Packet $oCutoff = null) : self;

    /**
     * Filter a Packet
     *
     * @param  Packet $oInput
     * @return Packet
     */
    public function filter(Signal\Audio\Packet $oInput) : Signal\Audio\Packet;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IResonanceControlled
 *
 * Builds upon the ICutoffControlled interface, adding support for
 */
interface IResonanceControlled extends ICutoffControlled {

    const
        F_MIN_RESONANCE = 0.0,
        F_DEF_RESONANCE = 0.0,
        F_MAX_RESONANCE = 1.0
    ;

    /**
     * Set the resonance level. Uses a normalised scale in which 1.0 is the highest setting
     * supported by the filter before self-oscillation or other chaotic behaviours emerge.
     * Zero implies no resonance.
     *
     * @param  float $fCutoff - 0 < $fResonance <= 1.0
     * @return self
     */
    public function setResonance(float $fResonance) : self;

    /**
     * Get the resonance. This may return a value ifferent than what was set if the specific
     * filter implementation clamped the range.
     *
     * @return float
     */
    public function getResonance() : float;

    /**
     * Set a control packet for the resonance. This allows the control of the filter from other signal sources.
     * The values in the packet will be applied on every call to filter() until a new control Packet is set
     * or the existing one is cleared by setting to null. When a control packet is set, it overries whatever
     * the default cutoff has been set to.
     *
     * @param Packet|null $oCutoff
     */
    public function setResonanceControl(Signal\Control\Packet $oResonance = null) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Common base class for filter implementations
 */
abstract class Base implements ICutoffControlled {

    /** @var SPLFixedArray $oCutoff */
    protected ?SPLFixedArray $oCutoff;

    /** @var float $fCutoff */
    protected float $fCutoff;

    /**
     * @inheritdoc
     */
    public function reset() : self {
        $this->oCutoff = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCutoff(float $fCutoff) : self {
        $this->fCutoff = max($fCutoff, self::F_MIN_CUTOFF);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCutoff() : float {
        return $this->fCutoff;
    }

    /**
     * @inheritdoc
     */
    public function setCutoffControl(Signal\Control\Packet $oCutoff = null) : self {
        if ($oCutoff) {
            $this->oCutoff = clone $oCutoff->getValues();
        } else {
            $this->oCutoff = null;
        }
        return $this;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Common base class for resonant filter implementations
 */
abstract class Resonant extends Base implements IResonanceControlled {

    /** @var SPLFixedArray $oResonance */
    protected ?SPLFixedArray $oResonance;

    /** @var float $fResonance */
    protected float $fResonance;

    /**
     * @inheritdoc
     */
    public function reset() : self {
        parent::reset();
        $this->oResonance = null;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResonance(float $fResonance) : self {
        $this->fResonance = max(
            $fResonance,
            self::F_MIN_RESONANCE
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResonance() : float {
        return $this->fResonance;
    }

    /**
     * @inheritdoc
     */
    public function setResonanceControl(Signal\Control\Packet $oResonance = null) : IResonanceControlled {
        if ($oResonance) {
            $this->oResonance = clone $oResonance->getValues();
        } else {
            $this->oResonance = null;
        }
        return $this;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'filter/ResonantLowPass.php';
require_once 'filter/Karlsen.php';
require_once 'filter/Factory.php';
