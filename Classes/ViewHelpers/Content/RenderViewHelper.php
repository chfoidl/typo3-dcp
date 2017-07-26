<?php

namespace Sethorax\Dcp\ViewHelpers\Content;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class RenderViewHelper extends AbstractViewHelper implements CompilableInterface
{
    use CompileWithRenderStatic;

    /**
     * Children must not be escaped, to be able to pass {bodytext} directly to it
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Plain HTML should be returned, no output escaping allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uid', 'string', 'uid of the content element that should be rendered', true, '0');
        $this->registerArgument('mode', 'string', 'plugin mode', true, '');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string the parsed string.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $output = '';

        $GLOBALS['TSFE']->cObj->cObjGetSingle('LOAD_REGISTER', [
            'dcpMode' => $arguments['mode']
        ]);

        $output = $GLOBALS['TSFE']->cObj->cObjGetSingle('RECORDS', [
            'source' => $arguments['uid'],
            'tables' => 'tt_content',
            'dontCheckPid' => true
        ]);

        $GLOBALS['TSFE']->cObj->cObjGetSingle('RESTORE_REGISTER', []);

        return $output;
    }
}