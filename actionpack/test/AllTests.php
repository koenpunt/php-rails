<?php
error_reporting(E_ALL ^E_NOTICE);
set_include_path(
	get_include_path() . PATH_SEPARATOR .
	realpath( '../../vendor' ) . PATH_SEPARATOR .
	realpath( '../../actionpack/lib' ) . PATH_SEPARATOR . 
	realpath( '../../activesupport/lib' ) . PATH_SEPARATOR . 
	realpath( '../../' ) 
);


$test_files = glob('*Test.php');
$test_files += glob('template/*Test.php');

foreach ($test_files as $file){
	require $file;
}


class AllTests
{
	public static function suite()
	{
		global $test_files;
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		foreach ($test_files as $file)
		{
			$file = pathinfo($file, PATHINFO_FILENAME);
			
			$suite->addTestSuite($file);
		}

		return $suite;
	}
}
