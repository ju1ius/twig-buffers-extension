<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

final class ModuleDisplayWrapperNode extends Node
{
    const POSITION_START = 0;
    const POSITION_END = 1;

    public function __construct(int $position, Node $body, array $references, array $open)
    {
        parent::__construct(
            ['body' => $body],
            [
                'position' => match ($position) {
                    self::POSITION_START, self::POSITION_END => $position,
                },
                'references' => $references,
                'open' => $open,
            ]
        );
    }

    public function compile(Compiler $compiler): void
    {
        $position = $this->getAttribute('position');
        if ($position === self::POSITION_START) {
            $compiler
                ->write($this->compileEnter())
                ->subcompile($this->getNode('body'))
                ->write("ob_start();\n")
                ->write("try {\n")
                ->indent();
        } else {
            $compiler
                ->outdent()
                ->write("} catch (\Throwable \$err) {\n")
                ->indent()
                ->write("ob_end_clean();\n")
                ->write("throw \$err;\n")
                ->outdent()
                ->write("}\n")
                ->write("\$this->bufferingContext->leave(ob_get_clean());\n");
        }
    }

    private function compileEnter(): string
    {
        $references = $this->getAttribute('references');
        $toOpen = $this->getAttribute('open');
        $buffers = array_unique(array_merge($references, $toOpen));
        $names = array_map(fn($name) => "'{$name}'", $buffers);

        return sprintf(
            "\$this->bufferingContext->enter(%s);\n",
            implode(', ', $names)
        );
    }
}
