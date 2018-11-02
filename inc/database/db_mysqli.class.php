<?php
namespace Molle\Database\MySQLi;

use Molle\Database\DB_Engine;
use Molle\Database\DB_E_Error;

class DB_MySQLi implements DB_Engine
{
    static public $name = "MySQLi";
    public $connect;
    public $prefix;

    private $connect_error = -1;

    public function __construct($host, $user, $pass, $name, $prefix)
    {
        $this->prefix = $prefix;
        $this->connect($host, $user, $pass, $name);
    }

    public function connect ($host, $user, $pass, $name): void
    {
        try {
            $this->connect = new \mysqli($host, $user, $pass, $name);
            if ($this->connect->connect_error)
                throw new \Exception($this->connect->connect_error);

        } catch (\mysqli_sql_exception $e)
        {
            $this->error(MOLLE_DB, $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    public function prefix(string $cell):string
    {
        if ("id" == strtolower($cell))
            return $cell;

        return $this->prefix."_".$cell;
    }

    public function query(string $sql)
    {
        try {
            $var = $this->connect->query($sql);
            if (!$this->connect->error)
                return $var;

            throw new \Exception($this->connect->error);
        } catch (\mysqli_sql_exception $e)
        {
            $this->error(MOLLE_DB, $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    public function fetch_array($query)
    {
        try {
            $result = $this->query($query);
            $var = $result->fetch_array();
            if (!$this->connect->error)
                return $var;

            throw new \Exception($this->connect->error);
        } catch (\mysqli_sql_exception $e)
        {
            $this->error(MOLLE_DB, $e->getMessage(), $e->getFile(), $e->getLine());
        }

    }

    public function fetch_field($query, $field)
    {
        return $this->fetch_assoc($query)[$this->prefix($field)];
    }

    public function fetch_assoc ($query)
    {
        try {
            $result = $this->query($query);
            $array = $result->fetch_assoc();
            if (!$this->connect->error)
                return $array;

            throw new \Exception($this->connect->error);
        } catch (\mysqli_sql_exception $e)
        {
            $this->error(MOLLE_DB, $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }


    public function close()
    {
        return $this->connect->close();
    }

    public function error($type, string $message, string $file, int $number = 0)
    {
        DB_E_Error::add($type, $message, $file, $number);
    }
}