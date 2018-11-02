<?php
namespace Molle\Functions;

function generate ():void
{
    global $generate;
    $generate['stop'] = (float) microtime(true);

    echo $generate['stop'] - $generate['start'];
}

function str_random (int $length = 8, bool $number = true, bool $small_char = true, bool $big_char = true)
{
    $string = null;
    if ($length <= 0)
        $length = 1;

    for ($i = 0; $i <= $length; $i++)
    {
        if ($number == false)
            $rand_type = rand(1, 2);
        else
            $rand_type = rand(1, 3);


        if ($rand_type == 1){
            $string .= chr(rand(97, 122));
        } elseif ($rand_type == 2){
            $string .= chr(rand(65, 90));
        } elseif ($rand_type == 3){
            $string .= rand(0, 9);
        }

        if ($small_char == false)
            $string = strtoupper($string);
        elseif ($big_char == false)
            $string = strtolower($string);
    }

    return $string;
}

function replace_message (array $message): string
{
    global $core;
    $string = $core->get_handler('languages')->get($message[0]);

    if (count($message) > 0)
    {
        for ($i = 1; $i <= count($message)-1; $i++)
        {
            $string = str_replace("%".$i, $message[$i], $string);
        }
    }

    return $string;
}

function explode_composer (string $text):array
{
    $text = explode(',', $text);
    if (is_array($text))
    {
        foreach ($text as $v)
        {
            $v = explode(':', $v);
            $value[$v[0]] = $v[1];
        }
    } else
    {
        $v = explode(':', $text);
        $value[$v[0]] = $v[1];
    }

    return $value;
}
