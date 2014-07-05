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
        $this->mountpoint = $mountpoint;
    }

    public function has($file)
    {
        $stat = $this->zip->statName($file);
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

}
