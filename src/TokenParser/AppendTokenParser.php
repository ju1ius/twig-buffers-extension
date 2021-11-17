<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\AppendNode;
use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use Twig\Node\Node;

final class AppendTokenParser extends BufferInsertionTokenParser
{
    public function getTag(): string
    {
        return 'append';
    }

    protected function createNode(
        string $name,
        Node $body,
        ?string $id,
        int $onMissing,
        int $lineno,
    ): BufferInsertionNode {
        return new AppendNode($name, $body, $id, $onMissing, $lineno, $this->getTag());
    }

}
