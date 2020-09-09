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
            $oGenerator = $this->getGenerator($oDescription);
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription, $oGenerator);
        }
        throw new \Exception('Unknown Audio Oscillator Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * Get the generator dependency
     *
     * @param object $oDescription
     * @return Signal\IGenerator
     * @throws \Exception
     */
    private function getGenerator(object $oDescription) : Signal\IGenerator {
        if (!isset($oDescription->generator) || !is_object($oDescription->generator)) {
            throw new \Exception('Audio Oscillator missing generator');
        }
        return Signal\Generator\Factory::get()->createFrom($oDescription->generator);
    }

    /**
     * @param  object $oDescription
     * @param  Signal\IGenerator $oGenerator
     * @return Simple
     */
    private function createSimple(object $oDescription, Signal\IGenerator $oGenerator) : Simple {
        return new Simple(
            $oGenerator,
            (float)($oDescription->freq ??  ILimits::F_DEF_FREQ),
            (float)($oDescription->phase ?? 0)
        );
    }

    /**
     * @param  object $oDescription
     * @param  Signal\IGenerator $oGenerator
     * @return Super
     */
    private function createSuper(object $oDescription, Signal\IGenerator $oGenerator) : Super {
        if (!isset($oDescription->stack) || !is_array($oDescription->stack)) {
            throw new \Exception('Missing or empty harmonic stack for Super Oscillator');
        }
        return new Super(
            $oGenerator,
            $oDescription->stack,
            (float)($oDescription->freq ??  ILimits::F_DEF_FREQ)
        );
    }
}
