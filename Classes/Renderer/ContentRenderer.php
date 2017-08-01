<?php

namespace Sethorax\Dcp\Renderer;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class ContentRenderer
{
    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var string
     */
    protected $contentUids;

    /**
     * @var string
     */
    protected $pluginMode;

    /**
     * @var string
     */
    protected $order;

    /**
     * @var string
     */
    protected $sortDirection;

    /**
     * @var string
     */
    protected $limit;

    /**
     * Class constructor.
     *
     * @param string $contentUids
     * @param string $pluginMode
     * @param string $order
     * @param string $sortDirection
     * @param string $limit
     */
    public function __construct($contentUids, $pluginMode, $order = '', $sortDirection = 'ASC', $limit = '')
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->contentObject = $this->configurationManager->getContentObject();

        $this->contentUids = $this->convertStringListToArray($contentUids);
        $this->pluginMode = $pluginMode;
        $this->order = $order;
        $this->sortDirection = $sortDirection;
        $this->limit = $limit;
    }

    /**
     * Gets raw records by uid and returns rendered html output.
     *
     * @return void
     */
    public function render()
    {
        $renderedContentElements = [];
        $rawRecords = $this->getRecordsById($this->contentUids);

        return $this->getRenderedRecords($rawRecords);
    }

    /**
     * Gets records by uid.
     *
     * @param string $contentUids
     * @return void
     */
    protected function getRecordsById($contentUids)
    {
        $order = $this->order;
        $limit = $this->limit;
        $currentLanguage = $GLOBALS['TSFE']->sys_language_content;
        $languageUids = [-1, $currentLanguage];

        if (!empty($order)) {
            $sortDirection = strtoupper(trim($this->sortDirection));
            if ($sortDirection !== 'ASC' && $sortDirection !== 'DESC') {
                $sortDirection = 'ASC';
            }
            $order = $order . ' ' . $sortDirection;
        }

        if (null !== $GLOBALS['TSFE']->sys_language_contentOL) {
            $languageUids[] = $GLOBALS['TSFE']->sys_language_contentOL;
        }

        $languageCondition = sprintf('(sys_language_uid IN (%s)', implode(", ", $languageUids));
        if (0 < $currentLanguage) {
            if (true === $hideUntranslated) {
                $languageCondition .= ' AND l18n_parent > 0';
            }
            $nestedQuery = $GLOBALS['TYPO3_DB']->SELECTquery('l18n_parent', 'tt_content', 'sys_language_uid = ' .
                $currentLanguage . $GLOBALS['TSFE']->cObj->enableFields('tt_content'));
            $languageCondition .= ' AND uid NOT IN (' . $nestedQuery . ')';
        }
        $languageCondition .= ')';

        $conditions = 'uid IN (' . implode(',', $contentUids) . ')';
        $conditions .= $this->contentObject->enableFields('tt_content', false, ['pid' => true, 'hidden' => true]) . ' AND ' . $languageCondition;

        if (ExtensionManagementUtility::isLoaded('workspaces')) {
            $conditions .= BackendUtility::versioningPlaceholderClause('tt_content');
        }

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'tt_content', $conditions, '', $order, $limit);

        if (!ExtensionManagementUtility::isLoaded('workspaces')) {
            return $rows;
        }

        $workspaceId = isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER']->workspace : 0;
        if ($workspaceId) {
            foreach ($rows as $index => $row) {
                if (BackendUtility::getMovePlaceholder('tt_content', $row['uid'])) {
                    unset($rows[$index]);
                } else {
                    $rows[$index] = $row;
                }
            }
        }
        return $rows;
    }

    /**
     * Sets global register variable and renders the record by uid.
     *
     * @param array $rows
     * @return void
     */
    protected function getRenderedRecords(array $rows)
    {
        $GLOBALS['TSFE']->register['dcpMode'] = $this->pluginMode;

        $elements = [];
        foreach ($rows as $row) {
            $elements[] = static::renderRecord($row);
        }

        unset($GLOBALS['TSFE']->register['dcpMode']);

        return $elements;
    }

    /**
     * Converts a string list to array.
     *
     * @param mixed $list
     * @return array
     */
    protected function convertStringListToArray($list)
    {
        if (is_string($list)) {
            return explode(',', $list);
        }

        return $list;
    }

    /**
     * Renders the record via typoscript RECORDS.
     * Does cache already rendered content elements.
     *
     * @param array $row
     * @return void
     */
    protected static function renderRecord(array $row) {
       if ($GLOBALS['TSFE']->recordRegister['tt_content:' . $row['uid']] > 0) {
            return null;
        }
        $parent = $GLOBALS['TSFE']->currentRecord;
        // If the currentRecord is set, we register, that this record has invoked this function.
        // It should not be allowed to do this again!
        if (!empty($parent)) {
            ++$GLOBALS['TSFE']->recordRegister[$parent];
        }

        $renderedElement = $GLOBALS['TSFE']->cObj->cObjGetSingle('RECORDS', [
            'tables' => 'tt_content',
            'source' => $row['uid'],
            'dontCheckPid' => 1
        ]);

        $GLOBALS['TSFE']->currentRecord = $parent;
        if (!empty($parent)) {
            --$GLOBALS['TSFE']->recordRegister[$parent];
        }

        return $renderedElement;
    }
}
