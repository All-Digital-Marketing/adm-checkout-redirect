<?php
require('updater/boot.php');
require('updater/updater.php');
require('updater/theme-updater.php');
require('updater/plugin-updater.php');

$updater = MakeitWorkPress\WP_Updater\Boot::instance();
$updater->add(['type' => 'plugin', 'source' => 'https://github.com/All-Digital-Marketing/adm-checkout-redirect']);