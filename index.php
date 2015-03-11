<?php
	require('key-value-api.php');

//	$Servers = [
//		['127.0.0.1', 11211],
//		['localhost', 11211],
//		['127.0.0.1']
//	];

	//$Servers = [
	//	'127.0.0.1:11211', 'localhost:11211', '127.0.0.1'
	//];

	$Servers = '127.0.0.1:11211;localhost:11211;127.0.0.1';

	try
	{
		$KV = new kv(
			array('Enabled' => false),
			array('Enabled' => true, 'Servers' => $Servers)
		);
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
	echo '<hr />';
	kv::set('TestNumber', 42);
	echo $KV['TestNumber'];
	echo '<hr />';
	kv::inc('TestNumber', 2);
	echo $KV['TestNumber'];
?>