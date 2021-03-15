<?php
namespace pavlikm;
//use Composer\InstalledVersions;

class CacheHtmlPart
{

    /**
     * @param string $output
     * @return string
     */
    public static function render(string $output)
    {
        $startTag = '<!-- static -->';
        $endTag = '<!-- static-end -->';
        if (strpos($output, $startTag) && strpos($output, $endTag)) {
            $stashed = isset($_COOKIE['static']) ? explode(",", $_COOKIE['static']) : [];
            $version = '1.0.0'; //InstalledVersions::getPrettyVersion('pavlikm/cache-html-part');
            preg_match_all("/<!-- static -->([\s\S]*?)<!-- static-end -->/mi", $output, $matchAll);
            if ($matchAll) {
                \CoreObject::var_dump($matchAll);
                for ($i = 0; $i < count($matchAll[0]); $i++) {
                    $withTag = $matchAll[0][$i];
                    $withoutTag = $matchAll[1][$i];
                    $hash = md5($withoutTag . $version);
                    $replace = '';
                    $needStashScript = $needUnstashScript = false;
                    if (in_array($hash, $stashed)) {
                        $needUnstashScript = true;
                        $replace .= '<static ref="' . $hash . '"></static>';
                    } else {
                        $needStashScript = true;
                        $replace .= "<!-- static " . $hash . " -->" . $withoutTag . $endTag;
                    }
                    if ($i === count($matchAll[0]) - 1) {
                        $script = "/* cache-html-part " . $version . "*/";
                        if ($needStashScript) {
                            $script .= file_get_contents(__DIR__ . '/stash-minified.js');
                        }
                        if ($needUnstashScript) {
                            $script .= file_get_contents(__DIR__ . '/unstash-minified.js');
                        }
                        $replace .= '<script>' . $script . '</script>';
                    }
                    $output = str_replace($withTag, $replace, $output);
                }
            }
        }
        return $output;
    }

}