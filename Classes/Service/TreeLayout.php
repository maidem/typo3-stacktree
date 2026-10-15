<?php

declare(strict_types=1);

namespace Maidem\Stacktree\Service;

use Closure;
use Maidem\Stacktree\Domain\Model\Node;

/**
 * Computes tile positions and curved connector paths in plain viewBox units,
 * so the page needs no JS to measure or draw anything.
 *
 * Two variants: horizontal (parent left, children right, S-curves) for wide
 * screens and vertical (indented like a file tree, rounded elbows) for phones.
 */
final class TreeLayout
{
    private const TILE_H = 46;
    private const PAD = 24;
    private const TEXT_X = 50; // marker slot (dot or icon) + gap
    private const TITLE_CHAR_W = 8.4; // monospace 14px
    private const SUB_CHAR_W = 7.2; // monospace 12px
    private const TAG_CHAR_W = 7.2; // tag label, incl. letter spacing
    private const H_TILE_MIN = 150;
    private const H_TILE_MAX = 260;
    private const H_COL_GAP = 56;
    private const H_ROW_GAP = 8;
    private const V_WIDTH = 380;
    private const V_INDENT = 32;
    private const V_ROW_GAP = 10;
    private const V_RADIUS = 14;

    /**
     * @param list<array{node: Node, children: list<mixed>}> $tree
     * @param Closure(Node): ?string $href
     * @return array{h: ?array, v: ?array}
     */
    public function both(array $tree, Closure $href): array
    {
        if ($tree === []) {
            return ['h' => null, 'v' => null];
        }
        return ['h' => $this->layout($tree, false, $href), 'v' => $this->layout($tree, true, $href)];
    }

    /**
     * @param list<array{node: Node, children: list<mixed>}> $tree
     * @param Closure(Node): ?string $href
     */
    private function layout(array $tree, bool $vertical, Closure $href): array
    {
        $items = [];
        $walk = function (array $branch, int $depth, ?int $parent) use (&$walk, &$items): void {
            foreach ($branch as $entry) {
                $i = count($items);
                $items[] = ['node' => $entry['node'], 'depth' => $depth, 'parent' => $parent, 'kids' => []];
                if ($parent !== null) {
                    $items[$parent]['kids'][] = $i;
                }
                $walk($entry['children'], $depth + 1, $i);
            }
        };
        $walk($tree, 0, null);

        // row slot per item: file-tree order (vertical) or leaf order with parents centred (horizontal)
        $slots = [];
        if ($vertical) {
            $slots = array_keys($items);
            $rows = count($items);
        } else {
            $next = 0;
            $place = function (int $i) use (&$place, &$next, &$slots, $items): float {
                if ($items[$i]['kids'] === []) {
                    return $slots[$i] = $next++;
                }
                $first = $last = 0.0;
                foreach ($items[$i]['kids'] as $n => $kid) {
                    $last = $place($kid);
                    $first = $n === 0 ? $last : $first;
                }
                return $slots[$i] = ($first + $last) / 2;
            };
            foreach ($items as $i => $item) {
                if ($item['parent'] === null) {
                    $place($i);
                }
            }
            $rows = $next;
        }

        // horizontal: every column is as wide as its longest label, so no tile wastes space
        $colW = [];
        foreach ($items as $item) {
            $n = $item['node'];
            $tagW = $n->getTag() === '' ? 0 : mb_strlen($n->getTag()) * self::TAG_CHAR_W + 10;
            $need = self::TEXT_X + 14 + max(
                mb_strlen($n->getTitle()) * self::TITLE_CHAR_W + $tagW,
                mb_strlen($n->getSubtitle()) * self::SUB_CHAR_W,
            );
            $colW[$item['depth']] = max($colW[$item['depth']] ?? 0, (int)ceil(min(self::H_TILE_MAX, max(self::H_TILE_MIN, $need))));
        }
        $colX = [];
        $cursor = self::PAD;
        for ($d = 0; $d < count($colW); $d++) {
            $colX[$d] = $cursor;
            $cursor += $colW[$d] + self::H_COL_GAP;
        }

        $rowStep = self::TILE_H + ($vertical ? self::V_ROW_GAP : self::H_ROW_GAP);
        $maxDepth = 0;
        $nodes = [];
        foreach ($items as $i => $item) {
            $maxDepth = max($maxDepth, $item['depth']);
            $x = $vertical ? self::PAD + $item['depth'] * self::V_INDENT : $colX[$item['depth']];
            $w = $vertical ? self::V_WIDTH - self::PAD - $x : $colW[$item['depth']];
            $tag = $item['node']->getTag();
            $tagW = $tag === '' ? 0 : mb_strlen($tag) * self::TAG_CHAR_W + 10;
            $room = $w - self::TEXT_X - 12;
            $node = $item['node'];
            $nodes[$i] = [
                'x' => $x,
                'y' => self::PAD + $slots[$i] * $rowStep,
                'w' => $w,
                'h' => self::TILE_H,
                // text rows are relative to the tile centre, so the tile height can change freely
                'cy' => self::TILE_H / 2,
                'titleY' => self::TILE_H / 2 - ($node->getSubtitle() !== '' ? 4 : -5),
                'subY' => self::TILE_H / 2 + 13,
                'depth' => $item['depth'],
                'title' => mb_strimwidth($node->getTitle(), 0, (int)floor(($room - $tagW) / self::TITLE_CHAR_W), '…'),
                'subtitle' => mb_strimwidth($node->getSubtitle(), 0, (int)floor($room / self::SUB_CHAR_W), '…'),
                'tag' => $tag,
                'tagX' => $w - 12,
                'category' => $node->getCategory(),
                'planned' => $node->isPlanned(),
                'icon' => $node->getIcon(),
                'href' => $href($node),
                'parent' => $item['parent'],
            ];
        }

        $edges = [];
        foreach ($nodes as $i => $n) {
            if ($n['parent'] === null) {
                continue;
            }
            $p = $nodes[$n['parent']];
            $y2 = $n['y'] + self::TILE_H / 2;
            if ($vertical) {
                $x1 = $p['x'] + 20;
                $d = sprintf('M%s %sV%sQ%s %s %s %sH%s', $x1, $p['y'] + self::TILE_H, $y2 - self::V_RADIUS, $x1, $y2, $x1 + self::V_RADIUS, $y2, $n['x']);
            } else {
                $x1 = $p['x'] + $p['w'];
                $y1 = $p['y'] + self::TILE_H / 2;
                $mid = ($x1 + $n['x']) / 2;
                $d = sprintf('M%s %sC%s %s %s %s %s %s', $x1, $y1, $mid, $y1, $mid, $y2, $n['x'], $y2);
            }
            $edges[] = ['d' => $d, 'planned' => $n['planned'], 'depth' => $n['depth']];
        }

        $height = 2 * self::PAD + $rows * self::TILE_H + ($rows - 1) * ($rowStep - self::TILE_H);
        $width = $vertical
            ? self::V_WIDTH
            : $cursor - self::H_COL_GAP + self::PAD;

        return ['width' => $width, 'height' => $height, 'nodes' => array_values($nodes), 'edges' => $edges];
    }
}
