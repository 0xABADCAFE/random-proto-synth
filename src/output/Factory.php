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

namespace ABadCafe\Synth\Output;
use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Utility;
use function Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Factory for output types
 */
class Factory implements Utility\IFactory {

    use Utility\TSingleton;

    const PRODUCT_TYPES = [
        'raw'  => 'createRaw',
        'wav'  => 'createWav',
        'play' => 'createPlay'
    ];

    /**
     * @param  object $oDescription
     * @return IPCMOutput
     * @throws \Exception
     */
    public function createFrom(object $oDescription) : IPCMOutput {
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
     * @return Raw16BitLittle
     * @throws \Exception
     */
    private function createRaw(object $oDescription) : Raw16BitLittle {
        $sPath = $oDescription->path ?? 'output_raw_' . time() . '.bin';
        $oOut  = new Raw16BitLittle();
        $oOut->open($sPath);
        return $oOut;
    }

    /**
     * @param  object $oDescription
     * @return Wav
     * @throws \Exception
     */
    private function createWav(object $oDescription) : Wav {
        $sPath = $oDescription->path ?? 'output_wav_' . time() . '.wav';
        $oOut  = new Wav(
            (int)($oDescription->rate ?? Wav::I_DEF_RATE_SIGNAL_DEFAULT),
            (int)($oDescription->bits ?? Wav::I_DEF_RESOLUTION_BITS)
        );
        $oOut->open($sPath);
        return $oOut;
    }

    /**
     * @param  object $oDescription
     * @return Play
     * @throws \Exception
     */
    private function createPlay(object $oDescription) : Play {
        $oOut = new Play();
        $oOut->open('device');
        return $oOut;
    }
}
