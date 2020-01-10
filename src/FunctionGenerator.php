<?php

namespace Synth\FunctionGenerator;

interface Limits {
    const
        // Sample rate
        MIN_RATE = 11025,
        MAX_RATE = 192000,
        DEF_RATE = 44100,

        // Frequency
        MIN_FREQ = 55.0,
        MAX_FREQ = 3520.0,
        DEF_FREQ = 440.0,

        // Amplitude
        MIN_SLVL = -1.0,
        MAX_SLVL = 1.0
    ;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FunctionGenerator\Base
 */
abstract class Base implements Limits {

    protected
        /** @var int $iSampleRate */
        $iSampleRate,

        /** @var int $iSamplePosition */
        $iSamplePosition = 0,

        /** @var float $fFrequency */
        $fFrequency
    ;

    /**
     * Constructor. Set default sample rate and frequency here. Sample rate is immutable once set.
     *
     * @param int   $iSampleRate
     * @param float $fFrequency
     */
    public function __construct(
        int   $iSampleRate = self::DEF_RATE,
        float $fFrequency  = self::DEF_FREQ
    ) {
        $this->iSampleRate = $this->clamp($iSampleRate, self::MIN_RATE, self::MAX_RATE);
        $this->setFrequency($fFrequency);
    }

    public function __toString() : string {
        return sprintf(
            "%s [freq:%.3fHz rate:%dHz, pos:%d]",
            static::class,
            $this->fFrequency,
            $this->iSampleRate,
            $this->iSamplePosition
        );
    }

    /**
     * Get the generator sample rate, in Hz.
     *
     * @return int
     */
    public function getSampleRate() : int {
        return $this->iSampleRate;
    }

    /**
     * Get the generator sample position, which is the total number of samples generated since
     * instantiation or the last call to reset().
     *
     * @return int
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * Get the generator signal frequency
     *
     * @return int
     */
    public function getFrequency() : float {
        return $this->fFrequency;
    }

    /**
     * Reset the duty cycle
     *
     * @param  float $fFrequency
     * @return self
     */
    public function reset() : Base {
        $this->iSamplePosition = 0;
        return $this;
    }

    /**
     * Set the generator signal frequency
     *
     * @param  float $fFrequency
     * @return self
     */
    public function setFrequency(float $fFrequency) : Base {
        $this->fFrequency = $this->clamp($fFrequency, self::MIN_FREQ, self::MAX_FREQ);
        return $this;
    }

    /**
     * Generate a set of samples
     *
     * @param  int $iLength
     * @return float[]
     */
    abstract public function generate(int $iLength) : array;

    /**
     * Clamp some numeric vale between a minimum and maximum
     *
     * @param  float|int $mValue
     * @param  float|int $mMin
     * @param  float|int $mMax
     * @return float|int
     */
    protected function clamp($mValue, $mMin, $mMax) {
        return max(min($mValue, $mMax), $mMin);
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FunctionGenerator\DC
 *
 * Generates a continuous DC signal
 */
class DC extends Base {

    private $fLevel = 0.0;

    public function setLevel(float $fLevel) : DC {
        $this->fLevel = $fLevel;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generate(int $iLength) : array {
        $this->iSamplePosition += $iLength;
        return array_fill(0, $iLength, $this->fLevel);
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////

class Noise extends Base {

    /**
     * @inheritdoc
     */
    public function generate(int $iLength) : array {
        static $fNormalize = null;
        if (null === $fNormalize) {
            $fNormalize = (self::MAX_SLVL - self::MIN_SLVL) / (float)mt_getrandmax();
        }

        $aSamples = [];
        while ($iLength-- > 0) {
            $aSamples[] = self::MIN_SLVL + mt_rand() * $fNormalize;
        }
        return $aSamples;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * FunctionGenerator\Sine
 *
 * Generates a continuous sine wave
 */
class Sine extends Base {

    private
        $fScaleVal = 0
    ;

    /**
     * @inheritdoc
     */
    public function setFrequency(float $fFrequency) : Base {
        parent::setFrequency($fFrequency);
        $this->fScaleVal = (2.0 * $this->fFrequency * M_PI) / (float)$this->iSampleRate;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generate(int $iLength) : array {
        $aSamples = [];
        while ($iLength-- > 0) {
            $aSamples[] = sin($this->fScaleVal * $this->iSamplePosition++);
        }
        return $aSamples;
    }
}


