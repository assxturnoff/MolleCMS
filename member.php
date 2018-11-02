<?php
include_once "global.php";
include_once "inc/constructors/member.class.php";

$constructor = new \Molle\Constructors\Member\Member();

$constructor->get();

if (isset($_GET["generate"]))
    \Molle\Functions\generate();

