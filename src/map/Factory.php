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

namespace ABadCafe\Synth\Map\Note;
use ABadCafe\Synth\Utility;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        '12tone_scaled' => 'createTwelveToneEqualTemperament',
    ];


    /**
     * @param  object $oDescription
     * @return IMIDINumber
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IMIDINumber {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Note Map Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    private function createTwelveToneEqualTemperament(object $oDescription) : TwelveToneEqualTemperament {
        return new TwelveToneEqualTemperament(
            (float)($oDescription->center ?? 1.0),
            (float)($oDescription->scale ?? 1.0),
            (bool)($oDescription->invert ?? false)
        );
    }
}
