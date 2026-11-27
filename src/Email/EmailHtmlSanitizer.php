<?php

namespace Sovic\Cms\Email;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Strips email template HTML down to the allowed elements, unsafe attributes and URLs are removed.
 * Disallowed elements are unwrapped (text is kept), executable / embedded elements are removed with their content.
 */
class EmailHtmlSanitizer
{
    /**
     * Allowed elements with their allowed attributes.
     */
    private const AllowedElements = [
        'a' => ['class', 'href', 'rel', 'target', 'title'],
        'br' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'i' => [],
        'img' => ['alt', 'height', 'src', 'title', 'width'],
        'li' => [],
        'ol' => [],
        'p' => [],
        'strong' => [],
        'ul' => [],
    ];

    /**
     * Elements renamed to an allowed element.
     */
    private const RenamedElements = [
        'b' => 'strong',
        'em' => 'i',
    ];

    /**
     * Elements removed including their content.
     */
    private const RemovedElements = [
        'base',
        'button',
        'embed',
        'form',
        'frame',
        'frameset',
        'head',
        'iframe',
        'input',
        'link',
        'math',
        'meta',
        'noscript',
        'object',
        'script',
        'select',
        'style',
        'svg',
        'template',
        'textarea',
        'title',
    ];

    /**
     * Allowed link classes, used by the editor and EmailTheme (CTA button).
     */
    private const AllowedLinkClasses = [
        'ext_link',
        'int_cta_link',
    ];

    private const RootId = 'sovic-cms-email-root';

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8"?><div id="' . self::RootId . '">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById(self::RootId);
        if ($root === null) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        // libxml URL-encodes href / src, restore {placeholder} variables
        $result = preg_replace('/%7B([A-Za-z0-9_.-]+)%7D/', '{$1}', $result) ?? $result;
        // keep non-breaking spaces visible in the editor
        $result = str_replace("\u{00A0}", '&nbsp;', $result);

        return trim($result);
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMText) {
                continue;
            }
            if (!$node instanceof DOMElement) {
                // comments, processing instructions, CDATA
                $parent->removeChild($node);
                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::RemovedElements, true)) {
                $parent->removeChild($node);
                continue;
            }

            if (isset(self::RenamedElements[$tag])) {
                $node = $this->rename($node, self::RenamedElements[$tag]);
                $tag = self::RenamedElements[$tag];
            }

            $this->sanitizeChildren($node);

            if (!isset(self::AllowedElements[$tag])) {
                $this->unwrap($node);
                continue;
            }

            $this->sanitizeAttributes($node, $tag);
            if ($tag === 'img' && !$node->hasAttribute('src')) {
                $parent->removeChild($node);
            }
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::AllowedElements[$tag];

        /** @var DOMNode[] $attributes */
        $attributes = iterator_to_array($element->attributes);
        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue ?? '');
            if (!in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            $sanitized = match ($name) {
                'class' => $this->sanitizeLinkClass($value),
                'href' => $this->isSafeUrl($value, true) ? $value : null,
                'src' => $this->isSafeUrl($value, false) ? $value : null,
                'target' => $value === '_blank' ? $value : null,
                'width', 'height' => preg_match('/^\d{1,4}$/', $value) === 1 ? $value : null,
                default => $value,
            };
            if ($sanitized === null || ($sanitized === '' && $name !== 'alt')) {
                $element->removeAttribute($attribute->nodeName);
            } elseif ($sanitized !== $attribute->nodeValue) {
                $element->setAttribute($attribute->nodeName, $sanitized);
            }
        }
    }

    private function sanitizeLinkClass(string $value): ?string
    {
        $classes = preg_split('/\s+/', $value) ?: [];
        $classes = array_values(array_intersect($classes, self::AllowedLinkClasses));

        return $classes ? implode(' ', array_unique($classes)) : null;
    }

    private function isSafeUrl(string $url, bool $isLink): bool
    {
        if ($url === '') {
            return false;
        }
        // template variable, e.g. {link}
        if (str_starts_with($url, '{')) {
            return true;
        }

        // remove whitespace and control characters browsers ignore inside the scheme
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $url) ?? '');
        if (preg_match('/^([a-z][a-z0-9+.-]*):/', $normalized, $matches) !== 1) {
            // relative URL
            return true;
        }

        $allowedSchemes = $isLink ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'];

        return in_array($matches[1], $allowedSchemes, true);
    }

    private function rename(DOMElement $element, string $tag): DOMElement
    {
        $renamed = $element->ownerDocument->createElement($tag);
        while ($element->firstChild !== null) {
            $renamed->appendChild($element->firstChild);
        }
        $element->parentNode?->replaceChild($renamed, $element);

        return $renamed;
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }
        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }
}
