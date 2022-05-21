<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use Stringable;

final class BufferReference implements Stringable
{
    private readonly string $key;

    public function __construct(
        private readonly Buffer $buffer,
        private readonly string|Stringable $glue = '',
        private readonly string|Stringable|null $finalGlue = null,
    ) {
        $hash = spl_object_hash($this);
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

    public function __toString(): string
    {
        return $this->key;
    }
}
