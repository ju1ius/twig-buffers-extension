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
    private BufferingContext $bufferingContext;

    public function __construct()
    {
        $this->bufferingContext = new BufferingContext();
    }

    public function getContext(): BufferingContext
    {
        return $this->bufferingContext;
    }

    public function getTokenParsers()
    {
        return [
            new BufferTokenParser(),
            new AppendTokenParser(),
            new PrependTokenParser(),
        ];
    }

    public function getNodeVisitors()
    {
        return [
            new ModuleNodeVisitor(),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('clear_buffer', [$this, 'clearBuffer']),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('buffer', [$this, 'hasBuffer']),
            new TwigTest('empty_buffer', [$this, 'bufferIsEmpty']),
        ];
    }

    public function clearBuffer(string $name): void
    {
        $this->bufferingContext->clear($name);
    }

    public function hasBuffer(string $bufferName): bool
    {
        return $this->bufferingContext->has($bufferName);
    }

    public function bufferIsEmpty(string $bufferName): bool
    {
        return $this->bufferingContext->isEmpty($bufferName);
    }
}
