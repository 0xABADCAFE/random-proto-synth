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

namespace ABadCafe\Synth;
require_once '../Synth.php';

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_OUTPUT_EXAMPLES = '[
    {
        "comment":"Output Operator: Raw 16 bit output to a file",
        "type":"output",
        "destination":{
            "type":"raw",
            "path":"output/dummy.bin"
        }
    },
    {
        "comment":"Output Operator: Default output render to a file",
        "type":"output",
        "destination":{
            "type":"wav",
            "path":"output/dummy.wav"
        }
    },
    {
        "comment":"Output Operator: 8-bit render to a file",
        "type":"output",
        "destination":{
            "type":"wav",
            "path":"output/dummy8.wav",
            "bits":8
        }
    },
    {
        "comment":"Output Operator: Realtime via soxplay",
        "type":"output",
        "destination":{
            "type":"play"
        }
    }
]';

foreach(json_decode(S_OUTPUT_EXAMPLES) as $oDefinition) {
    $oOperator = Operator\Factory::get()->createFrom($oDefinition);
    echo $oOperator, "\n\n";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_SUMMING_EXAMPLES  = '[
    {
        "comment":"Mixing Operator: nothing more to do here",
        "type":"mixer"
    }
]';

foreach(json_decode(S_SUMMING_EXAMPLES) as $oDefinition) {
    $oOperator = Operator\Factory::get()->createFrom($oDefinition);
    echo $oOperator, "\n\n";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_FILTER_EXAMPLES  = '[
    {
        "comment":"Filter Operator: Trivial Lowpass",
        "type":"filter",
        "model":{
            "type":"lowpass",
            "cutoff":2.0,
            "resonance":3.5
        }
    },
    {
        "comment":"Filter Operator: Lowpass with LFO controlled Cutoff",
        "type":"filter",
        "model":{
            "type":"lowpass",
            "cutoff":2.0,
            "resonance":3.5
        },
        "cutoff_control":{
            "type":"oscillator",
            "config":{
                "type":"lfo",
                "rate":15.0,
                "depth":0.66,
                "generator":{
                    "type":"sine",
                    "min":-0.75,
                    "max":0.75
                }
            }
        }
    },
    {
        "comment":"Filter Operator: Lowpass with envelope controlled Cutoff",
        "type":"filter",
        "model":{
            "type":"lowpass",
            "cutoff":2.0,
            "resonance":3.5
        },
        "cutoff_control":{
            "type":"envelope",
            "config":{
                "comment":"Custom envelope shape with rate and level key scales",
                "type":"custom",
                "shape":{
                    "initial":0.0,
                    "points":[
                        [1.0, 0.1],
                        [0.75, 1.0],
                        [0.25, 5.0],
                        [0.0, 10]
                    ]
                },
                "keyscale_speed":{
                    "type":"12tone_scaled",
                    "center":0.75,
                    "scale":1.25,
                    "invert":false
                },
                "keyscale_level":{
                    "type":"12tone_scaled",
                    "center":1.0,
                    "scale":1.0,
                    "invert":true
                }
            }
        }
    },
    {
        "comment":"Filter Operator: Bandpass with envelope controlled Cutoff",
        "type":"filter",
        "model":{
            "type":"karlsen",
            "mode":"bandpass",
            "cutoff":2.0,
            "resonance":3.5
        },
        "cutoff_control":{
            "type":"envelope",
            "config":{
                "comment":"Custom envelope shape with rate and level key scales",
                "type":"custom",
                "shape":{
                    "initial":0.0,
                    "points":[
                        [1.0, 0.1],
                        [0.75, 1.0],
                        [0.25, 5.0],
                        [0.0, 10]
                    ]
                },
                "keyscale_speed":{
                    "type":"12tone_scaled",
                    "center":0.75,
                    "scale":1.25,
                    "invert":false
                },
                "keyscale_level":{
                    "type":"12tone_scaled",
                    "center":1.0,
                    "scale":1.0,
                    "invert":true
                }
            }
        },
        "resonance_control":{
            "type":"oscillator",
            "config":{
                "type":"lfo",
                "rate":15.0,
                "depth":0.66,
                "generator":{
                    "type":"triangle",
                    "min":-0.75,
                    "max":0.75
                }
            }
        }
    }
]';

foreach(json_decode(S_FILTER_EXAMPLES) as $oDefinition) {
    $oOperator = Operator\Factory::get()->createFrom($oDefinition);
    echo $oOperator, "\n\n";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const S_OSCILLATOR_EXAMPLES  = '[
    {
        "comment":"Oscillator Operator: Trivial Sine wave",
        "type":"oscillator",
        "unmodulated":true,
        "model":{
            "type":"simple",
            "generator":{
                "type":"sine",
                "min":-1.0,
                "max":1.0
            }
        }
    },
    {
        "comment":"Oscillator Operator: Trivial Saw wave, with level envelope and pitch LFO",
        "type":"oscillator",
        "unmodulated":true,
        "model":{
            "type":"simple",
            "generator":{
                "type":"sawdown",
                "min":-1.0,
                "max":1.0
            }
        },
        "level_control":{
            "type":"envelope",
            "config":{
                "comment":"Custom envelope shape",
                "type":"custom",
                "shape":{
                    "initial":0.0,
                    "points":[
                        [1.0, 0.1],
                        [0.75, 1.0],
                        [0.25, 5.0],
                        [0.0, 10]
                    ]
                }
            }
        },
        "pitch_control":{
            "comment":"simple unchanging 5Hz LFO",
            "type":"oscillator",
            "config":{
                "type":"lfo",
                "rate":5.0,
                "depth":0.1,
                "generator":{
                    "type":"sine"
                }
            }
        }
    },
    {
        "comment":"Oscillator Operator: Rich example microtonal multisaw",
        "type":"oscillator",
        "model":{
            "type":"super",
            "stack":[
                [1.0, 0.5, 0.0],
                [2.0, 0.25, 0.0],
                [3.0, 0.25, 0.0]
            ],
            "generator":{
                "type":"sawdown",
                "min":-0.75,
                "max":0.75
            }
        },
        "level_control":{
            "type":"envelope",
            "config":{
                "comment":"Custom envelope shape",
                "type":"custom",
                "shape":{
                    "initial":0.0,
                    "points":[
                        [1.0, 0.1],
                        [0.75, 1.0],
                        [0.25, 5.0],
                        [0.0, 10]
                    ]
                },
                "keyscale_level":{
                    "type":"12tone_scaled",
                    "center":1.0,
                    "scale":1.0,
                    "invert":true
                }
            }
        },
        "pitch_control":{
            "comment":"simple unchanging 5Hz LFO",
            "type":"oscillator",
            "config":{
                "type":"lfo",
                "rate":5.0,
                "depth":0.1,
                "generator":{
                    "type":"sine"
                }
            }
        },
        "keyscale_pitch":{
            "type":"12tone_scaled",
            "center":1.0,
            "scale":0.5,
            "invert":true
        }
    }
]';

foreach(json_decode(S_OSCILLATOR_EXAMPLES) as $oDefinition) {
    $oOperator = Operator\Factory::get()->createFrom($oDefinition);
    echo $oOperator, "\n\n";
}

