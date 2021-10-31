<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\TokenParser;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use Twig\Error\SyntaxError;
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
        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        $id = null;
        if ($idToken = $stream->nextIf(Token::NAME_TYPE)) {
            $id = $idToken->getValue();
        }

        $this->parser->pushLocalScope();

        $capture = false;
        if ($stream->nextIf(Token::BLOCK_END_TYPE)) {
            $capture = true;
            $body = $this->parser->subparse(fn(Token $token) => $token->test("end{$this->getTag()}"), true);
            if ($token = $stream->nextIf(Token::NAME_TYPE)) {
                $value = $token->getValue();
                if ($value !== $name) {
                    throw new SyntaxError(
                        sprintf(
                            "Expected end%s for buffer '%s', but got %s",
                            $this->getTag(),
                            $name,
                            $value,
                        ),
                        $stream->getCurrent()->getLine(),
                        $stream->getSourceContext(),
                    );
                }
            }
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
