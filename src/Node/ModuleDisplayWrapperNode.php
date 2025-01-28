<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\Utils\Output;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;

#[YieldReady]
final class ModuleDisplayWrapperNode extends Node
{
    public function __construct(ModuleDisplayWrapperPosition $position, Node $body, array $references, array $open)
    {
        parent::__construct(
            ['body' => $body],
            [
                'position' => $position,
                'references' => $references,
                'open' => $open,
            ]
        );
        $this->setSourceContext($body->getSourceContext());
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
            ->subcompile($this->getNode('body'))
            ->write($this->compileEnter($contextId))
            ->write(\sprintf(
                'yield $this->bufferingContext->leave(%s, ',
                var_export($contextId, true),
            ))
            ->raw(\sprintf(
                '\%s::%s(',
                Output::class,
                $useYield ? 'join' : 'capture',
            ))
            ->raw('(function() use (&$context, $macros, $blocks) {')->raw("\n")
            ->indent()
        ;
    }

    private function compileEndNode(Compiler $compiler): void
    {
        $compiler
            ->write("yield from [];\n")
            ->outdent()
            ->write("})()));\n")
            ->subcompile($this->getNode('body'))
        ;
    }

    private function compileEnter(string $contextId): string
    {
        $references = $this->getAttribute('references');
        $toOpen = $this->getAttribute('open');
        $buffers = array_unique(array_merge($references, $toOpen));
        $args = array_map(
            fn($name) => var_export($name, true),
            [$contextId, ...$buffers],
        );

        return sprintf(
            "\$this->bufferingContext->enter(%s);\n",
            implode(', ', $args)
        );
    }
}
