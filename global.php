<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$working_dir = dirname(__FILE__);
if(!$working_dir)
{
    $working_dir = '.';
}

require_once $working_dir.'/inc/lib/init.php';
$core->get_handler('plugins')->run_all();

