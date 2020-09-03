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
 * Control
 */
namespace ABadCafe\Synth\Oscillator\Control;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for IOscillator implementations
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'fixed' => 'createFixedLFO',
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
        throw new \Exception('Unknown Control Oscillator Type ' . $sType);
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
            throw new \Exception('Control Oscillator missing generator');
        }
        return Signal\Generator\Factory::get()->createFrom($oDescription->generator);
    }

    private function createFixedLFO(object $oDescription, Signal\IGenerator $oGenerator) : IOscillator {
        return new FixedLFO($oGenerator);
    }
}
