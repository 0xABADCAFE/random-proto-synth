<?php

/**
 *      _/_/_/  _/_/_/  _/      _/  _/_/_/      _/_/_/
 *   _/          _/    _/_/  _/_/  _/    _/  _/
 *  _/  _/_/    _/    _/  _/  _/  _/_/_/      _/_/
 * _/    _/    _/    _/      _/  _/              _/
 *  _/_/_/  _/_/_/  _/      _/  _/        _/_/_/
 *
 *  - Grossly Impractical Modular PHP Synthesiser -
 *
 */

declare(strict_types = 1);

/**
 * Controller
 */
namespace ABadCafe\Synth\Controller;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Limits for MIDI Byte data
 */
interface IMIDIByteLimits {
    const
        I_MIN_SINGLE_BYTE_VALUE = 0,
        I_MAX_SINGLE_BYTE_VALUE = 127
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Limits for MIDI Byte data
 */
interface IMIDINote {
    const
        CENTRE_REFERENCE = 69 // LMAO: A4
    ;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Standard MIDI note numbers
 */
interface IMIDINoteStandard extends IMIDINote {
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
        'F9'   =>  125, 'F#9'  => 126, 'Gb9'  => 126, 'G9'   => 127
    ];

    /**
     * Maps a note name to a number
     *
     * @param  string $sNote
     * @return int
     * @throws \OutOfBoundsException
     */
    public function getNoteNumber(string $sNote) : int;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Standard trait implementation to provide IMIDINoteStandard::getNoteNumber()
 */
trait TMIDINoteStandardLookup {
    /**
     * @inheritdoc
     */
    public function getNoteNumber(string $sNote) : int {
        if (isset(self::A_NOTE_NAMES[$sNote])) {
            return self::A_NOTE_NAMES[$sNote];
        }
        throw new \OutOfBoundsException();
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Mimimal interface for entities that need to react to MIDI note events
 */
interface IMIDINoteEventListener {

    /**
     * Invoked on a note on event
     *
     * @param int $iNumber   - MIDI note number (0-127)
     * @param int $iVelocity - MIDI note velocity (0-127)
     */
    public function noteOn(int $iNumber, int $iVelocity) : self;

    /**
     * Invoked on a note off event
     *
     * @param int $iNumber   - MIDI note number (0-127)
     */
    public function noteOff(int $iNumber) : self;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Extension interface that allows notenames to be set instead.
 */
interface IMIDINoteStandardEventListener extends IMIDINoteEventListener, IMIDINoteStandard {

    /**
     * Invoked on a note on event
     *
     * @param int $iNumber   - MIDI note number (0-127)
     * @param int $iVelocity - MIDI note velocity (0-127)
     */
    public function noteNameOn(string $sNoteName, int $iVelocity) : self;

    /**
     * Invoked on a note off event
     *
     * @param int $iNumber   - MIDI note number (0-127)
     */
    public function noteNameOff(string $sNoteName) : self;
}
