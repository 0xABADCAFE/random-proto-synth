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

namespace ABadCafe\Synth\Signal\Control;
use ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Utility;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory class for control sources
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'oscillator' => 'createOscillator',
        'envelope'   => 'createEnvelope',
        // TODO - MIDI input
    ];

    /**
     * @param  object $oDescription
     * @return IStream
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IStream {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            if (isset($oDescription->config) && is_object($oDescription->config)) {
                $cCreator = [$this, $sProduct];
                return $cCreator($oDescription->config);
            }
            throw new \Exception('Missing config for ' . $sType);
        }
        throw new \Exception('Unknown Control Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param object $oDescription
     * @return IOscillator
     */
    private function createOscillator(object $oDescription) : Oscillator\Control\IOscillator {
        return Oscillator\Control\Factory::get()->createFrom($oDescription);
    }

    /**
     * @param object $oDescription
     * @return Envelope\IGenerator
     */
    private function createEnvelope(object $oDescription) : Envelope\IGenerator {
        return Envelope\Factory::get()->createFrom($oDescription);
    }
}
