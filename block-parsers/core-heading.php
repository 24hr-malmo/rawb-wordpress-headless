<?php

    add_filter('headless_helper__core/heading', function($block) {

        $parsed_block = new stdclass();
        $parsed_block->content = $block->innerHTML;

        // Autocopy all attributes
        foreach ($block->attrs as $key => $value) {
            $parsed_block->{$key} = $block->attrs[$key];
        }

        return $parsed_block;

    });
