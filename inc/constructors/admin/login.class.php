<?php
namespace Molle\Constructors\Admin\Login;

use Molle\Constructors\Constructors;

class Login implements Constructors
{
    private $handler;

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
    }

    public function login ()
    {
        $_SESSION['user']['pa'] = true;
        $return = $this->handler['users']->login($_POST['login'], $_POST['password']);

        return $return;
    }

    public function get()
    {
        $this->handler['languages']->load();
        if (isset($_POST['login']))
        {
            if ($this->login())
            {
                global $core;
                header ("Location: //".$core->get_variable('settings')['website']."/admin/index.php");
            } else
            {
                $_SESSION['user']['pa'] = false;
            }

        }

        echo $this->handler['template_admin']->render('start_login', 0);
    }
}