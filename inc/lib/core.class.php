<?php
class Core
{
    private $handler;
    private $variables;
    private $handler_add;
    private $start = false;
    private $type_lib = ['users', 'templates', 'plugins', 'languages', 'db', 'constructors', 'messages'];
    private $type_lib_add;

    public CONST VERSION = "1.0.0";

    public function __construct()
    {

        $protected = array("_GET", "_POST", "_SERVER", "_COOKIE", "_FILES", "_ENV", "GLOBALS");
        foreach($protected as $var)
        {
            if(isset($_POST[$var]) || isset($_GET[$var]) || isset($_COOKIE[$var]) || isset($_FILES[$var]))
            {
                die("Hacking attempt");
            }
        }

        //$this->post_refresh();
    }

    public function post_refresh ()
    {
        if (!isset($_POST))
            return;

        if (isset($_SESSION['post_refresh']) && $_SESSION['post_refresh'] == $_POST && $_SESSION['post_refresh'] != null && $_SESSION['post_refresh'] != "") 
        {
            $_SESSION['post_refresh_old'] = $_POST;
            unset($_POST);
            unset($_SESSION['post_refresh']);
        } else
        {
            if (isset($_SESSION['post_refresh_old']) && $_SESSION['post_refresh_old'] == $_POST)
                unset($_POST);
            else
                $_SESSION['post_refresh'] = $_POST;
        }
    }

    public function start ():bool
    {
        if ($this->start != false)
            return false;

        $this->start = true;

        include_once ROOT."inc/settings.php";
        $this->variables['settings'] = &$settings;

        include_once ROOT."inc/config.php";
        $this->variables['config'] = &$config;

        include_once ROOT."inc/lib/errors.class.php";
        $this->handler['errors'] = new \Molle\Errors\Errors();

        include_once ROOT . "inc/lib/db_engine.class.php";
        include_once ROOT . "inc/database/db_e_error.class.php";

        if ($this->variables['settings']['db_type'] == "mysqli")
        {
            include_once ROOT . "inc/database/db_mysqli.class.php";
            $db = new Molle\Database\MySQLi\DB_MySQLi($this->variables['settings']['db_host'], $this->variables['settings']['db_user'], $this->variables['settings']['db_pass'], $this->variables['settings']['db_name'], $this->variables['settings']['db_prefix']);
        }

        if ($this->variables['settings']['db_type'] == "pdo")
        {
            include_once ROOT . "inc/database/db_pdo.class.php";
            $db = new Molle\Database\MySQLi\DB_PDO($this->variables['settings']['db_host'], $this->variables['settings']['db_user'], $this->variables['settings']['db_pass'], $this->variables['settings']['db_name'], $this->variables['settings']['db_prefix']);
        }

        $this->handler['db'] = &$db;

        include_once ROOT."inc/functions.php";
        include_once ROOT."inc/file.func.php";

        include_once ROOT."inc/lib/plugins.class.php";
        $this->handler['plugins'] = new \Molle\Plugins\Plugins();

        include_once ROOT."inc/lib/languages.class.php";
        $this->handler['languages'] = new \Molle\Languages\Languages();

        include_once ROOT."inc/lib/messages.class.php";
        $this->handler['messages'] = new \Molle\Messages\Messages();

        include_once ROOT."inc/lib/users.class.php";
        $this->handler['users'] = new \Molle\Users\Users();

        $this->handler['templates'] = $this->new_templates  ();

        include_once ROOT."inc/lib/constructors.class.php";

        return true;
    }

    public function new_templates ()
    {
        include_once ROOT."inc/lib/templates.class.php";
        return new \Molle\Templates\Templates();
    }

    public function get_handler (string $handler = "")
    {
        if ($handler == "")
            return $this->handler;

        if (isset($this->handler[$handler]))
            return $this->handler[$handler];

        return false;
    }

    public function get_variable (string $var = "")
    {
        if ($var == "")
            return $this->variables;

        if (isset($this->variables[$var]))
            return $this->variables[$var];

        return false;
    }

    public function add_handler (string $handler, object $class): void
    {
        $this->handler_add[] = &$handler;
        $this->handler[$handler] = &$class;
    }

    public function remove_handler (string $handler): void
    {
        if (in_array($handler, $this->handler_add))
        {
            unset($this->handler[$handler]);
            unset($this->handler_add[$handler]);
        }
    }
    public function get_type_lib()
    {
        return $this->type_lib;
    }

    public function add_type_lib ($type):bool
    {
        if (in_array($type, $this->type_lib))
            return false;

        $this->type_lib[] = $type;
        $this->type_lib_add[] = $type;
    }
}