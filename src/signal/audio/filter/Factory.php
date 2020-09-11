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

namespace ABadCafe\Synth\Signal\Audio\Filter;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Oscillator;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for audio filter implementations
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'lowpass' => 'createLowPass',
        'karlsen' => 'createKarlsen',
    ];

    const KARLSEN_MODES = [
        'lowpass'  => KarlsenLowPass::class,
        'bandpass' => KarlsenBandPass::class,
        'highpass' => KarlsenHighPass::class,
        'notch'    => KarlsenNotchReject::class,
    ];

    /**
     * @param  object $oDescription
     * @return Signal\Audio\IFilter
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : Signal\Audio\IFilter {
        $sType    = strtolower($oDescription->type ?? '<none>');
        $sProduct = self::PRODUCT_TYPES[$sType] ?? null;
        if ($sProduct) {
            $cCreator = [$this, $sProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Filter Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param  object $oDescription
     * @return ResonantLowPass
     * @throws \Exception
     */
    private function createLowPass(object $oDescription) : ResonantLowPass {
        return new ResonantLowPass(
            (float)($oDescription->cutoff ?? Signal\Audio\IFilter::F_DEF_CUTOFF),
            (float)($oDescription->resonance ?? IResonanceControlled::F_DEF_RESONANCE)
        );
    }

    /**
     * @param  object $oDescription
     * @return Karlsen
     * @throws \Exception
     */
    private function createKarlsen(object $oDescription) : Karlsen {
        $sMode      = strtolower($oDescription->mode ?? 'lowpass');
        $sClass     = self::KARLSEN_MODES[$sMode] ?? null;
        if ($sClass) {
            return new $sClass(
                (float)($oDescription->cutoff ?? Signal\Audio\IFilter::F_DEF_CUTOFF),
                (float)($oDescription->resonance ?? IResonanceControlled::F_DEF_RESONANCE)
            );
        }
        throw new \Exception('Unknown Filter Mode ' . $sMode);
    }
}
