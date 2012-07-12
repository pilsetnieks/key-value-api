<?php
/**
 * Key-value cache class, non-permanent. Works with APC for now, optionally should be extended to memcache.
 * Can be accessed as static class methods or instantiated to an object and accessed as an array.
 * Static access gives more possibilities, of course.
 *
 * @package key-value-api
 * @version 0.1
 * @author Martins Pilsetnieks
 */
	class kv implements ArrayAccess
	{
		/**
		 * Does nothing for now (with APC), maybe will do something with memcache
		 */
		public function __construct()
		{
			if (!ini_get('apc.enabled') || !function_exists('apc_store'))
			{
				throw new Exception('APC not available');
			}
		}

		/**
		 * Retrieves a value from the cache
		 *
		 * @param string Key
		 *
		 * @return mixed Value or boolean false, if unsuccessful
		 */
		public static function get($Key)
		{
			return apc_fetch($Key);
		}

		/**
		 * Stores a value in the cache
		 *
		 * @param string Key
		 * @param mixed Value
		 * @param int Time to live (seconds). 0 = unlimited
		 *
		 * @return boolean Operation status
		 */
		public static function set($Key, $Value, $TTL = 0)
		{
			return apc_store($Key, $Value, $TTL);
		}

		/**
		 * Retrieves a value if it exists and calls and gets a new one if it doesn't
		 *
		 * @param string Key
		 * @param callback Function or method to call for value. If you need to add any parameters to the function/method call,
		 *	just wrap it in an anonymous function
		 *
		 * @return mixed Value
		 */
		public static function wrap($Key, $Callback = null)
		{
			$Value = self::get($Key);
			if ($Value === false && is_callable($Callback))
			{
				$Value = call_user_func($Callback);
				self::set($Key, $Value);
			}
			return $Value;
		}

		/**
		 * Increments a numeric value
		 *
		 * @param string Key
		 * @param int Amount to increment by, defaults to 1
		 *
		 * @return boolean Operation status
		 */
		public static function inc($Key, $Value = 1)
		{
			return apc_inc($Key, (int)$Value);
		}

		/**
		 * Decrements a numeric value
		 *
		 * @param string Key
		 * @param int Amount to decrement by, defaults to 1
		 *
		 * @return boolean Operation status
		 */
		public static function dec($Key, $Value = 1)
		{
			return apc_dec($Key, (int)$Value);
		}

		/**
		 * Clears a specific key
		 *
		 * @param string Key
		 */
		public static function clear($Key)
		{
			return apc_delete($Key);
		}

		/**
		 * Clears everything
		 */
		public static function clear_all()
		{
			return apc_clear_cache('user');
		}

		// !ArrayAccess methods
		/**
		 * Checks if a value with the given key exists
		 *
		 * @param string Key
		 *
		 * @return boolean Exists or not
		 */
		public function offsetExists($Offset)
		{
			return apc_exists($Offset);
		}

		/**
		 * Retrieves a value, see KV::Get
		 *
		 * @param string Key
		 *
		 * @param mixed Value
		 */
		public function offsetGet($Offset)
		{
			return self::get($Offset);
		}

		/**
		 * Sets a value, see KV::Set
		 *
		 * @param string Key
		 * @param mixed Value
		 *
		 * @param boolean Operation status
		 */
		public function offsetSet($Offset, $Value)
		{
			return self::set($Offset, $Value);
		}

		/**
		 * Clears a value, see KV::Clear
		 *
		 * @param string Key
		 *
		 * @return boolean Operation status
		 */
		public function offsetUnset($Offset)
		{
			return self::clear($Offset);
		}
	}
?>