<?php
namespace Molle\Languages;
use Molle\Functions;

class Languages
{
    public $id;
    protected $lang;
    private $handler;

    public function __construct()
    {
        global $core;
        $this->id = $core->get_variable('config')['lang'];
        $this->handler = $core->get_handler();
    }

    public function load ()
    {
        $path = ROOT."inc/languages/".$this->id;
        $list = Functions\file_list($path, array(), true);

        if (!count($list) > 0)
        {
            try {
                throw new \Exception("Brak jezykow");
            } catch (\Exception $e)
            {
                $this->handler['errors']->add(MOLLE_LANGUAGE, $e->getMessage(), $e->getFile(), $e->getLine());
            }
            return;
        }

        $l = array();

        foreach ($list as $file)
        {
            include_once $path."/".$file;
        }

        $this->lang = $l;
    }

    public function get ($name)
    {
        if (isset($this->lang[$name]))
            return $this->lang[$name];

        return $name;
    }
}