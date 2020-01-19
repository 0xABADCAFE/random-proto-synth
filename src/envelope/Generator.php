<?php

namespace ABadCafe\Synth\Envelope\Generator;

use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\Packet;
use ABadCafe\Synth\Envelope\Shape;

/**
 * Generator
 *
 * Calculates the continuous signal packet stream for an envelope defined by a given Shape
 */
class LinearInterpolated {

    private
        /** @var Shape $oShape : input Shape */
        $oShape          = null,

        /** @var Packet $oOutputPacket : Buffer for signal */
        $oOutputPacket   = null,

        /** @var Packet $oFinalPacket : Fixed packet filled with the final envelope value */
        $oFinalPacket    = null,

        /** @var {int, float}[] $aProcessPoints : Envelope Shape points, converted into Sample Position => Level pairs */
        $aProcessPoints  = [],

        /** @var int[] $aProcessPoints : Indexes to the Process Points array, keyed by the Sample Position they start at  */
        $aProcessIndexes = [],

        /** @var int $iSamplePosition : Current Sample Position */
        $iSamplePosition = 0,

        /** @var int $iLastPosition : Used to early out and return the fixed packet */
        $iLastPosition   = 0,

        /** @var float $fGradient : Current Interpolant Gradient */
        $fGradient       = 0,

        /** @var float $fYOffset : Current Interpolant Y Offset */
        $fYOffset        = 0,

        /** @var int $iXOffset : Current Interpolant X Offset */
        $iXOffset        = 0
    ;

    /**
     * Constructor
     */
    public function __construct(Shape $oShape) {
        $this->oShape = $oShape;
        $this->oOutputPacket = new Packet();
        $this->oFinalPacket  = new Packet();
        $this->reset();
    }

    /**
     * Get the oscillator sample position, which is the total number of samples generated since
     * instantiation or the last call to reset().
     *
     * @return int
     */
    public function getPosition() : int {
        return $this->iSamplePosition;
    }

    /**
     * Reset the envelope. This resets the sample output position and re-evaluates the Shape in case of any changes.
     *
     * @return self
     */
    public function reset() : self {
        $this->iSamplePosition = 0;
        $this->aProcessPoints  = [];
        $iProcessRate = Context::get()->getProcessRate();
        $fTimeTotal   = 0.0;
        $i = 0;
        foreach ($this->oShape->getAll() as $aPoint) {
            $fTimeTotal += $aPoint[1];
            $iPosition = (int)($fTimeTotal * $iProcessRate);
            $this->aProcessIndexes[$iPosition] = $i;
            $this->aProcessPoints[$i++] = (object)[
                'iStart' => $iPosition,
                'fLevel' => $aPoint[0]
            ];
        }
        $oLastPoint = end($this->aProcessPoints);

        // Pad on the last point again with a slight time offset. This ensures th interpolant code is always acting between a pair
        // of points and avoids wandering off the end of the array.
        $this->aProcessPoints[$i] = (object)[
            'iStart' => $oLastPoint->iStart + 16,
            'fLevel' => $oLastPoint->fLevel
        ];

        $this->iLastPosition = $oLastPoint->iStart;
        $this->oFinalPacket->fillWith($oLastPoint->fLevel);
        return $this;
    }

    /**
     * Emit the next signal Packet.
     *
     * @return Packet
     */
    public function emit() : Packet {

        $iLength = Context::get()->getPacketLength();

        // If we are at the end of the envelope, just return the final packet
        if ($this->iSamplePosition >= $this->iLastPosition) {
            $this->iSamplePosition += $iLength;
            return clone $this->oFinalPacket;
        }

        $oValues = $this->oOutputPacket->getValues();

        for ($i = 0; $i < $iLength; $i++) {
            // If the sample position hits a process index position, we need to recalculate our interpolants
            if (isset($this->aProcessIndexes[$this->iSamplePosition])) {
                $this->updateInterpolants();
            }
            $oValues[$i] = $this->fYOffset + (++$this->iSamplePosition - $this->iXOffset)*$this->fGradient;
        }
        return $this->oOutputPacket;
    }

    /**
     * Calculate the interpolants for the current phase of the envelope
     *
     * @return Packet
     */
    private function updateInterpolants() {
        $iIndex  = $this->aProcessIndexes[$this->iSamplePosition];
        $oPointA = $this->aProcessPoints[$iIndex];
        $oPointB = $this->aProcessPoints[$iIndex + 1];
        $this->fGradient = ($oPointB->fLevel - $oPointA->fLevel) / (float)($oPointB->iStart - $oPointA->iStart);
        $this->fYOffset   = $oPointA->fLevel;
        $this->iXOffset   = $oPointA->iStart;
    }
}
