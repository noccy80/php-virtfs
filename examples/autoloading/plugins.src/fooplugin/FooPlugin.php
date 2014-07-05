<?php

namespace Plugins\FooPlugin;

class FooPlugin
{
    protected $root;

    public function load($root)
    {
        $this->root = $root;
        echo "This is ".__CLASS__." loading from ".__FILE__."\n";
        echo "Root is {$this->root}\n";
        $tattoo = trim(file_get_contents($this->root."/tattoo.txt"));
        echo "My tattoo says {$tattoo}\n";
    }
}
