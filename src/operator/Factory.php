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
use ABadCafe\Synth\Map;
use ABadCafe\Synth\Oscillator;
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
        'filter'      => 'createFilter',
        'oscillator'  => 'createOscillator'
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

    /**
     * @param object $oDescription
     * @return Summing
     */
    private function createSumming(object $oDescription) : Summing {
        return new Summing();
    }

    /**
     * @param object $oDescription
     * @return ControlledFilter
     */
    private function createFilter(object $oDescription) : ControlledFilter {
        // Mandatory
        if (!isset($oDescription->model) || !is_object($oDescription->model)) {
            throw new \Exception('Missing model for Filter operator');
        }
        $oModel = Signal\Audio\Filter\Factory::get()->createFrom($oDescription->model);

        // Optional
        $oCutoffControl = isset($oDescription->cutoff_control) && is_object($oDescription->cutoff_control) ?
            Signal\Control\Factory::get()->createFrom($oDescription->cutoff_control) : null;

        // Optional
        $oResonanceControl = isset($oDescription->resonance_control) && is_object($oDescription->resonance_control) ?
            Signal\Control\Factory::get()->createFrom($oDescription->resonance_control) : null;

        return new ControlledFilter(
            $oModel,
            $oCutoffControl,
            $oResonanceControl
        );
    }

    /**
     * @param object $oDescription
     * @return UnmodulatedOscillator|ModulatableOscillator
     */
    private function createOscillator(object $oDescription) : UnmodulatedOscillator {
        // Mandatory
        if (!isset($oDescription->model) || !is_object($oDescription->model)) {
            throw new \Exception('Missing model for UnmodulatedOscillator operator');
        }
        $oOscillator = Oscillator\Audio\Factory::get()->createFrom($oDescription->model);

        // Amplitude Control Source
        $oLevelControl = isset($oDescription->level_control) && is_object($oDescription->level_control) ?
            Signal\Control\Factory::get()->createFrom($oDescription->level_control) : null;

        // Pitch Control Source
        $oPitchControl = isset($oDescription->pitch_control) && is_object($oDescription->pitch_control) ?
            Signal\Control\Factory::get()->createFrom($oDescription->pitch_control) : null;

        // Basic Key Scale for pitch
        $oKeyscaleMap  = isset($oDescription->keyscale_pitch) && is_object($oDescription->keyscale_pitch) ?
            Map\Note\Factory::get()->createFrom($oDescription->keyscale_pitch) : null;

        $fFreqRatio    = (float)($oDescription->ratio  ?? 1.0);
        $fDetune       = (float)($oDescription->detune ?? 0.0);
        $bUnmodulated  = (bool)($oDescription->unmodulated ?? false);

        if ($bUnmodulated) {
            return new UnmodulatedOscillator(
                $oOscillator,
                $fFreqRatio,
                $fDetune,
                $oLevelControl,
                $oPitchControl,
                $oKeyscaleMap
            );
        } else {
            // ModulatableOscillator extends UnmodulatedOscillator
            return new ModulatableOscillator(
                $oOscillator,
                $fFreqRatio,
                $fDetune,
                $oLevelControl,
                $oPitchControl,
                $oKeyscaleMap
            );
        }
    }

}
