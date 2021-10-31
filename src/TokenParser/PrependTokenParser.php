<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use ju1ius\TwigBuffersExtension\Node\PrependNode;

final class PrependTokenParser extends BufferInsertionTokenParser
{
    public function getTag()
    {
        return 'prepend';
    }

    protected function createNode(string $name, ?string $id, $body, int $lineno): BufferInsertionNode
    {
        return new PrependNode($name, $id, $body, $lineno, $this->getTag());
    }
}
