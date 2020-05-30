<?php

    add_filter('headless_helper__core/paragraph', function($block) {

        $parsed_block = new stdclass();
        $doc = new DOMDocument();
        $doc->loadHTML($block->innerHTML);
        $selector = new DOMXPath($doc);
        $result = $selector->query('//p');

        // This makes sure we allow the shy tag
        $parsed_block->content = preg_replace(['/&lt;/', '/&amp;shy;/'], ['<', '&shy;'], utf8_decode($result[0]->nodeValue));
        $parsed_block->tag = 'p';

        // Autocopy all attributes
        foreach ($block->attrs as $key => $value) {
            $parsed_block->{$key} = $block->attrs[$key];
        }

        return $parsed_block;

    });

