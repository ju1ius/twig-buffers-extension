<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use ju1ius\TwigBuffersExtension\Utils\Output;
use SplStack;
use Stringable;
use Throwable;

final class BufferingContext
{
    /**
     * @var array<string, Buffer>
     */
    private array $buffers = [];
    /**
     * @var BufferReference[]
     */
    private array $references = [];
    /**
     * @var SplStack<string>
     */
    private readonly SplStack $scopes;

    public function __construct()
    {
        $this->scopes = new SplStack();
    }

    /**
     * @param string $scopeId
     * @param string[] $bufferNames
     * @param bool $capturing
     * @param callable(): string $input
     * @return string
     * @throws Throwable
     */
    public function enter(string $scopeId, array $bufferNames, bool $capturing, callable $input): string
    {
        $this->openBuffers(...$bufferNames);
        $this->scopes->push($scopeId);

        $output = match ($capturing) {
            true => Output::capture($input()),
            false => Output::join($input()),
        };

        $this->scopes->pop();

        return $this->scopes->isEmpty() ? $this->flush($output) : $output;
    }

    public function has(string $bufferName): bool
    {
        return isset($this->buffers[$bufferName]);
    }

    public function reference(
        string $bufferName,
        string|Stringable $glue = '',
        string|Stringable|null $finalGlue = null
    ): BufferReference {
        return $this->references[] = new BufferReference($this->get($bufferName), $glue, $finalGlue);
    }

    public function append(string $bufferName, $content, ?string $uid = null): void
    {
        $this->get($bufferName)->append($content, $uid);
    }

    public function prepend(string $bufferName, $content, ?string $uid = null): void
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

    private function flush(string $outputBuffer): string
    {
        $pairs = [];
        foreach ($this->references as $ref) {
            $pairs[$ref->getKey()] = $ref->getValue();
        }
        $this->buffers = $this->references = [];

        return \strtr($outputBuffer, $pairs);
    }

    private function get(string $bufferName): Buffer
    {
        if ($buffer = $this->buffers[$bufferName] ?? null) {
            return $buffer;
        }
        throw new UnknownBuffer(sprintf(
            'Unknown buffer "%s".',
            $bufferName,
        ));
    }

    private function openBuffers(string ...$bufferNames): void
    {
        foreach ($bufferNames as $bufferName) {
            if (!isset($this->buffers[$bufferName])) {
                $this->buffers[$bufferName] = new Buffer();
            }
        }
    }
}
