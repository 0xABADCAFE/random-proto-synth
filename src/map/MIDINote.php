<?php

namespace ABadCafe\Synth\Map\Note;

use ABadCafe\Synth\Map\MIDIByte;
use \OutOfBoundsException;

use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IMIDINumber
 *
 * Enumerates MIDI note numbers
 */
interface IMIDINumber {
    const
        CENTRE_REFERENCE = 69; // LMAO: A4

    /**
     * Maps a note name to a number
     *
     * @param  string $sNote
     * @return int
     * @throws OutOfBoundsException
     */
    public function getNoteNumber(string $sNote) : int;

    /**
     * Invokes mapByte() for a named note
     *
     * @param  string $sNote
     * @return float
     * @throws OutOfBoundsException
     */
    public function mapNote(string $sNote) : float;

};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IMIDINumberAware
 *
 * Interface for component systems that use Map\Note\IMIDINumber entities
 */
interface IMIDINumberAware {

    /**
     * Obtain a list of use cases that IMIDINumber maps can be set for.
     *
     * @return string[]
     */
    public function getNoteNumberMapUseCases() : array;

    /**
     * Set a new Note Map. An implementor may use multiple Note Maps for multiple things, for exanple, the effect of
     * note number on envelope speeds, amplitudes, filter cutoff etc. The use cases are specific to the implementor.
     *
     * @param  IMIDINumber $oNoteMap
     * @param  string      $sUseCase
     * @return self
     */
    public function setNoteNumberMap(IMIDINumber $oNoteMap, string $sUseCase) : self;

    /**
     * Get the current Note Map.
     *
     * @param string $sUseCase
     *
     * @return IMIDINumber
     */
    public function getNoteNumberMap(string $sUseCase) : IMIDINumber;

    /**
     * Set the note number to use. The expectation is that the consuming class will use the Note Map to derive some
     * control paramter base on the note.
     *
     * @param  int $iNote
     * @return self
     * @throws OutOfRangeException
     */
    public function setNoteNumber(int $iNote) : self;

    /**
     * Set the note to use, by name. The expectation is that the consuming class will use the Note Map to derive some
     * control paramter base on the note.
     *
     * @param  string $sNote
     * @return self
     * @throws OutOfBoundsException
     */
    public function setNoteName(string $sNote) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Invariant
 *
 * Used to allow IMIDINumberAware implementors to have a default "do nothing" map. All note values map to 1.0
 */
class Invariant extends MIDIByte implements IMIDINumber {

    private static $oInstance = null;

