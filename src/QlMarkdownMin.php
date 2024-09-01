<?php

namespace Hoochicken\QlMarkdownMin;

class QlMarkdownMin
{
    const HEADLINE_MARKER = '#';

    public static function parse(string $markdown): string
    {
        $html = explode("\n", $markdown);
        $html = array_filter($html);
        foreach ($html as $k => $line) {
            $html[$k] = self::parseLine($line);
        }

        $html = self::addListTags($html);
        return implode("\n", $html);
    }


    private static function addListTags(array $html): array
    {
        $return = [];
        $inList = false;
        $firstInList = false;
        foreach ($html as $line) {
            $currentLineIsListItem = (false !== strpos($line, '<li>'));
            if ($currentLineIsListItem && !$inList) {
                $inList = true;
                $firstInList = true;
            } elseif ($currentLineIsListItem) {
                $inList = true;
                $firstInList = false;
            }
            if (!$currentLineIsListItem && $inList) {
                $inList = false;
                $firstInList = false;
                $return[] = '</ul>';
            }
            if ($inList && $firstInList) {
                $return[] = '<ul>';
            }
            $return[] = $line;
        }
        return $return;
    }

    private static function parseLine(string $line): string
    {
        $line = trim($line);
        if (empty($line)) return '';
        if (self::isH1($line)) return self::setAsH1($line);
        if (self::isH2($line)) return self::setAsH2($line);
        if (self::isH3($line)) return self::setAsH3($line);
        $line = self::markLinks($line);
        $line = self::markStrong($line);
        $line = self::markItalic($line);
        if (self::isListItem($line)) return self::markListItem($line);
        return self::setAsParagraph($line);
    }

    private static function setAsParagraph(string $line): string
    {
        return sprintf('<p>%s</p>', $line);
    }

    private static function isH1(string $line)
    {
        return self::isHeadline($line, 1);
    }

    private static function setAsH1(string $line)
    {
        return self::setAsHeadline($line, 1);
    }

    private static function isH2(string $line)
    {
        return self::isHeadline($line, 2);
    }

    private static function setAsH2(string $line)
    {
        return self::setAsHeadline($line, 2);
    }

    private static function isH3(string $line)
    {
        return self::isHeadline($line, 3);
    }

    private static function setAsH3(string $line)
    {
        return self::setAsHeadline($line, 3);
    }

    private static function isHeadline(string $line, int $hier = 1): bool
    {
        $hash = str_repeat(self::HEADLINE_MARKER, $hier);
        $length = strlen($hash);
        $offset = 0;
        $offsetPlus = $length;
        return $hash === substr($line, $offset, $length) && self::HEADLINE_MARKER !== substr($line, $offsetPlus, 1);
    }

    private static function setAsHeadline(string $line, int $hier = 1): string
    {
        return sprintf('<h%s>%s</h%s>', $hier, trim(str_replace('#', '', $line)), $hier);
    }

    private static function markLinks(string $line): string
    {
        $regex = '/\<([a-zA-Z0-9\,\.-_\?\=\/]*)\>/';
        return preg_replace($regex, '<a href="$1" target="_blank">$1</a>', $line);
    }

    private static function markStrong(string $line): string
    {
        $regex = '/\*\*([a-zA-Z0-9\,\.-_\?\=\/]*)\*\*/';
        return preg_replace($regex, '<strong>$1</strong>', $line);
    }

    private static function markItalic(string $line): string
    {
        $regex = '/\*([a-zA-Z0-9\,\.-_\?\=\/]*)\*/';
        return preg_replace($regex, '<em>$1</em>', $line);
    }

    private static function isListItem(string $line): string
    {
        return '* ' === substr($line, 0, 2);
    }

    private static function markListItem(string $line): string
    {
        return sprintf( '<li>%s</li>', substr($line, 2));
    }
}
