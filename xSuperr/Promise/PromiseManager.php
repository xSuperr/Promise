<?php

namespace xSuperr\Promise;

class PromiseManager {
    private array $promises = [];
    private array $timeouts = [];

    public function __construct() {
    }

    public function createPromise(int $timeout = 5): array {
        $resolver = new BetterPromiseResolver();
        $id = spl_object_id($resolver);

        $this->promises[$id] = $resolver;
        $this->timeouts[$id] = microtime(true) + $timeout;

        return [$id, $resolver->getPromise()];
    }

    public function resolve(int $id, mixed $value): void {
        if (isset($this->promises[$id])) {
            $this->promises[$id]->resolve($value);
            unset($this->promises[$id], $this->timeouts[$id]);
        }
    }

    public function reject(int $id, mixed $err): void
    {
        if (isset($this->promises[$id])) {
            $this->promises[$id]->reject($err);
            unset($this->promises[$id], $this->timeouts[$id]);
        }
    }

    public function checkTimeouts(): void {
        $now = microtime(true);
        foreach ($this->timeouts as $id => $expireTime) {
            if ($now > $expireTime && isset($this->promises[$id])) {
                $this->promises[$id]->reject(new \Exception("timeout"));
                unset($this->promises[$id], $this->timeouts[$id]);
            }
        }
    }
}
