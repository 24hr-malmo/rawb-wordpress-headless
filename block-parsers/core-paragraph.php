<?php

    add_filter('headless_helper__core/paragraph', function($block) {

        $parsed_block = new stdclass();
        $value = preg_replace('/<p>|<\/p>/', '', $block->innerHTML);

        // This makes sure we allow the shy tag
        // $parsed_block->content = preg_replace(['/&lt;/', '/&amp;shy;/'], ['<', '&shy;'], utf8_decode($value));
        $parsed_block->content = preg_replace(['/&lt;/', '/&amp;shy;/'], ['<', '&shy;'], $value);
        $parsed_block->tag = 'p';

        // Autocopy all attributes
        foreach ($block->attrs as $key => $value) {
            $parsed_block->{$key} = $block->attrs[$key];
        }

        return $parsed_block;

    });