    /**
     * Singleton
     *
     * @retuurn self
     */
    public static function get() : self {
        if (!self::$oInstance) {
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }

    /**
     * @inheritdoc
     */
    public function mapNote(string $sNote) : float {
        return 1.0;
    }

    /**
     * @inheritdoc
     */
    public function getNoteNumber(string $sNote) : int {
        return self::CENTRE_REFERENCE;
    }

    /**
     * @inheritdoc
     */
    protected function populateMap() {
        for ($i = self::I_MIN_SINGLE_BYTE_VALUE; $i <= self::I_MAX_SINGLE_BYTE_VALUE; ++$i) {
            $this->oMap[$i] = 1.0;
        }
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * TwelveTone
 *
 * Base class for all Twelve Tone Map classes. This defines all the allowed note names, mapping them back to a
 * compatible MIDI note number and provies a method for getting a mapped value by note name.
 */
abstract class TwelveTone extends MIDIByte implements IMIDINumber {

    /** @const int I_SEMIS_PER_OCTAVE */
    const I_SEMIS_PER_OCTAVE = 12;

    /** @const int[] A_NOTE_NAMES - keyed by note name */
    const A_NOTE_NAMES = [
        'C-1'  =>    0, 'C#-1' =>   1, 'Db-1' =>   1, 'D-1'  =>   2, 'D#-1' =>   3, 'Eb-1' =>   3, 'E-1'  =>   4,
        'F-1'  =>    5, 'F#-1' =>   6, 'Gb-1' =>   6, 'G-1'  =>   7, 'G#-1' =>   8, 'Ab-1' =>   8, 'A-1'  =>   9,
        'A#-1' =>   10, 'Bb-1' =>  10, 'B-1'  =>  11,

        'C0'   =>   12, 'C#0'  =>  13, 'Db0'  =>  13, 'D0'   =>  14, 'D#0'  =>  15, 'Eb0'  =>  15, 'E0'   =>  16,
        'F0'   =>   17, 'F#0'  =>  18, 'Gb0'  =>  18, 'G0'   =>  19, 'G#0'  =>  20, 'Ab0'  =>  20, 'A0'   =>  21,
        'A#0'  =>   22, 'Bb0'  =>  22, 'B0'   =>  23,

        'C1'   =>   24, 'C#1'  =>  25, 'Db1'  =>  25, 'D1'   =>  26, 'D#1'  =>  27, 'Eb1'  =>  27, 'E1'   =>  28,
        'F1'   =>   29, 'F#1'  =>  30, 'Gb1'  =>  30, 'G1'   =>  31, 'G#1'  =>  32, 'Ab1'  =>  32, 'A1'   =>  33,
        'A#1'  =>   34, 'Bb1'  =>  34, 'B1'   =>  35,

        'C2'   =>   36, 'C#2'  =>  37, 'Db2'  =>  37, 'D2'   =>  38, 'D#2'  =>  39, 'Eb2'  =>  39, 'E2'   =>  40,
        'F2'   =>   41, 'F#2'  =>  42, 'Gb2'  =>  42, 'G2'   =>  43, 'G#2'  =>  44, 'Ab2'  =>  44, 'A2'   =>  45,
        'A#2'  =>   46, 'Bb2'  =>  46, 'B2'   =>  47,

        'C3'   =>   48, 'C#3'  =>  49, 'Db3'  =>  49, 'D3'   =>  50, 'D#3'  =>  51, 'Eb3'  =>  51, 'E3'   =>  52,
        'F3'   =>   53, 'F#3'  =>  54, 'Gb3'  =>  54, 'G3'   =>  55, 'G#3'  =>  56, 'Ab3'  =>  56, 'A3'   =>  57,
        'A#3'  =>   58, 'Bb3'  =>  58, 'B3'   =>  59,

        'C4'   =>   60, 'C#4'  =>  61, 'Db4'  =>  61, 'D4'   =>  62, 'D#4'  =>  63, 'Eb4'  =>  63, 'E4'   =>  64,
        'F4'   =>   65, 'F#4'  =>  66, 'Gb4'  =>  66, 'G4'   =>  67, 'G#4'  =>  68, 'Ab4'  =>  68, 'A4'   =>  69,
        'A#4'  =>   70, 'Bb4'  =>  70, 'B4'   =>  71,

        'C5'   =>   72, 'C#5'  =>  73, 'Db5'  =>  73, 'D5'   =>  74, 'D#5'  =>  75, 'Eb5'  =>  75, 'E5'   =>  76,
        'F5'   =>   77, 'F#5'  =>  78, 'Gb5'  =>  78, 'G5'   =>  79, 'G#5'  =>  80, 'Ab5'  =>  80, 'A5'   =>  81,
        'A#5'  =>   82, 'Bb5'  =>  82, 'B5'   =>  83,

        'C6'   =>   84, 'C#6'  =>  85, 'Db6'  =>  85, 'D6'   =>  86, 'D#6'  =>  87, 'Eb6'  =>  87, 'E6'   =>  88,
        'F6'   =>   89, 'F#6'  =>  90, 'Gb6'  =>  90, 'G6'   =>  91, 'G#6'  =>  92, 'Ab6'  =>  92, 'A6'   =>  93,
        'A#6'  =>   94, 'Bb6'  =>  94, 'B6'   =>  95,

        'C7'   =>   96, 'C#7'  =>  97, 'Db7'  =>  97, 'D7'   =>  98, 'D#7'  =>  99, 'Eb7'  =>  99, 'E7'   => 100,
        'F7'   =>  101, 'F#7'  => 102, 'Gb7'  => 102, 'G7'   => 103, 'G#7'  => 104, 'Ab7'  => 104, 'A7'   => 105,
        'A#7'  =>  106, 'Bb7'  => 106, 'B7'   => 107,

        'C8'   =>  108, 'C#8'  => 109, 'Db8'  => 109, 'D8'   => 110, 'D#8'  => 111, 'Eb8'  => 111, 'E8'   => 112,
        'F8'   =>  113, 'F#8'  => 114, 'Gb8'  => 114, 'G8'   => 115, 'G#8'  => 116, 'Ab8'  => 116, 'A8'   => 117,
        'A#8'  =>  118, 'Bb8'  => 118, 'B8'   => 119,

        'C9'   =>  120, 'C#9'  => 121, 'Db9'  => 121, 'D9'   => 122, 'D#9'  => 123, 'Eb9'  => 123, 'E9'   => 124,
        'F9'   =>  125, 'F#9'  => 126, 'Gb9'  => 126, 'G9'   => 127,


        //'G#9'  => 128, 'Ab9'  => 128, 'A9'   => 129, 'A#9'  => 130, 'Bb9'  => 130, 'B9'  => 131,
    ];

    /**
     * @inheritdoc
     */
    public function getNoteNumber(string $sNote) : int {
        if (isset(self::A_NOTE_NAMES[$sNote])) {
            return self::A_NOTE_NAMES[$sNote];
        }
        throw new OutOfBoundsException();
    }

    /**
     * @inheritdoc
     */
    public function mapNote(string $sNote) : float {
        return $this->mapByte($this->getNoteNumber($sNote));
    }

    public function debug() {
        $aStrings = [];
        foreach (self::A_NOTE_NAMES as $sNote => $iNote) {
            $aStrings[] = "\t" . $sNote . ' (' . $iNote . ') : ' . $this->oMap[$iNote];
        }
        dprintf(
            "%s()\n%s\n",
            __METHOD__,
            implode("\n", $aStrings)
        );
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * TwelveToneEqualTemperament
 *
 * Maps note number to a multiplier (centred on A4) where the the multiplier has a set value at the CENTRE_REFERNCE
 * note and scales up and down per octave based on a scaling rate.
 */
class TwelveToneEqualTemperament extends TwelveTone {

    private
        /**
         * @var float $fCentreValue - value at CENTRE_REFERENCE
         */
        $fCentreValue,

        /**
         * @var float $fScalePerOctave
         */
        $fScalePerOctave,

        /**
         * @var bool $bInversed
         */
        $bInversed
    ;

    /**
     * Constructor
     *
     * @param float $fCentreValue    - Defines the value at the CENTRE_REFERENCE note
     * @param float $fScalePerOctave - Defines the scaling per octave; 1.0 gives a standard doubling per octave.
     *
     */
    public function __construct(
        float $fCentreValue    = 1.0,
        float $fScalePerOctave = 1.0,
        bool  $bInversed       = false
    ) {
        $this->fCentreValue    = $fCentreValue;
        $this->fScalePerOctave = $fScalePerOctave;
        $this->bInversed       = $bInversed;
        parent::__construct();
    }

    /**
     * @todo - make this defer to a global tuning value
     *
     * @return self
     */
    public static function getStandardNoteMap() : self {
        static $oMap = null;
        if (null === $oMap) {
            $oMap = new self(440);
        }
        return $oMap;
    }

    /**
     * Get the centre value, i.e. the value for the CENTRE_REFERENCE note number.
     *
     * @return float
     */
    public function getCentreValue() : float {
        return $this->fCentreValue;
    }

    /**
     * Get the scale per octave value.
     *
     * @return float
     */
    public function getScalePerOctave() : float {
        return $this->fScalePerOctave;
    }

    /**
     * @inheritdoc
     */
    protected function populateMap() {
        for ($i = self::I_MIN_SINGLE_BYTE_VALUE; $i <= self::I_MAX_SINGLE_BYTE_VALUE; ++$i) {

            $fValue = (2**(
                $this->fScalePerOctave * ($i - self::CENTRE_REFERENCE) / self::I_SEMIS_PER_OCTAVE)
            );

            $this->oMap[$i] = $this->fCentreValue * ($this->bInversed ? 1.0 / $fValue : $fValue);
        }
    }
}
