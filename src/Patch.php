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
 * Module
 */
class Module implements Map\Note\IMIDINumberAware {

    const S_KEY_OUTPUT = 'out';

    const MODULATION_MAP = [
        'audio'     => Operator\InputKind::E_SIGNAL,
        'signal'    => Operator\InputKind::E_SIGNAL,
        'am'        => Operator\InputKind::E_AMPLITUDE,
        'amplitude' => Operator\InputKind::E_AMPLITUDE,
        'fm'        => Operator\InputKind::E_PHASE,
        'phase'     => Operator\InputKind::E_PHASE,
    ];

    /** @var Operator\IOperator[] $aOperators */
    protected array $aOperatorList     = [];

    /** @var float[][][] $aModulationMatrix */
    protected array $aModulationMatrix = [];

    /**
     * Constructor
     *
     * @param Operator\IOperator[] $aOperatorList
     * @param float[][][]          $aModulationMatrix
     */
    public function __construct(array $aOperatorList, array $aModulationMatrix) {
        $this->checkOperatorList($aOperatorList);
        $this->checkModulationMatrix($aOperatorList, $aModulationMatrix);
        $this->aOperatorList     = $aOperatorList;
        $this->aModulationMatrix = $aModulationMatrix;
        $this->connectOperators();
    }

    /**
     * Obtain the Operator used for output
     *
     * @return Operator\IOperator
     */
    public function getOutputOperator() : Operator\IOperator {
        return $this->aOperatorList[self::S_KEY_OUTPUT];
    }

    /**
     * Obtain a list of use cases that IMIDINumber maps can be set for.
     *
     * @return string[]
     */
    public function getNoteNumberMapUseCases() : array {
        return [];
    }

    /**
     * Set a new Note Map. An implementor may use multiple Note Maps for multiple things, for exanple, the effect of
     * note number on envelope speeds, amplitudes, filter cutoff etc. The use cases are specific to the implementor.
     *
     * @param  Map\Note\IMIDINumber $oNoteMap
     * @param  string               $sUseCase
     * @return self
     */
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : self {
        return $this;
    }

    /**
     * Get the current Note Map.
     *
     * @param string $sUseCase
     *
     * @return Map\Note\IMIDINumber
     */
    public function getNoteNumberMap(string $sUseCase) : Map\Note\IMIDINumber {
        return Map\Note\Invariant::get();
    }

    /**
     * Set the note number to use. The expectation is that the consuming class will use the Note Map to derive some
     * control paramter base on the note.
     *
     * @param  int $iNote
     * @return self
     * @throws OutOfRangeException
     */
    public function setNoteNumber(int $iNote) : self {
        foreach ($this->aOperatorList as $oOperator) {
            $oOperator->setNoteNumber($iNote);
        }
        return $this;
    }

    /**
     * Set the note to use, by name. The expectation is that the consuming class will use the Note Map to derive some
     * control paramter base on the note.
     *
     * @param  string $sNote
     * @return self
     * @throws OutOfBoundsException
     */
    public function setNoteName(string $sNote) : self {
        foreach ($this->aOperatorList as $oOperator) {
            $oOperator->setNoteName($sNote);
        }
        return $this;
    }

    /**
     * Validates the Operator list. There must be at least one output operator and one
     * source Operator. The output operator must be declared by the key name defined in
     * S_KEY_OUTPUT and be an instance of the Operator\Summing or one of it's subclasses.
     *
     * All entries in the list must be implementations of the Operator\IOperator interface.
     *
     * @param  Operator\IOperator[] $aOperatorList
     * @throws \Exception
     */
    private function checkOperatorList(array $aOperatorList) {
        if (count($aOperatorList) < 2) {
            throw new \Exception('At least 2 Operators expected (one output and one source)');
        }
        if (!(
            isset($aOperatorList[self::S_KEY_OUTPUT]) &&
            $aOperatorList[self::S_KEY_OUTPUT] instanceof Operator\Summing
        )) {
            throw new \Exception('Output operator not defined or is not an appropriate type');
        }
        foreach ($aOperatorList as $sKey => $oOperator) {
            if (!($oOperator instanceof Operator\IOperator)) {
                throw new \Exception('Operator "' . $sKey . '" does not define a valid Operator');
            }
        }
    }

    /**
     * Validates the Connection Matrix. The connection matrix specifies mappings between operators.
     * Must contain an entry for the output operator which in turm must have an input.
     *
     * @param Operator\IOperator[] $aOperatorList
     * @param float[][][]          $aModulationMatrix
     */
    private function checkModulationMatrix(array $aOperatorList, array $aModulationMatrix) {
        if (!isset($aModulationMatrix[self::S_KEY_OUTPUT]) || empty($aModulationMatrix[self::S_KEY_OUTPUT])) {
            throw new \Exception("Nothing is connected to the output Operator");
        }
        foreach ($aModulationMatrix as $sKey => $aInputs) {
            $this->checkOperatorInputs($aOperatorList, $sKey, $aInputs);
        }
    }

    /**
     * Validates the inputs to an Operator in the Connection Matrix.
     * 1. The Operator reference must refer to an Operator that was in the list.
     * 2. The set of Inputs for that Operator reference must not be empty.
     * 3. The key for each Input must refer to an Operator that was in the list.
     * 4. The value for each Input must be an array of Modulation => Level pairs
     * 5. Each Modulation Type must be one of the defined types.
     * 6. The Modulation Level must be numeric.
     *
     * @param Operator\IOperator[] $aOperatorList
     * @param string               $sKey
     * @param float[][]            $aInputs
     */
    private function checkOperatorInputs(array $aOperatorList, string $sKey, array $aInputs) {
        if (!isset($aOperatorList[$sKey])) {
            throw new \Exception('Unknown Operator reference "' . $sKey . '"');
        }
        if (!is_array($aInputs) || empty($aInputs)) {
            throw new \Exception('Invalid matrix input entry for Operator "' . $sKey . '"');
        }
        foreach ($aInputs as $sInputKey => $aInputLevels) {
            if (!isset($aOperatorList[$sInputKey])) {
                throw new \Exception('Invalid Operator referece "' . $sInputKey . '"');
            }
            foreach($aInputLevels as $sKind => $fValue) {
                if (!isset(self::MODULATION_MAP[$sKind])) {
                    throw new \Exception('Unknown modulation type "' . $sKind . '" on Operator "' . $sKey . '"');
                }
                if (!is_numeric($fValue)) {
                    throw new \Exception('Modulation level for type "' . $sKind . '" on Operator "' . $sKey . '" must be numeric');
                }
            }
        }
    }

    /**
     * Connect up the OperatorList based on the Modulation Matrix
     */
    private function connectOperators() {
        foreach ($this->aModulationMatrix as $sCarrierKey => $aInputs) {
            $oCarrier = $this->aOperatorList[$sCarrierKey];

            dprintf(
                "Adding inputs to %s \"%s\"\n",
                $oCarrier,
                $sCarrierKey
            );

            foreach ($aInputs as $sModulatorKey => $aInputLevels) {
                $oModulator = $this->aOperatorList[$sModulatorKey];

                foreach ($aInputLevels as $sInputKind => $fLevel) {
                    $oInputKind = Operator\InputKind::get(self::MODULATION_MAP[$sInputKind]);
                    $oCarrier->attachInput($oModulator, (float)$fLevel, $oInputKind);

                    dprintf(
                        "\tAttaching output of %s \"%s\" as %s at intensity %.2f\n",
                        $oModulator,
                        $sModulatorKey,
                        $sInputKind,
                        $fLevel
                    );

                }
            }
        }
    }
}

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
