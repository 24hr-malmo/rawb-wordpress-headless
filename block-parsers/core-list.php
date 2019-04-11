<?php

	add_filter('headless_helper__core/list', function($block) {
        $parsed_block = new stdclass();
        $parsed_block->content = $block->innerHTML;
        return $parsed_block;
    });


