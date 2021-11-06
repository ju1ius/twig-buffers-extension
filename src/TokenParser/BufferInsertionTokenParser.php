<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

abstract class BufferInsertionTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $stream->expect(Token::NAME_TYPE, 'to');
        $name = $stream->expect(Token::NAME_TYPE)->getValue();

        $id = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'as')) {
            $id = $stream->expect(Token::NAME_TYPE)->getValue();
        }

        $this->parser->pushLocalScope();

        $capture = false;
        if ($stream->nextIf(Token::BLOCK_END_TYPE)) {
            $capture = true;
            $body = $this->parser->subparse(fn(Token $token) => $token->test("end{$this->getTag()}"), true);
        } else {
            $body = new Node([
                new PrintNode($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ]);
        }

        $this->parser->popLocalScope();
        $stream->expect(Token::BLOCK_END_TYPE);

        return $this->createNode($name, $id, $body, $lineno);
    }

    abstract protected function createNode(string $name, ?string $id, $body, int $lineno): BufferInsertionNode;
}
