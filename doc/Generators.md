# Generators

## Background

_Generators_ represent basic periodic waveform shapes in a time/frequency independent manner. Generators are used to build _Oscillators_ which produce a time-varying output _Signal_ following the waveform shape.

### Shape

The _Shape_ is the basic curve that the generator follows for increasing values of _x_ and repeats once every _Period_. The _Shape_ is ultimately defined by some numerical function, for example _sine()_.

### Period

The _Period_ is distance _p_ along the _x_-axis after which the _Shape_ repeats. For a simple sinewave generator, the _Period_ is 2_PI_. The _Period_ is constant for a given _Generator_.

### Minimum and Maximum Level

The lower and upper limits for the _y_-axis values of the _Shape_. Generators allow these to to be specifed. For all _Generators_ the default is -1.0 for _Minimum Level_and 1.0 for _Maximum Level_. These can be set differently for a given _Shape_. For example, setting the _Minimum Level_ to 0.0 and _Maximum Level_ to 2.0 has the effect of shifting the _Shape_ up the _y_-axis such that it is never negative,

## Included Generators

The following predifined _Generators_ are included:

### Sine

Basic _sine_ wave, harmonically the simplest _Generator_. The _Period_ of this _Generator_ is 2_PI_.

### Triangle

Simple Triangular wave. Linearly increases from the _Minimum Level_ to the _Maximum Level_ and then linearly decreases back to the _Minimum Level_. Richer in harmonics than _Sine_ but still relatively soft. The _Period_ this _Generator_ is 2.

### Saw Up

Saw tooth wave. Linearly increases from the _Minimum Level_ to the _Maximum Level_ then abruptly resets and repeats. Rich in harmonics. The _Period_ of this _Generator_ is 1.

### Saw Down

Saw tooth wave. Mirror image of the _Saw Up_. Linearly decreases from the _Maximum Level_ to the _Minimum Level_ then abruptly resets and repeats. Rich in harmonics. The _Period_ of this _Generator_ is 1.

### Square

Hard edge sware wave. Alternates between _Minimum Level_ and _Maximum Level_ each half period. Rich in harmonics. The _Period_ of this _Generator_ is 2.

### Noise

Pseudoramdom output (White noise). The _Period_ of this _Generator_ is 1.

