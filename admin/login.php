<?php
$admin_login = true;

include_once "admin.php";
include_once ROOT."inc/constructors/admin/login.class.php";

$constructor = new \Molle\Constructors\Admin\Login\Login();

$constructor->get();

if (isset($_GET["generate"]))
    \Molle\Functions\generate();
