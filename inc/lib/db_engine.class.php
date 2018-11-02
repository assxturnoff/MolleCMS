<?php
namespace Molle\Database;

interface DB_Engine
{
    public function connect($host, $user, $pass, $name);

    public function prefix (string $cell);

    public function query (string $sql);

    public function fetch_array($query);

    public function fetch_field($query, $field);

    public function fetch_assoc($query);

    public function close ();

    public function error ($type, string $message, string $file,int $number = 0);
}