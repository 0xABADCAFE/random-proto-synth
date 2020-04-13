<?php

declare(strict_types = 1);

namespace ABadCafe\Synth\Utility;
use \OutOfBoundsException;

/**
 * Mixin to allow the definition of enumerated values as distinct value-object types.
 */
trait TEnum {
   /**
    * @var self[] $aDefinedValues, keyed by the scalar
    */
    private static array $aDefinedValues = [];

    /**
     * @var mixed $mValue
     */
    private $mValue = null;

    /**
     * Flyweight.
     *
     * @param  mixed $mValue - should be scalar
     * @return self
     * @throws OutOfBoundsException
     */
    public static function get($mValue) : self {
        if (empty(self::$aDefinedValues)) {
            $oInitial = new self(null);
            foreach ($oInitial->defineAllowedValues() as $mDefinedValue) {
                self::$aDefinedValues[(string)$mDefinedValue] = new self($mDefinedValue);
            }
        }
        $sKey = (string)$mValue;
        if (!isset(self::$aDefinedValues[$sKey])) {
            throw new OutOfBoundsException();
        }
        return self::$aDefinedValues[$sKey];
    }

    /**
     * Return a string representation of this enumeration instance (can override if necessary).
     *
     * @return string
     */
    public function __toString() : string {
        return (string)$this->mValue;
    }

    /**
     * Obtain the value of this enumeration instance.
     *
     * @return mixed
     */
    public function getValue() {
        return $this->mValue;
    }

    /**
     * Return the flyweight set of allowed values for this enumeration type.
     *
     * @return self[]
     */
    public static function getAllowedValues() : array {
        if (empty(self::$aDefinedValues)) {
            $oInitial = new self(null);
            foreach ($oInitial->defineAllowedValues() as $mDefinedValue) {
                self::$aDefinedValues[(string)$mDefinedValue] = new self($mDefinedValue);
            }
        }
        return self::$aDefinedValues;
    }

    /**
     * Return the array of allowed values. The using class must define this.
     *
     * @return mixed[]
     */
    protected abstract function defineAllowedValues() : array;

    /**
     * Constructor.
     *
     * @param mixed $mValue
     */
    private function __construct($mValue) {
        $this->mValue = $mValue;
    }
}
