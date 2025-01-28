<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;
use Twig\Attribute\YieldReady;

#[YieldReady]
final class PrependNode extends BufferInsertionNode
{
    protected function getMethod(): string
    {
        return 'prepend';
    }
}
