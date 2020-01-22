<?php

namespace ABadCafe\Synth\Utility;

/**
 * Interface for entities that have a runtime enumerated instance ID
 */
interface IEnumeratedInstance {

    /**
     * @return int
     */
    public function getInstanceID() : int;
}

/**
 * Common mixin for implementors of the IEnumeratedInstance interface
 */
trait TEnumeratedInstance {

    /** @var int */
    private static $iNextInstanceID = 0;
    private $iInstanceID = 0;

    private function assignInstanceID() {
        $this->iInstanceID = ++self::$iNextInstanceID;
        echo self::class, ":", $this->iInstanceID, "\n";
    }

    public function getInstanceID() : int {
        return $this->iInstanceID;
    }
}
