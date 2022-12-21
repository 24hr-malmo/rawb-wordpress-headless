<?php

	add_filter('headless_helper__core/list', function($block) {
        $parsed_block = new stdclass();

        // Autocopy all attributes
        foreach ($block->attrs as $key => $value) {
            $parsed_block->{$key} = $block->attrs[$key];
        }

        if ($block->innerBlocks) {
            $parsed_block->content = $block->innerBlocks;
        } else {
            $parsed_block->content = $block->innerHTML;
        }
        
        return $parsed_block;
    });


