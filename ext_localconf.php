<?php

defined('TYPO3_MODE') or die();

$boot = function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Sethorax.dcp',
        'Pi1',
        [
            'DCP' => 'list'
        ]
    );
};

$boot();
unset($boot);