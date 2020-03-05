<?php

/**
 * Output
 */
namespace ABadCafe\Synth\Output;

use \Exception;
use ABadCafe\Synth\Signal\Audio\IPacket;

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
     * Write an audio packet
     *
     * @param  Packet $oPacket
     * @throws IOException
     */
    public function write(IPacket $oPacket);

    /**
     * Close the output stream
     */
    public function close();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once 'output/Raw.php';
require_once 'output/Wav.php';
require_once 'output/Play.php';
