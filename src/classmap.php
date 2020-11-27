<?php

namespace ABadCafe\Synth;

const CLASS_MAP = [
  'ABadCafe\\Synth\\Output\\IOException' => '/Output.php',
  'ABadCafe\\Synth\\Output\\IPCMOutput' => '/Output.php',
  'ABadCafe\\Synth\\Operator\\InputKind' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\IOperator' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\IProcessor' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\IAmplitudeModulated' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\IPhaseModulated' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\ISource' => '/Operator.php',
  'ABadCafe\\Synth\\Operator\\IOutput' => '/Operator.php',
  'ABadCafe\\Synth\\Controller\\IMIDIByteLimits' => '/Controller.php',
  'ABadCafe\\Synth\\Controller\\IMIDINote' => '/Controller.php',
  'ABadCafe\\Synth\\Controller\\IMIDINoteStandard' => '/Controller.php',
  'ABadCafe\\Synth\\Controller\\TMIDINoteStandardLookup' => '/Controller.php',
  'ABadCafe\\Synth\\Controller\\IMIDINoteEventListener' => '/Controller.php',
  'ABadCafe\\Synth\\Controller\\IMIDINoteStandardEventListener' => '/Controller.php',
  'ABadCafe\\Synth\\Oscillator\\IOscillator' => '/Oscillator.php',
  'ABadCafe\\Synth\\Envelope\\ILimits' => '/Envelope.php',
  'ABadCafe\\Synth\\Envelope\\IGenerator' => '/Envelope.php',
  'ABadCafe\\Synth\\Envelope\\IShape' => '/Envelope.php',
  'ABadCafe\\Synth\\Envelope\\IShaped' => '/Envelope.php',
  'ABadCafe\\Synth\\Signal\\ILimits' => '/Signal.php',
  'ABadCafe\\Synth\\Signal\\IChannelMode' => '/Signal.php',
  'ABadCafe\\Synth\\Signal\\IPacket' => '/Signal.php',
  'ABadCafe\\Synth\\Signal\\IStream' => '/Signal.php',
  'ABadCafe\\Synth\\Signal\\IWaveform' => '/Signal.php',
  'ABadCafe\\Synth\\Signal\\Control\\Packet' => '/signal/Control.php',
  'ABadCafe\\Synth\\Signal\\Control\\IStream' => '/signal/Control.php',
  'ABadCafe\\Synth\\Signal\\TPacketImplementation' => '/signal/Packet.php',
  'ABadCafe\\Synth\\Signal\\Context' => '/signal/Context.php',
  'ABadCafe\\Synth\\Signal\\TContextIndexAware' => '/signal/Context.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Packet' => '/signal/Audio.php',
  'ABadCafe\\Synth\\Signal\\Audio\\IStream' => '/signal/Audio.php',
  'ABadCafe\\Synth\\Signal\\Audio\\IFilter' => '/signal/Audio.php',
  'ABadCafe\\Synth\\Signal\\Control\\Factory' => '/signal/control/Factory.php',
  'ABadCafe\\Synth\\Signal\\Control\\Stream\\FixedMixer' => '/signal/control/Stream.php',
  'ABadCafe\\Synth\\Signal\\Control\\Stream\\SemitonesToMultiplier' => '/signal/control/Stream.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Triangle' => '/signal/waveform/Triangle.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Square' => '/signal/waveform/Square.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Sine' => '/signal/waveform/Sine.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Factory' => '/signal/waveform/Factory.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Noise' => '/signal/waveform/Noise.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\SawUp' => '/signal/waveform/Saw.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\SawDown' => '/signal/waveform/Saw.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Primitive' => '/signal/waveform/Primitive.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\IShaper' => '/signal/waveform/Shaper.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Table' => '/signal/waveform/Table.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Shaper\\FixedPhaseFeedback' => '/signal/waveform/shaper/FixedPhaseFeedback.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Shaper\\Factory' => '/signal/waveform/shaper/Factory.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Shaper\\FixedPhaseFeedbackWithCapacitance' => '/signal/waveform/shaper/FixedPhaseFeedbackWithCapacitance.php',
  'ABadCafe\\Synth\\Signal\\Waveform\\Shaper\\FixedCapacitance' => '/signal/waveform/shaper/FixedCapacitance.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\ICutoffControlled' => '/signal/audio/Filter.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\IResonanceControlled' => '/signal/audio/Filter.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\Base' => '/signal/audio/Filter.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\Resonant' => '/signal/audio/Filter.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Stream\\FixedMixer' => '/signal/audio/Stream.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Stream\\Amplifier' => '/signal/audio/Stream.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Stream\\Modulator' => '/signal/audio/Stream.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\ResonantLowPass' => '/signal/audio/filter/ResonantLowPass.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\Factory' => '/signal/audio/filter/Factory.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\Karlsen' => '/signal/audio/filter/Karlsen.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\KarlsenLowPass' => '/signal/audio/filter/Karlsen.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\KarlsenBandPass' => '/signal/audio/filter/Karlsen.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\KarlsenHighPass' => '/signal/audio/filter/Karlsen.php',
  'ABadCafe\\Synth\\Signal\\Audio\\Filter\\KarlsenNotchReject' => '/signal/audio/filter/Karlsen.php',
  'ABadCafe\\Synth\\Operator\\ControlledFilter' => '/operator/Filter.php',
  'ABadCafe\\Synth\\Operator\\PCMOutput' => '/operator/Output.php',
  'ABadCafe\\Synth\\Operator\\Summing' => '/operator/Summing.php',
  'ABadCafe\\Synth\\Operator\\Factory' => '/operator/Factory.php',
  'ABadCafe\\Synth\\Operator\\Base' => '/operator/Base.php',
  'ABadCafe\\Synth\\Operator\\UnmodulatedOscillator' => '/operator/UnmodulatedOscillator.php',
  'ABadCafe\\Synth\\Operator\\ModulatableOscillator' => '/operator/ModulatableOscillator.php',
  'ABadCafe\\Synth\\Output\\Wav' => '/output/Wav.php',
  'ABadCafe\\Synth\\Output\\Raw' => '/output/Raw.php',
  'ABadCafe\\Synth\\Output\\Raw16BitLittle' => '/output/Raw.php',
  'ABadCafe\\Synth\\Output\\Factory' => '/output/Factory.php',
  'ABadCafe\\Synth\\Output\\Play' => '/output/Play.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\ILimits' => '/oscillator/Control.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\IOscillator' => '/oscillator/Control.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\ILimits' => '/oscillator/Audio.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\IOscillator' => '/oscillator/Audio.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\FixedLFO' => '/oscillator/control/FixedLFO.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\Factory' => '/oscillator/control/Factory.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\Base' => '/oscillator/control/Base.php',
  'ABadCafe\\Synth\\Oscillator\\Control\\ControlledLFO' => '/oscillator/control/ControlledLFO.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\Factory' => '/oscillator/audio/Factory.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\Base' => '/oscillator/audio/Base.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\Prototype' => '/oscillator/audio/Prototye.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\Super' => '/oscillator/audio/Super.php',
  'ABadCafe\\Synth\\Oscillator\\Audio\\Simple' => '/oscillator/audio/Simple.php',
  'ABadCafe\\Synth\\Map\\MIDIByte' => '/map/MIDIByte.php',
  'ABadCafe\\Synth\\Map\\Note\\Factory' => '/map/Factory.php',
  'ABadCafe\\Synth\\Map\\Note\\IMIDINumber' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\Note\\IMIDINumberAware' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\Note\\Invariant' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\Note\\TwelveTone' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\Note\\TwelveToneEqualTemperament' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\Note\\TKeyedSetFactoryUser' => '/map/MIDINote.php',
  'ABadCafe\\Synth\\Map\\KeyedSet' => '/map/KeyedSet.php',
  'ABadCafe\\Synth\\Envelope\\Factory' => '/envelope/Factory.php',
  'ABadCafe\\Synth\\Envelope\\Shape' => '/envelope/Shape.php',
  'ABadCafe\\Synth\\Envelope\\Generator\\TKeyedSetFactoryUser' => '/envelope/generator/KeyedSetFactoryUser.php',
  'ABadCafe\\Synth\\Envelope\\Generator\\KeyedSet' => '/envelope/generator/KeyedSet.php',
  'ABadCafe\\Synth\\Envelope\\Generator\\DecayPulse' => '/envelope/generator/DecayPulse.php',
  'ABadCafe\\Synth\\Envelope\\Generator\\LinearInterpolated' => '/envelope/generator/LinearInterpolated.php',
  'ABadCafe\\Synth\\Patch\\Loader' => '/patch/Loader.php',
  'ABadCafe\\Synth\\Patch\\Module' => '/patch/Module.php',
  'ABadCafe\\Synth\\Utility\\IEnumeratedInstance' => '/utility/EnumeratedInstance.php',
  'ABadCafe\\Synth\\Utility\\TEnumeratedInstance' => '/utility/EnumeratedInstance.php',
  'ABadCafe\\Synth\\Utility\\TEnum' => '/utility/Enum.php',
  'ABadCafe\\Synth\\Utility\\IFactory' => '/utility/Factory.php',
  'ABadCafe\\Synth\\Utility\\TSet' => '/utility/Set.php',
  'ABadCafe\\Synth\\Utility\\TSingleton' => '/utility/Singleton.php',
];