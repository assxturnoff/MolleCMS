<?php
namespace Molle\Constructors\Member;

use Molle\Constructors\Constructors;

class Member implements Constructors
{
    private $handler;

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
    }

    protected function user_register()
    {
        if (isset($_POST) && isset($_POST['email']))
            return $this->handler['users']->register($_POST['login'], $_POST['email'], $_POST['password']);
    }

    protected function user_login()
    {
        if (isset($_POST) && isset($_POST['login']))
            return $this->handler['users']->login($_POST['login'], $_POST['password']);
    }

    public function get()
    {
        $this->handler['languages']->load();
        if ($this->handler['users']->is_login())
            echo "Jestes zalogowany: ".$this->handler['users']->get_user('nickname');
        if (isset($_GET['register']))
            $this->user_register();
        if (isset($_GET['login']))
            $this->user_login();

        if (isset($_GET['login']) || isset($_GET['register']))
            echo $this->handler['templates']->render('member', 1);
    }

}