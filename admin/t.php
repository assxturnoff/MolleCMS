<?php
$list = array();
if ($handle = opendir(dirname(__FILE__))) {
    while (false !== ($entry = readdir($handle)))
    {
        if (in_array($entry, ['.', '..']))
            continue;

        $list[] = $entry;
    }
    closedir($handle);
}
