<?php

defined('TYPO3_MODE') or die();

$boot = function () {
    /**
     * Include Plugins
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'dcp',
		'Pi1',
		'Dynamic Content Plugin',
		'ext-dcp-pi1-icon'
	);

    /**
     * Disable not needed fields in tt_content
     */
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['dcp_pi1'] =
        'select_key,pages,recursive';

    /**
     * Include Flexform
     */
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['dcp_pi1'] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'dcp_pi1',
        'FILE:EXT:dcp/Configuration/FlexForms/flexform_dcp.xml'
    );

    /**
     * ContentElementWizard
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:dcp/Configuration/TSconfig/ContentElementWizard.txt">');

    /**
     * Include TypoScript
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'dcp',
        'Configuration/TypoScript',
        'Dynamic Content Plugin'
    );

    /**
     * Register icons
     */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'ext-dcp-pi1-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:dcp/Resources/Public/Icons/dcp_pi1.svg']
    );
};

$boot();
unset($boot);
