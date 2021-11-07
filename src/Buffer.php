<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

final class Buffer implements \Stringable
{
    /**
     * @var \Stringable[]
     */
    private array $contents = [];
    /**
     * @var array<string, true>
     */
    private array $uids = [];

    public function clear(): void
    {
        $this->contents = [];
    }

    public function append(string|\Stringable $content, string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        $this->contents[] = $content;
    }

    public function prepend(string|\Stringable $content, string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        \array_unshift($this->contents, $content);
    }

    public function didInsert(string $uid): bool
    {
        return isset($this->uids[$uid]);
    }

    public function isEmpty(): bool
    {
        return \count($this->contents) === 0;
    }

    public function __toString()
    {
        return \implode('', $this->contents);
    }
}
