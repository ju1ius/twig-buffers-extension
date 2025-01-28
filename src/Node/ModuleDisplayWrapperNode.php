<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\Utils\Output;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

#[YieldReady]
final class ModuleDisplayWrapperNode extends Node
{
    public function __construct(ModuleDisplayWrapperPosition $position, array $references, array $open)
    {
        parent::__construct([], [
            'position' => $position,
            'references' => $references,
            'open' => $open,
        ]);
    }

    public function compile(Compiler $compiler): void
    {
        $position = $this->getAttribute('position');
        $contextId = $this->getSourceContext()->getName();
        $useYield = $compiler->getEnvironment()->useYield();
        match ($position) {
            ModuleDisplayWrapperPosition::Start => $this->compileStartNode($compiler, $contextId, $useYield),
            ModuleDisplayWrapperPosition::End => $this->compileEndNode($compiler),
        };
    }

    private function compileStartNode(Compiler $compiler, string $contextId, bool $useYield): void
    {
        $compiler
            ->write(\sprintf(
                'yield $this->bufferingContext->enter(%s, %s, %s, ',
                var_export($contextId, true),
                $this->compileBufferNames(),
                $useYield ? 'false' : 'true',
            ))
            ->raw('function() use (&$context, $macros, $blocks) {')->raw("\n")
            ->indent()
        ;
    }

    private function compileEndNode(Compiler $compiler): void
    {
        $compiler
            ->write("yield from [];\n")
            ->outdent()
            ->write("});\n")
        ;
    }

    private function compileBufferNames(): string
    {
        $references = $this->getAttribute('references');
        $toOpen = $this->getAttribute('open');
        $buffers = array_unique(array_merge($references, $toOpen));
        $args = array_map(fn($name) => var_export($name, true), $buffers);

        return \sprintf('[%s]', \implode(', ', $args));
    }
}
