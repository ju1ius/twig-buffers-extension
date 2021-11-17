<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use JetBrains\PhpStorm\Pure;
use ju1ius\TwigBuffersExtension\Exception\UnknownBuffer;
use SplStack;
use Stringable;

final class BufferingContext
{
    /**
     * @var array<string, Buffer>
     */
    private array $buffers = [];
    /**
     * @var SplStack<Scope>
     */
    private SplStack $scopes;

    #[Pure]
    public function __construct()
    {
        $this->scopes = new SplStack();
    }

    public function enter(string ...$bufferNames): void
    {
        foreach ($bufferNames as $bufferName) {
            if (!isset($this->buffers[$bufferName])) {
                $this->buffers[$bufferName] = new Buffer();
            }
        }
        $this->scopes->push(new Scope());
    }

    public function leave(string $templateBuffer = null): void
    {
        $scope = $this->scopes->pop();
        match ($templateBuffer) {
            '', null => null,
            default => $this->flush($templateBuffer, $scope),
        };
        if ($this->scopes->isEmpty()) {
            $this->buffers = [];
        }
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
        $scope = $this->scopes->top();
        $buffer = $this->get($bufferName);
        return $scope->references[] = new BufferReference($buffer, $glue, $finalGlue);
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

    private function flush(string $templateBuffer, Scope $scope): void
    {
        $search = [];
        $replace = [];
        foreach ($scope->references as $ref) {
            $search[] = $ref->getKey();
            $replace[] = $ref->getValue();
        }
        echo \str_replace($search, $replace, $templateBuffer);
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
}
