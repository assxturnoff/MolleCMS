<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$generate['start'] = (float) microtime(true);

session_start();
if(!defined('ROOT'))
{
    define('ROOT', dirname(dirname(dirname(__FILE__)))."/");
}

include_once ROOT."inc/lib/core.class.php";
$core = new Core();
$core->start();

if (isset($_GET['logout']))
    $core->get_handler('users')->logout($_GET['logout']);