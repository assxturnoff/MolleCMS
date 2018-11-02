<?php
namespace Molle\Constructors\Admin\Users;

use Molle\Constructors\Constructors;

class Users implements Constructors
{
    private $handler;

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
    }

    public function get()
    {
        $this->handler['languages']->load();
        echo $this->handler['template_admin']->render('start', 0);
    }

    public function select ($filter = null)
    {
        $style_user_window = $this->handler['template_admin']->get_theme('name'); //TODO Name
        $style_users = null;
        $db = $this->handler['db'];
        $sql = "";
        $result = $db->fetch_assoc($sql);
        while ($result)
        {
            $style_user = str_replace('', '', $style_user_window);
        }

        $style_users .= $style_user;

        $this->handler['template_admin']->add_variable('name_user', $style_users); //TODO Name
        $this->handler['template_admin']->add_variable('name', $this->handler['template_admin']->get_theme('name')); //TODO Name x2
    }
}