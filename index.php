<?php
include_once "global.php";
include_once "inc/constructors/index.class.php";

$constructor = new \Molle\Constructors\Index\Index();

$constructor->get();

if (isset($_GET["generate"]))
    \Molle\Functions\generate();
