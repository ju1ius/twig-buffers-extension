<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

final class AppendNode extends BufferInsertionNode
{
    protected function getMethod(): string
    {
        return 'append';
    }
}
