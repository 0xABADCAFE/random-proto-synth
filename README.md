# random-proto-synth

Probably the least practical synthesizer ever realised, implemented in php7.
This code does not conform to any specific PSR standards.

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
Generators represent basic waveform shapes in a time/frequency independent manner. Generators define a numerical period after which for some given input value, the output will be repeated. Generators typically accept a Packet of input values for which a Packet of the corresponding output values will be returned.

### Oscillators
Oscillators are responsible for basic audio signal generation. They accept a Generator and have a settable frequency. They will produce Packets sequentially that represent the given Generator waveform at the specified frequency. Oscillator implementations exist that can accept input Packets that perform phase or amplitude modulation of their Generator output.

### Envelopes
Envelopes are used to control the output level of an Oscillator over time. Envelopes are composed of two key components:
- Shapes: A collection of level/time pairs that are interpolated between over time.
- Generators: Produce a steam of Packet data that represents the envelope level for the discrete sample positions within the Packet.

Envelopes are applied to oscillator output by using the Packet modulate behaviour.

### Operators
Operators are interconnectable units for generation and processing of signals.
- Summing Outputs : Combine the output of multiple operators into a single channel
- Filters : Combines the output of multiple operators and passes through envelope controlled filters
- Modulated Oscillators : Combines the output of multiple operators to be used as amplitue or phase modulation to an envelope controlled oscillator.
