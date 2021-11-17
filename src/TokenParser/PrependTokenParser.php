<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use ju1ius\TwigBuffersExtension\Node\PrependNode;
use Twig\Node\Node;

final class PrependTokenParser extends BufferInsertionTokenParser
{
    public function getTag(): string
    {
        return 'prepend';
    }

    protected function createNode(
        string $name,
        Node $body,
        ?string $id,
        int $onMissing,
        int $lineno,
    ): BufferInsertionNode {
        return new PrependNode($name, $body, $id, $onMissing, $lineno, $this->getTag());
    }
}
