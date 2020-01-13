<?php

namespace ABadCafe\Synth\Signal;

/**
 * ILimits
 *
 * Defines limits for signal data.
 */
interface ILimits {
    const
        F_MIN_NOCLIP = -1.0,
        F_MAX_NOCLIP = 1.0
    ;
}

require_once 'signal/Packet.php';
require_once 'signal/Generator.php';
