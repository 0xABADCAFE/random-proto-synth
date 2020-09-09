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

namespace ABadCafe\Synth\Operator;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Output;
use ABadCafe\Synth\Utility;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory class for envelope generators
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'output'      => 'createOutput',
        'mixer'       => 'createSumming',
        'simple'      => 'createUnmodulatedOscillator',
        'modulatable' => 'createModulatableOscillator',
        'filter'      => 'createFilter',

    ];

    /**
     * @param  object $oDescription
     * @return IGenerator
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IOperator {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Operator Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param object $oDescription
     * @return PCMOutput
     */
    private function createOutput(object $oDescription) : PCMOutput {
        if (!isset($oDescription->destination) || !is_object($oDescription->destination)) {
            throw new \Exception('Missing destination config for Output operator');
        }
        return new PCMOutput(
            Output\Factory::get()->createFrom($oDescription->destination)
        );
    }

    private function createSumming(object $oDescription) : Summing {
        return new Summing();
    }

    private function createFilter(object $oDescription) : ControlledFilter {

        if (!isset($oDescription->model) || !is_object($oDescription->model)) {
            throw new \Exception('Missing model for Filter operator');
        }

        // Mandatory
        $oModel = Signal\Audio\Filter\Factory::get()->createFrom($oDescription->model);

        // Optional
        $oCutoffControl = isset($oDescription->cutoff) && is_object($oDescription->cutoff) ?
            Signal\Control\Factory::get()->createFrom($oDescription->cutoff) : null;

        // Optional
        $oResonanceControl = isset($oDescription->resonance) && is_object($oDescription->resonance) ?
            Signal\Control\Factory::get()->createFrom($oDescription->resonance) : null;

        return new ControlledFilter(
            $oModel,
            $oCutoffControl,
            $oResonanceControl
        );
    }
}
