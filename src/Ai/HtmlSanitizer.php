<?php

namespace Sovic\Cms\Ai;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Removes executable / unsafe markup from AI generated HTML, keeps structure and classes (components rely on them).
 */
class HtmlSanitizer
{
    private const RemovedElements = [
        'base',
        'embed',
        'form',
        'head',
        'input',
        'link',
        'meta',
        'object',
        'script',
        'select',
        'style',
        'textarea',
        'title',
    ];

    /**
     * Elements removed while their children are kept.
     */
    private const UnwrappedElements = [
        'html',
        'body',
    ];

    private const UrlAttributes = [
        'action',
        'formaction',
        'href',
        'poster',
        'src',
        'xlink:href',
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8"?><div id="sovic-cms-ai-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sovic-cms-ai-root');
        if ($root === null) {
            return '';
        }

        $xpath = new DOMXPath($document);
        foreach (self::UnwrappedElements as $tag) {
            foreach (iterator_to_array($xpath->query('.//' . $tag, $root)) as $node) {
                while ($node->firstChild !== null) {
                    $node->parentNode?->insertBefore($node->firstChild, $node);
                }
                $node->parentNode?->removeChild($node);
            }
        }
        foreach (self::RemovedElements as $tag) {
            foreach (iterator_to_array($xpath->query('.//' . $tag, $root)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
        foreach (iterator_to_array($xpath->query('.//comment()', $root)) as $node) {
            $node->parentNode?->removeChild($node);
        }
        foreach (iterator_to_array($xpath->query('.//*', $root)) as $element) {
            if ($element instanceof DOMElement) {
                $this->sanitizeAttributes($element);
            }
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        if ($tag === 'iframe') {
            $src = trim($element->getAttribute('src'));
            if (!str_starts_with(strtolower($src), 'https://')) {
                $element->parentNode?->removeChild($element);

                return;
            }
        }

        /** @var DOMNode[] $attributes */
        $attributes = iterator_to_array($element->attributes);
        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcdoc'], true)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }
            if (in_array($name, self::UrlAttributes, true) && !$this->isSafeUrl($attribute->nodeValue ?? '', $tag, $name)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    private function isSafeUrl(string $url, string $tag, string $attribute): bool
    {
        // remove whitespace and control characters browsers ignore inside the scheme
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $url) ?? '');
        if (str_starts_with($normalized, 'javascript:') || str_starts_with($normalized, 'vbscript:')) {
            return false;
        }
        if (str_starts_with($normalized, 'data:')) {
            return $tag === 'img' && $attribute === 'src' && str_starts_with($normalized, 'data:image/') && !str_starts_with($normalized, 'data:image/svg');
        }

        return true;
    }
}
