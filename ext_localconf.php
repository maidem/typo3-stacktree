<?php

use Maidem\Stacktree\Controller\TreeController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin('Stacktree', 'Tree', [TreeController::class => 'show']);
