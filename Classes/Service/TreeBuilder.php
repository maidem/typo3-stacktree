<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Service;

use Maidem\Stacktree\Domain\Model\Node;

/**
 * Turns the flat node list into a nested tree.
 * Nodes whose parent is missing (hidden, deleted, other folder) become roots;
 * nodes caught in a parent cycle are hung on the top level, so nothing vanishes.
 */
final class TreeBuilder
{
    /**
     * @param iterable<Node> $nodes
     * @return list<array{node: Node, children: list<mixed>}>
     */
    public function build(iterable $nodes): array
    {
        $nodes = [...$nodes];
        $known = [];
        foreach ($nodes as $node) {
            $known[$node->getUid()] = true;
        }
        $children = [];
        foreach ($nodes as $node) {
            $parent = $node->getParent();
            $children[isset($known[$parent]) && $parent !== $node->getUid() ? $parent : 0][] = $node;
        }

        $seen = [];
        $attach = function (int $parent) use (&$attach, &$seen, $children): array {
            $branch = [];
            foreach ($children[$parent] ?? [] as $node) {
                if (isset($seen[$node->getUid()])) {
                    continue;
                }
                $seen[$node->getUid()] = true;
                $branch[] = ['node' => $node, 'children' => $attach($node->getUid())];
            }
            return $branch;
        };

        $tree = $attach(0);
        foreach ($nodes as $node) {
            if (!isset($seen[$node->getUid()])) {
                $seen[$node->getUid()] = true;
                $tree[] = ['node' => $node, 'children' => $attach($node->getUid())];
            }
        }
        return $tree;
    }
}
