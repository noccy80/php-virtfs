<?php

require_once __DIR__."/../../vendor/autoload.php";

use NoccyLabs\VirtFs\VirtFs;
use NoccyLabs\VirtFs\VirtFsLoader;

$vfs_plugins = new VirtFs("plugins");

// Bind a plugin .zip file to the virtual filesystem. The path is determined
// from the filename
function bind_plugin_zip($plugin_zip, VirtFs $vfs)
{
    // This is really just a name for the mountoint of this plugin zip. We
    // use it with VirtFs#addArchive() to mount the plugin zip in its own
    // directory.
    $plugin_name = basename($plugin_zip,".zip");
    $vfs->addArchive($plugin_zip, $plugin_name);
    
}

// Add a directory to the plugin vfs
function add_plugin_src($plugin_dir, VirtFs $vfs)
{
    $vfs->addDirectory($plugin_dir);
}

// Load a plugin from the vfs based on its name
function load_plugin($plugin_name, VirtFs $vfs)
{
    // Now that that is done, we can query the plugin.json file via the
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

function add_plugin_zips($path, VirtFs $vfs)
{
    foreach(glob($path."*.zip") as $plugin_zip) {
        bind_plugin_zip($plugin_zip, $vfs);
    }
}

// Grab all the .zip files and load them
add_plugin_zips(__DIR__."/plugins/", $vfs_plugins);
// Then add the plugins directory
add_plugin_src(__DIR__."/plugins/", $vfs_plugins);

// activate plugins. note that glob doesn't glob yet, so leave out the *
$plugins = $vfs_plugins->glob("/");
foreach($plugins as $plugin_src) {
    if (!fnmatch("*.zip", $plugin_src)) {
        load_plugin(basename($plugin_src), $vfs_plugins);
    }
}

