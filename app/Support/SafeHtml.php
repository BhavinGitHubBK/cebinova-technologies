<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class SafeHtml
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a'];

    public static function clean(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') return null;
        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $document->getElementsByTagName('div')->item(0);
        if (! $root) return null;
        self::sanitizeChildren($root);
        $output = '';
        foreach ($root->childNodes as $child) $output .= $document->saveHTML($child);
        return trim($output) ?: null;
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (! in_array(strtolower($child->tagName), self::TAGS, true)) {
                    while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes ?? []) as $attribute) {
                    if ($child->tagName !== 'a' || ! in_array($attribute->name, ['href', 'title'], true)) $child->removeAttribute($attribute->name);
                }
                if ($child->tagName === 'a') {
                    $href = trim($child->getAttribute('href'));
                    if (! preg_match('/^(https?:\/\/|mailto:)/i', $href)) $child->removeAttribute('href');
                    else { $child->setAttribute('rel', 'noopener noreferrer'); $child->setAttribute('target', '_blank'); }
                }
            }
            self::sanitizeChildren($child);
        }
    }
}
