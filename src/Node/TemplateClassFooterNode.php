<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\BufferingContext;
use Twig\Compiler;
use Twig\Node\Node;

final class TemplateClassFooterNode extends Node
{
    public function compile(Compiler $compiler): void
    {
        $this->compileBuffersDeclaration($compiler);
    }

    private function compileBuffersDeclaration(Compiler $compiler): void
    {
        $code = <<<'PHP'

            private %s $bufferingContext;

        PHP;

        $compiler->raw(sprintf(
            $code,
            BufferingContext::class,
        ));
    }
}
