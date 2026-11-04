<?php

declare(strict_types=1);
namespace ProfessionalWiki\NeoWiki\EntryPoints\Processing;

/** A confirmed core sort-key warning is not a failed data computation.
 * Unknown errors remain blocked. This does not alter the HTML or hide the warning.
 */
final class ParserFailureCheck {
    public const OLD_KEY = 'NEOWIKIOLDKEY7CB32';
    public const NEW_KEY = 'NEOWIKINEWKEY9AD46';

    public static function hasFailure(string $html, ?string $sortWarningTemplate): bool {
        // Never whitelist an actual Scribunto error, even if it appears inside other markup.
        if (preg_match('/class=["\x27][^"\x27]*\bscribunto-error\b/i',$html)) { return true; }
        if ($sortWarningTemplate !== null && substr_count($sortWarningTemplate,self::OLD_KEY)===1 && substr_count($sortWarningTemplate,self::NEW_KEY)===1) {
            $plain=static fn(string $value): string => html_entity_decode(strip_tags($value),ENT_QUOTES|ENT_HTML5,'UTF-8');
            $pattern='/\A'.str_replace([self::OLD_KEY,self::NEW_KEY],['.+?','.+?'],preg_quote($plain($sortWarningTemplate),'/')).'\z/us';
            // No nested spans: an unrecognized or structurally changed warning stays conservative.
            $html=preg_replace_callback('/<span\b[^>]*\bclass=["\x27]error["\x27][^>]*>((?:(?!<\/?span\b).)*?)<\/span\s*>/is',
                static fn(array $m): string => preg_match($pattern,$plain($m[1]))===1 ? '' : $m[0],$html) ?? $html;
        }
        return preg_match('/class=["\x27][^"\x27]*\b(?:error|scribunto-error)\b/i',$html)===1;
    }
}
