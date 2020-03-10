<?php

namespace ABadCafe\Synth\Operator;

use ABadCafe\Synth\Signal;
use ABadCafe\Synth\Utility;
use ABadCafe\Synth\Map\Note\IMIDINumber      as IMIDINoteMap;
use ABadCafe\Synth\Map\Note\IMIDINumberAware as IMIDINoteMapAware;
use AbadCafe\Synth\Map\Note\Invariant        as IMIDIInvariantNoteMap;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Low Level Base implementation for Operators that may be invoked several times to emit a Packet due to the topology
 * of the Operator -> Operator configuration.
 *
 * This class provides a mecahism where each emitted Packet has an index
 */
abstract class Base implements IOperator, Utility\IEnumeratedInstance {

    use Utility\TEnumeratedInstance;

    protected
        /** @var Packet $oLastPacket */
        $oLastPacket,

        /** @var int $iPacketIndex */
        $iPacketIndex              = 0
    ;

    /**
     * @inheritdoc
     */
    public function emit() : Signal\IPacket {
        return $this->emitPacketForIndex($this->iPacketIndex + 1);
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMapUseCases() : array {
        return [];
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumberMap(IMIDINoteMap $oNoteMap, string $sUseCase) : IMIDINoteMapAware {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function getNoteNumberMap(string $sUseCase) : IMIDINoteMap {
        return IMIDIInvariantNoteMap::get();
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : IMIDINoteMapAware {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see IMIDINumberAware
     */
    public function setNoteName(string $sNote) : IMIDINoteMapAware {
        return $this;
    }

    /**
     * Generate the actual packet or return the last one if the index is unchanged. Implementors of this method
     * must bumo the iPacketIndex for every new Packet that is calculated.
     *
     * @param  int $iPacketIndex
     * @return Signal\IPacket
     */
    protected abstract function emitPacketForIndex(int $iPacketIndex) : Signal\IPacket;

}
