<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use JetBrains\PhpStorm\Pure;
use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use SplStack;

final class BufferingContext
{
    /**
     * @var SplStack
     */
    private SplStack $stack;

    /**
     * @var array<string, Buffer>
     */
    private array $buffers = [];

    #[Pure]
    public function __construct()
    {
        $this->stack = new SplStack();
    }

    /**
     * @param string[] $bufferReferences
     */
    public function push(array $bufferReferences)
    {
        foreach ($bufferReferences as $reference) {
            $this->emplace($reference);
        }
        $this->stack->push(null);
    }

    public function pop(string $templateBuffer)
    {
        $this->stack->pop();
        if ($this->stack->isEmpty()) {
            $this->flush($templateBuffer);
        } else {
            echo $templateBuffer;
        }
    }

    public function has(string $bufferName): bool
    {
        return isset($this->buffers[$bufferName]);
    }

    public function reference(string $bufferName): string
    {
        $buffer = $this->get($bufferName);
        $hash = spl_object_hash($buffer);
        return "<!-- buffer:{$hash} -->";
    }

    public function append(string $bufferName, $content, string $uid = null)
    {
        $this->get($bufferName)->append($content, $uid);
    }

    public function prepend(string $bufferName, $content, string $uid = null)
    {
        $this->get($bufferName)->prepend($content, $uid);
    }

    public function clear(string $bufferName)
    {
        $this->get($bufferName)->clear();
    }

    public function didUniquelyInsert(string $bufferName, string $uid): bool
    {
        return $this->get($bufferName)->didInsert($uid);
    }

    private function flush(string $templateBuffer): void
    {
        $search = [];
        $replace = [];
        foreach ($this->buffers as $name => $buffer) {
            $hash = spl_object_hash($buffer);
            $search[] = "<!-- buffer:{$hash} -->";
            $replace[] = (string)$buffer;
        }
        echo str_replace($search, $replace, $templateBuffer);
        $this->buffers = [];
    }

    private function emplace(string $name): Buffer
    {
        if (!isset($this->buffers[$name])) {
            $this->buffers[$name] = new Buffer();
        }
        return $this->buffers[$name];
    }

    private function get(string $bufferName): Buffer
    {
        $buffer = $this->buffers[$bufferName] ?? null;
        if (!$buffer) {
            throw new UnknownBuffer(sprintf(
                'Unknown buffer "%s".',
                $bufferName,
            ));
        }

        return $buffer;
    }
}
