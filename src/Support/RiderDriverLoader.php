<?php

namespace LBHurtado\XRider\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use LBHurtado\XRider\Exceptions\RiderDriverNotFound;
use Symfony\Component\Yaml\Yaml;

class RiderDriverLoader
{
    public function __construct(
        protected Filesystem $files,
    ) {}

    public function load(?string $name = null): array
    {
        $name ??= (string) config('x-rider.driver', 'default');

        $paths = $this->candidatePaths($name);

        foreach ($paths as $path) {
            if ($this->files->exists($path)) {
                return $this->parse($path);
            }
        }

        throw RiderDriverNotFound::named($name, $paths);
    }

    public function rider(?string $name = null): array
    {
        return Arr::get($this->load($name), 'rider', []);
    }

    protected function candidatePaths(string $name): array
    {
        $file = str_ends_with($name, '.yaml') || str_ends_with($name, '.yml')
            ? $name
            : "{$name}.yaml";

        return [
            rtrim((string) config('x-rider.drivers_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file,
            rtrim((string) config('x-rider.package_drivers_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file,
        ];
    }

    protected function parse(string $path): array
    {
        $parsed = Yaml::parseFile($path);

        return is_array($parsed) ? $parsed : [];
    }
}