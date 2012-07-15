<?php
/**
 * Key-value cache class, non-permanent. Works with APC for now, optionally should be extended to memcache.
 * Can be accessed as static class methods or instantiated to an object and accessed as an array.
 * Static access gives more possibilities, of course.
 *
 * @package key-value-api
 * @uses pecl/memcached
 * @uses pecl/apc
 * @version 0.1
 * @author Martins Pilsetnieks
 */
	class kv implements ArrayAccess
	{
		private static $DefaultAPCOptions = array(
			'Enabled' => true
		);
		private static $DefaultMemcacheOptions = array(
			'Enabled' => true,
			'Servers' => array(
				array('localhost', 11211)
			)
		);

		private static $APCOptions = array();
		private static $MemcacheOptions = array();

		private static $APCOn = false;
		private static $MemcacheOn = false;

		private static $Memcache = null;

		/**
		 * Does nothing for now (with APC), maybe will do something with memcache
		 */
		public function __construct(array $APCOptions = null, array $MemcacheOptions = null)
		{
			if ($APCOptions && !empty($APCOptions['Enabled']) && !ini_get('apc.enabled') || !function_exists('apc_store'))
			{
				throw new Exception('APC not available');
			}
			elseif ($APCOptions)
			{
				self::$APCOptions = array_merge(self::$DefaultAPCOptions, $APCOptions);
				self::$APCOn = !empty(self::$APCOptions['Enabled']);
			}

			if ($MemcacheOptions && !empty($MemcacheOptions['Enabled']) && !class_exists('Memcached'))
			{
				throw new Excption('Memcache not available');
			}
			elseif ($MemcacheOptions)
			{
				self::$MemcacheOptions = array_merge(self::$DefaultMemcacheOptions, $MemcacheOptions);
				self::$MemcacheOn = !empty(self::$MemcacheOptions['Enabled']);

				self::$Memcache = new Memcached;
				self::$Memcache -> addServers(self::$MemcacheOptions['Servers']);
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
			if (self::$APCOn)
			{
				return apc_fetch($Key);
			}
			if (self::$MemcacheOn)
			{
				return self::$Memcache -> get($Key);
			}
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
			$Status = true;
			if (self::$APCOn)
			{
				$Status = $Status && apc_store($Key, $Value, $TTL);
			}
			if (self::$MemcacheOn)
			{
				// If the TTL is longer than 30 days, memcache considers it to be a Unix timestamp instead of seconds to live
				if ($TTL > 2592000)
				{
					$TTL = time() + $TTL;
				}

				$Status = $Status && self::$Memcache -> set($Key, $Value, $TTL);
			}
			return $Status;
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
			if (self::$APCOn)
			{
				$Value = self::get($Key);
				if ($Value === false && is_callable($Callback))
				{
					$Value = call_user_func($Callback);
					self::set($Key, $Value);
				}
			}

			if (self::$MemcacheOn)
			{
				$Value = self::$Memcache -> get($Key, $Callback);
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
			if (self::$MemcacheOn)
			{
				$Result = self::$Memcache -> increment($Key, $Value);
			}
			if (self::$APCOn)
			{
				$Result = apc_inc($Key, (int)$Value);
			}
			return $Result;
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
			if (self::$MemcacheOn)
			{
				$Result = self::$Memcache -> decrement($Key, $Value);
			}
			if (self::$APCOn)
			{
				$Result = apc_dec($Key, (int)$Value);
			}
			return $Result;
		}

		/**
		 * Clears a specific key
		 *
		 * @param string Key
		 */
		public static function clear($Key)
		{
			if (self::$MemcacheOn)
			{
				$Status = self::$Memcache -> delete($Key);
			}
			if (self::$APCOn)
			{
				$Status = apc_delete($Key);
			}

			return $Status;
		}

		/**
		 * Clears everything
		 */
		public static function clear_all()
		{
			if (self::$APCOn)
			{
				$Status = apc_clear_cache('user');
			}
			if (self::$MemcacheOn)
			{
				$Status = self::$Memcache -> flush();
			}
			return $Status;
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
			if (self::$APCOn)
			{
				return apc_exists($Offset);
			}
			if (self::$MemcacheOn)
			{
				$Val = self::$Memcache -> get($Offset);
				return !(self::$Memcache -> getResultCode() == Memcached::RES_NOTFOUND);
			}
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