<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\Node;

enum MissingBufferAction
{
    case Error;
    case Ignore;
    case Create;
}
