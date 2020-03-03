<?php

namespace ABadCafe\Synth\Signal\Control;
use ABadCafe\Synth\Signal\TPacket;
use \SPLFixedArray;

use function ABadCafe\Synth\Utility\clamp;

class Packet {
    use TPacket;

    public function __construct() {
        // just for testing
        $this->oValues = new SPLFixedArray(16);
    }
}
