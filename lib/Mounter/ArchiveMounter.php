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

class ArchiveMounter implements MounterInterface
{
    protected $archive;
    
    protected $zip;
    
    protected $mountpoint;
    
    protected $priority;
    
    protected $writable = false;

    public function __construct($path, $mountpoint)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("{$path} passed to ArchiveMounter does not exist");
        }
        $this->archive = $path;
        $this->zip = new \ZipArchive();
        $this->zip->open($path);
        $this->mountpoint = "/".trim($mountpoint,"/");
    }

    public function has($file)
    {
        //printf("node has? file=%s zip=%s mount=%s\n", $file, $this->archive, $this->mountpoint);
        $stat = $this->zip->statName(trim($file,"/"));
        //printf("ret=%d\n", $stat);
        return (!!$stat);
    }

    public function getPath($file)
    {
        $local = "zip://".$this->archive.'#'.ltrim($file,"/");
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
    }
    
    public function getIsWritable()
    {
        return false;
    }
    
    public function glob($pattern)
    {
        foreach($this->zip as $file=>$info) {
            var_dump($file);
            var_dump($info);
            echo "----\n";
        }
    }

    public function getDirectoryListing($path='/')
    {
        // get pretty mountpoint and find resulting path
        $mp = rtrim($this->getMountPoint(), DIRECTORY_SEPARATOR);
        $rp = rtrim(substr($path, strlen($mp)), DIRECTORY_SEPARATOR);
        $gp = $rp."/*";
 
        $gp = ltrim($gp,"/");
        $glob = array();
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $filename = $this->zip->getNameIndex($i);
            if (fnmatch($gp, $filename)) {
                $glob[] = basename($filename);
            }
        }
 
        return $glob;
    }
        
}

