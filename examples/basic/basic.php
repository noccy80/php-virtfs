<?php

require_once __DIR__."/../../vendor/autoload.php";

use NoccyLabs\VirtFs\VirtFs;

$vfs_data = new VirtFs("appdata");
$vfs_data
    ->addDirectory(__DIR__."/data/","/",true)
    ->addArchive(__DIR__."/data.zip","/")
    ;

// Reading from actual ./data dir    
$fh = fopen("appdata://foo.txt", "r");
echo fread($fh, 1024);
fclose($fh);

// This file is inside of data.zip
$f3 = fopen("appdata://baz.txt", "r");
echo fread($f3, 1024);
fclose($f3);

// Writing to resource marked as writable
$f2 = fopen("appdata://bar.txt", "w");
fwrite($f2, "herro!\n");
fclose($f2);

