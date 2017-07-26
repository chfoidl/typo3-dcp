<?php

namespace Sethorax\Dcp\Controller;

class DCPController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * List action
     */
    public function listAction()
    {
        $this->view->assign('mode', $this->settings['mode']);
        $this->view->assign('ceUids', $this->getContentElementsByCategoriesAndStorage($this->settings['categories'], $this->settings['startingpoint']));
    }

    /**
     * Gets content elements by categories and storage ids
     *
     * @param string $categoryIds
     * @param string $storageIds
     * @return array
     */
    protected function getContentElementsByCategoriesAndStorage($categoryIds, $storageIds)
    {
        $matchedContentElements = [];
        $categoryIdArray = explode(',', $categoryIds);
        $storageIdArray = explode(',', $storageIds);

        foreach ($categoryIdArray as $categoryId) {
            $categoryCollection = \TYPO3\CMS\Frontend\Category\Collection\CategoryCollection::load($categoryId, true, 'tt_content');
            $categoryCollection->loadContents();

            foreach ($categoryCollection as $contentElement) {
                if (in_array($contentElement['pid'], $storageIdArray)) {
                    $matchedContentElements[] = $contentElement['uid'];
                }
            }
        }

        return array_unique($matchedContentElements);
    }
}