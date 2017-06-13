<?php

class Fwt_Translate
{

    /**
     *  Core functions
     */
    function split($text, $languages) {
        $blocks = $this->get_language_blocks($text);
        return $this->split_blocks($blocks, $languages);
    }

    public function get_language_blocks($text)
    {
        $split_regex = "#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism";
        return preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    }

    public function split_blocks($blocks, $languages = [])
    {
        $result = array();
        $current_language = false;

        foreach($languages as $language) {
            $result[$language] = '';
        }

        foreach($blocks as $block) {
            // detect c-tags
            if(preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
                // detect b-tags
            } elseif(preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
                // detect s-tags @since 3.3.6 swirly bracket encoding added
            } elseif(preg_match("#^\{:([a-z]{2})\}$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
            }

            switch($block) {
                case '[:]':
                case '{:}':
                case '<!--:-->':
                    $current_language = false;
                    break;
                default:
                    // correctly categorize text block
                    if ($current_language) {
                        if (!isset($result[$current_language])) $result[$current_language] = '';
                        $result[$current_language] .= $block;
                        $current_language = false;
                    } else {
                        foreach ($languages as $language) {
                            $result[$language] .= $block;
                        }
                    }
                    break;
            }
        }

        foreach ($result as $lang => $text) {
            $result[$lang] = trim($text);
        }

        return $result;
    }

    public function split_languages($blocks)
    {
        $result = array();
        $current_language = false;

        foreach($blocks as $block) {
            // detect c-tags
            if (preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
                // detect b-tags
            } elseif (preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
                // detect s-tags @since 3.3.6 swirly bracket encoding added
            } elseif (preg_match("#^\{:([a-z]{2})\}$#ism", $block, $matches)) {
                $current_language = $matches[1];
                continue;
            }

            switch($block) {
                case '[:]':
                case '{:}':
                case '<!--:-->':
                    $current_language = false;
                    break;
                default:
                    // correctly categorize text block
                    if($current_language){
                        if(!isset($result[$current_language])) $result[$current_language]='';
                        $result[$current_language] .= $block;
                        $current_language = false;
                    }
                    break;
            }
        }

        foreach($result as $lang => $text){
            $result[$lang] = trim($text);
        }

        return $result;
    }
}