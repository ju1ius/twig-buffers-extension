<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Attribute\YieldReady;

#[YieldReady]
final class AppendNode extends BufferInsertionNode
{
    protected function getMethod(): string
    {
        return 'append';
    }
}
