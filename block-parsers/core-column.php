<?php

    add_filter('headless_helper__core/column', function($block, $format_blocks) {

        $parsed_block = new stdclass(); 
        $parsed_block->blocks = $format_blocks($block->innerBlocks, $block->blockName);
        return $parsed_block;

    }, 10, 2);
 

 
