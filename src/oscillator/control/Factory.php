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

namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for control oscillators (e.g. LFOs etc)
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'lfo' => 'createFixedLFO',
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
        throw new \Exception('Unknown Control Oscillator Type ' . $sType);
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
            throw new \Exception('Control Oscillator missing waveform');
        }
        return Signal\Waveform\Factory::get()->createFrom($oDescription->waveform);
    }

    /**
     * @param  object $oDescription
     * @param  Signal\IWaveform $oWaveform
     * @return FixedLFO
     */
    private function createFixedLFO(object $oDescription, Signal\IWaveform $oWaveform) : FixedLFO {
        return new FixedLFO(
            $oWaveform,
            (float)($oDescription->rate ??  ILimits::F_DEF_FREQ),
            (float)($oDescription->depth ?? 0.5)
        );
    }
}
