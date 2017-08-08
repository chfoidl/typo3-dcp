<?php

namespace Sethorax\Dcp\Controller;

use Sethorax\Dcp\Renderer\ContentRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DCPController extends ActionController
{
    /**
     * List action
     */
    public function listAction()
    {
        $renderer = new ContentRenderer(
            $this->settings['categories'],
            $this->settings['storage'],
            $this->settings['mode'],
            $this->settings['order'],
            $this->settings['sort_direction'],
            $this->settings['limit']
        );

        $this->view->assign('mode', $this->settings['mode']);
        $this->view->assign('elements', $renderer->render());
    }
}
