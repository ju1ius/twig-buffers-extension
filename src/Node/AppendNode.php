<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Node\NodeCaptureInterface;

final class AppendNode extends BufferInsertionNode implements NodeCaptureInterface
{
    protected function getMethod(): string
    {
        return 'append';
    }
}
