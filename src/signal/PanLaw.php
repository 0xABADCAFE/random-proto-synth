<?php

namespace ABadCafe\Synth\Signal\PanLaw;

use ABadCafe\Synth\Signal\ILimits;
use ABadCafe\Synth\Signal\Context;
use ABadCafe\Synth\Signal\MonoPacket;
use ABadCafe\Synth\Signal\StereoPacket;
use ABadCafe\Synth\Signal\IChannelMode;
use ABadCafe\Synth\Signal\IPanLaw;
use \SPLFixedArray;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Base - common code for PanLaw implementations.
 */
abstract class Base implements IPanLaw {

    /**
     * @var Packet $oPrototyope
     */
    protected static $oOutputPrototype = null;

    /**
     * Constructor
     */
    public function __construct() {
        if (null === self::$oOutputPrototype) {
            self::$oOutputPrototype = new StereoPacket();
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Linear - simple linear pan law.
 *
 * When the pan value is -1.0, the left output is 1.0 and the right output is 0.0
 * When the pan value is 0.0, the left and right outputs are 0.5
 * When the pan value is 1.0, the left output is 0.0 and the right output is 1.0
 *
 */
class Linear extends Base {

    /**
     * @inheritdoc
     */
    public function map(MonoPacket $oPanPacket) : StereoPacket {
        $oOutputPacket = clone self::$oOutputPrototype;
        $oOutput = $oOutputPacket->getValues();
        $i = 0;
        foreach ($oPanPacket->getValues() as $fPanPosition) {
            $fLevel = 0.5 * (1.0 + $fPanPosition);
            $oOutput[$i++] = 1.0 - $fLevel;
            $oOutput[$i++] = $fLevel;
        }
        return $oOutputPacket;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * CentreMax - linear, clamped maximum at the centre value. This results in an attenuation of the right channel as the
 * position is panned left of centre, and vice versa.
 *
 * The intent of this PanLaw is primarily for FM operation where we desire that a centre panned stereo signal has the
 * same modulation impact as a genuine mono signal.
 */
class CentreMax extends Base {

    /**
     * @inheritdoc
     */
    public function map(MonoPacket $oPanPacket) : StereoPacket {
        $oOutputPacket = clone self::$oOutputPrototype;
        $oOutput = $oOutputPacket->getValues();
        $i = 0;
        foreach ($oPanPacket->getValues() as $fPanPosition) {
            $oOutput[$i++] = $fPanPosition > 0 ? 1.0 - $fPanPosition : 1.0;
            $oOutput[$i++] = $fPanPosition < 0 ? 1.0 + $fPanPosition : 1.0;
        }
        return $oOutputPacket;
    }
}
