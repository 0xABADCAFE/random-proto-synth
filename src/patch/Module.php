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
use ABadCafe\Synth\Controller;
use ABadCafe\Synth\Map;
use ABadCafe\Synth\Operator;

use function ABadCafe\Synth\Utility\dprintf;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Module
 */
class Module implements Controller\IMIDINoteStandardEventListener {

    use Controller\TMIDINoteStandardLookup;

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
     * @inheritDoc
     * @implements IMIDINoteEventListener::noteOn()
     */
    public function noteOn(int $iNote, $iVelocity) : self {
        foreach ($this->aOperatorList as $oOperator) {
            $oOperator->setNoteNumber($iNote);
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @implements IMIDINoteEventListener::noteOff()
     */
    public function noteOff(int $iNumber) : self {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function noteNameOn(string $sNoteName, int $iVelocity) : self {
        return $this->noteOn($this->getNoteNumber($sNoteName), $iVelocity);
    }

    /**
     * @inheritDoc
     */
    public function noteNameOff(string $sNoteName) : self {
        return $this->noteOff($this->getNoteNumber($sNoteName));
    }

    /**
     * Validates the Operator list.
     *
     * All entries in the list must be implementations of the Operator\IOperator interface.
     *
     * @param  Operator\IOperator[] $aOperatorList
     * @throws \Exception
     */
    private function checkOperatorList(array $aOperatorList) {
        if (empty($aOperatorList)) {
            throw new \Exception('No operators defined!');
        }
        if (!(
            isset($aOperatorList[self::S_KEY_OUTPUT])
        )) {
            throw new \Exception('No operator is designated as the output');
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
