<?php

namespace I18n;

function array_flatten(array $array)
{
	$i = 0;
	$n = count($array);

	while ($i < $n) {
		if (is_array($array[$i])) {
			array_splice($array,$i,1,$array[$i]);
        } else {
			++$i;
		}
		$n = count($array);
    }
    return $array;
}

function var_dump_to_string($var)
{
	ob_start();
	print_r($var);
	$result = ob_get_clean();
	return $result;
}

?>