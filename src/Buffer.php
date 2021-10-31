<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension;

final class Buffer
{
    private array $contents = [];
    private array $uids = [];

    public function clear(): void
    {
        $this->contents = [];
    }

    public function append($content, string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        $this->contents[] = $content;
    }

    public function prepend($content, string $uid = null): void
    {
        if ($uid) {
            if (isset($this->uids[$uid])) return;
            $this->uids[$uid] = true;
        }
        array_unshift($this->contents, $content);
    }

    public function __toString()
    {
        return implode('', $this->contents);
    }
}
