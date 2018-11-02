<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$working_dir = dirname(__FILE__);
if(!$working_dir)
{
    $working_dir = '.';
}

include_once $working_dir.'/../inc/lib/init.php';
$core->get_handler('plugins')->run_all(true);


if(!defined('ROOT_ADMIN'))
{
    define('ROOT_ADMIN', ROOT."/admin/");
}


$core->add_handler("template_admin", $core->new_templates());
$user = $core->get_handler('users');

if ((isset($admin_login) && $admin_login == false) || isset($admin_login) != true)
{

    if ($user->get_user('admin') != true)
    {
        header ("Location: //".$core->get_variable('settings')['website']."/admin/login.php");
    }
} else
{
    if ($user->is_login() && $user->get_user('admin'))
    {
        header ("Location: //".$core->get_variable('settings')['website']."/admin/index.php");
    }
}

