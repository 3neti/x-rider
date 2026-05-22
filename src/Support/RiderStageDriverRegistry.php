<?php

namespace LBHurtado\XRider\Support;

use LBHurtado\XRider\Contracts\RiderStageDriverContract;
use RuntimeException;

class RiderStageDriverRegistry
{
    /**
     * @var array<string, RiderStageDriverContract>
     */
    protected array $drivers = [];

    public function register(RiderStageDriverContract $driver): self
    {
        $this->drivers[$driver->type()] = $driver;

        return $this;
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->drivers);
    }

    public function get(string $type): RiderStageDriverContract
    {
        if (! $this->has($type)) {
            throw new RuntimeException("Rider stage driver [{$type}] is not registered.");
        }

        return $this->drivers[$type];
    }

    /**
     * @return array<string, RiderStageDriverContract>
     */
    public function all(): array
    {
        return $this->drivers;
    }

    public function forget(string $type): self
    {
        unset($this->drivers[$type]);

        return $this;
    }

    public function flush(): self
    {
        $this->drivers = [];

        return $this;
    }
}