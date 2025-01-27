<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

final class BufferReferenceNode extends Node
{
    public function __construct(
        string $name,
        ?AbstractExpression $glue,
        ?AbstractExpression $finalGlue,
        int $lineno = 0,
        ?string $tag = null
    ) {
        parent::__construct([], ['name' => $name, 'glue' => $glue, 'final_glue' => $finalGlue], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $name = $this->getAttribute('name');
        $glue = $this->getAttribute('glue');
        $finalGlue = $this->getAttribute('final_glue');
        $compiler->addDebugInfo($this);
        $compiler
            ->write('echo $this->bufferingContext->reference(')
            ->string($name);
        if ($glue) {
            $compiler->raw(', ')->subcompile($glue);
            if ($finalGlue) {
                $compiler->raw(', ')->subcompile($finalGlue);
            }
        }

        $compiler->raw(");\n");
    }
}
