<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

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
        match ($position) {
            ModuleDisplayWrapperPosition::Start => $this->compileStartNode($compiler, $contextId),
            ModuleDisplayWrapperPosition::End => $this->compileEndNode($compiler, $contextId),
        };
    }

    private function compileStartNode(Compiler $compiler, string $contextId): void
    {
        $compiler
            ->write($this->compileEnter($contextId))
            ->subcompile($this->getNode('body'))
            ->write("ob_start();\n")
            ->write("try {\n")
            ->indent();
    }

    private function compileEndNode(Compiler $compiler, string $contextId): void
    {
        $compiler
            ->outdent()
            ->write('} catch (\Throwable $err) {')->raw("\n")
            ->indent()
            ->write('ob_end_clean();')->raw("\n")
            ->write('throw $err;')->raw("\n")
            ->outdent()
            ->write("}\n")
            ->write(sprintf(
                '$this->bufferingContext->leave(%s, ob_get_clean());',
                var_export($contextId, true),
            ))
            ->raw("\n");
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
