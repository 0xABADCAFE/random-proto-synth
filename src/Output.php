<?php

namespace ABadCafe\Synth\Output;

use \Exception;
use ABadCafe\Synth\Signal\Packet;

/**
 *
 */
class IOException extends Exception {};

/**
 *
 */
interface IPCMOutput {
    /**
     * @param  string $sPath
     * @throws IOException
     */
    public function open(string $sPath);

    /**
     * @param  Packet $oPacket
     * @throws IOException
     */
    public function write(Packet $oPacket);

    /**
     *
     */
    public function close();
}

require_once 'output/Raw.php';
