<?php
namespace Molle\Plugins;


use function Molle\Functions\file_list;

class Plugins
{
    public $current_hook;
    private $handler;
    public $hooks;
    public $list;

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
        $this->load();
    }

    public function load ()
    {
        $path = ROOT."inc/plugins";
        $list = file_list($path, ['.', '~'], true);

        $init = "";

        foreach ($list as $file)
        {
            include_once $path."/".$file;

            if ($_SESSION['plugins_load_init'] == $init)
                continue;

            if ($init != "" && $init != null)
                $_SESSION['plugins_load_init'] = $init;

            if (!in_array($init, $this->list))
                $this->list[$init] = $path."/".$file;
        }
    }

    public function run_all (bool $type = false):void
    {
        if (!is_array($this->list))
            return;

        $count = count($this->list);
        for ($i = 0; $i <= $count; $i++)
        {
            $plugin = array_keys($this->list)[$i];
            $this->run($type, $plugin);
        }


    }

    public function run (bool $type = false, string $plugin):void
    {
        if (!$this->exist($plugin))
            return;

        include_once $this->list[$plugin];

        $func = $plugin."_run_global";
        if ($type)
            $func = $plugin."_run_admin";
        
        if (function_exists($func))
        {
            if ($this->is_active($plugin))
                $func($type);
        }

    }

    public function get_list ()
    {
        return $this->list;
    }

    public function exist (string $init = ""):bool
    {
        if (isset($this->list[$init]))
        {
            if (file_exists($this->list[$init]))
                return true;
        }

        return false;
    }

    public function installed (string $init): bool
    {
        if ($this->is_install())
        {
            $this->handler['messages']->add_code("plugins", 0);
            $this->handler['plugins']->run_hooks("plugins_installed", $init);
            return false;
        }

        $db = $this->handler['db'];
        $sql = "INSERT INTO `molle_plugins` (`".$db->prefix('init')."`) VALUES (".$init.")";
        if($db->query($sql) === true)
        {
            $this->handler['messages']->add_code("plugins", 1);
            $this->handler['plugins']->run_hooks("plugins_installed", $init);
            return true;
        }

        $this->handler['messages']->add_code("plugins", 2);
        $this->handler['plugins']->run_hooks("plugins_installed", $init);
        return false;
    }

    public function uninstalled(string $init): bool
    {
        if (!$this->is_install())
        {

            $this->handler['messages']->add_code("plugins", 3);
            $this->handler['plugins']->run_hooks("plugins_uninstalled", $init);
            return false;
        }

        $db = $this->handler['db'];
        $sql = "DELETE FROM `molle_plugins` WHERE `".$db->prefix('init')."` = '".$init."'";
        if($db->query($sql) === true)
        {
            $this->handler['messages']->add_code("plugins", 4);
            $this->handler['plugins']->run_hooks("plugins_uninstalled", $init);
            return true;
        }

        $this->handler['messages']->add_code("plugins", 5);
        $this->handler['plugins']->run_hooks("plugins_uninstalled", $init);
        return false;
    }

    public function is_install(string $init): bool
    {
        $db = $this->handler['db'];
        $sql = "SELECT * FROM `molle_plugins` WHERE `".$db->prefix('init')."` = '".$init."'";
        if ($db->fetch_field($sql, 'active') == true)
        {
            $this->handler['plugins']->run_hooks("plugins_is_install", $init);
            return true;
        }

        $this->handler['plugins']->run_hooks("plugins_is_install_not", $init);
        return false;
    }

    public function activation (string $init): bool
    {
        if ($this->is_active())
        {
            $this->handler['messages']->add_code("plugins", 6);
            $this->handler['plugins']->run_hooks("plugins_activation", $init);
            return false;
        }

        $db = $this->handler['db'];
        $sql = "UPDATE `molle_plugins` SET `".$db->prefix('active')."` = 1 WHERE `".$db->prefix('init')."` = '".$init."'";

        if ($db->query($sql) === true)
        {
            $this->handler['messages']->add_code("plugins", 7);
            $this->handler['plugins']->run_hooks("plugins_activation", $init);
            return true;
        }

        $this->handler['messages']->add_code("plugins", 8);
        $this->handler['plugins']->run_hooks("plugins_activation", $init);
        return false;

    }

    public function deactivation(string $init): bool
    {
        if (!$this->is_active($init))
        {
            $this->handler['messages']->add_code("plugins", 9);
            $this->handler['plugins']->run_hooks("plugins_deactivation", $init);
            return false;
        }

        $db = $this->handler['db'];
        $sql = "UPDATE `molle_plugins` SET `".$db->prefix('active')."` = 0 WHERE `".$db->prefix('init')."` = '".$init."'";

        if ($db->query($sql) === true)
        {
            $this->handler['messages']->add_code("plugins", 10);
            $this->handler['plugins']->run_hooks("plugins_deactivation", $init);
            return true;
        }

        $this->handler['messages']->add_code("plugins", 11);
        $this->handler['plugins']->run_hooks("plugins_deactivation", $init);
        return false;
    }

    public function is_active (string $init): bool
    {
        if (!$this->is_install($init))
        {
            return false;
        }

        $db = $this->handler['db'];
        $sql = "SELECT `".$db->prefix('active')."` FROM `molle_plugins` WHERE `".$db->prefix('init')."` = '".$init."'";

        if ($db->fetch_field($sql, 'active') == true)
        {
            $this->handler['plugins']->run_hooks("plugins_is_active", $init);
            return true;
        }

        $this->handler['plugins']->run_hooks("plugins_is_active_not", $init);
        return false;
    }

    public function add_hook (string $name, $action, int $priority=10, string $file="")
    {
        if (!is_array($action))
        {
            $this->hooks[$name][$priority][$action] = array ('action' => $action, 'method' => 'func', 'file' => $file);
            return true;
        }

        $func = $this->array_sprintf($action);
        if ($func == false)
            return false;

        $this->hooks[$name][$priority][$func] = array ('action' => $action, 'method' => 'class', 'file' => $file);
        return true;
    }

    public function remove_hook (string $name, $action, int $priority=10)
    {
        if (!is_array($action))
        {
            unset($this->hooks[$name][$priority][$action]);
            return true;
        }

        $func = $this->array_sprintf($action);
        if ($func == false)
            return false;

        unset($this->hooks[$name][$priority][$func]);
        return true;
    }

    public function run_hooks (string $name, $args = null)
    {
        if (!isset($this->hooks[$name]) || !is_array($this->hooks[$name]))
            return $args;

        $this->current_hook = $name;
        ksort($this->hooks[$name]);

        foreach ($this->hooks[$name] as $priority => $hook)
        {
            if (!is_array($hook))
                return false;

            foreach ($hook as $value)
            {
                if ($value['file'])
                    require_once $value['file'];

                if ($value['method'] == "func")
                {
                    $value['action']($args);
                } else
                {
                    if (is_array($args))
                        call_user_func_array($value['action'], $args);
                    else
                        call_user_func_array($value['action'], array(&$args));
                }
            }
        }
        $this->current_hook = null;
        return $args;
    }

    private function array_sprintf (array $array)
    {
        if (count($array) != 2 || !is_array($array))
            return false;

        if(is_string($array[0]))
            return sprintf('%s::%s', $array[0], $array[1]);

        if(is_object($array[0]))
            return sprintf('%s->%s', get_class($array[0]), $array[1]);

        return false;
    }

    public function version ($v)
    {

    }
}