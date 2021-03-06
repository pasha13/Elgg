<?php

/**
 * Elgg Session Management
 *
 * Reserved keys: last_forward_from, msg, sticky_forms, user, guid, id, code, name, username
 * 
 * ArrayAccess was deprecated in Elgg 1.9. This means you should use 
 * $session->get('foo') rather than $session['foo'].
 * Warning: You can not access multidimensional arrays through ArrayAccess like
 * this $session['foo']['bar']
 *
 * @package    Elgg.Core
 * @subpackage Session
 * @see        elgg_get_session()
 */
class ElggSession implements ArrayAccess {

	/** @var Elgg_Http_SessionStorage */
	protected $storage;

	/**
	 * Constructor
	 *
	 * @param Elgg_Http_SessionStorage $storage The storage engine
	 * @access private Use elgg_get_session()
	 */
	public function __construct(Elgg_Http_SessionStorage $storage) {
		$this->storage = $storage;
	}

	/**
	 * Start the session
	 *
	 * @return boolean
	 * @throws RuntimeException If session fails to start.
	 * @since 1.9
	 */
	public function start() {
		$result = $this->storage->start();
		$this->generateSessionToken();
		return $result;
	}

	/**
	 * Migrates the session to a new session id while maintaining session attributes
	 *
	 * @param boolean $destroy Whether to delete the session or let gc handle clean up
	 * @return boolean
	 * @since 1.9
	 */
	public function migrate($destroy = false) {
		return $this->storage->regenerate($destroy);
	}

	/**
	 * Invalidates the session
	 *
	 * Deletes session data and session persistence. Starts a new session.
	 *
	 * @return boolean
	 * @since 1.9
	 */
	public function invalidate() {
		$this->storage->clear();
		$result = $this->migrate(true);
		$this->generateSessionToken();
		return $result;
	}

	/**
	 * Has the session been started
	 *
	 * @return boolean
	 * @since 1.9
	 */
	public function isStarted() {
		return $this->storage->isStarted();
	}

	/**
	 * Get the session ID
	 *
	 * @return string
	 * @since 1.9
	 */
	public function getId() {
		return $this->storage->getId();
	}

	/**
	 * Set the session ID
	 *
	 * @param string $id Session ID
	 * @return void
	 * @since 1.9
	 */
	public function setId($id) {
		$this->storage->setId($id);
	}

	/**
	 * Get the session name
	 *
	 * @return string
	 * @since 1.9
	 */
	public function getName() {
		return $this->storage->getName();
	}

	/**
	 * Set the session name
	 *
	 * @param string $name Session name
	 * @return void
	 * @since 1.9
	 */
	public function setName($name) {
		$this->storage->setName($name);
	}

	/**
	 * Get an attribute of the session
	 *
	 * @param string $name    Name of the attribute to get
	 * @param mixed  $default Value to return if attribute is not set (default is null)
	 * @return mixed
	 */
	public function get($name, $default = null) {
		return $this->storage->get($name, $default);
	}

	/**
	 * Set an attribute
	 *
	 * @param string $name  Name of the attribute to set
	 * @param mixed  $value Value to be set
	 * @return void
	 */
	public function set($name, $value) {
		$this->storage->set($name, $value);
	}

	/**
	 * Remove an attribute
	 *
	 * @param string $name The name of the attribute to remove
	 * @return mixed The removed attribute
	 * @since 1.9
	 */
	public function remove($name) {
		return $this->storage->remove($name);
	}

	/**
	 * Alias to offsetUnset()
	 *
	 * @param string $key Name
	 * @return void
	 * @deprecated 1.9 Use remove()
	 */
	public function del($key) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);
		$this->remove($key);
	}

	/**
	 * Has the attribute been defined
	 *
	 * @param string $name Name of the attribute
	 * @return bool
	 * @since 1.9
	 */
	public function has($name) {
		return $this->storage->has($name);
	}

	/**
	 * Adds a token to the session
	 * 
	 * This is used in creation of CSRF token
	 * 
	 * @return void
	 */
	protected function generateSessionToken() {
		// Generate a simple token that we store server side
		if (!$this->has('__elgg_session')) {
			$this->set('__elgg_session', md5(microtime() . rand()));
		}
	}

	/**
	 * Test if property is set either as an attribute or metadata.
	 *
	 * @param string $key The name of the attribute or metadata.
	 *
	 * @return bool
	 * @deprecated 1.9 Use has()
	 */
	public function __isset($key) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);
		return $this->offsetExists($key);
	}

	/**
	 * Set a value, go straight to session.
	 *
	 * @param string $key   Name
	 * @param mixed  $value Value
	 *
	 * @return void
	 * @deprecated 1.9 Use set()
	 */
	public function offsetSet($key, $value) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);
		$_SESSION[$key] = $value;
	}

	/**
	 * Get a variable from either the session, or if its not in the session
	 * attempt to get it from an api call.
	 *
	 * @see ArrayAccess::offsetGet()
	 *
	 * @param mixed $key Name
	 *
	 * @return mixed
	 * @deprecated 1.9 Use get()
	 */
	public function offsetGet($key) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);

		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}

		$orig_value = NULL;
		$value = elgg_trigger_plugin_hook('session:get', $key, NULL, $orig_value);
		if ($orig_value !== $value) {
			elgg_deprecated_notice("Plugin hook session:get has been deprecated.", 1.9);
		}

		$_SESSION[$key] = $value;
		return $value;
	}

	/**
	 * Unset a value from the cache and the session.
	 *
	 * @see ArrayAccess::offsetUnset()
	 *
	 * @param mixed $key Name
	 *
	 * @return void
	 * @deprecated 1.9 Use remove()
	 */
	public function offsetUnset($key) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);
		unset($_SESSION[$key]);
	}

	/**
	 * Return whether the value is set in either the session or the cache.
	 *
	 * @see ArrayAccess::offsetExists()
	 *
	 * @param int $offset Offset
	 *
	 * @return bool
	 * @deprecated 1.9 Use has()
	 */
	public function offsetExists($offset) {
		elgg_deprecated_notice(__METHOD__ . " has been deprecated.", 1.9);

		if (isset($_SESSION[$offset])) {
			return true;
		}

		if ($this->offsetGet($offset)) {
			return true;
		}

		return false;
	}
}
