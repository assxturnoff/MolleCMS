<?php
namespace Molle\Database\PDO;

use Molle\Database\DB_Engine;
use Molle\Database\DB_Engine_error;

class DB_PDO implements DB_Engine
{
    public function connect($host, $user, $pass, $name)
    {
        // TODO: Implement connect() method.
    }

    public function prefix(string $cell)
    {
        // TODO: Implement prefix() method.
    }

    public function query(string $sql)
    {
        // TODO: Implement query() method.
    }

    public function fetch_array($query)
    {
        // TODO: Implement fetch_array() method.
    }

    public function fetch_field($query, $field)
    {
        // TODO: Implement fetch_field() method.
    }

    public function fetch_assoc($query)
    {
        // TODO: Implement fetch_assoc() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function error($type, string $message, string $file, int $number = 0)
    {
        DB_E_Error::add($type, $message, $file, $number);
    }
}