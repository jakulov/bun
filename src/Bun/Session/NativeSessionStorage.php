<?php
namespace Bun\Session;

/**
 * Class NativeSessionStorage
 *
 * @package Bun\Session
 */
class NativeSessionStorage implements SessionStorageInterface
{
    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Start session if needed
     */
    protected function start()
    {
        $sid = session_id();
        if ($sid === '') {
            session_start();
        }
    }

    /**
     * @param $sid
     * @return array|null
     */
    public function get($sid)
    {
        $this->start();
        if ($sid === session_id()) {
            return $_SESSION;
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        $this->start();
        if ($key === session_id()) {
            $_SESSION = $value;
            session_write_close();

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function generateId()
    {
        if(session_id() === '') {
            session_start();
        }

        return session_id();
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if($key === session_id()) {
            return session_destroy();
        }

        return true;
    }
}