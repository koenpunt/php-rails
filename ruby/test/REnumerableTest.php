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
		
		$this->assertEquals(new REnumerable(array(1,2,3,4)), $result);
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
		
	}
	public function testDrop(){
		
	}
	public function testDrop_while(){
		
	}
	public function testEach_cons(){
		
	}
	public function testEach_entry(){
		
	}
	public function testEach_slice(){
		
	}
	public function testEach_with_index(){
		
	}
	public function testEach_with_object(){
		
	}
	public function testEntries(){
		
	}
	public function testFind(){
		
	}
	public function testFind_all(){
		
	}
	public function testFind_index(){
		
	}
	public function testFirst(){
		
	}
	public function testFlat_map(){
		
	}
	public function testGrep(){
		
	}
	public function testGroup_by(){
		
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
		
	}
	public function testTake_while(){
		
	}
	public function testTo_a(){
		
	}
	public function testZip(){
		
	}
	
}
