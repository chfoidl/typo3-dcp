<?php

namespace Sethorax\Dcp\Hooks;

use Sethorax\Dcp\Utility\BackendUtility;
use Sethorax\Dcp\Utility\ConnectionUtility;
use Sethorax\Dcp\Utility\PluginModeUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Category\Collection\CategoryCollection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageLayoutViewHook implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var BackendUtility $backendUtility
     */
    protected $backendUtility;

    /**
     * @var PluginModeUtility $pluginModeUtility
     */
    protected $pluginModeUtility;

    public function __construct()
    {
        $this->backendUtility = GeneralUtility::makeInstance(BackendUtility::class);
        $this->pluginModeUtility = GeneralUtility::makeInstance(PluginModeUtility::class);
    }

    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionalities
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     * @return void
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        if ($row['list_type'] !== 'dcp_pi1') {
            return;
        }

        $this->loadExtensionLocalization();

        $drawItem = false;
        $flexformValues = $this->parseFlexFormValues($row['pi_flexform']);

        $headerContent = $this->generateHeaderContent();
        $itemContent = $this->generateItemContent($row, $flexformValues);
    }

    /**
     * Generates header content.
     *
     * @return string
     */
    protected function generateHeaderContent()
    {
        $title = $this->getLanguageService()->getLL('pi1_title');
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $iconFactory->getIcon(
            'ext-dcp-pi1-icon',
            Icon::SIZE_SMALL
        )->render();

        return <<<HTML
<header style="margin-bottom: 8px; padding-bottom: 10px; border-bottom: 1px solid #E3E3E3;">
    <span style="padding-right: 5px;">$icon</span><span><strong>$title</strong></span>
</header>
HTML;
    }

    protected function getCategories($flexformValues)
    {
        $categories = $this->backendUtility->getCategoryNamesByIds($flexformValues['settings.categories']);

        if (count($categories) > 0 && $categories[0] !== null) {
            return implode('', array_map(function ($n) {
                return '<li>' . $n . '</li>';
            }, $categories));
        }

        return '-';
    }

    /**
     * Generates item content.
     *
     * @param array $row
     * @param array $flexformValues
     * @return string
     */
    protected function generateItemContent($row, $flexformValues)
    {
        if ($flexformValues['settings.storage'] === '') {
            $storageNotSetLabel = $this->getLanguageService()->getLL('plugin_preview.no_storage_set');

            return '<strong style="color: #F60F0F;">' . $storageNotSetLabel . '</strong>';
        }

        $modeLabel = $this->getLanguageService()->getLL('flexforms_general.mode');
        $categoryLabel = $this->getLanguageService()->getLL('flexforms_general.categories');
        $storageLabel = $this->getLanguageService()->getLL('flexforms_general.storage');
        $limitLabel = $this->getLanguageService()->getLL('flexforms_advanced.limit');
        $orderLabel = $this->getLanguageService()->getLL('flexforms_advanced.order');
        $sortDirectionLabel = $this->getLanguageService()->getLL('flexforms_advanced.sort_direction');
        $elementsTotalLabel = $this->getLanguageService()->getLL('plugin_preview.elements_total');
        $availableLabel = $this->getLanguageService()->getLL('plugin_preview.available');

        $modeValue = $this->pluginModeUtility->getModeLabelByKey($row['pid'], $flexformValues['settings.mode']);
        $categoryValue = $this->getCategories($flexformValues);
        $storageValue = implode(array_map(function ($n) {
            return '<li>' . $n . '</li>';
        }, $this->backendUtility->getPageNamesByIds($flexformValues['settings.storage'])));
        $limitValue = $this->formatValue($flexformValues['settings.limit']);
        $orderValue = $this->formatValue($flexformValues['settings.order']);
        $sortDirectionValue = $this->getSortDirectionName($this->formatValue($flexformValues['settings.sort_direction']));
        $elementsTotalValue = $this->getMatchedElementCount($flexformValues['settings.categories'], $flexformValues['settings.storage']);

        if ((int)$limitValue > 0 && $elementsTotalValue > $limitValue) {
            $elementsTotalValue = $limitValue . ' (' . $elementsTotalValue . ' '. $availableLabel . ')';
        }

        return <<<HTML
<table>
    <tbody>
        <tr>
            <td style="vertical-align: top; padding-right: 12px; border-right: 1px solid #E3E3E3;">
                <table>
                    <tbody>
                        <tr>
                            <tr>
                                <td style="padding: 3px 3px 10px; width: 130px; font-weight: bold; vertical-align: top;">$modeLabel</td>
                                <td style="padding: 3px 3px 10px; vertical-align: top;">$modeValue</td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 3px 10px; width: 130px; font-weight: bold; vertical-align: top;">$categoryLabel</td>
                                <td style="padding: 3px 3px 10px; vertical-align: top;">
                                    <ul style="list-style: none; padding: 0; margin: 0;">$categoryValue</ul>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 3px 10px; width: 130px; font-weight: bold; vertical-align: top;">$storageLabel</td>
                                <td style="padding: 3px 3px 10px; vertical-align: top;">
                                    <ul style="list-style: none; padding: 0; margin: 0;">$storageValue</ul>
                                </td>
                            </tr>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="vertical-align: top; padding-left: 12px;">
                <table>
                    <tbody>
                        <tr>
                            <td style="padding: 3px 3px 10px; width: 150px; font-weight: bold; vertical-align: top;">$limitLabel</td>
                            <td style="padding: 3px 3px 10px; vertical-align: top;">
                                <ul style="list-style: none; padding: 0; margin: 0;">$limitValue</ul>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 3px 10px; width: 150px; font-weight: bold; vertical-align: top;">$orderLabel</td>
                            <td style="padding: 3px 3px 10px; vertical-align: top;">
                                <ul style="list-style: none; padding: 0; margin: 0;">$orderValue</ul>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 3px 0; width: 150px; font-weight: bold; vertical-align: top;">$sortDirectionLabel</td>
                            <td style="padding: 3px 3px 0; vertical-align: top;">
                                <ul style="list-style: none; padding: 0; margin: 0;">$sortDirectionValue</ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<footer style="margin-top: 8px; padding-top: 10px; border-top: 1px solid #E3E3E3;">
    <span style="display: inline-block; padding: 3px 3px 0; width: 133px;"><strong>$elementsTotalLabel</strong></span><span>$elementsTotalValue</span>
</footer>
HTML;
    }

    /**
     * Returns a special string if $value is empty.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue($value)
    {
        if ($value || $value !== '') {
            return $value;
        }

        return '-';
    }

    /**
     * Get pretty name for sort direction.
     *
     * @param string $key
     * @return string
     */
    protected function getSortDirectionName($key)
    {
        if (strtoupper(trim($key === 'ASC'))) {
            return $this->getLanguageService()->getLL('flexforms_advanced.sort_direction.ascending');
        }

        return $this->getLanguageService()->getLL('flexforms_advanced.sort_direction.descending');
    }

    /**
     * Gets total element count.
     *
     * @param string $categoryIds
     * @param string $storageIds
     * @return int
     */
    protected function getMatchedElementCount($categoryIds, $storageIds)
    {
        $matchedContentElements = [];
        $storageIdArray = explode(',', $storageIds);
        
        if ($categoryIds !== '') {
            $categoryIdArray = explode(',', $categoryIds);

            foreach ($categoryIdArray as $categoryId) {
                $categoryCollection = CategoryCollection::load($categoryId, true, 'tt_content');
    
                foreach ($categoryCollection as $contentElement) {
                    if (in_array($contentElement['pid'], $storageIdArray)) {
                        $matchedContentElements[] = $contentElement['uid'];
                    }
                }
            }
        } else {
            $queryBuilder = ConnectionUtility::getDBConnectionForTable('tt_content')->createQueryBuilder();

            return $queryBuilder
                ->select('uid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->in('pid', $storageIds)
                )
                ->execute()
                ->rowCount();
        }

        return count(array_unique($matchedContentElements));
    }

    /**
     * Parse flexform xml structure and return values.
     *
     * @param string $flexformXml
     * @return array
     */
    protected function parseFlexFormValues($flexformXml)
    {
        $flexformValues = [];

        $xml = simplexml_load_string($flexformXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml);
        $sheets = json_decode($json, true)['data']['sheet'];

        foreach ($sheets as $sheet) {
            foreach ($sheet['language']['field'] as $entry) {
                $value = !is_array($entry['value']) ? $entry['value'] : '';
                $flexformValues[$entry['@attributes']['index']] = $value;
            }
        }

        return $flexformValues;
    }

    /**
     * Load extension localization file.
     *
     * @return void
     */
    protected function loadExtensionLocalization()
    {
        $this->getLanguageService()->includeLLFile('EXT:dcp/Resources/Private/Language/locallang_be.xlf');
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
