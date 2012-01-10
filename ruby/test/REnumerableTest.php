<?php 
require_once '../lib/REnumerable.php';

class REnumerableTest extends PHPUnit_Framework_TestCase{
		
	public function testAll__(){
		$e_false = new REnumerable(array(null, true, 99));
		$e_true = new REnumerable(array(true, 99));
		
		
		$this->assertFalse($e_false->all__());
		$this->assertTrue($e_true->all__());
		
	}
	
	public function testAll__WithBlock(){
		$e = new REnumerable(array('ant', 'bear', 'cat'));
		
		$false_result = $e->all__(function($entry){
			return strlen($entry) >= 4;
		});
		$true_result = $e->all__(function($entry){
			return strlen($entry) >= 3;
		});
		
		$this->assertFalse($false_result);
		$this->assertTrue($true_result);
		
	}
	
	
	public function testAny__(){
		$e_false = new REnumerable(array(null, false));
		$e_true = new REnumerable(array(false, 1));
		
		
		$this->assertFalse($e_false->any__());
		$this->assertTrue($e_true->any__());
	}
	
	public function testAny__WithBlock(){
		$e = new REnumerable(array('ant', 'bear', 'cat'));
		
		$false_result = $e->any__(function($entry){
			return strlen($entry) >= 5;
		});
		$true_result = $e->any__(function($entry){
			return strlen($entry) >= 3;
		});
		
		$this->assertFalse($false_result);
		$this->assertTrue($true_result);
		
	}
	
	
	public function testChunk(){
		$e = new REnumerable(array(3,1,4,1,5,9,2,6,5,3,5));
		
		$chunks = $e->chunk(function($entry){
			return $entry % 2;
		});
		
		$this->assertCount(5, $chunks);
	}
	
	
	public function testCollect(){
		$e = new REnumerable(array(1,2,3,4));
		
		$result = $e->collect();
		$this->assertEquals($e, $result);
	}
	
	public function testCollectWithBlock(){
		$e = new REnumerable(array(1,2,3,4));
		
		$result = $e->collect(function($entry){
			return $entry * $entry;
		});
		
		$this->assertEquals(array(1, 4, 9, 16), $result);
	}

	public function testCollect_concat(){
		$e = new REnumerable(array(array(1,2),array(3,4)));
		
		$result = $e->collect_concat();
		
		$this->assertEquals(new REnumerator(array(1,2,3,4)), $result);
	}
	
	public function testCollect_concatWithBlock(){
		$e = new REnumerable(array(array(1,2),array(3,4)));
		
		$result = $e->collect_concat(function($entry){
			return $entry;
		});
		
		$this->assertEquals(array(1, 2, 3, 4), $result);
	}
	
	/**
	 * @depends testCollect_concat
	 */
	public function testCount(){
		$e = new REnumerable(array(array(1,2),array(3,4)));
		
		$this->assertSame(2, $e->count());
		
		$this->assertSame(4, $e->collect_concat()->count());
	}
	
	public function testCycle(){
		$e = new REnumerable(array(1,2,3,4));
		$result = $e->cycle(2, function($entry){
			return $entry;
		});
		
		$this->assertEquals(array(1,2,3,4,1,2,3,4), $result);
	}
	
	public function testDetect(){
		$e = new REnumerable('1..10');
		$ifnone = null;
		$false_result = $e->detect(function(){
			return 'ifnone';
		}, function($i){
			return  $i % 5 == 0 && $i % 7 == 0;
		});
		
		$e = new REnumerable('1..100');
		$true_result = $e->detect(null, function($i){
			return  $i % 5 == 0 && $i % 7 == 0;
		});
		
		$this->assertEquals(35, $true_result);
		
		$this->assertEquals('ifnone', $false_result);
	}
	
	public function testDrop(){
		$e = new REnumerable(array(1, 2, 3, 4, 5, 0));
		$this->assertEquals(array(4, 5, 0), $e->drop(3));
	}
	
	/**
	 * @depends testDrop
	 */
	public function testDrop_while(){
		$e = new REnumerable(array(1, 2, 3, 4, 5, 0));
		$result = $e->drop_while(function($i){
			return $i < 3;
		});
		$this->assertEquals(array(3, 4, 5, 0), $result);
		
		$e = new REnumerable(array(1, 2, 3, 4, 5, 0));
		$result = $e->drop_while(function($i){
			return $i < 1;
		});
		$this->assertEquals(array(1, 2, 3, 4, 5, 0), $result);
		
	}
	
	
	public function testEach_cons(){
		$e = new REnumerable('0..10');
		
		$this->assertEquals(new REnumerator(array(
			array(0, 1, 2),
			array(1, 2, 3),
			array(2, 3, 4),
			array(3, 4, 5),
			array(4, 5, 6),
			array(5, 6, 7),
			array(6, 7, 8),
			array(7, 8, 9),
			array(8, 9, 10)
		)), $e->each_cons(3));
		unset($e);
	}
	
