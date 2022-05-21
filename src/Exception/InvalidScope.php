<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Exception;

final class InvalidScope extends \LogicException
{
    public static function expecting(string $actual, string $expected): self
    {
        return new self(sprintf(
            'Invalid scope "%s", expected "%s".',
            $actual,
            $expected,
        ));
    }
}
