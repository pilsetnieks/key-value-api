key-value-api
=============

Simple key-value cache API to abstract the underlying key-value store. Currently implements APC and memcache

Usage example
=============

    $KV = new kv(
    	array('Enabled' => false),
		array('Enabled' => true, 'Servers' => $Servers)
    );

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

Servers for memcache can be specified in multiple ways:

    $Servers = [
        ['localhost', 11211],
        ['127.0.0.1']
    ];

    $Servers = ['localhost:11211', '127.0.0.1'];

    $Servers = 'localhost11211;127.0.0.1';

    $KV = new kv(
    	array('Enabled' => false),
		array('Enabled' => true, 'Servers' => $Servers)
    );
