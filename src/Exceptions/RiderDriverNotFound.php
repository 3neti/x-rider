<?php

namespace LBHurtado\XRider\Exceptions;

use RuntimeException;

class RiderDriverNotFound extends RuntimeException
{
    public static function named(string $name, array $searchedPaths = []): self
    {
        $paths = collect($searchedPaths)
            ->filter()
            ->map(fn (string $path) => "- {$path}")
            ->implode(PHP_EOL);

        return new self(
            "Rider driver [{$name}] was not found."
            .($paths ? PHP_EOL."Searched paths:".PHP_EOL.$paths : '')
        );
    }
}