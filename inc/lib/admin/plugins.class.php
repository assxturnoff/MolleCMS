<?php
namespace Molle\Admin\Plugins;

class Plugins
{
    public $current_plugin;
    public $handler;
    public $list = ['select', 'installed', 'uninstalled', 'activation', 'deactivation'];

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
        if (isset($_GET['action']) && in_array($_GET['action'], $this->list))
        {
            if ($_GET['action'] == "select" || (isset($_GET['plugin']) && $this->handler['plugins']->exist($_GET['plugin']) != true) || isset($_GET['plugin']) == false)
            {
                $this->select();
            } else
            {
                $this->$_GET['action']($_GET['plugin']);
            }

        } else
        {
            $this->select();
        }
    }

    public function run (int $type = 0, string $plugin = "")
    {

    }

    public function select ():array
    {
        $handler = $this->handler['plugins'];

        $plugins = array();

        $count = count($handler->get_list());
        for ($i = 0; $i <= $count; $i++)
        {
            $plugin = array_keys($handler->get_list())[$i];
            $plugins[$plugin] = array (
                'is_install' => $handler->is_install($plugin),
                'is_active' => $handler->is_active($plugin)
            );

        }

        return $plugins;
    }

    public function installed (string $init): bool
    {
        return $this->handler['plugins']->installed($init);
    }

    public function uninstalled(string $init): bool
    {
        return $this->handler['plugins']->uninstalled($init);
    }

    public function activation (string $init): bool
    {
        return $this->handler['plugins']->activation($init);
    }

    public function deactivation(string $init): bool
    {
        return $this->handler['plugins']->deactivation($init);
    }

}