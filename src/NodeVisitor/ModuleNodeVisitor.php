<?php declare(strict_types=1);

namespace ju1ius\TwigBuffersExtension\NodeVisitor;

use ju1ius\TwigBuffersExtension\Node\BufferInsertionNode;
use ju1ius\TwigBuffersExtension\Node\BufferReferenceNode;
use ju1ius\TwigBuffersExtension\Node\ModuleDisplayWrapperNode;
use ju1ius\TwigBuffersExtension\Node\TemplateClassFooterNode;
use ju1ius\TwigBuffersExtension\Node\TemplateConstructorNode;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

final class ModuleNodeVisitor implements NodeVisitorInterface
{
    private array $bufferReferences = [];
    private array $buffersToOpen = [];

    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ModuleNode) {
            $this->bufferReferences = [];
            $this->buffersToOpen = [];
        }

        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof BufferReferenceNode) {
            $this->bufferReferences[] = $node->getAttribute('name');
        } else if (
            $node instanceof BufferInsertionNode
            && $node->getAttribute('on_missing') === BufferInsertionNode::ON_MISSING_CREATE
        ) {
            $this->buffersToOpen[] = $node->getAttribute('name');
        } else if ($node instanceof ModuleNode) {
            $this->registerModuleBuffers($node);
        }

        return $node;
    }

    public function getPriority(): int
    {
        return -10;
    }

    private function registerModuleBuffers(ModuleNode $module): void
    {
        $footer = $module->getNode('class_end');
        $footer->setNode('buffers', new TemplateClassFooterNode());

        $constructor = $module->getNode('constructor_end');
        $constructor->setNode('buffers', new TemplateConstructorNode());

        $references = array_unique($this->bufferReferences);
        $buffersToOpen = array_unique($this->buffersToOpen);

        $displayStart = $module->getNode('display_start');
        $module->setNode(
            'display_start',
            new ModuleDisplayWrapperNode(
                ModuleDisplayWrapperNode::POSITION_START,
                $displayStart,
                $references,
                $buffersToOpen,
            )
        );

        $displayEnd = $module->getNode('display_end');
        $module->setNode(
            'display_end',
            new ModuleDisplayWrapperNode(
                ModuleDisplayWrapperNode::POSITION_END,
                $displayEnd,
                $references,
                $buffersToOpen,
            )
        );
    }
}
