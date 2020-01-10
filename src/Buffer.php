<?php

namespace Synth\Buffer;

/**
 * Synth\Buffer\SignalPacket
 *
 * Represents a packet of a signal, quantised by sample rate but with continuous (float) intensity
 */
class SignalPacket implements \Countable {

    private
        $aSamples,
        $iLength
    ;

    public function __construct(array $aSamples) {
        $this->aSamples = $aSamples;
        $this->iLength  = count($aSamples);
    }

    /**
     * Get packet length
     *
     * @return int
     */
    public function count() : int {
        return $this->iLength;
    }

    /**
     * Perform an amplitude modulation with another packet. The values in this packet are multiplied
     * by the values in the supplied packet. Where the incoming packet length is different, only the
     * overlapping region, aligned at the start is processed.
     *
     * @param  SignalPacket $oPacket
     * @return SignalPacket fluent
     */
    public function multiply(SignalPacket $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] *= $oPacket->aSamples[$i];
        }
        return $this;
    }

    /**
     * Perform an amplitude summation with another packet. The values in this packet are summed with the
     * values in the supplied packet. Where the incoming packet length is different, only the overlapping
     * region, aligned at the start is processed.
     *
     * @param  SignalPacket $oPacket
     * @return SignalPacket fluent
     */
    public function add(SignalPacket $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] += $oPacket->aSamples[$i];
        }
        return $this;
    }

    /**
     * Perform an amplitude difference with another packet. The values in the supplied packet are subtracted
     * from the values in this packet. Where the incoming packet length is different, only the overlapping
     * region, aligned at the start is processed.
     *
     * @param  SignalPacket $oPacket
     * @return SignalPacket fluent
     */
    public function subtract(SignalPacket $oPacket) : self {
        $iMax = min($this->iLength, $oPacket->iLength);
        for ($i = 0; $i < $iMax; ++$i) {
            $this->aSamples[$i] -= $oPacket->aSamples[$i];
        }
        return $this;
    }
}

