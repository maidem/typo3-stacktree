<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$ctype = ExtensionUtility::registerPlugin('Stacktree', 'Tree', 'Stack-Baum', 'module-stacktree', 'plugins');
// the record folder is picked via the plugin's "Starting point"
ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'pages', $ctype, 'after:header');
