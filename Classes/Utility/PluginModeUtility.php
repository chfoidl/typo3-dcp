<?php

namespace Sethorax\Dcp\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginModeUtility implements SingletonInterface
{

    /**
     * Get available plugin modes for a certain page
     *
     * @param int $pageUid
     * @return array
     */
    public function getAvailablePluginModes($pageUid)
    {
        $pluginModes = [];
        // Check if the layouts are extended by ext_tables
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['dcp']['pluginModes'])
            && is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['dcp']['pluginModes'])
        ) {
            $pluginModes = $GLOBALS['TYPO3_CONF_VARS']['EXT']['dcp']['pluginModes'];
        }
        // Add TsConfig values
        foreach ($this->getPluginModesFromTsConfig($pageUid) as $modeKey => $title) {
            if (GeneralUtility::isFirstPartOfStr($title, '--div--')) {
                $optGroupParts = GeneralUtility::trimExplode(',', $title, true, 2);
                $title = $optGroupParts[1];
                $modeKey = $optGroupParts[0];
            }
            $pluginModes[] = [$title, $modeKey];
        }
        return $pluginModes;
    }
    /**
     * Get plugin modes defined in TsConfig
     *
     * @param $pageUid
     * @return array
     */
    protected function getPluginModesFromTsConfig($pageUid)
    {
        $pluginModes = [];
        $pagesTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        if (isset($pagesTsConfig['tx_dcp.']['pluginModes.']) && is_array($pagesTsConfig['tx_dcp.']['pluginModes.'])) {
            $pluginModes = $pagesTsConfig['tx_dcp.']['pluginModes.'];
        }
        return $pluginModes;
    }
}