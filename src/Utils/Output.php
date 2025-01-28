<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Utils;

final class Output
{
    /**
     * @param iterable<string> $body
     * @return string
     */
    public static function join(iterable $body): string
    {
        $output = '';
        foreach ($body as $part) {
            $output .= $part;
        }
        return $output;
    }

    /**
     * @param iterable<string> $body
     * @return string
     * @throws \Throwable
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
