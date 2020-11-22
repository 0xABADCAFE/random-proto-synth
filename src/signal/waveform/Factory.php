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

namespace ABadCafe\Synth\Signal\Waveform;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for signal generators
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const SIMPLE_TYPES = [
        'sine'     => Sine::class,
        'triangle' => Triangle::class,
        'square'   => Square::class,
        'sawup'    => SawUp::class,
        'sawdown'  => SawDown::class,
        'noise'    => Noise::class,
    ];

    const COMPLEX_TYPES = [
        'table' => 'createTable',
    ];

    /**
     * @param  object $oDescription
     * @return Signal\IWaveform
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : Signal\IWaveform {
        $sType          = strtolower($oDescription->type ?? '<none>');
        $sSimpleProduct = self::SIMPLE_TYPES[$sType] ?? null;
        if ($sSimpleProduct) {
            return new $sSimpleProduct(
                (float)($oDescription->min ?? Signal\ILimits::F_MIN_LEVEL_NO_CLIP),
                (float)($oDescription->max ?? Signal\ILimits::F_MAX_LEVEL_NO_CLIP),
                isset($oDescription->shaper) && is_object($oDescription->shaper) ?
                    Shaper\Factory::get()->createFrom($oDescription->shaper) :
                    null
            );
        }
        $sComplexProduct = self::COMPLEX_TYPES[$sType] ?? null;
        if ($sComplexProduct) {
            $cCreator = [$this, $sComplexProduct];
            return $cCreator($oDescription);
        }
        throw new \Exception('Unknown Waveform Type ' . $sType);
    }

    /**
     * Nothing to do here
     */
    protected function singletonInitialise() {

    }

    /**
     * @param  object $oDescription
     * @return Signal\IWaveform
     * @throws \Exception
     */
    private function createTable(object $oDesciption) : Signal\IWaveform {
        if (
            !isset($oDesciption->data) ||
            !is_array($oDesciption->data) ||
            empty($oDesciption->data)
        ) {
            throw new \Exception("Missing or invalid WaveTable data");
        }
        $iSize    = count($oDesciption->data);
        $iSizeExp = (int)log($iSize, 2);
        if ($iSizeExp < WaveTable::I_MIN_SIZE_EXP || (1<<$iSizeExp) !== $iSize) {
            throw new \Exception("Invalid WaveTable length");
        }
        $oWaveform =  new Table($iSizeExp);
        $oTableData = $oWaveform->getValues();
        foreach ($oDesciption->data as $i => $fValue) {
            $oTableData[$i] = (float)$fValue;
        }
        return $oWaveform;
    }
}
