<?php
/**
 * Zoek - 简洁优雅的 Blog 系统
 * 
 * @link http://zoek.mingfunwong.com/
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfunw@gmail.com>
 */
define('ROOT_DIR', realpath(dirname(__FILE__)) .'/');
define('CONTENT_DIR', ROOT_DIR .'content/');
define('CONTENT_EXT', '.md');
define('LIB_DIR', ROOT_DIR .'lib/');
define('PLUGINS_DIR', ROOT_DIR .'plugins/');
define('THEMES_DIR', ROOT_DIR .'themes/');
define('CACHE_DIR', LIB_DIR .'cache/');

require(ROOT_DIR .'vendor/autoload.php');
require(LIB_DIR .'zoek.php');
$zoek = new Zoek();