	public function testEach_consWithBlock(){
		$e = new REnumerable('0..10');
		$result = array();
		$e->each_cons(4, function($entry) use (&$result){
			array_push($result, $entry);
		});
		
		$this->assertEquals(array(
			array(0, 1, 2, 3),
			array(1, 2, 3, 4),
			array(2, 3, 4, 5),
			array(3, 4, 5, 6),
			array(4, 5, 6, 7),
			array(5, 6, 7, 8),
			array(6, 7, 8, 9),
			array(7, 8, 9, 10)
		), $result);
		unset($e);
	}
	
	public function testEach_entry(){
		
	}
	
	public function testEach_slice(){
		$e = new REnumerable('1..10');
		$this->assertEquals(new REnumerator(array(
			array(1, 2, 3),
			array(4, 5, 6),
			array(7, 8, 9),
			array(10)
		)), $e->each_slice(3));
		unset($e);
	}
	
	public function testEach_sliceWithBlock(){
		$e = new REnumerable('1..10');
		$result = array();
		$e->each_slice(4, function($entry) use (&$result){
			array_push($result, $entry);
		});
		
		$this->assertEquals(array(
			array(1, 2, 3, 4),
			array(5, 6, 7, 8),
			array(9, 10)
		), $result);
		unset($e);
	}
	
	
	public function testEach_with_index(){
		$e = new REnumerable('cat dog wombat');
		$result = $e->each_with_index();
		$this->assertEquals(new REnumerator(array(
			'cat' => 0,
			'dog' => 1, 
			'wombat' => 2
		)), $result);
		unset($e);
	}
	
	public function testEach_with_indexWithBlock(){
		$e = new REnumerable('cat dog wombat');
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
		$e = new REnumerable('1..10');
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
		$e = new REnumerable('1..10');
		$result = $e->each_with_object(array('test'), function($entry, $object){
			array_push($object, $entry);
		});
		
		$this->assertEquals(array('test', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), $result);
	}
	
	public function testEntries(){
		$e = new REnumerable('cat dog wombat');
		$this->assertEquals(array('cat', 'dog', 'wombat'), $e->entries());
		unset($e);
	}
	
	public function testFind(){
		$e = new REnumerable('1..10');
		$ifnone = null;
		$false_result = $e->find(function(){
			return 'ifnone';
		}, function($i){
			return  $i % 5 == 0 && $i % 7 == 0;
		});
		
		$e = new REnumerable('1..100');
		$true_result = $e->find(null, function($i){
			return  $i % 5 == 0 && $i % 7 == 0;
		});
		
		$this->assertEquals(35, $true_result);
		
		$this->assertEquals('ifnone', $false_result);
		unset($e);
	}
	
	public function testFind_all(){
		$e = new REnumerable('1..10');
		$result = $e->find_all(function($i){
			return $i % 3 == 0;
		});
		$this->assertEquals(array(3, 6, 9), $result);
		
		$result = $e->find_all(function($i){
			return $i > 10;
		});
		$this->assertEquals(array(), $result);
		
		$result = $e->find_all();
		$this->assertInstanceOf('REnumerator', $result);
		unset($e);
	}
	
	public function testFind_index(){
		$e = new REnumerable('1..100');
		$this->assertEquals(49, $e->find_index(50));
		
		$se = new REnumerable('1..10');
		$result = $se->find_index(function($i){ return $i % 5 == 0 && $i % 7 == 0; });
		$this->assertEquals(null, $result);  #=> nil
		
		$result = $e->find_index(function($i){ return $i % 5 == 0 && $i % 7 == 0; });
		$this->assertEquals(34, $result);   #=> 34
		
	}
	
	public function testFirst(){
		$e = new REnumerable('foo bar baz');
		
		$this->assertEquals('foo', $e->first());
		
		$this->assertEquals(array('foo', 'bar'), $e->first(2));
		
		$e = new REnumerable(array());
		
		$this->assertEquals(null, $e->first());
		
		
	}
	
	public function testFlat_map(){
		$e = new REnumerable(array(array(1,2),array(3,4)));
		
		$result = $e->flat_map();
		
		$this->assertEquals(new REnumerator(array(1,2,3,4)), $result);
	}
	
	public function testFlat_mapWithBlock(){
		$e = new REnumerable(array(array(1,2),array(3,4)));
		
		$result = $e->flat_map(function($entry){
			return $entry;
		});
		
		$this->assertEquals(array(1, 2, 3, 4), $result);
	}
	
	public function testGrep(){
	}
	
	public function testGroup_by(){
		$e = new REnumerable('1..6');
		
		$this->assertInstanceOf('REnumerator', $e->group_by());
		
		$result = $e->group_by(function($i){
			return $i % 3;
		});
		
		$this->assertEquals(new RHash(array(
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
		$e = new REnumerable(array(1,2,3,4,5,6,7,8));
		
		$this->assertEquals(array(1,2), $e->take(2));
	}
	public function testTake_while(){
		
	}
	public function testTo_a(){
		
	}
	public function testZip(){
		
	}
	
}
