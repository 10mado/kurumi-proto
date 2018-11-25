<?php
declare(strict_types=1);

namespace Kurumi\Session;

use RuntimeException;

class CsrfGuard
{
    protected const STORAGE_KEY = '__kurumi_csrf__';

    protected const STORAGE_LIMIT = 8;

    protected const TOKEN_STRENGTH = 16;

    protected $storage;

    protected $persistentTokenMode = false;

    protected $tokenInputItemName = 'csrf_token';

    protected $tokenHeaderName = 'X-CSRF-TOKEN';

    protected $failureCallable;

    public function __construct()
    {
        if (! isset($_SESSION)) {
            throw new RuntimeException('Session must be started.');
        }
        $_SESSION[self::STORAGE_KEY] = $_SESSION[self::STORAGE_KEY] ?? [];
        $this->storage = &$_SESSION[self::STORAGE_KEY];
    }

    /**
     * Get a current token.
     *
     * @return string
     */
    public function getToken(): string
    {
        $token = null;
        if ($this->persistentTokenMode) {
            $token = $this->getLastToken();
        }
        if (is_null($token)) {
            $token = $this->generateToken();
            $this->storage[] = $token;
        }
        $this->dequeue();
        return $token;
    }

    /**
     * Verify csrf_token.
     *
     * @param string $token
     * @return boolean
     */
    public function verify(string $token): bool
    {
        $index = array_search($token, $this->storage, true);
        if ($index !== false) {
            if (! $this->persistentTokenMode) {
                // delete used token and reindex
                unset($this->storage[$index]);
                $this->storage = array_merge([], $this->storage);
            }
            return true;
        }
        return false;
    }

    public function setPersistentTokenMode(bool $persistentTokenMode): void
    {
        $this->persistentTokenMode = $persistentTokenMode;
    }

    public function setTokenInputItemName(string $tokenInputItemName): void
    {
        $this->tokenInputItemName = $tokenInputItemName;
    }

    public function getTokenInputItemName(): string
    {
        return $this->tokenInputItemName;
    }

    public function setTokenHeaderName(string $tokenHeaderName): void
    {
        $this->tokenHeaderName = $tokenHeaderName;
    }

    public function getTokenHeaderName(): string
    {
        return $this->tokenHeaderName;
    }

    public function setFailureCallable(callable $failureCallable): void
    {
        $this->failureCallable = $failureCallable;
    }

    public function getFailureCallable(): callable
    {
        if (is_null($this->failureCallable)) {
            $this->failureCallable = function (Request $request): Response {
                return (new TextResponse('Invalid CSRF token.', 400));
            };
        }
        return $this->failureCallable;
    }

    protected function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_STRENGTH));
    }

    protected function getLastToken(): ?string
    {
        if (count($this->storage) === 0) {
            return null;
        }
        $lastToken = end($this->storage);
        reset($this->storage);
        return $lastToken;
    }

    protected function dequeue(): void
    {
        $size = count($this->storage);
        while ($size > self::STORAGE_LIMIT) {
            array_shift($this->storage);
            $size--;
        }
    }
}
