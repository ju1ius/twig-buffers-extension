<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\AppendNode;
use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;

final class AppendTokenParser extends BufferInsertionTokenParser
{
    public function getTag()
    {
        return 'append';
    }

    protected function createNode(string $name, ?string $id, $body, int $lineno): BufferInsertionNode
    {
        return new AppendNode($name, $id, $body, $lineno, $this->getTag());
    }
}
