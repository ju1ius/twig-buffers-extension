<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Utils;

final class Output
{
    /**
     * @param iterable<string> $parts
     * @return string
     * @internal
     */
    public static function join(iterable $parts): string
    {
        if (\is_array($parts)) {
            return \implode('', $parts);
        }

        $output = '';
        foreach ($parts as $part) {
            $output .= $part;
        }
        return $output;
    }

    /**
     * @param iterable<string> $body
     * @return string
     * @internal
     */
    public static function capture(iterable $body): string
    {
        $level = ob_get_level();
        ob_start();

        try {
            foreach ($body as $data) {
                echo $data;
            }
        } catch (\Throwable $err) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $err;
        }

        return ob_get_clean();
    }
}
