<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use ju1ius\TwigBuffersExtension\Utils\Lines;
use Twig\Compiler;
use Twig\Node\CaptureNode;
use Twig\Node\Node;
use Twig\Node\NodeCaptureInterface;

abstract class BufferInsertionNode extends Node implements NodeCaptureInterface
{
    public function __construct(
        string $name,
        Node $body,
        ?string $id,
        MissingBufferAction $onMissing = MissingBufferAction::Error,
        int $lineno = 0,
    ) {
        parent::__construct(
            ['body' => $body],
            ['name' => $name, 'id' => $id, 'on_missing' => $onMissing],
            $lineno,
        );
    }

    abstract protected function getMethod(): string;

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $bufferName = $this->getAttribute('name');
        $uid = $this->getAttribute('id');
        $onMissing = $this->getAttribute('on_missing');

        $conditions = [];

        if ($onMissing === MissingBufferAction::Ignore) {
            $conditions[] = sprintf(
                "\$this->bufferingContext->has('%s')",
                $bufferName,
            );
        }

        if ($uid) {
            $conditions[] = sprintf(
                "!\$this->bufferingContext->didUniqueInsert('%s', '%s')",
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

        $body = new CaptureNode(
            $this->getNode('body'),
            $this->getNode('body')->lineno
        );
        $body->setAttribute('raw', true);
        $compiler
            ->write('$tmp = ')
            ->subcompile($body)
        ;

        $code = <<<'PHP'
        match ($tmp) {
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
