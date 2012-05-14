<?php 

require_once 'active_support/core_ext/RArray.php';
require_once 'active_support/core_ext/Enumerable.php';

use ActiveSupport\CoreExt\Enumerable; 
use ActiveSupport\CoreExt\RArray; 

class EnumerableTest extends PHPUnit_Framework_TestCase{
		
	
	public function testAny__(){
		$e_false = new Enumerable(array(null, false));
		$e_true = new Enumerable(array(false, 1));
		
		
		$this->assertFalse($e_false->any__());
		$this->assertTrue($e_true->any__());
	}
	
	public function testAny__WithBlock(){
		$e = new Enumerable(array('ant', 'bear', 'cat'));
		
		$false_result = $e->any__(function($entry){
			return strlen($entry) >= 5;
		});
		$true_result = $e->any__(function($entry){
			return strlen($entry) >= 3;
		});
		
		$this->assertFalse($false_result);
		$this->assertTrue($true_result);
		
	}
	public function testCount(){
		$e = new Enumerable(array(array(1,2),array(3,4)));
		
		$this->assertSame(2, $e->count());
	}
	
	
	public function testEach_with_index(){
		$e = new Enumerable('cat dog wombat');
		$result = $e->each_with_index();
		$this->assertEquals(new Enumerator(array(
			'cat' => 0,
			'dog' => 1, 
			'wombat' => 2
		)), $result);
		unset($e);
	}
	
	public function testEach_with_indexWithBlock(){
		$e = new Enumerable('cat dog wombat');
		$hash = array();
		$e->each_with_index(function($item, $index) use (&$hash){
			$hash[$item] = $index;
		});
		$this->assertEquals(array(
			'cat' => 0,
			'dog' => 1, 
			'wombat' => 2
		), $hash);
		unset($e);
	}
	
	public function testEach_with_object(){
		$e = new Enumerable('1..10');
		$result = $e->each_with_object(array());
		
		$this->assertEquals(new REnumerator(array(
			array(1,array()),
			array(2,array()),
			array(3,array()),
			array(4,array()),
			array(5,array()),
			array(6,array()),
			array(7,array()),
			array(8,array()),
			array(9,array()),
			array(10,array())
		)), $result);
	}
	
	public function testEach_with_objectWithBlock(){
		$e = new Enumerable('1..10');
		$result = $e->each_with_object(array('test'), function($entry, $object){
			array_push($object, $entry);
		});
		
		$this->assertEquals(array('test', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), $result);
	}
	
	public function testEntries(){
		$e = new Enumerable('cat dog wombat');
		$this->assertEquals(array('cat', 'dog', 'wombat'), $e->entries());
		unset($e);
	}
	
	public function testFind_index(){
		$e = new Enumerable('1..100');
		$this->assertEquals(49, $e->find_index(50));
		
		$se = new Enumerable('1..10');
		$result = $se->find_index(function($i){ return $i % 5 == 0 && $i % 7 == 0; });
		$this->assertEquals(null, $result);  #=> nil
		
		$result = $e->find_index(function($i){ return $i % 5 == 0 && $i % 7 == 0; });
		$this->assertEquals(34, $result);   #=> 34
		
	}
	
	public function testFirst(){
		$e = new Enumerable('foo bar baz');
		
		$this->assertEquals('foo', $e->first());
		
		$this->assertEquals(array('foo', 'bar'), $e->first(2));
		
		$e = new Enumerable(array());
		
		$this->assertEquals(null, $e->first());
		
		
	}
	
	public function testFlat_map(){
		$e = new Enumerable(array(array(1,2),array(3,4)));
		
		$result = $e->flat_map();
		
		$this->assertEquals(new REnumerator(array(1,2,3,4)), $result);
	}
	
	public function testFlat_mapWithBlock(){
		$e = new Enumerable(array(array(1,2),array(3,4)));
		
		$result = $e->flat_map(function($entry){
			return $entry;
		});
		
		$this->assertEquals(array(1, 2, 3, 4), $result);
	}
	
	public function testGrep(){
	}
	
	public function testGroup_by(){
		$e = new Enumerable('1..6');
		
		$this->assertInstanceOf('REnumerator', $e->group_by());
		
		$result = $e->group_by(function($i){
			return $i % 3;
		});
		
		$this->assertEquals(new Hash(array(
			0 => array(3, 6),
			1 => array(1, 4),
			2 => array(2, 5)
		)), $result);
	}
	
	public function testInclude__(){
		
	}
	
	public function testInject(){
		
	}
	public function testMap(){
		
	}
	public function testMax(){
		
	}
	public function testMax_by(){
		
	}
	public function testMember__(){
		
	}
	public function testMin(){
		
	}
	public function testMin_by(){
		
	}
	public function testMinmax(){
		
	}
	public function testMinmax_by(){
		
	}
	public function testNone__(){
		
	}
	public function testOne__(){
		
	}
	public function testPartition(){
		
	}
	public function testReduce(){
		
	}
	public function testReject(){
		
	}
	public function testReverse_each(){
		
	}
	public function testSelect(){
		
	}
	public function testSlice_before(){
		
	}
	public function testSort(){
		
	}
	public function testSort_by(){
		
	}
	public function testTake(){
		$e = new Enumerable(array(1,2,3,4,5,6,7,8));
		
		$this->assertEquals(array(1,2), $e->take(2));
	}
	public function testTake_while(){
		
	}
	public function testTo_a(){
		
	}
	public function testZip(){
		
	}
	
}
