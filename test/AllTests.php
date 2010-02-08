<?php
require_once 'PHPUnit/Framework.php';

foreach (glob('*_test.php') as $file)
{
	require $file;
}

class AllTests
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit');

		foreach (glob('*_test.php') as $file)
		{
			$suite->addTestSuite(substr($file,0,-4));
		}

		return $suite;
	}
}
?>
