<?php
include_once "admin.php";
include_once ROOT."inc/constructors/admin/index.class.php";

use Molle\Constructors\Admin\Index\Index;

$constructor = new Index();
$constructor->get();

if (isset($_GET["generate"]))
    \Molle\Functions\generate();
