<?php

use Composer\InstalledVersions;

class CacheHtmlPart
{

    const START_TAG = '<!-- static -->';
    const END_TAG = '<!-- static-end -->';

    /**
     * @param string $output
     * @return string
     */
    public static function render(string $output)
    {
        if (substr(self::START_TAG, $output) && substr(self::END_TAG, $output)) {
            $stashed = $_COOKIE['static'] ? explode(",", $_COOKIE['static']) : [];
            $version = InstalledVersions::getPrettyVersion('pavlikm/cache-html-part');
            preg_match_all("/<!-- static -->([\s\S]*?)<!-- static-end -->/mi", $output, $matchAll);
            if ($matchAll) {
                for ($i = 0; $i <= count($matchAll[0]); $i++) {
                    $withTag = $matchAll[0][$i];
                    $withoutTag = $matchAll[1][$i];
                    $hash = md5($withoutTag . $version);
                    $replace = '';
                    $needStashScript = $needStashScript = false;
                    if (in_array($hash, $stashed)) {
                        $needUnstashScript = true;
                        $replace .= '<static ref="' . $hash . '"></static>';
                    } else {
                        $needStashScript = true;
                        $replace = "<!-- static " . $hash . " -->" . $withoutTag . self::END_TAG;
                    }
                    if ($i === count($matchAll[0]) - 1) {
                        $script = "/* cache-html-part " . $version . "*/";
                        if ($needStashScript) {
                            $script .= file_get_contents(__DIR__ . '/browser/stash-minified.js');
                        }
                        if ($needUnstashScript) {
                            $script .= file_get_contents(__DIR__ . '/browser/unstash-minified.js');
                        }
                        $replace .= '<script>' . $script . '</script>';
                    }
                }
                $output = str_replace($withTag, $replace, $output);
            }
            return $output;
        }
    }

}