<?php
	require('key-value-api.php');

	try
	{
		$KV = new kv;
	}
	catch (Exception $E)
	{
		echo $E -> getMessage();
	}

	// Object style
	$KV['Value1'] = 'asdf';
	echo $KV['Value1']; // Outputs "asdf"
	echo '<hr />';

	// Static class style
	echo kv::get('Value1'); // Outputs "asdf";
	echo '<hr />';
	kv::set('Value1', 'qwerty');
	echo kv::get('Value1'); // Outputs "qwerty";
	echo '<hr />';
	kv::clear_all();
	echo kv::get('Value1'); // Outputs "";
?>