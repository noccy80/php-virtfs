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

namespace NoccyLabs\VirtFs\Mounter;

class DirectoryMounter implements MounterInterface
{
    protected $path;
    
    protected $mountpoint;
    
    protected $priority;
    
    protected $writable = false;

    public function __construct($path, $mountpoint)
    {
        $this->path = "/".trim($path,"/")."/";
        $this->mountpoint = "/".trim($mountpoint,"/");
    }

    public function has($file)
    {
        $local = $this->path.$file;
        return file_exists($local);
    }

    public function getPath($file)
    {
        $local = $this->path.$file;
        return $local;
    }
    
    public function getMountpoint()
    {
        return $this->mountpoint;
    }

    public function setPriority($priority)
    {
    }
    
    public function getPriority()
    {
    }

    public function setIsWritable($is_writable)
    {
        $this->writable = (bool)$is_writable;
    }
    
    public function getIsWritable()
    {
        return $this->writable;
    }
    
    public function glob($pattern)
    {
        return glob($this->path.$pattern.'*');
    }
    
    public function getDirectoryListing($path='/')
    {
        // get pretty mountpoint and find resulting path
        $mp = rtrim($this->getMountPoint(), DIRECTORY_SEPARATOR)?:DIRECTORY_SEPARATOR;
        $rp = rtrim($this->path.substr($path, strlen($mp)), DIRECTORY_SEPARATOR);
        $gp = $rp."/*";
        $glob = glob($gp);
        $globo = array();

        foreach($glob as $item) {
            if (is_dir($item)) { $item.='/'; }
            $globo[] = ltrim(str_replace($rp, "", $item),'/');
        }
        return $globo;
    }

}

