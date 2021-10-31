<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Node;

abstract class BufferInsertionNode extends Node
{
    public function __construct(string $name, ?string $id, Node $body, int $lineno = 0, string $tag = null)
    {
        parent::__construct(
            ['body' => $body],
            ['name' => $name, 'id' => $id],
            $lineno,
            $tag
        );
    }

    abstract protected function getMethod(): string;

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $debug = $compiler->getEnvironment()->isDebug();
        if ($debug) {
            $compiler->write("ob_start();\n");
        } else {
            $compiler->write("ob_start(fn() => '');\n");
        }

        $compiler->subcompile($this->getNode('body'));

        $code = <<<'PHP'
        if ($tmp = ob_get_clean()) $this->bufferingContext->%s('%s', new Markup($tmp, $this->env->getCharset()), %s);

        PHP;

        $name = $this->getAttribute('name');
        $id = $this->getAttribute('id');
        $compiler->write(sprintf(
            $code,
            $this->getMethod(),
            $name,
            $id ? "'{$id}'" : 'null',
        ));
    }
}
