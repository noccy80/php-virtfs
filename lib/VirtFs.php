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

class VirtFs
{
    /** @var string The protocol bound (f.ex. 'data' for 'data://..') */
    protected $protocol;
    
    protected $handler;
    
    protected $nodes;
 
    protected $stream;
    
    protected static $handlers = array();
    
    protected $loaders = array();

    public function __construct($proto=null)
    {
        if ($proto) {
            $this->protocol = $proto;
            self::$handlers[$proto] = $this;
            $this->registerStreamWrapper();
        }
    }
    
    public function __destruct()
    {
        if ($this->protocol) {
            $this->unregisterStreamWrapper();
        }
    }
    
    public function addAutoloader($namespace, $path=null, $psr4=false)
    {
        $loader = new VirtFsLoader($this);
        $loader->register($namespace, $path, $psr4);
        $this->pushLoader($loader);
    }
    
    public function pushLoader(VirtFsLoader $loader)
    {
        if (!in_array($loader,$this->loaders)) {
            $this->loaders[] = $loader;
        }
        return $this;
    }
    
    public function registerStreamWrapper()
    {
        return stream_wrapper_register($this->protocol, __CLASS__, 0);
    }

    public function unregisterStreamWrapper()
    {
        return stream_wrapper_unregister($this->protocol);
    }

    public function addDirectory($path, $mountpoint='/', $writable=false, $priority=0) {
        $mounter = new Mounter\DirectoryMounter($path, $mountpoint);
        $mounter->setPriority($priority);
        $mounter->setIsWritable($writable);
        $this->nodes[] = $mounter;
        return $this;
    }
    
    public function addArchive($path, $mountpoint='/', $priority=0) {
        $mounter = new Mounter\ArchiveMounter($path, $mountpoint);
        $mounter->setPriority($priority);
        $this->nodes[] = $mounter;
        return $this;
    }

    public function has($file)
    {
        $file = "/".ltrim($file,"/");
        foreach($this->nodes as $node) {
            $mount = $node->getMountPoint();
            if (strncmp($mount, $file, strlen($mount)) === 0) {
                $nodefile = substr($file, strlen($mount));
                if ($node->has($nodefile)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getPath($file)
    {
        $file = "/".ltrim($file,"/");

        $best = null;
        foreach($this->nodes as $node) {
            $mount = $node->getMountPoint();
            if (strncmp($mount, $file, strlen($mount)) === 0) {
                $nodefile = substr($file, strlen($mount));
                //printf("nodefile=%s mount=%s file=%s\n", $nodefile, $mount, $file);
                if ($node->has($nodefile)) {
                    return $node->getPath($nodefile);
                }
                if (!$best) {
                    $best = $node->getPath($nodefile);
                }
                if ($node->getIsWritable($nodefile)) {
                    $best = $node->getPath($nodefile);
                }
            }
        }
        return $best;

    }

    public function isWritable($file)
    {
        $file = "/".ltrim($file,"/");
        foreach($this->nodes as $node) {
            $mount = $node->getMountPoint();
            if (strncmp($mount, $file, strlen($mount)) === 0) {
                if ($node->getIsWritable()) { return true; }
            }
        }
        return false;
    }
    
    public function glob($path)
    {
        $path = "/".ltrim($path,"/");
        $ret = array();
        foreach($this->nodes as $node) {
            $mount = $node->getMountPoint();
            if (fnmatch($path.'*', $mount) && (strlen($mount)>1)) {
                $ret[] = $mount;
            }
            if (strncmp($mount, $path, strlen($mount)) === 0) {
                $restpath = substr($path,strlen($mount));
                $glob = $node->glob($restpath);
                $ret = array_merge($ret, $glob);
            } 
        }
        return $ret;
    }
    
    public function url_stat($file)
    {
        list($proto,$path) = explode("://", $file);
        $handler = self::$handlers[$proto];
        $mapped_file = $handler->getPath($path);
        return @stat($mapped_file);
    }

    public function stream_stat()
    {
        return @stat($this->stream);
    }

    public function stream_open($file, $mode, $options, &$opened_path)
    {
        // extract protocol and path
        list($proto,$path) = explode("://", $file);
        // find the handler
        $handler = self::$handlers[$proto];
        // resolve the mapped file uri, so we can open it
        $mapped_file = $handler->getPath($path);
        // check so that we can really write to the file
        if ((!$handler->isWritable($path)) && (strpos($mode,"r")===false)) {
            error_log("Error: Resource {$file} is not writable");
            return false;
        }
        
        $this->stream = fopen($mapped_file, $mode);
        // If we got a resource, we succeeded
        if (is_resource($this->stream)) {
            $opened_file = $mapped_file;
            return true;
        }
        
        error_log("Error: Could not open {$file} with modes {$mode}");
        return false;
    }
    
    public function stream_close()
    {
        fclose($this->stream);
    }
    
    public function stream_read($count)
    {
        return fread($this->stream, $count);
    }
    
    public function stream_write($data)
    {
        return fwrite($this->stream, $data);
    }
    
    public function stream_tell()
    {
        return ftell($this->stream);
    }
    
    public function stream_eof()
    {
        return feof($this->stream);
    }

    public function stream_seek($offset, $whence)
    {
        return fseek($this->stream, $offset, $whence);
    }
}
