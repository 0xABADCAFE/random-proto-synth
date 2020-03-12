<?php

namespace ABadCafe\Synth\Signal\Audio;
use ABadCafe\Synth\Signal;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Common interface for Audio Paclets.
 */
interface IPacket {

    /**
     * @return MonoPacket
     */
    public function toMono()   : MonoPacket;

    /**
     * @return StereoPacket
     */
    public function toStereo() : StereoPacket;

    /**
     * @param  Signal\Control\Packet $oControlPacket
     * @return IPacket
     */
    public function levelControl(Signal\Control\Packet $oControlPacket) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Audio Packet (Mono)
 */
class MonoPacket extends Signal\BasePacket implements IPacket {
    use Signal\TPacketOperations;

    /**
     * @inheritdoc
     */
    public function toMono() : MonoPacket {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toStereo() : StereoPacket {
        $oPacket = new StereoPacket();
        $oOutput = $oPacket->getValues();
        $oInput  = $this->oValues;
        $i = 0;
        foreach ($oInput as $fValue) {
            $oOutput[$i++] = $fValue;
            $oOutput[$i++] = $fValue;
        }
        return $oPacket;
    }

    /**
     * @inheritdoc
     */
    public function levelControl(Signal\Control\Packet $oControlPacket) : IPacket {
        $oValues = $oControlPacket->oValues;
        foreach ($this->oValues as $i => $fValue) {
            $this->oValues[$i] = $fValue * $oValues[$i];
        }
        return $this;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(Signal\IChannelMode::I_CHAN_MONO);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Audio Packet (Stereo)
 */
class StereoPacket extends Signal\BasePacket {
    use Signal\TPacketOperations;

    /**
     * Convert to mono
     *
     * @return MonoPacket
     */
    public function toMono() : MonoPacket {
        $oPacket = new MonoPacket();
        $oOutput = $oPacket->getValues();
        $oInput  = $this->oValues;
        $i = 0;
        foreach ($oOutput as $j => $fDummy) {
            $oOutput[$j] = 0.5 * ($oInput[$i++] + $oInput[$i++]);
        }
        return $oPacket;
    }

    /**
     * @inheritdoc
     */
    public function toStereo() : StereoPacket {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function levelControl(Signal\Control\Packet $oControlPacket) : IPacket {
        $oValues = $oControlPacket->oValues;
        $i = 0;
        foreach ($oValues as $fValue) {
            $this->oValues[$i++] *= $fValue;
            $this->oValues[$i++] *= $fValue;
        }
        return $this;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oValues = self::initEmptyValues(Signal\IChannelMode::I_CHAN_STEREO);
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Control IStream
 *
 * Interface for generators of Control Packets
 */
interface IMonoStream {

    /**
     * Get the current stream position
     *
     * @return int
     */
    public function getPosition() : int;

    /**
     * Reset the stream
     */
    public function reset() : self;

    /**
     * Get the next control packet
     */
    public function emit() : MonoPacket;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Control IStream
 *
 * Interface for generators of Control Packets
 */
interface IStereoStream {

    /**
     * Get the current stream position
     *
     * @return int
     */
    public function getPosition() : int;

    /**
     * Reset the stream
     */
    public function reset() : self;

    /**
     * Get the next control packet
     */
    public function emit() : StereoPacket;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Adapter class to allow an IStereoStream implementation to be used where an IMonoStream is expected
 */
class StereoToMonoAdapter implements IMonoStream {

    /**
     * @var IStereoStream $oStream
     */
    private $oStream;

    /**
     * @param IStereoStream $oStream
     */
    public function __construct(IStereoStream $oStream) {
        $this->oStream = $oStream;
    }

    /**
     * @inheritdoc
     */
    public function emit() : MonoPacket {
        return $this->oStream->emit()->toMono();
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->oStream->getPosition();
    }

    /**
     * @inheritdoc
     */
    public function reset() : IMonoStream {
        $this->oStream->reset();
        return $this;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Adapter class to allow an IMonoStream implementation to be used where an IStereoStream is expected
 */
class MonoToStereo implements IStereoStream {

    /**
     * @var IMonoStream $oStream
     */
    private $oStream;

    /**
     * @param IStereoStream $oStream
     */
    public function __construct(IMonoStream $oStream) {
        $this->oStream = $oStream;
    }

    /**
     * @inheritdoc
     */
    public function emit() : StereoPacket {
        return $this->oStream->emit()->toStereo();
    }

    /**
     * @inheritdoc
     */
    public function getPosition() : int {
        return $this->oStream->getPosition();
    }

    /**
     * @inheritdoc
     */
    public function reset() : IStereoStream {
        $this->oStream->reset();
        return $this;
    }
}
