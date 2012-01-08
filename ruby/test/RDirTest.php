<?php
require_once '../lib/RDir.php';


class RDirTest extends PHPUnit_Framework_TestCase{
	
	protected 
		$_dir = null,
		$_dirname = null;
	
	protected static 
		$_tmpdir = '/tmp/phpunit/RDirTest';
	
	public function _getDirname($dirname){
		return self::$_tmpdir . '/' . $dirname;
	}
	
	public static function setUpBeforeClass(){
		mkdir(self::$_tmpdir, 0777, true);
	}

	public static function tearDownAfterClass(){
		exec('rm -rf ' . self::$_tmpdir);
	}
	
	public function testMkdir(){
		$dirname = $this->_getDirname('test_mkdir');
		$dir = RDir::mkdir($dirname);
		$this->assertTrue($dir);
	}
	
	/**
	 * @depends testMkdir
	 */
	public function testOpen(){
		$dirname = $this->_getDirname('test_open');
		RDir::mkdir($dirname);
		$dir = RDir::open($dirname);
		
		$this->assertInstanceOf('RDir', $dir);
		
		$dir->close();
	}
	
	/**
	 * @depends testMkdir
	 */
	public function testOpenWithBlock(){
		$dirname = $this->_getDirname('test_open_with_block');
		RDir::mkdir($dirname);
		$dir = RDir::open($dirname, function($_dir){
			$_dir;
		});
	}
	
	
	/**
	 * @depends testOpen
	 */
	public function testClose(){
		$dirname = $this->_getDirname('test_close');
		RDir::mkdir($dirname);
		$dir = RDir::open($dirname);
		
		$this->assertNull($dir->close());
		
	}
	
	public function testChdir(){
		
	}
	
	public function testChroot(){
		
	}
	
	public function testEntries(){
		$entries = RDir::entries('/var');
		$this->assertInternalType('array', $entries);
	}
	
	public function testDirectory__(){
		$this->assertTrue(RDir::directory__('/tmp'), 'Existing directory tested as not existing');
		$this->assertFalse(RDir::directory__('/koen'), 'Not existing directory tested as existing');
	}
	
	public function testExists__(){
		$this->assertTrue(RDir::exists__('/tmp'), 'Existing directory tested as not existing');
		$this->assertFalse(RDir::exists__('/koen'), 'Not existing directory tested as existing');
	}
	
	/**
	 * @depends testOpen
	 */
	public function testFeach(){
		$dir = RDir::feach('/var');
		$this->assertInstanceOf('REnumerable', $dir);
	}
	
	/**
	 * @depends testOpen
	 */
	public function testFeachWithBlock(){
		$file = null;
		RDir::mkdir($this->_getDirname('test_feach'));
		RDir::mkdir($this->_getDirname('test_feach/check'));
		$dir = RDir::feach($this->_getDirname('test_feach'), function($filename) use (&$file){
			$file = $filename;
		});
		$this->assertEquals('check', $file);
		$this->assertNull($dir);
	}
	
	
	public function testGetwd(){
		$wd = RDir::getwd();
		$this->assertEquals('/var/apps/php-rails/ruby/test', $wd);
	}
	
	public function testGlob(){
	}
	
	public function testHome(){
		$home = RDir::home();
		$this->assertEquals('/', $home);
	}
	
	public function testPwd(){
		$pwd = RDir::pwd();
		$this->assertEquals('/var/apps/php-rails/ruby/test', $pwd);
	}

	/**
	 * @depends testMkdir
	 */
	public function testRmdir(){
		$dir = $this->_getDirname('test_rmdir');
		RDir::mkdir($dir);
		$this->assertTrue(RDir::delete($dir));
	}
	
	/**
	 * @depends testMkdir
	 */
	public function testUnlink(){
		$dir = $this->_getDirname('test_unlink');
		RDir::mkdir($dir);
		$this->assertTrue(RDir::delete($dir));
	}
	
	/**
	 * @depends testMkdir
	 */
	public function testDelete(){
		$dir = $this->_getDirname('test_delete');
		RDir::mkdir($dir);
		$this->assertTrue(RDir::delete($dir));
	}
	
	/**
	 * @depends testEntries
	 */
	public function testEach(){
		RDir::mkdir($this->_getDirname('test_each'));
		RDir::mkdir($this->_getDirname('test_each/file1'));
		RDir::mkdir($this->_getDirname('test_each/file2'));
		RDir::mkdir($this->_getDirname('test_each/file3'));
		
		$dir = new RDir($this->_getDirname('test_each'));
		$this->assertInstanceOf('REnumerable', $dir->each());
	}
	
	/**
	 * @depends testEntries
	 */
	public function testEachWithBlock(){
		RDir::mkdir($this->_getDirname('test_each_with_block'));
		RDir::mkdir($this->_getDirname('test_each_with_block/file1'));
		RDir::mkdir($this->_getDirname('test_each_with_block/file2'));
		RDir::mkdir($this->_getDirname('test_each_with_block/file3'));
		
		$dir = new RDir($this->_getDirname('test_each_with_block'));
		$files = array();
		$dir->each(function($file) use (&$files){
			array_push($files, $file);
		});
		
		$this->assertCount(5, $files);
	}
	
	
	
	public function testInspect(){
	}
	
	public function testPath(){
		RDir::mkdir($this->_getDirname('test_path'));
		$dir = new Dir($this->_getDirname('test_path'));
		$this->assertEquals($this->_getDirname('test_path'), $dir->path());
	}
	
	#public function testPos(){
	#	
	#}
	#
	#public function testPos(){ # = setter
	#	
	#}
	
	/**
	 * @depends testMkdir
	 */
	public function testRead(){
		RDir::mkdir($this->_getDirname('test_read'));
		RDir::mkdir($this->_getDirname('test_read/file'));
		
		$dir = new RDir($this->_getDirname('test_read'));
		
		$entries = array();
		for($i = 0;$i < 4;$i ++){
			array_push($entries, $dir->read());
		}
		$this->assertCount(4, $entries);
		$entries = array_filter($entries);
		$this->assertCount(3, $entries, 'NULL entry not removed from $entries?');
	}
	
	/**
	 * @depends testMkdir
	 */
	public function testRewind(){
		RDir::mkdir($this->_getDirname('test_rewind'));
		RDir::mkdir($this->_getDirname('test_rewind/file'));
		
		$dir = new RDir($this->_getDirname('test_rewind'));
		$first_entry = $dir->read();
		$dir->rewind();
		$this->assertSame($first_entry, $dir->read());
		
	}
	
	public function testSeek(){
		
	}
	
	public function testTell(){
	}
	
	public function testTo_path(){
		RDir::mkdir($this->_getDirname('test_to_path'));
		$dir = new Dir($this->_getDirname('test_to_path'));
		$this->assertEquals($this->_getDirname('test_to_path'), $dir->to_path());
	}
}