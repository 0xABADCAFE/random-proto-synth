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

namespace ABadCafe\Synth\Signal\Generator\WaveShaper;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for signal generators
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'capacitance'      => 'createCapacitance',
        'phasefeedback'    => 'createPhaseFeedback',
        'phasefeedbackcap' => 'createPhaseFeedbackWithCapacitance',
    ];

    /**
     * @param  object $oDescription
     * @return Signal\Generator\IWaveShaper
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : Signal\Generator\IWaveShaper {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Output Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param  object $oDescription
     * @return FixedCapacitance
     */
    private function createCapacitance(object $oDescription) : FixedCapacitance {
        return new FixedCapacitance(
            (float)($oDescription->capacitance ?? FixedCapacitance::F_DEFAULT_AMOUNT)
        );
    }

    /**
     * @param  object $oDescription
     * @return FixedPhaseFeedback
     */
    private function createPhaseFeedback(object $oDescription) : FixedPhaseFeedback {
        return new FixedPhaseFeedback(
            (float)($oDescription->feedback ?? FixedPhaseFeedback::F_DEFAULT_LEVEL)
        );
    }

    /**
     * @param  object $oDescription
     * @return FixedPhaseFeedback
     */
    private function createPhaseFeedbackWithCapacitance(object $oDescription) : FixedPhaseFeedbackWithCapacitance {
        return new FixedPhaseFeedbackWithCapacitance(
            (float)($oDescription->feedback    ?? FixedPhaseFeedback::F_DEFAULT_LEVEL),
            (float)($oDescription->capacitance ?? FixedCapacitance::F_DEFAULT_AMOUNT)
        );
    }
}
