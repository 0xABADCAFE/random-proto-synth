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
use ABadCafe\Synth\Utility;
use ABadCafe\Synth\Map;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Low Level Base implementation for Operators that may be invoked several times to emit a Packet due to the topology
 * of the Operator -> Operator configuration.
 *
 * This class provides a mecahism where each emitted Packet has an index
 */
abstract class Base implements IOperator, Utility\IEnumeratedInstance {

    use Utility\TEnumeratedInstance;

    protected Signal\Audio\Packet $oLastPacket;
    protected int $iPacketIndex = 0;

    /**
     * @inheritdoc
     */
    public function emit() : Signal\Audio\Packet {
        return $this->emitPacketForIndex($this->iPacketIndex + 1);
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see Map\Note\IMIDINumberAware
     */
    public function getNoteNumberMapUseCases() : array {
        return [];
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see Map\Note\IMIDINumberAware
     */
    public function setNoteNumberMap(Map\Note\IMIDINumber $oNoteMap, string $sUseCase) : self {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see Map\Note\IMIDINumber
     */
    public function getNoteNumberMap(string $sUseCase) : Map\Note\IMIDINumber {
        return Map\Note\Invariant::get();
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see Map\Note\IMIDINumberAware
     */
    public function setNoteNumber(int $iNote) : self {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * This is a stub and should be overridden by any implementation supporting a number map control
     *
     * @see Map\Note\IMIDINumberAware
     */
    public function setNoteName(string $sNote) : self {
        return $this;
    }

    /**
     * Generate the actual packet or return the last one if the index is unchanged. Implementors of this method
     * must bumo the iPacketIndex for every new Packet that is calculated.
     *
     * @param  int $iPacketIndex
     * @return Signal\Audio\Packet
     */
    protected abstract function emitPacketForIndex(int $iPacketIndex) : Signal\Audio\Packet;

}
