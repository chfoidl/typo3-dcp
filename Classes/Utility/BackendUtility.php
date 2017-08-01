<?php

namespace Sethorax\Dcp\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility as BackendCoreUtility;
use TYPO3\CMS\Core\Category\Collection\CategoryCollection;
use TYPO3\CMS\Core\SingletonInterface;

class BackendUtility implements SingletonInterface
{
	/**
	 * Gets page names by uids.
	 *
	 * @param array|string $uids
	 * @return array
	 */
	public function getPageNamesByIds($uids)
	{
		if (!is_string($uids) && !is_array($uids)) {
			return '';
		}

		$pageNames = [];

		if (is_string($uids)) {
			$ids = explode(',', $uids);
		} else {
			$ids = $uids;
		}

		foreach ($ids as $id) {
			$pageNames[] = BackendCoreUtility::getRecord('pages', $id)['title'];
		}
		
		return $pageNames;
	}

	/**
	 * Gets category names by uids.
	 *
	 * @param array|string $cids
	 * @return array
	 */
	public function getCategoryNamesByIds($cids)
	{
		if (!is_string($cids) && !is_array($cids)) {
			return '';
		}

		$categoryNames = [];

		if (is_string($cids)) {
			$ids = explode(',', $cids);
		} else {
			$ids = $cids;
		}

		foreach ($ids as $id) {
			$categoryCollection = CategoryCollection::load($id, true, 'tt_content');
			$categoryNames[] = $categoryCollection->getTitle();
		}

		return $categoryNames;
	}
}
