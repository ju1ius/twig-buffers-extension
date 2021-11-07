<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use JetBrains\PhpStorm\Pure;
use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use SplStack;

final class BufferingContext
{
    /**
     * @var array<string, Buffer>
     */
    private array $buffers = [];

    private \SplStack $stack;

    #[Pure]
    public function __construct()
    {
        $this->stack = new SplStack();
    }

    public function enter(string ...$bufferNames): void
    {
        foreach ($bufferNames as $bufferName) {
            if (!isset($this->buffers[$bufferName])) {
                $this->buffers[$bufferName] = new Buffer();
            }
        }
        $this->stack->push($bufferNames);
    }

    public function leave(string $templateBuffer = null, string ...$references): void
    {
        match($templateBuffer) {
            '', null => null,
            default => $this->flush($templateBuffer, $references),
        };
        $this->stack->pop();
        if ($this->stack->isEmpty()) {
            $this->buffers = [];
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

    public function append(string $bufferName, $content, string $uid = null): void
    {
        $this->get($bufferName)->append($content, $uid);
    }

    public function prepend(string $bufferName, $content, string $uid = null): void
    {
        $this->get($bufferName)->prepend($content, $uid);
    }

    public function clear(string $bufferName): void
    {
        $this->get($bufferName)->clear();
    }

    public function isEmpty(string $bufferName): bool
    {
        if ($buffer = $this->buffers[$bufferName] ?? null) {
            return $buffer->isEmpty();
        }
        return true;
    }

    public function didUniqueInsert(string $bufferName, string $uid): bool
    {
        return $this->get($bufferName)->didInsert($uid);
    }

    private function flush(string $templateBuffer, array $references): void
    {
        $search = [];
        $replace = [];
        foreach ($references as $bufferName) {
            $buffer = $this->get($bufferName);
            $hash = spl_object_hash($buffer);
            $search[] = "<!-- buffer:{$hash} -->";
            $replace[] = (string)$buffer;
        }
        echo str_replace($search, $replace, $templateBuffer);
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
