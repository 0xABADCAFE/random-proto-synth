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
    protected $iInstanceID = 0;

    protected function assignInstanceID() {
        $this->iInstanceID = ++self::$iNextInstanceID;
        echo self::class, ":", $this->iInstanceID, "\n";
    }

    /**
     * Get the instance ID associated with this entity
     */
    public function getInstanceID() : int {
        return $this->iInstanceID;
    }
}
