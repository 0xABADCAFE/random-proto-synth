# random-proto-synth

Probably the least practical synthesizer ever realised, implemented in php7.
This code does not conform to any specific PSR standards.

## Key Ideas

### Signals
Internally, all audio and control signals are handled as floating point data, processed at a global "processing rate", which, when ultimately rendered to the output is equivalent to sample rate.

### Packets
Signal data are generated and processed in discrete Packets that contain a globally configured number of entries. Packets support a number of elemental operations, including summation, modulation, etc.

### Generators
Generators represent basic waveform shapes in a time/frequency independent manner. Generators define a numerical period after which for some given input value, the output will be repeated. Generators typically accept a Packet of input values for which a Packet of the corresponding output values will be returned.

### Oscillators
Oscillators are responsible for basic audio signal generation. They accept a Generator and have a settable frequency. They will produce Packets sequentially that represent the given Generator waveform at the specified frequency. Oscillator implementations exist that can accept input Packets that perform phase or amplitude modulation of their Generator output.

### Envelopes
TODO

### Outputs
TODO
