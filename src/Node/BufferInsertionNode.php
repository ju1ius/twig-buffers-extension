<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\Utils\Lines;
use Twig\Compiler;
use Twig\Node\Node;

abstract class BufferInsertionNode extends Node
{
    public function __construct(
        string $name,
        Node $body,
        ?string $id,
        bool $ignoreMissing,
        int $lineno = 0,
        string $tag = null
    ) {
        parent::__construct(
            ['body' => $body],
            ['name' => $name, 'id' => $id, 'ignore_missing' => $ignoreMissing],
            $lineno,
            $tag
        );
    }

    abstract protected function getMethod(): string;

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $bufferName = $this->getAttribute('name');
        $uid = $this->getAttribute('id');
        $ignoreMissing = $this->getAttribute('ignore_missing');

        $conditions = [];

        if ($ignoreMissing) {
            $conditions[] = sprintf(
                "\$this->bufferingContext->has('%s')",
                $bufferName,
            );
        }

        if ($uid) {
            $conditions[] = sprintf(
                "!\$this->bufferingContext->didUniquelyInsert('%s', '%s')",
                $bufferName,
                $uid,
            );
        }

        if ($conditions) {
            $compiler
                ->write(sprintf("if (%s) {\n", implode(' && ', $conditions)))
                ->indent()
            ;
        }

        if ($compiler->getEnvironment()->isDebug()) {
            $compiler->write("ob_start();\n");
        } else {
            $compiler->write("ob_start(fn() => '');\n");
        }

        $compiler->subcompile($this->getNode('body'));

        $code = <<<'PHP'
        match ($tmp = ob_get_clean()) {
            '', false => null,
            default => $this->bufferingContext->%s('%s', new Markup($tmp, $this->env->getCharset()), %s),
        };
        PHP;

        $compiler
            ->write(...Lines::split(sprintf(
                $code,
                $this->getMethod(),
                $bufferName,
                $uid ? "'{$uid}'" : 'null',
            )))
            ->raw("\n")
        ;

        if ($conditions) {
            $compiler
                ->outdent()
                ->write("}\n")
            ;
        }
    }
}
