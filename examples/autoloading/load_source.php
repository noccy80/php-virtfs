<?php

require_once __DIR__."/../../vendor/autoload.php";

use NoccyLabs\VirtFs\VirtFs;
use NoccyLabs\VirtFs\VirtFsLoader;

$vfs_plugins = new VirtFs("plugins");
$vfs_plugins->addDirectory(__DIR__."/plugins.src");

function load_plugin_src($plugin_name, VirtFs $vfs)
{
    
    // Now  we can query the plugin.json file via the
    // plugins:// wrapper. So, we read the json and find out what to load
    // and where.
    $file = "plugins://{$plugin_name}/plugin.json";
    $json = file_get_contents($file);
    $info = (object)json_decode($json);
    
    // When creating the loader, we pass the VirtFs and the mountpoint to
    // operate upon, in this case the plugin name we created previous.
    $vfs->addAutoloader($info->ns, $plugin_name, true);
    
    // Now we can assemble the class name and create an instance of the actual
    // plugin.
    $plugin_class = $info->ns.$info->name;
    $plugin = new $plugin_class();
    $plugin->load("plugins://{$plugin_name}");
}

// Grab all the .zip files and load them
foreach(glob("plugins.src/*") as $plugin_src) {
    load_plugin_src(basename($plugin_src),$vfs_plugins);
}

