<?php

/*
 * Copyright (C) 2014, NoccyLabs
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace NoccyLabs\VirtFs;

class VirtFsLoader
{
    protected $registered;
    
    protected $virt_fs;
    
    protected $load_ns;
    
    protected $load_psr4;
    
    protected $load_path;

    public function __construct(VirtFs $virt_fs, $path = null)
    {
        $this->virt_fs = $virt_fs;
        $this->load_path = $path;
    }
    
    /**
     * Register autoloader for specified namespace. If $psr4 is true, the
     * namespace will be stripped off when matching files.
     *
     * @param string The namespace to register
     * @param bool Register a PSR-4 instead of PSR-0 autoloader
     */
    public function register($namespace=null, $psr4=false)
    {
        $this->load_ns = $namespace;
        $this->load_psr4 = $psr4;
        spl_autoload_register(array($this,"spl_autoload_callback"));
        $this->registered = true;
    }
    
    public function unregister()
    {
        spl_autoload_unregister(array($this,"spl_autoload_callback"));
        $this->registered = false;
    }
    
    public function spl_autoload_callback($class)
    {
        if (strncmp($class, $this->load_ns, strlen($this->load_ns)) === 0) {
            if ($this->load_psr4) {
                $class = substr($class, strlen($this->load_ns));
            }
            if ($this->load_path) {
                $this->load_path = trim($this->load_path,"/")."/";
            }
            //printf("load_path=%s\n", $this->load_path);
            $classfile = $this->load_path.strtr($class,"\\","/").".php";
            
            //printf("class=%s classfile=%s\n", $class, $classfile);
            if ($this->virt_fs->has($classfile)) {
                $file = $this->virt_fs->getPath($classfile);
                require_once $file;
            }
        }
    }
}
