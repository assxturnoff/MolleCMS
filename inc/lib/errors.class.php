<?php
namespace Molle\Errors;

define("MOLLE_DB", 33);
define("MOLLE_PLUGIN", 34);
define("MOLLE_LANGUAGE", 35);
define("MOLLE_USER", 36);
define("MOLLE_TEMPLATE", 37);
define("MOLLE_CONSTRUCTOR", 38);


class Errors
{
    private $reports;
    private $error_types = array(
        E_ERROR							=> 'Error',
        E_WARNING						=> 'Warning',
        E_PARSE							=> 'Parsing Error',
        E_NOTICE						=> 'Notice',
        E_CORE_ERROR					=> 'Core Error',
        E_CORE_WARNING					=> 'Core Warning',
        E_COMPILE_ERROR					=> 'Compile Error',
        E_COMPILE_WARNING				=> 'Compile Warning',
        E_DEPRECATED					=> 'Deprecated Warning',
        E_USER_ERROR					=> 'User Error',
        E_USER_WARNING					=> 'User Warning',
        E_USER_NOTICE					=> 'User Notice',
        E_USER_DEPRECATED	 			=> 'User Deprecated Warning',
        E_STRICT						=> 'Runtime Notice',
        E_RECOVERABLE_ERROR				=> 'Catchable Fatal Error',
        MOLLE_DB 						=> 'MyBB SQL Error',
        MOLLE_PLUGIN 					=> 'MyBB SQL Error',
        MOLLE_LANGUAGE 					=> 'MyBB SQL Error',
        MOLLE_USER 						=> 'MyBB SQL Error',
        MOLLE_CONSTRUCTOR			    => 'MyBB Template Error'
    );

    public $ignore_types = array(
        E_DEPRECATED,
        E_NOTICE,
        E_USER_NOTICE,
        E_STRICT
    );

    public function add_error ($type, $message, string $file=null, int $line=0)
    {
        if (in_array($type, $this->ignore_types))
            return false;

        /**
         * Jezeli $message string to tekst erroru
         */
        if (is_array($message))
        {
            $message = replace_message($message);
        }

        $reports[$type][] = array('message' => $message, 'file' => $file, 'line' => $line);
    }

    public function is_error ()
    {

        if (count($this->reports) > 0)
            return true;
    }
}