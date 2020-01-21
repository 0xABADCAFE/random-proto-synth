<?php

namespace ABadCafe\Synth\Signal;

use \LogicException;
use \RangeException;

/**
 * Context Singleton
 *
 * Manages one time initiated global properties
 */
class Context {

    /** @var Context $oInstance */
    private static $oInstance = null;

    private
        $iProcessRate,
        $iPacketLength
    ;

    /**
     * One time initialisation. If this is not called before we first obtain the context, the defaults will be used.
     * Throws if called more than once as the initialisation is global and should not be changed.
     *
     * @param  int $iProcessRate
     * @param  int $iPacketLengthExp
     * @throws LogicException
     */
    public static function init(int $iProcessRate, int $iPacketLengthExp) {
        if (self::$oInstance) {
            throw new LogicException('Context already initialised');
        }
        self::$oInstance = new self($iProcessRate, 1<<$iPacketLengthExp);
    }

    /**
     * Singleton
     *
     * @return Context
     */
    public static function get() : self {
        if (!self::$oInstance) {
            self::$oInstance = new self(
                ILimits::I_DEF_PROCESS_RATE,
                ILimits::I_DEF_PACKET_LENGTH
            );
        }
        return self::$oInstance;
    }

    /**
     * Get the signal processing rate (Hz)
     *
     * @return int
     */
    public function getProcessRate() : int {
        return $this->iProcessRate;
    }

    /**
     * Get the signal packet length (samples)
     *
     * @return int
     */
    public function getPacketLength() : int {
        return $this->iPacketLength;
    }

    /**
     * Private constructor
     *
     * @param  int $iProcessRate
     * @param  int $iPacketLength
     * @throws RangeException
     */
    private function __construct(int $iProcessRate, int $iPacketLength) {
        if (
            $iProcessRate < ILimits::I_MIN_PROCESS_RATE ||
            $iProcessRate > ILimits::I_MAX_PROCESS_RATE
        ) {
            throw new RangeException(
                'Unsupported Processing Rate ' . $iProcessRate .
                '['   . ILimits::I_MIN_PROCESS_RATE .
                '...' . ILimits::I_MAX_PROCESS_RATE .
                ']'
            );
        }
        if (
            $iPacketLength < ILimits::I_MIN_PACKET_LENGTH ||
            $iPacketLength > ILimits::I_MAX_PACKET_LENGTH
        ) {
            throw new RangeException(
                'Unsupported Packet Length ' . $iPacketLength .
                '['   . ILimits::I_MIN_PACKET_LENGTH .
                '...' . ILimits::I_MAX_PACKET_LENGTH .
                ']'
            );
        }
        $this->iProcessRate  = $iProcessRate;
        $this->iPacketLength = $iPacketLength;
    }
}
