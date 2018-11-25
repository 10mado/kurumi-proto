<?php
declare(strict_types=1);

namespace Kurumi\Session;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

/**
 * Flash messages
 */
class FlashMessage
{
    protected const STORAGE_KEY = '__kurumi_flash_message__';

    /**
     * Messages from previous request
     *
     * @var array
     */
    protected $fromPrevious = [];

    /**
     * Message storage
     *
     * @var array
     */
    protected $storage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (! isset($_SESSION)) {
            throw new RuntimeException('Session must be started.');
        }
        $_SESSION[self::STORAGE_KEY] = $_SESSION[self::STORAGE_KEY] ?? [];
        $this->storage = &$_SESSION[self::STORAGE_KEY];
        // load
        if (count($this->storage) > 0) {
            $this->fromPrevious = $this->storage;
        }
        // clear
        $this->storage = [];
    }

    /**
     * Add a flash message.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add(string $key, $value): void
    {
        $this->storage[$key] = $value;
    }

    /**
     * Get all flash messages.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->fromPrevious;
    }

    /**
     * Get a flash message
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->fromPrevious[$key] ?? null;
    }

    /**
     * Check if a flash message exists.
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return array_key_extists($key, $this->fromPrevious);
    }

    /**
     * Clear a flash message or all flash messages.
     * If $key is specified, only a specified message will be cleared.
     *
     * @param string $key
     * @return void
     */
    public function clear(string $key = null): void
    {
        if (is_null($key)) {
            $this->storage = [];
            $this->fromPrevious = [];
        } else {
            if (isset($this->storage[$key])) {
                unset($this->storage[$key]);
            }
            if (isset($this->fromPrevious[$key])) {
                unset($this->fromPrevious[$key]);
            }
        }
    }
}
