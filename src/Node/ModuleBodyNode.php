<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

final class ModuleBodyNode extends Node
{
    public function __construct(Node $body, int $lineno = 0, string $tag = null)
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->write('$this->bufferingContext->push($this->bufferReferences);')
            ->raw("\n");
        $compiler
            ->write("ob_start();\n")
            ->write("try {\n");
        $compiler
            ->indent()
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("} catch (\Throwable \$err) {\n")
            ->indent()
            ->write("ob_end_clean();\n")
            ->write("throw \$err;\n")
            ->outdent()
            ->write("}\n");
        $compiler
            ->write('$this->bufferingContext->pop(ob_get_clean());')
            ->raw("\n");
    }
}
