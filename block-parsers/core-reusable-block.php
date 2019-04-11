<?php

	add_filter('headless_helper__core/block', function($block) {
        $parsed_block = new stdclass();
        $parsed_block->__autopopulate = true;
        $slug = get_post_field( 'post_name', get_post($block->attrs['ref']) );
        $parsed_block->__reference = "/wp_block/$slug";
        return $parsed_block;
    });


