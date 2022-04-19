<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\TwigBuffersExtension;
use Twig\Compiler;
use Twig\Node\Node;

final class TemplateConstructorNode extends Node
{
    public function compile(Compiler $compiler): void
    {
        $code = <<<'PHP'
        $this->bufferingContext = $this->extensions['%s']->getContext();
        PHP;

        $compiler
            ->raw("\n")
            ->write(sprintf($code, TwigBuffersExtension::class))
            ->raw("\n")
        ;
    }
}
