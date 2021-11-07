<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

final class ModuleBodyNode extends Node
{
    public function __construct(Node $body, array $references, array $open)
    {
        parent::__construct(['body' => $body], ['references' => $references, 'open' => $open]);
    }

    public function compile(Compiler $compiler)
    {
        if ($this->getAttribute('references')) {
            $this->compileWithReferences($compiler);
        } else {
            $this->compileNoReferences($compiler);
        }
    }

    private function compileWithReferences(Compiler $compiler)
    {
        $compiler
            ->write($this->compileEnter())
            ->write("ob_start();\n")
            ->write("try {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("} catch (\Throwable \$err) {\n")
            ->indent()
            ->write("ob_end_clean();\n")
            ->write("throw \$err;\n")
            ->outdent()
            ->write("}\n")
            ->write($this->compileLeave())
        ;
    }

    private function compileNoReferences(Compiler $compiler)
    {
        $compiler
            ->write($this->compileEnter())
            ->subcompile($this->getNode('body'))
            ->write($this->compileLeave())
        ;
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

    private function compileLeave(): string
    {
        $references = $this->getAttribute('references');
        if ($references) {
            $names = array_map(fn($name) => "'{$name}'", $references);
            return sprintf(
                "\$this->bufferingContext->leave(ob_get_clean(), %s);\n",
                implode(', ', $names)
            );
        }

        return "\$this->bufferingContext->leave();\n";
    }
}
