<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Domain\Model;

enum NodeStatus: string
{
    case Built = 'built';
    case Planned = 'planned';
}
