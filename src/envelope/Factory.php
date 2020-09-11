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

namespace ABadCafe\Synth\Envelope;
use ABadCafe\Synth\Map;
use ABadCafe\Synth\Utility;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory class for envelope generators
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'custom' => 'createLinearInterpolated',
    ];

    /**
     * @param  object $oDescription
     * @return IGenerator
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IGenerator {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Envelope Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param  object $oDescription
     * @return Generator\LinearInterpolated
     */
    private function createLinearInterpolated(object $oDescription) : Generator\LinearInterpolated {
        if (!isset($oDescription->shape) || !is_object($oDescription->shape)) {
            throw new \Exception('Missing or malformed Shape definition');
        }

        $oSpeedScale = isset($oDescription->keyscale_speed) && is_object($oDescription->keyscale_speed) ?
            $this->createMap($oDescription->keyscale_speed) : null;
        $oLevelScale = isset($oDescription->keyscale_level) && is_object($oDescription->keyscale_level) ?
            $this->createMap($oDescription->keyscale_level) : null;

        return new Generator\LinearInterpolated(
            $this->createShape($oDescription->shape),
            $oSpeedScale,
            $oLevelScale
        );
    }

    /**
     * @param  object $oDescription
     * @return Shape
     */
    private function createShape(object $oDescription) : Shape {
        if (!isset($oDescription->points) || !is_array($oDescription->points)) {
            throw new \Exception('Missing or malformed points for Shape');
        }
        return new Shape(
            (float)$oDescription->initial ?? 0.0,
            $oDescription->points
        );
    }

    /**
     * @param  object $oDescription
     * @return Map\Note\IMIDINumber
     */
    private function createMap(object $oDescription) : Map\Note\IMIDINumber {
        return Map\Note\Factory::get()->createFrom($oDescription);
    }
}
