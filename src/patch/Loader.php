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

namespace ABadCafe\Synth\Patch;

use ABadCafe\Synth\Map;
use ABadCafe\Synth\Operator;

use function ABadCafe\Synth\Utility\dprintf;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Loader
 */
class Loader {

    /**
     * @param  string $sFile
     * @return Module
     * @throws \Exception
     */
    public function load(string $sFile) : Module {
        $this->assertFileReadable($sFile);
        $oData = $this->deserialiseFileContent($sFile);
        $this->assertBasicDataStructure($oData);
        return new Module(
            $this->buildOperators($oData),
            $this->buildModulationMatrix($oData)
        );
    }

    /**
     * @param  object $oData
     * @return Operator\IOperator[]
     */
    private function buildOperators(object $oData) : array {
        $aOperatorList = [];
        $oFactory = Operator\Factory::get();
        foreach ($oData->operators as $sIdentity => $oDescription) {
            $aOperatorList[$sIdentity] = $oFactory->createFrom($oDescription);
        }
        return $aOperatorList;
    }

    /**
     * @param  object $oData
     * @return float[][][]
     */
    private function buildModulationMatrix(object $oData) : array {
        $aMatrix = [];
        foreach ($oData->matrix as $sCarrierIdentity => $oInputs) {
            $aMatrix[$sCarrierIdentity] = [];
            foreach ($oInputs as $sModulatorIdentity => $oLevels) {
                $aMatrix[$sCarrierIdentity][$sModulatorIdentity] = (array)$oLevels;
            }
        }
        return $aMatrix;
    }

    /**
     * @param  string $sFile
     * @throws \Exception
     */
    private function assertFileReadable(string $sFile) {
        if (!is_file($sFile) || !is_readable($sFile)) {
            throw new \Exception('"' . $sFile . '" is not a readable file.');
        }
    }

    /**
     * @param  object $oData
     * @throws \Exception
     */
    private function assertBasicDataStructure(object $oData) {
        if (!isset($oData->operators) || !is_object($oData->operators)) {
            throw new \Exception('Missing required operators section');
        }
        if (!isset($oData->matrix) || !is_object($oData->matrix)) {
            throw new \Exception('Missing required matrix section');
        }
    }

    /**
     * @param  string $sFile
     * @return object
     * @throws \Exception
     */
    private function deserialiseFileContent(string $sFile) : object {
        return json_decode(file_get_contents($sFile), false, 512, JSON_THROW_ON_ERROR);
    }
}
