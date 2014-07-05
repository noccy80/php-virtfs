VirtFs: Virtual Filesystems for PHP
===================================

VirtFs is to PHP what PhysFs is to C. It allows you to create a virtual
filesystem root, to then attach directories and archives to it, optionally
mounted in their own virtual path in the vfs. In addition to this, VirtFs
also registers a StreamWrapper to give you access to the VFS via a stream
prefix, such as "userdata://..".


    $vfs = new VirtFs("dirs");
    $vfs->add(new DirectoryMounter("./dir_a", "a"));
    $vfs->add(new DirectoryMounter("./dir_b", "b"));

What we have now is:

          /      The filesystem root
          |--a   The contents of ./dir_a
          '--b   The contents of ./dir_b

In the above example, these would be valid:

    dirs://a/hello.txt      -  ./dir_a/hello.txt
    dirs://b/foo            -  ./dir_b/foo


## Plugin behavior

To load plugins from individual .zip-archives, or directly from one or more
plugin directories, see [examples/autoloading/](examples/autoloading/). These
examples also demonstrates the autoloader


### Autoloader
    
To be able to autoload classes from a VirtFs filesystem, use the `VirtFsLoader`
class:

    use NoccyLabs\VirtFs\VirtFsLoader;
    
    $loader = new VirtFsLoader($virtfs);
    $loader->register
