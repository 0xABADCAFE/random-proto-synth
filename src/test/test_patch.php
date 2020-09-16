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

$aOptions = getopt('p:n::t::v::o::');

if (!isset($aOptions['p'])) {
    echo "Usage -p<patch> [-n<note>] [-t<time>] [-o<output>]\n";
    exit();
}

$sPatch  = sprintf('patches/%s.json', $aOptions['p']);
$sNote   = strtoupper($aOptions['n'] ?? 'C3');
$fTime   = (float)($aOptions['t'] ?? 2.0);
$fVolume = (float)($aOptions['v'] ?? 1.0);
$sOutput = $aOptions['o'] ?? '';

$oPatch = (new Patch\Loader())->load($sPatch);
$oPatch->setNoteName($sNote);

$oOutputOperator = new Operator\PCMOutput(
    !empty($sOutput) ? new Output\Wav() : new Output\Play()
);

$oOutputOperator
    ->open($sOutput)
    ->attachInput($oPatch->getOutputOperator(), $fVolume)
    ->render($fTime);
