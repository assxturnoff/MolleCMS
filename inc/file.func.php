<?php
namespace Molle\Functions;

function file_list (string $path, array $disable, bool $disable_file = false, array $file = array()): array
{
    if (!file_exists($path))
        return array();

    $file_disable = ['.', '..'];

    if ($disable_file)
    {
        $file_disable[] = 'index.html';
        $file_disable[] = 'index.htm';
        $file_disable[] = 'index.php';

        if (count($file) > 0)
        {
            foreach ($file as $item)
            {
                $file_disable[] = $item;
            }
        }
    }


    $list = array();
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle)))
        {
            if (in_array($entry, $file_disable))
                continue;

            if(!in_array(substr($entry, 0, 1), $disable) && strpos($entry, '.') !== false)
                $list[] = $entry;
        }
        closedir($handle);
    }

    return $list;
}