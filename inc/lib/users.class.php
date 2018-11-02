<?php
namespace Molle\Users;

use function Molle\Functions\explode_composer;
use function Molle\Functions\str_random;

class Users
{
    private $user = array();
    protected $handler;
    protected $variable;
    public $help;

    function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
        $this->variable = $core->get_variable();

        if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0)
        {
            $this->user['id'] = $_SESSION['user']['id'];
        }


        if ($this->is_login())
        {
            $this->set_user($this->user['id']);

            if (isset($this->user['language']))
                $this->handler['languages']->id = $this->user['language'];

            if (isset($this->user['templates']))
                $this->handler['templates']->id = $this->user['template'];
        }
    }

    public function get_user (string $field = "")
    {
        if (!$this->is_login())
            return false;

        if ($field == "")
            return $this->user;

        if (in_array($field, $this->user))
            return $this->user[$field];

        return false;

    }

    public function set_user (int $id = -1)
    {
        if ($id == -1)
        {
            if (!$this->is_login())
                return;

            $id = $this->user['id'];
        }

        $group = explode_composer($this->exist_user('id', $id, 'group'));
        $this->user = array(
            'id'      => $id,
            'login'   => $this->exist_user('id', $id, 'login'),
            'group'   => $group['group'],
            'group_v' => $group['group_v']
        );

        if (($template = $this->exist_user('id', $id, 'template')) > 0)
            $this->user['template'] = $template;

        if (($language = $this->exist_user('id', $id, 'language')) != '')
            $this->user['language'] = $language;

        if (isset($_SESSION['user']['pa']) && $_SESSION['user']['pa'] && $this->has_permission('admin'))
        {
            $this->user['admin'] = true;
        }

        $this->user['nickname'] = $this->get_nickname();
        $_SESSION['user']['id'] = $id;
        $this->handler['plugins']->run_hooks("users_set_user", $id);
    }

    public function has_permission (string $permission):bool
    {
        if ($this->is_login())
            $id = $this->user['id'];
        else
            $id = 0;
        $group = explode_composer($this->exist_user('id', $id, 'group'))['group'];
        $permission = $this->get_permission($group)[$permission];

        if ($permission == 1)
            return true;

        return false;

    }

    public function get_nickname()
    {
        if (!$this->is_login())
            return false;

        $db = $this->handler['db'];
        $sql = "SELECT `".$db->prefix('nickname')."` FROM `molle_groups` WHERE `".$db->prefix('id')."` = '".$this->user['group_v']."'";

        if (($nickname = $db->fetch_field($sql, 'nickname')) == false)
        {
            $this->handler['plugins']->run_hooks("users_get_nickname_not");
            return $this->user['login'];
        }

        $this->handler['plugins']->run_hooks("users_get_nickname");
        return str_replace("{login}", $this->user['login'], $nickname);

    }

    public function get_permission (int $group)
    {
        $db = $this->handler['db'];
        $sql = "SELECT `".$db->prefix('permissions')."` FROM `molle_groups` WHERE `".$db->prefix('id')."` = '".$group."'";
        $permission = explode_composer($db->fetch_field($sql, 'permissions'));

        if (!isset($permission['_inherit']))
        {
            $this->handler['plugins']->run_hooks("users_get_permission", $permission);
            return $permission;
        }

        $permissions[0] = $permission['_inherit'];
        $last_child = $permissions[0];

        while (isset($permission['_inherit']) != false)
        {
            $sql = "SELECT `".$db->prefix('permission')."` FROM `molle_groups` WHERE `".$db->prefix('id')."` = '".$last_child."'";
            $permission_child = explode_composer($db->fetch_assoc($sql));

            if (isset($permission_child['_inherit']) && $permission_child['_inherit'] == $permissions[0])
            {
                unset($permission['_inherit']);
                continue;
            }

            if (isset($permission_child['_inherit']))
            {
                $permissions[] = $permission_child['_inherit'];
            } else
            {
                unset($permission['_inherit']);
            }
        }

        if (count($permissions) >= 1)
        {
            foreach ($permissions as $permission_child)
            {
                $permission_child = explode_composer($permission_child);
                foreach ($permission_child as $permission_child_name => $permission_child_value)
                {
                    $permission[$permission_child_name] = $permission_child_value;
                }
            }
        }

        $this->handler['plugins']->run_hooks("users_get_permission", $permission);
        return $permission;
    }

    public function register ($login, $email, $password):bool
    {

        if ($this->is_login())
        {
            $this->handler['messages']->add_code("users", 0);
            $this->handler['plugins']->run_hooks("users_register", array($login, $email, $password));
            return false;
        }

        $error_email = $this->check_email($email);
        $error_login = $this->check_login($login);
        $error_password = $this->check_password($password);

        if ($error_email || $error_login || $error_password)
        {
            $this->handler['plugins']->run_hooks("users_register", array($login, $email, $password));
            return false;
        }

        $salt = str_random();
        $password = $this->hash($password, $salt);
        $db = $this->handler['db'];
        $group = "group:".$this->variable['settings']['user_group_default'].",group_v:".$this->variable['settings']['user_group_default'];
        $sql = "INSERT INTO `molle_users`( `".$db->prefix('login')."`, `".$db->prefix('email')."`, `".$db->prefix('password')."`, `".$db->prefix('salt')."`, `".$db->prefix('group')."`) VALUES ('".$login."', '".$email."', '".$password."', '".$salt."', '".$group."')";

        if ($this->handler['db']->query($sql) === true)
        {

            $this->set_user($this->exist_user('login', $login));
            $this->handler['messages']->add_code("users", 1);
            $this->handler['plugins']->run_hooks("users_register", array($login, $email, $password));
            return true;
        }

        $this->handler['messages']->add_code("users", 2);
        $this->handler['plugins']->run_hooks("users_register", array($login, $email, $password));
        return false;
    }

    public function login (string $login, string $password):bool
    {
        if ($this->is_login() && isset($_SESSION['user']['pa']) == false || (isset($_SESSION['user']['pa']) && $_SESSION['user']['pa'] == false))
        {
            $this->handler['messages']->add_code("users", 0);
            $this->handler['plugins']->run_hooks("users_login", array($login, $password));
            $_SESSION['user']['pa'] = false;
            return false;
        }

        if (($salt = $this->exist_user('login', $login, 'salt')) == false)
        {
            $this->handler['messages']->add_code("users", 4, array('Login nie istnieje'));
            $this->handler['plugins']->run_hooks("users_login", array($login, $password));
            $_SESSION['user']['pa'] = false;
            return false;
        }

        $password = $this->hash($password, $salt);

        $db = $this->handler['db'];
        $sql = "SELECT `".$db->prefix('id')."` FROM `molle_users` WHERE `".$db->prefix('login')."` = '".$login."' AND `".$db->prefix('password')."` = '".$password."'";

        if (($id = $db->fetch_field($sql, 'id')) != false)
        {
            $this->set_user($id);
            $this->handler['messages']->add_code("users", 3, array('Witaj', $login. "w panelu: ".$this->user['admin']));
            $this->handler['plugins']->run_hooks("users_login", array($login, $password));
            return true;
        }

        $this->handler['messages']->add_code("users", 4, array(""));
        $this->handler['plugins']->run_hooks("users_login", array($login, $password));
        $_SESSION['user']['pa'] = false;
        return false;
    }

    protected function exist_user (string $user, $value, string $field = 'id')
    {
        $db = $this->handler['db'];
        $sql = "SELECT `".$db->prefix($field)."` FROM `molle_users` WHERE `".$db->prefix($user)."` = '".$value."'";

        if (($id = $db->fetch_field($sql, $field)) === false)
            return false;

        return $id;
    }

    public function hash (string $password, string $salt):string
    {
        return md5(md5($password).$salt);
    }

    private function check_email($value):bool
    {
        $error = false;

        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            $this->handler['messages']->add_code("users", 5);
            $error = true;
        }

        $sql = "SELECT `".$this->handler['db']->prefix('email')."` FROM `molle_users` WHERE `".$this->handler['db']->prefix('email')."` = '".$value."'";

        if ($this->handler['db']->fetch_field($sql, 'email') == $value)
        {
            $this->handler['messages']->add_code("users", 6);
            $error = true;
        }

        $this->handler['plugins']->run_hooks("users_check_email", $value);
        return $error;
    }

    private function check_password($value):bool
    {
        $error = false;
        $settings = $this->variable['settings'];

        if ($settings['user_password_length_min'] > strlen($value))
        {
            $this->handler['messages']->add_code("users", 7);
            $error = true;
        }

        if ($settings['user_password_length_max'] < strlen($value))
        {
            $this->handler['messages']->add_code("users", 8);
            $error = true;
        }

        if (preg_match($settings['user_password_char'], $value) == false)
        {
            $this->handler['messages']->add_code("users", 9);
            $error = true;
        }

        $this->handler['plugins']->run_hooks("users_check_password", $value);
        return $error;
    }

    private function check_login($value):bool
    {
        $error = false;
        $settings = $this->variable['settings'];

        $sql = "SELECT `".$this->handler['db']->prefix('login')."` FROM `molle_users` WHERE `".$this->handler['db']->prefix('login')."` = '".$value."'";
        if ($this->handler['db']->fetch_field($sql, 'login') == $value)
        {
            $this->handler['messages']->add_code("users", 10);
            $error = true;
        }

        if ($settings['user_login_length_min'] > strlen($value))
        {
            $this->handler['messages']->add_code("users", 11);
            $error = true;
        }

        if ($settings['user_login_length_max'] < strlen($value))
        {
            $this->handler['messages']->add_code("users", 12);
            $error = true;
        }

        if (preg_match($settings['user_login_char'], $value) == false)
        {
            $this->handler['messages']->add_code("users", 13);
            $error = true;
        }

        $this->handler['plugins']->run_hooks("users_check_login", $value);
        return $error;
    }

    public function is_login ():bool
    {
        if (isset($this->user['id']))
        {
            $this->handler['plugins']->run_hooks("users_is_login");
            return true;
        }

        $this->handler['plugins']->run_hooks("users_is_login_not");
        return false;
    }

    public function logout ($admin = false):void
    {
        if ($admin == false)
        {
            unset($_SESSION['user']);
            $this->user = null;
        } else
        {
            unset($_SESSION['user']['pa']);
            $this->user['pa'] = false;
            $this->user['admin'] = false;
        }
    }
}