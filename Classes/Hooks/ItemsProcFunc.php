<?php

namespace Sethorax\Dcp\Hooks;

use Sethorax\Dcp\Utility\PluginModeUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemsProcFunc
{
    /**
     * @var PluginModeUtility $pluginModeUtility
     */
    protected $pluginModeUtility;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->pluginModeUtility = GeneralUtility::makeInstance(PluginModeUtility::class);
    }

    /**
     * Itemsproc function to extend the selection of pluginMode in the plugin
     *
     * @param array &$config configuration array
     */
    public function user_pluginModes(array &$config)
    {
        $pageId = 0;
        $pageId = $this->getPageId($config['flexParentDatabaseRow']['pid']);
        if ($pageId > 0) {
            $pluginModes = $this->pluginModeUtility->getAvailablePluginModes($pageId);
            foreach ($pluginModes as $mode) {
                $additionalMode = [
                    htmlspecialchars($this->getLanguageService()->sL($mode[0])),
                    $mode[1]
                ];
                array_push($config['items'], $additionalMode);
            }
        }
    }

    /**
     * Itemsproc function to extend the selection of order fields in the plugin
     *
     * @param array &$config configuration array
     */
    public function user_orderFields(array &$config)
    {
        $contentRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', '');
        
        foreach ($contentRecord as $key => $value) {
            $config['items'][] = [$key, $key];
        }
    }

    /**
     * Get page id, if negative, then it is a "after record"
     *
     * @param int $pid
     * @return int
     */
    protected function getPageId($pid)
    {
        $pid = (int)$pid;
        if ($pid > 0) {
            return $pid;
        } else {
            $row = BackendUtilityCore::getRecord('tt_content', abs($pid), 'uid,pid');
            return $row['pid'];
        }
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
