<?php

require(realpath(dirname(__FILE__)).'/../public_html/includes/functions.php');

$tests=array(
// array(format, test val, test good result)
array('%{toto:2}', 'abcdef', 'ab'),
array('%{toto:3:-2}', 'abcdef', 'bc'),
array('%{toto:1:0}', 'abcdef', 'bcdef'),
array('%{toto:-2}', 'abcdef', 'ef'),
array('%{toto:-3:2}', 'abcdef', 'de'),
array('%{toto:-1}', 'abcdef', 'f'),
array('%{toto!}', '<a>tiTé', '<A>TITÉ'),
array('%{toto_}', '<a>tiTé', '<a>tité'),
array('%{toto~}', '<a>tiTé', '<a>tiTe'),
array('%{toto%}', '<a>tiTé', '&lt;a&gt;tiT&eacute;'),
array('%{toto!%}', '<a>tiTé', '&lt;A&gt;TIT&Eacute;'),
array('%{toto!~}', '<a>tiTé', '<A>TITE'),
array('%{toto!~%}', '<a>tiTé', '&lt;A&gt;TITE'),
array('%{toto:1!%}', '<a>tiTé', '&lt;'),
array('%{toto:1:0!~}', '<a>tiTé', 'A>TITE'),
array('%{toto:-3!~%}', '<a>tiTé', 'ITE'),
array('%{toto:-3:2!~%}', '<a>tiTé', 'IT'),
);

foreach ($tests as $test) {
	$result = getFData($test[0], $test[1]);
	$ok = (($result == $test[2])?'OK':"\n\t!!!! NOK !!!!");
	echo "Test : \"$test[0]\" ($test[2]) : \"$test[1]\" -> \"$result\" => $ok\n";
}
