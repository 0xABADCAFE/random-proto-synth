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

namespace ABadCafe\Synth\Oscillator\Audio;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for audio oscillators
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'simple' => 'createSimple',
        'super'  => 'createSuper',
    ];

    /**
     * @param  object $oDescription
     * @return IOscillator
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IOscillator {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $oWaveform = $this->getWaveform($oDescription);
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription, $oWaveform);
        }
        throw new \Exception('Unknown Audio Oscillator Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * Get the waveform dependency
     *
     * @param object $oDescription
     * @return Signal\IWaveform
     * @throws \Exception
     */
    private function getWaveform(object $oDescription) : Signal\IWaveform {
        if (!isset($oDescription->waveform) || !is_object($oDescription->waveform)) {
            throw new \Exception('Audio Oscillator missing waveform');
        }
        return Signal\Waveform\Factory::get()->createFrom($oDescription->waveform);
    }

    /**
     * @param  object $oDescription
     * @param  Signal\IWaveform $oWaveform
     * @return Simple
     */
    private function createSimple(object $oDescription, Signal\IWaveform $oWaveform) : Simple {
        return new Simple(
            $oWaveform,
            (float)($oDescription->freq ??  ILimits::F_DEF_FREQ),
            (float)($oDescription->phase ?? 0)
        );
    }

    /**
     * @param  object $oDescription
     * @param  Signal\IWaveform $oWaveform
     * @return Super
     */
    private function createSuper(object $oDescription, Signal\IWaveform $oWaveform) : Super {
        if (!isset($oDescription->stack) || !is_array($oDescription->stack)) {
            throw new \Exception('Missing or empty harmonic stack for Super Oscillator');
        }
        return new Super(
            $oWaveform,
            $oDescription->stack,
            (float)($oDescription->freq ??  ILimits::F_DEF_FREQ)
        );
    }
}
