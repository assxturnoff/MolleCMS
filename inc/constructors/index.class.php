<?php
namespace Molle\Constructors\Index;

use Molle\Constructors\Constructors;

class Index implements Constructors
{
    private $handler;

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
    }

    protected function user()
    {
        $this->handler['users'];
    }

    public function get()
    {
        $this->user();
        $this->handler['languages']->load();
        $this->handler['plugins']->add_hook('t_post', array($this, 'elo_test'));
        echo $this->handler['templates']->render('start', 1);
    }

    public function elo_test ()
    {
        if (isset($_POST['login']))
            $this->handler['templates']->add_variable('post_l', $_POST['login']);
        else
            $this->handler['templates']->add_variable('post_l', "Stop refresh!");
    }
}