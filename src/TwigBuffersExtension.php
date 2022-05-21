<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\NodeVisitor\ModuleNodeVisitor;
use ju1ius\TwigBuffersExtension\TokenParser\AppendTokenParser;
use ju1ius\TwigBuffersExtension\TokenParser\BufferTokenParser;
use ju1ius\TwigBuffersExtension\TokenParser\PrependTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

final class TwigBuffersExtension extends AbstractExtension
{
    private readonly BufferingContext $bufferingContext;

    public function __construct()
    {
        $this->bufferingContext = new BufferingContext();
    }

    public function getContext(): BufferingContext
    {
        return $this->bufferingContext;
    }

    public function getTokenParsers(): array
    {
        return [
            new BufferTokenParser(),
            new AppendTokenParser(),
            new PrependTokenParser(),
        ];
    }

    public function getNodeVisitors(): array
    {
        return [
            new ModuleNodeVisitor(),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('clear_buffer',$this->clearBuffer(...)),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('buffer', $this->hasBuffer(...)),
            new TwigTest('empty_buffer', $this->bufferIsEmpty(...)),
        ];
    }

    private function clearBuffer(string $name): void
    {
        $this->bufferingContext->clear($name);
    }

    private function hasBuffer(string $bufferName): bool
    {
        return $this->bufferingContext->has($bufferName);
    }

    private function bufferIsEmpty(string $bufferName): bool
    {
        return $this->bufferingContext->isEmpty($bufferName);
    }
}
