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

namespace ABadCafe\Synth\Envelope\Generator;
use ABadCafe\Synth\Envelope;

/**
 * TKeyedSetUser user trait - for entities that rely on KeyedSet of Envelope\Generator
 *
 */
trait TKeyedSetFactoryUser {
    /**
     * @var KeyedSet|null $oPredefinedEnvelopeSet
     */
    private ?KeyedSet $oPredefinedEnvelopeSet = null;

    /**
     * Set predefined note maps that can be referred to in the envelope description.
     *
     * @param  KeyedSet|null $oSet
     * @return self
     */
    public function setPredefinedEnvelopes(?KeyedSet $oSet) : self {
        $this->oPredefinedEnvelopeSet = $oSet;
        return $this;
    }

    /**
     * @param  object|string $mDescription
     * @return Envelope\IGenerator|null
     */
    private function getEnvelope($mDescription) : ?Envelope\IGenerator {
        if (is_object($mDescription)) {
            return Envelope\Factory::get()->createFrom($mDescription);
        }
        if (
            $this->oPredefinedEnvelopeSet &&
            is_string($mDescription)
        ) {
            return $this->oPredefinedEnvelopeSet->get($mDescription);
        }
        return null;
    }
}
