# WaveShapers

## Background

_WaveShapers_ are modifiers that can be used in conjunction with _Generators_ to modify their basic output in interesting ways. Unlike most components which process _Signal_ data in _Packets_ a _WaveShaper_ operates per value in a _Packet_. A _WaveShaper_ performs two distinct modifications:
1. Phase adjustment. The input values in the _Packet_ are modified before being passed to the fundamental function of the _Generator_.
2. Amplitude adjustment. The output value of the function is modified.

The key functionality that the _WaveShaper_ provides is the ability to make successive values in the output _Packet_ of the _Generator_ depend on each other in ways that are otherwise not practical when dealing with processing entire _Packets_.

## Included WaveShapers

The following predifined _WaveShapers_ are included:

### FixedCapacitance

This _WaveShaper_ models a capacitave load on the output of a _Generator_. This modifies the next output value by weighting it with previous ones. This results in softening the leading edge of any sudden transition. When applied to a basic squarewave _Generator_ the effect is to soften the leading edges in a manner similar to the charge and discharge of a capacitor. This _WaveShaper_ performs amplitude modification only. The strength of the effect is set on construction and cannot change.

### FixedPhaseFeedback

This _WaveShaper_ models the Feedback behaviour of classic FM synthesis. This uses an average of the most recent output values as a phase modification for the input of the next value. This_WaveShaper_ performs phase modification only. When applied to a basic sinewave _Generator_ the effect is to move the waveform towards a sawtooth. The strength of the effect is set on construction and cannot change.

### FixedPhaseFeedbackWithCapacitance

This _WaveShaper_ is a union of the _FixedPhaseFeedback_ and _FixedCapacitance_ performing both operations simultaneously.
