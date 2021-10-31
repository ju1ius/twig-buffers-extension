<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\BufferingContext;
use Twig\Compiler;
use Twig\Node\Node;

final class TemplateClassFooterNode extends Node
{
    public function __construct(array $buffers)
    {
        parent::__construct([], ['buffers' => $buffers]);
    }

    public function compile(Compiler $compiler)
    {
        $this->compileBuffersDeclaration($compiler);
    }

    private function compileBuffersDeclaration(Compiler $compiler)
    {
        $code = <<<'PHP'

            private %s $bufferingContext;
            private array $bufferReferences = [%s];

        PHP;

        $buffers = implode(', ', array_map(fn($name) => "'{$name}'", $this->getAttribute('buffers')));

        $compiler->raw(sprintf(
            $code,
            BufferingContext::class,
            $buffers,
        ));
    }
}
