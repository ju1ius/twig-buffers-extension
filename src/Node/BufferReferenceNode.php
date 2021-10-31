<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

final class BufferReferenceNode extends Node
{
    public function __construct(string $name, int $lineno = 0, string $tag = null)
    {
        parent::__construct([], ['name' => $name], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write(sprintf(
                'echo $this->bufferingContext->reference("%s");',
                $this->getAttribute('name'),
            ))
            ->raw("\n");
    }
}
