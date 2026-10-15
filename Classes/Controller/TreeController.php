<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Controller;

use Maidem\Stacktree\Domain\Model\Node;
use Maidem\Stacktree\Domain\Repository\NodeRepository;
use Maidem\Stacktree\Service\TreeBuilder;
use Maidem\Stacktree\Service\TreeLayout;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class TreeController extends ActionController
{
    public function __construct(
        private readonly NodeRepository $nodes,
        private readonly TreeBuilder $builder,
        private readonly TreeLayout $layout,
    ) {}

    public function showAction(): ResponseInterface
    {
        $cObj = $this->request->getAttribute('currentContentObject');
        $href = static fn(Node $node): ?string => $node->getLink() !== ''
            ? ($cObj->typoLink_URL(['parameter' => $node->getLink()]) ?: null)
            : null;

        $this->view->assignMultiple([
            'header' => $cObj->data['header'] ?? '',
            'layout' => $this->layout->both($this->builder->build($this->nodes->findAll()), $href),
        ]);
        return $this->htmlResponse();
    }
}
