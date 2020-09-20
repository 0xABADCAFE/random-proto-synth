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
     * @var Map\KeyedSet|null $oPredefinedNoteMapSet
     */
    private ?Map\KeyedSet $oPredefinedNoteMapSet = null;

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
     * Set predefined note maps that can be referred to in the envelope description.
     *
     * @param  Map\KeyedSet|null $oSet
     * @return self
     */
    public function setPredefinedNoteMaps(?Map\KeyedSet $oSet) : self {
        $this->oPredefinedNoteMapSet = $oSet;
        return $this;
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
        return new Generator\LinearInterpolated(
            $this->createShape($oDescription->shape),
            isset($oDescription->keyscale_speed) ? $this->getNoteMap($oDescription->keyscale_speed) : null,
            isset($oDescription->keyscale_level) ? $this->getNoteMap($oDescription->keyscale_level) : null
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
     * @param  object|string $mDescription
     * @return Map\Note\IMIDINumber|null
     */
    private function getNoteMap($mDescription) : ?Map\Note\IMIDINumber {
        if (is_object($mDescription)) {
            return Map\Note\Factory::get()->createFrom($mDescription);
        }
        if (
            $this->oPredefinedNoteMapSet &&
            is_string($mDescription)
        ) {
            return $this->oPredefinedNoteMapSet->get($mDescription);
        }
        return null;
    }

}
