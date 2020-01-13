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
        F_MAX_NOCLIP = 1.0,
        F_P2P_NOCLIP = self::F_MAX_NOCLIP - self::F_MIN_NOCLIP
    ;
}

require_once 'signal/Packet.php';
require_once 'signal/Generator.php';
