<?php

namespace Sethorax\Dcp\Renderer;

use TYPO3\CMS\Core\Database\ConnectionPool;
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
     * @var \TYPO3\CMS\Core\Database\ConnectionPool
     */
    protected $db;

    /**
     * @var string
     */
    protected $categoryUids;

    /**
     * @var string
     */
    protected $pids;

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
     * @param string $categoryUids
     * @param string $storageIds
     * @param string $pluginMode
     * @param string $order
     * @param string $sortDirection
     * @param string $limit
     */
    public function __construct($categoryUids, $storageIds, $pluginMode, $order = '', $sortDirection = 'ASC', $limit = '')
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->contentObject = $this->configurationManager->getContentObject();
        $this->db = GeneralUtility::makeInstance(ConnectionPool::class);

        $this->categoryUids = $categoryUids;
        $this->pids = $storageIds;
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
        $renderedRecords = [];

        if ($this->pids) {
            $renderedContentElements = [];
            $rawRecords = $this->getRecordsByPids();
            
            if ($rawRecords) {
                if ($this->categoryUids) {
                    $rawRecords = $this->filterElementsByCategory($rawRecords);
                }

                $renderedRecords = $this->getRenderedRecords($rawRecords);
            }
        }

        return $renderedRecords;
    }

    /**
     * Gets records by pids.
     *
     * @return void
     */
    protected function getRecordsByPids()
    {
        // Base config
        $conf = [
            'pidInList' => $this->pids,
            'selectFields' => 'uid'
        ];

        // Set order and sorting
        if (!empty($this->order)) {
            $sortDirection = strtoupper(trim($this->sortDirection));
            if ($sortDirection !== 'ASC' && $sortDirection !== 'DESC') {
                $sortDirection = 'ASC';
            }

            $conf['orderBy'] = $this->order . ' ' . $sortDirection;
        }

        // Set limit
        if ($this->limit > 0 && !$this->categoryUids) {
            $conf['max'] = $this->limit;
        }

        // Get rows
        return $this->contentObject->getRecords('tt_content', $conf);
    }

    /**
     * Filters elements by category
     *
     * @param array $records
     * @return array
     */
    protected function filterElementsByCategory($records)
    {
        $matchedElements = [];
        $contentUids = implode(',', array_map(function ($n) {
            return $n['uid'];
        }, $records));

        $queryBuilder = $this->db->getQueryBuilderForTable('sys_category_record_mm');
        $result = $queryBuilder
            ->select('uid_foreign AS uid')
            ->from('sys_category_record_mm')
            ->where(
                $queryBuilder->expr()->in('uid_local', $this->categoryUids),
                $queryBuilder->expr()->in('uid_foreign', $contentUids)
            )
            ->execute();

        $matchedUids = array_map(function ($n) {
            return $n['uid'];
        }, $result->fetchAll());

        foreach ($records as $record) {
            if (in_array($record['uid'], $matchedUids)) {
                $matchedElements[] = $record;
            }
        }

        // Set limit
        if ($this->limit > 0) {
            $matchedElements = array_slice($matchedElements, 0, $this->limit);
        }

        return $matchedElements;
    }

    /**
     * Sets global register variable and renders the record by uid.
     *
     * @param array $rows
     * @return void
     */
    protected function getRenderedRecords(array $rows)
    {
        $elements = [];

        if ($rows) {
            $GLOBALS['TSFE']->register['dcpMode'] = $this->pluginMode;

            foreach ($rows as $row) {
                $elements[] = $this->renderRecord($row);
            }

            unset($GLOBALS['TSFE']->register['dcpMode']);
        }

        return $elements;
    }

    /**
     * Renders the record via typoscript RECORDS.
     * Does cache already rendered content elements.
     *
     * @param array $row
     * @return void
     */
    protected function renderRecord(array $row)
    {
        if ($GLOBALS['TSFE']->recordRegister['tt_content:' . $row['uid']] > 0) {
            return null;
        }
        $parent = $GLOBALS['TSFE']->currentRecord;
        // If the currentRecord is set, we register, that this record has invoked this function.
        // It should not be allowed to do this again!
        if (!empty($parent)) {
            ++$GLOBALS['TSFE']->recordRegister[$parent];
        }

        $renderedElement = $this->contentObject->cObjGetSingle('RECORDS', [
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
