<?php
namespace Molle\Messages;

use function Molle\Functions\replace_message;

class Messages
{
    private $message = array();
    private $code = array();
    private $code_message = array (
        "users" => array (
            0  => 'message_users_is_login',
            1  => 'message_users_register',
            2  => 'message_users_register_not',
            3  => 'message_users_login',
            4  => 'message_users_login_not',
            5  => 'message_users_check_email_filter',
            6  => 'message_users_check_email_exist',
            7  => 'message_users_check_password_length_min',
            8  => 'message_users_check_password_length_max',
            9  => 'message_users_check_password_characters',
            10 => 'message_users_check_login_exist',
            11 => 'message_users_check_login_length_min',
            12 => 'message_users_check_login_length_max',
            13 => 'message_users_check_login_characters',
            14 => 'message_users_is_login_not',
            15 => 'message_users_has_permission_not'
        ),

        "templates" => array (

        ),

        "plugins" => array (
            0  => 'message_plugins_is_install',
            1  => 'message_plugins_installed',
            2  => 'message_plugins_installed_not',
            3  => 'message_plugins_is_install_not',
            4  => 'message_plugins_uninstalled',
            5  => 'message_plugins_uninstalled_not',
            6  => 'message_plugins_is_active',
            7  => 'message_plugins_activation',
            8  => 'message_plugins_activation_not',
            9  => 'message_plugins_is_active_not',
            10 => 'message_plugins_activation',
            11 => 'message_plugins_activation_not'
        )
    );

    public function add($message):void
    {
        if (is_array($message))
        {
            if (is_array($message[1]))
            {
                $message_new[] = $message[0];
                foreach ($message[1] as $item)
                {
                    $message_new[] = $item;
                }
                $message = $message_new;
            }

            $message = replace_message($message);
        }

        if (!in_array($message, $this->message))
            $this->message[] = $message;

    }

    public function add_code (string $type, int $code, $args = null):void
    {
        global $core;
        $type = strtolower($type);
        if (in_array($type, $core->get_type_lib()))
        {
            if (isset($this->code_message[$type][$code]))
            {
                $lang = $core->get_handler('languages')->get($this->code_message[$type][$code]);

                if ($args == null)
                    $this->add($lang);
                else
                    $this->add(array ($lang, $args));

                if (isset($this->code[$type]))
                {
                    if (!in_array($code, $this->code[$type]))
                        $this->code[$type][] = $code;
                } else
                {
                    $this->code[$type][] = $code;
                }

            }
        }
    }

    public function get ():string
    {
        $messages = "<ul class='handler_message'>";
        foreach ($this->message as $message)
        {
            $messages .= "<li>".$message."</li>";
        }
        $messages .= "</ul>";

        return $messages;
    }

    public function get_code(string $type)
    {
        global $core;
        $type = strtolower($type);
        if (in_array($type, $core->get_type_lib())) {
            return $this->code[$type];
        }
    }
}