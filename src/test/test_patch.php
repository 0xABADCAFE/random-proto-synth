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

$aOpts = getopt('p:n::t::');

if (!isset($aOpts['p'])) {
    echo "Usage -p<patch> -n<note> -t<time>";
    exit();
}

$sPatch = sprintf('patches/%s.json', $aOpts['p']);
$sNote  = strtoupper($aOpts['n'] ?? 'C3');
$fTime  = (float)($aOpts['t'] ?? 2.0);


$oPatch = (new Patch\Loader())->load($sPatch);
$oPatch->setNoteName($sNote)->getOutputOperator()->render($fTime);
