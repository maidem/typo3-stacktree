<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Domain\Repository;

use Maidem\Stacktree\Domain\Model\Node;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/** @extends Repository<Node> */
class NodeRepository extends Repository
{
    protected $defaultOrderings = ['sorting' => QueryInterface::ORDER_ASCENDING];
}
