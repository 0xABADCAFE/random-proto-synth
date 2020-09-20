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

/**
 * Output
 */
namespace ABadCafe\Synth\Output;
use ABadCafe\Synth\Signal;
use \Exception;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IOException
 *
 * Catch all IO Exception type
 */
class IOException extends Exception {

};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IPCMOutput
 *
 * Interface for PCM Output types
 */
interface IPCMOutput {

    /**
     * Open an output stream
     *
     * @param  string $sPath
     * @throws IOException
     */
    public function open(string $sPath);

    /**
     * Write a signal packet
     *
     * @param  Signal\IPacket $oPacket
     * @throws IOException
     */
    public function write(Signal\IPacket $oPacket);

    /**
     * Close the output stream
     */
    public function close();
}

