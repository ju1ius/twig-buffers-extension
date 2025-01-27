<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

use Stringable;

final class Buffer implements Stringable
{
    /**
     * @var string|Stringable[]
     */
    private array $head = [];

    /**
     * @var string|Stringable[]
     */
    private array $tail = [];

    private int $length = 0;

    /**
     * @var array<string, true>
     */
    private array $uids = [];

    public function clear(): void
    {
        $this->head = $this->tail = $this->uids = [];
        $this->length = 0;
    }

    public function append(string|Stringable $content, ?string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        $this->tail[] = $content;
        $this->length++;
    }

    public function prepend(string|Stringable $content, ?string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        $this->head[] = $content;
        $this->length++;
    }

    public function didInsert(string $uid): bool
    {
        return $this->uids[$uid] ?? false;
    }

    public function isEmpty(): bool
    {
        return $this->length === 0;
    }

    public function join(string|Stringable $glue = '', string|Stringable|null $finalGlue = null): string
    {
        $contents = [...$this->head, ...$this->tail];
        if ($finalGlue === null) {
            return \implode($glue, $contents);
        }
        $tail = \array_pop($contents);
        return \implode($glue, $contents) . $finalGlue . $tail;
    }

    public function __toString(): string
    {
        return $this->join();
    }
}
