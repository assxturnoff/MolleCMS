<?php
namespace Molle\Templates;

class Templates
{
    public $id;
    public $help = "";
    private $template;
    private $variables;
    private $code = array (
        "start" => '{',
        "active" => '$',
        "deactivate" => '#',
        "name" => 'molle',
        "argStart" => '[',
        "argStop" => ']',
        "stop" => '}'
    );

    protected $handler;

    private static $getter = array (
        'v' => "variable",
        't' => "theme",
        'l' => 'lang'
    );

    public function __construct()
    {
        global $core;
        $this->handler = $core->get_handler();
    }

    public function add_variable ($name, $value):void
    {
        $this->variables[$name] = $value;
    }

    public function get_variable (string $name)
    {
        if (explode("_", $name)[0] == "v")
            $name = substr($name, 2);

        $this->handler['plugins']->run_hooks('templates_v_'.$name);

        if (isset($this->variables[$name]))
            return $this->variables[$name];
    }

    public function get_theme (string $name)
    {
        if (explode("_", $name)[0] == "t")
            $name = substr($name, 2);

        $this->handler['plugins']->run_hooks('templates_t_'.$name);
        $db = & $this->handler['db'];
        $sql = "SELECT * FROM `molle_themes` WHERE `".$db->prefix('name')."` = '".$name."' AND `".$db->prefix('uid')."` = '".$this->id."'";

        return $db->fetch_field($sql, 'code');
    }

    public function get_lang (string $name)
    {
        if (explode("_", $name)[0] == "l")
            $name = substr($name, 2);

        $handler = $this->handler['languages'];

        return $handler->get($name);
    }

    public function search (int $search = 0, int $type = 0)
    {
        $text = $this->build_text("", $type, -1);
        $start = strpos($this->template, $text, $search);
        $stop  = strpos($this->template, $this->build_text("", $type, 1), $start);

        if ($start === false || $stop === false)
            return false;

        for ($i = $start+strpos($text, "[")+1, $arg = ""; $i < $stop; $i++)
        {
            $arg .= $this->template[$i];
        }

        $prefix = explode("_", $arg)[0];
        $this->handler['plugins']->run_hooks('templates_search', array ('prefix' => $prefix, 'arg' => $arg, 'type' => $type));

        return array ('prefix' => $prefix, 'arg' => $arg, 'stop' => $stop, 'type' => $type);
    }

    private function replace (int $type = 0):bool
    {
        $template = $this->template;

        $arg['stop'] = 0;
        while (($arg = $this->search($arg['stop'], $type)) != false)
        {
            if (in_array($arg['prefix'], ['v', 't', 'l']))
                $args[$arg['prefix']] = $arg['arg'];
        }

        if (!isset($args) || !is_array($args))
            return false;

        foreach ($args as $prefix => $arg)
        {
            $func = "get_".strtolower(self::$getter[$prefix]);

            if (!method_exists($this, $func))
                continue;

            try {
                if (!strpos($this->$func($arg), $this->build_text($arg, $type)) || $type == 1)
                    $this->template = str_ireplace($this->build_text($arg, $type), $this->$func($arg), $this->template);
                else
                    throw new \Exception(array('error_templates_replace_while_in_active', $type."_".$arg));
            } catch (\Exception $e)
            {
                $this->handler['errors']->add(MOLLE_TEMPLATE, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        }

        if ($template == $this->template)
            return false;

        return true;
    }

    public function render (string $name = "start", int $id = -1)
    {
        if ($id > -1)
            $this->id = $id;

        $this->template = $this->get_theme($name);

        $handler = $this->handler['messages'];
        $this->add_variable("handler_message", $handler->get());

        while ($this->replace(0) != false); //Active
        $this->replace(1); //Deactive

        return $this->template.$this->help;
    }

    public function build_text (string $text, int $type = 0, int $all = 0):string
    {
        switch ($type)
        {
            default: // 0
                $char = $this->code['active'];
                break;
            case 1:
                $char = $this->code['deactivate'];
                break;
        }

        switch ($all)
        {
            default: // 0
                return $this->code['start'].$char.$this->code['name'].$this->code['argStart'].$text.$this->code['argStop'].$char.$this->code['stop'];
            case -1:
                return $this->code['start'].$char.$this->code['name'].$this->code['argStart'].$text;
            case 1:
                return $text.$this->code['argStop'].$char.$this->code['stop'];
        }
    }
}