<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\TwigBuffersExtension;
use Twig\Compiler;
use Twig\Node\Node;

final class TemplateConstructorNode extends Node
{
    public function __construct(array $buffers)
    {
        parent::__construct([], ['buffers' => $buffers]);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw("\n")
            ->write('$this->bufferingContext = $env->getExtension(')
            ->string(TwigBuffersExtension::class)
            ->raw(')->getContext();')
            ->raw("\n");
    }
}
