<?php

namespace NoccyLabs\VirtFs;

class VirtFsTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
        $this->vfs = new VirtFs();
    }
    
    public function teardown()
    {
        $this->vfs = null;
    }
    
    public function testRegisterProtocol()
    {
        $this->vfs->registerStreamWrapper("vfstest");
        $wrappers = stream_get_wrappers();
        $this->assertContains("vfstest", $wrappers);

        $wrappers = stream_get_wrappers();
        $this->vfs->unregisterStreamWrapper();
        $this->assertContains("vfstest", $wrappers);
    }
    
    public function testWorkingWithDirectories()
    {
        $this->vfs->addDirectory( __DIR__."/../static" );
        $this->assertTrue( $this->vfs->has("static-file.txt") );
        $this->assertNotTrue( $this->vfs->has("non-existing-file.txt") );
    }

    public function testWorkingWithArchives()
    {
        $this->vfs->addArchive( __DIR__."/../static/archive.zip" );
        $this->assertTrue( $this->vfs->has("archived-file.txt") );
        $this->assertNotTrue( $this->vfs->has("non-existing-file.txt") );
    }
    
    public function testReadingFromWrapper()
    {
        $this->vfs->registerStreamWrapper("vfstest");
        
        $this->vfs->addDirectory( __DIR__."/../static" );
        $this->vfs->addArchive( __DIR__."/../static/archive.zip" );

        $static_expect = "static-file\n";
        $static_read = file_get_contents("vfstest://static-file.txt");
        
        $archive_expect = "archived-file\n";
        $archive_read = file_get_contents("vfstest://archived-file.txt");
        
        $this->assertEquals($static_expect, $static_read);
        $this->assertEquals($archive_expect, $archive_read);
    }
    
    public function testWritingToWrapper()
    {
        $this->vfs->registerStreamWrapper("vfstest");
        // writable
        $this->vfs->addDirectory( __DIR__."/../static", '/', true );
        
        if (file_exists( __DIR__."/../static/new-file.txt")) {
            unlink( __DIR__."/../static/new-file.txt");
        }
        $archive_expect = "new-file\n";
        file_put_contents("vfstest://new-file.txt", $archive_expect);
        
        $archive_read = file_get_contents("vfstest://new-file.txt");
        $this->assertEquals($archive_expect, $archive_read);
        
    }
    
    public function testAutoloading()
    {
        $this->vfs->registerStreamWrapper("vfstest");
        
        $this->vfs->addDirectory( __DIR__."/../static" );
        $this->vfs->addArchive( __DIR__."/../static/archive.zip" );
        $this->vfs->addArchive( __DIR__."/../static/classes.zip" );
        
        $this->vfs->addAutoloader("TestNamespace\\", '/', true);
        $this->vfs->addAutoloader("Test2Namespace\\", '/', false);

        $class1s = new \TestNamespace\TestClass;
        $class2s = new \Test2Namespace\Test2Class;

        $class1d = new \TestNamespace\TestClassDynamic;
        $class2d = new \Test2Namespace\Test2ClassDynamic;

    }
    
    public function testGetDirectoryListing()
    {
        $this->vfs->addDirectory( __DIR__."/../static" );
        $this->vfs->addArchive( __DIR__."/../static/archive.zip" );
        $this->vfs->addArchive( __DIR__."/../static/classes.zip" );
        
        $items = $this->vfs->getDirectoryListing();
        $this->assertContains( "archive.zip", $items);
        $this->assertContains( "static-file.txt", $items);
        $this->assertContains( "Test2Namespace/", $items);        

        $items = $this->vfs->getDirectoryListing("/Test2Namespace");
        $this->assertContains( "Test2Class.php", $items );
        $this->assertContains( "Test2ClassDynamic.php", $items );
        
    }
}