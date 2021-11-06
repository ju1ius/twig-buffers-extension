<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use ju1ius\TwigBuffersExtension\Node\PrependNode;
use Twig\Node\Node;

final class PrependTokenParser extends BufferInsertionTokenParser
{
    public function getTag()
    {
        return 'prepend';
    }

    protected function createNode(
        string $name,
        Node $body,
        ?string $id,
        bool $ignoreMissing,
        int $lineno
    ): BufferInsertionNode {
        return new PrependNode($name, $body, $id, $ignoreMissing, $lineno, $this->getTag());
    }
}
