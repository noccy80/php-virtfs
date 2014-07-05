<?php

require_once __DIR__."/../../vendor/autoload.php";

use NoccyLabs\VirtFs\VirtFs;
use NoccyLabs\VirtFs\VirtFsLoader;

$vfs_plugins = new VirtFs("plugins");

function load_plugin($plugin_zip, VirtFs $vfs)
{
    // This is really just a name for the mountoint of this plugin zip. We
    // use it with VirtFs#addArchive() to mount the plugin zip in its own
    // directory.
    $plugin_name = basename($plugin_zip,".zip");
    $vfs->addArchive($plugin_zip, $plugin_name);
    
    // Now that that is done, we can query the plugin.json file via the
    // plugins:// wrapper. So, we read the json and find out what to load
    // and where.
    $file = "plugins://{$plugin_name}/plugin.json";
    $json = file_get_contents($file);
    $info = (object)json_decode($json);
    
    // When creating the loader, we pass the VirtFs and the mountpoint to
    // operate upon, in this case the plugin name we created previous.
    $loader = new VirtFsLoader($vfs, $plugin_name);
    $loader->register($info->ns,true);
    
    // Now we can assemble the class name and create an instance of the actual
    // plugin.
    $plugin_class = $info->ns.$info->name;
    $plugin = new $plugin_class();
    print_r($plugin);
}

// Grab all the .zip files and load them
foreach(glob("plugins/*.zip") as $plugin_zip) {
    load_plugin($plugin_zip,$vfs_plugins);
}

