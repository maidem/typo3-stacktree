<?php

use Maidem\Stacktree\Controller\ModuleController;

return [
    'stacktree' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'path' => '/module/web/stacktree',
        'iconIdentifier' => 'module-stacktree',
        'labels' => 'LLL:EXT:maidem_stacktree/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Stacktree',
        'controllerActions' => [
            ModuleController::class => ['index'],
        ],
    ],
];
