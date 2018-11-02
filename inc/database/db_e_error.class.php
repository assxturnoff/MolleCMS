<?php
namespace Molle\Database;


trait DB_E_Error
{
    public static function add ($type, $message, $file = "", $number)
    {
        global $core;
        $handler = $core->get_handler("errors");
        if ($file == "")
            $file = ROOT.'/inc/database/db_'.$core->get_variable['settings']['db_type'].'.class.php';
        $handler->add_error($type, $message, $file, $number);
    }
}