<?php
namespace Molle\Constructors\Admin\Index;

use Molle\Constructors\Constructors;

class Index implements Constructors
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
}