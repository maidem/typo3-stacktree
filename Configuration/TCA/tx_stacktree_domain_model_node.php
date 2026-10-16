<?php

$lll = 'LLL:EXT:maidem_stacktree/Resources/Private/Language/locallang_db.xlf:';
$tabs = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:';

return [
    'ctrl' => [
        'title' => $lll . 'node',
        'label' => 'title',
        'label_alt' => 'subtitle',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'versioningWS' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'iconfile' => 'EXT:maidem_stacktree/Resources/Public/Icons/module.svg',
        'searchFields' => 'title,subtitle',
        'enablecolumns' => ['disabled' => 'hidden'],
    ],
    'columns' => [
        'title' => [
            'label' => $lll . 'node.title',
            'config' => ['type' => 'input', 'required' => true, 'max' => 60, 'eval' => 'trim'],
        ],
        'subtitle' => [
            'label' => $lll . 'node.subtitle',
            'config' => ['type' => 'input', 'max' => 80, 'eval' => 'trim'],
        ],
        'tag' => [
            'label' => $lll . 'node.tag',
            'description' => $lll . 'node.tag.description',
            'config' => ['type' => 'input', 'max' => 10, 'eval' => 'trim'],
        ],
        'category' => [
            'label' => $lll . 'node.category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'other',
                'items' => [
                    ['label' => $lll . 'node.category.own', 'value' => 'own'],
                    ['label' => $lll . 'node.category.external', 'value' => 'external'],
                    ['label' => $lll . 'node.category.frontend', 'value' => 'frontend'],
                    ['label' => $lll . 'node.category.infra', 'value' => 'infra'],
                    ['label' => $lll . 'node.category.other', 'value' => 'other'],
                ],
            ],
        ],
        'icon' => [
            'label' => $lll . 'node.icon',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'svg,png,webp',
            ],
        ],
        'status' => [
            'label' => $lll . 'node.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'built',
                'items' => [
                    ['label' => $lll . 'node.status.built', 'value' => 'built'],
                    ['label' => $lll . 'node.status.planned', 'value' => 'planned'],
                ],
            ],
        ],
        'link' => [
            'label' => $lll . 'node.link',
            'config' => ['type' => 'link'],
        ],
        'parent' => [
            'label' => $lll . 'node.parent',
            'description' => $lll . 'node.parent.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectTree',
                'foreign_table' => 'tx_stacktree_domain_model_node',
                // same folder, never the node itself
                'foreign_table_where' => 'AND {#tx_stacktree_domain_model_node}.{#pid} = ###CURRENT_PID###'
                    . ' AND {#tx_stacktree_domain_model_node}.{#uid} != ###THIS_UID###'
                    . ' ORDER BY tx_stacktree_domain_model_node.sorting',
                'maxitems' => 1,
                'size' => 10,
                'treeConfig' => [
                    'parentField' => 'parent',
                    'appearance' => ['expandAll' => true, 'showHeader' => true],
                ],
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => "title, subtitle, tag, category, parent, --div--;{$tabs}general, icon, status, link, --div--;{$tabs}access, hidden"],
    ],
];
