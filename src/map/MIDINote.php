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

namespace ABadCafe\Synth\Map\Note;
use ABadCafe\Synth\Map;
use ABadCafe\Synth\Controller;
use \OutOfBoundsException;
use function ABadCafe\Synth\Utility\dprintf;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * IMIDINumber
 *
 * Enumerates MIDI note numbers
 */
interface IMIDINumber extends Controller\IMIDINoteStandard {

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
class Invariant extends Map\MIDIByte implements IMIDINumber {

    private static ?self $oInstance = null;

    /**
     * Singleton
     *
     * @return self
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
abstract class TwelveTone extends Map\MIDIByte implements IMIDINumber, Controller\IMIDINoteStandard {

    use Controller\TMIDINoteStandardLookup;

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

    private float
        $fCentreValue,   // value at CENTRE_REFERENCE
        $fScalePerOctave
    ;

    private bool $bInversed;

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * TKeyedSetUser user trait - for entities that rely on KeyedSet of Map\Note\IMIDINumber
 */
trait TKeyedSetFactoryUser {
    /**
     * @var Map\KeyedSet|null $oPredefinedNoteMapSet
     */
    private ?Map\KeyedSet $oPredefinedNoteMapSet = null;

    /**
     * Set predefined note maps that can be referred to in the envelope description.
     *
     * @param  Map\KeyedSet|null $oSet
     * @return self
     */
    public function setPredefinedNoteMaps(?Map\KeyedSet $oSet) : self {
        $this->oPredefinedNoteMapSet = $oSet;
        return $this;
    }

    /**
     * @param  object|string $mDescription
     * @return IMIDINumber|null
     */
    private function getNoteMap($mDescription) : ?IMIDINumber {
        if (is_object($mDescription)) {
            return Envelope\Factory::get()->createFrom($mDescription);
        }
        if (
            $this->oPredefinedNoteMapSet &&
            is_string($mDescription)
        ) {
            return $this->oPredefinedNoteMapSet->get($mDescription);
        }
        return null;
    }
}
