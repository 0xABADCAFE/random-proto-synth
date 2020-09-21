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

    use
        Utility\TSingleton,
        Envelope\Generator\TKeyedSetFactoryUser
    ;

    const PRODUCT_TYPES = [
        'oscillator' => 'createOscillator',
        'envelope'   => 'getEnvelope',
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
            if (isset($oDescription->config)) {
                $cCreator = [$this, $sProduct];
                $oControl = $cCreator($oDescription->config);
                if (!$oControl) {
                    throw new \Exception('Missing definiton for ' . $sType);
                }
                return $oControl;
            }
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
}
