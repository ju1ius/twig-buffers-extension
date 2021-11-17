<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use Stringable;

final class BufferReference implements Stringable
{
    private string $key;

    public function __construct(
        private Buffer $buffer,
        private string|Stringable $glue = '',
        private string|Stringable|null $finalGlue = null,
    ) {
        $hash = spl_object_hash($this->buffer);
        $this->key = "<!-- ju1ius/buffer:{$hash} -->";
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->buffer->join($this->glue, $this->finalGlue);
    }

    public function __toString()
    {
        return $this->key;
    }
}
