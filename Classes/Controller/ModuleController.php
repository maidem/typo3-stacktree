<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Controller;

use Maidem\Stacktree\Domain\Model\Node;
use Maidem\Stacktree\Domain\Repository\NodeRepository;
use Maidem\Stacktree\Service\TreeBuilder;
use Maidem\Stacktree\Service\TreeLayout;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/** Backend preview: same diagram as the frontend, every tile opens its edit form. */
final class ModuleController extends ActionController
{
    private const TABLE = 'tx_stacktree_domain_model_node';
    private const LANG = 'LLL:EXT:maidem_stacktree/Resources/Private/Language/locallang_mod.xlf:';

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly BackendUriBuilder $backendUriBuilder,
        private readonly IconFactory $iconFactory,
        private readonly NodeRepository $nodes,
        private readonly TreeBuilder $builder,
        private readonly TreeLayout $layout,
    ) {}

    public function indexAction(): ResponseInterface
    {
        // the folder selected in the page tree is the extbase storage page
        $pid = (int)($this->request->getQueryParams()['id'] ?? 0);
        $returnUrl = (string)$this->request->getAttribute('normalizedParams')->getRequestUri();
        $view = $this->moduleTemplateFactory->create($this->request);
        $view->assign('pid', $pid);

        if ($pid > 0) {
            $edit = fn(Node $node): string => (string)$this->backendUriBuilder->buildUriFromRoute('record_edit', [
                'edit' => [self::TABLE => [$node->getUid() => 'edit']],
                'returnUrl' => $returnUrl,
            ]);
            $view->assign('layout', $this->layout->both($this->builder->build($this->nodes->findAll()), $edit(...)));

            $buttonBar = $view->getDocHeaderComponent()->getButtonBar();
            $buttonBar->addButton(
                $buttonBar->makeLinkButton()
                    ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('record_edit', [
                        'edit' => [self::TABLE => [$pid => 'new']],
                        'returnUrl' => $returnUrl,
                    ]))
                    ->setTitle($this->label('new'))->setShowLabelText(true)
                    ->setIcon($this->iconFactory->getIcon('actions-plus', IconSize::SMALL)),
                ButtonBar::BUTTON_POSITION_LEFT,
            );
            $buttonBar->addButton(
                $buttonBar->makeLinkButton()
                    ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('records', ['id' => $pid]))
                    ->setTitle($this->label('list'))->setShowLabelText(true)
                    ->setIcon($this->iconFactory->getIcon('actions-menu', IconSize::SMALL)),
                ButtonBar::BUTTON_POSITION_LEFT,
                2,
            );
        }
        return $view->renderResponse('Module/Index');
    }

    private function label(string $key): string
    {
        return $GLOBALS['LANG']->sL(self::LANG . $key);
    }
}
