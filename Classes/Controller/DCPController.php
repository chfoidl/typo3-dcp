<?php

namespace Sethorax\Dcp\Controller;

use Sethorax\Dcp\Renderer\ContentRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

class DCPController extends ActionController
{
    /**
     * List action
     */
    public function listAction()
    {
		$contentUids = $this->getContentElementsByCategoriesAndStorage($this->settings['categories'], $this->settings['storage']);
		$renderer = new ContentRenderer($contentUids, $this->settings['mode'], $this->settings['order'], $this->settings['sort_direction'], $this->settings['limit']);
		
        $this->view->assign('mode', $this->settings['mode']);
		$this->view->assign('elements', $renderer->render());
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
            $categoryCollection = CategoryCollection::load($categoryId, true, 'tt_content');
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
