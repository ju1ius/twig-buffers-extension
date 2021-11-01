<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Utils;

final class Lines
{
    /**
     * @param string $input
     * @return string[]
     */
    public static function split(string $input): array
    {
        return preg_split('/(?<=\n)/', $input, -1);
    }
}
