<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Node extends AbstractEntity
{
    protected string $title = '';
    protected string $subtitle = '';
    protected string $link = '';
    protected string $tag = '';
    protected string $category = 'other';
    protected NodeStatus $status = NodeStatus::Built;
    protected ?FileReference $icon = null;
    // uid of the parent node, kept as int so the whole tree comes from one query
    protected int $parent = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getStatus(): NodeStatus
    {
        return $this->status;
    }

    public function isPlanned(): bool
    {
        return $this->status === NodeStatus::Planned;
    }

    public function getIcon(): ?FileReference
    {
        return $this->icon;
    }

    public function getParent(): int
    {
        return $this->parent;
    }
}
