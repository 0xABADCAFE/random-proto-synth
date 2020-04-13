# random-proto-synth

Probably the least practical synthesizer ever realised, implemented in php7.
This code does not conform to any specific PSR standards.

## Version Requirements
Requires PHP 7.4 or above for the following:
- Typed Properties
- Strict Types
- Covariance

## Key Ideas

### Signals
Internally, all audio and control signals are handled as floating point data, processed at a global "processing rate", which, when ultimately rendered to the output is equivalent to sample rate.

### Packets
Signal data are generated and processed in discrete Packets that contain a globally configured number of entries. Packets support a number of elemental operations, including:
- Filling: Setting all entries to a fixed value.
- Scaling: Multiplying all entries by a fixed value, e.g. amplification or attenuation.
- Summation: Adding each value in one Packet with the corresponding value in another, e.g. mixing of signals.
- Modulation: Multiplying each value in one Packet with the corresponding value in another Packet, e.g. amplitude modulation of signals.

### Generators
Generators represent basic periodic waveform shapes in a time/frequency independent manner. Generators define the numerical period after they repeat. Generators typically accept a Packet of input values for which a Packet of the corresponding output values will be returned. Generators can have their minimum and maximum output levels specified. Generators are provided for:
- Flat : (DC) fixed level output.
- Sine : Basic sine wave
- Triangle: Triangle wave
- Sawtooth: Sawtooth wave, in both rising and falling edge variants
- Square: Hard square wave
- Noise: White noise

### Oscillators
Oscillators are responsible for basic audio signal generation. They accept a Generator and have a settable frequency and phase offset. They will produce Packets sequentially that represent the given Generator waveform at the specified frequency. Oscillator implementations exist that can accept input Packets that perform phase or amplitude modulation of their Generator output. The following oscillators are provided:
- Simple : Basic pitch / phase modulatable oscillator
- Morphing : Cycles between two input Generators using a third Generator as a blending function
- Super: Uses a single Generator but with a definable harmonic/phase stack.

### Filters
Filters are responsible for performing subtractive synthesis by cutting frequencies a desired part of the spectrum. Filters may also be resonant in which case the frequencies close to the cutoff can be boosted.

### Envelopes
Envelopes are used to control some value, such as an Oscillator output volume, over time. Envelopes are composed of two key components:
- Shapes: A collection of level/time pairs that are interpolated between over time.
- Generators: Produce a steam of Packet data that represents the envelope level for the discrete sample positions within the Packet.

### Operators
Operators are interconnectable units for generation and processing of signals.
- Summing Outputs : Combine the output of multiple operators into a single channel
- Filters : Combines the output of multiple operators and passes through envelope controlled filters
- Modulated Oscillators : Combines the output of multiple operators to be used as amplitue or phase modulation to an envelope controlled oscillator.

### NoteMaps
NoteMaps are property lookup tables that map a MIDI Note Number to some value. NoteMaps are used to control fundamental synthesis features that are expected to vary as a function of the note played. For example:
- Base frequency of an Oscillator
- Envelope levels and velocities
