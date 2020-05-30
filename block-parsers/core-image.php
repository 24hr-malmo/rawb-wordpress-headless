<?php

    add_filter('headless_helper__core/image', function($block) {

        $parsed_block = new stdclass();

        // Autocopy all attributes
        foreach ($block->attrs as $key => $value) {
            $parsed_block->{$key} = $block->attrs[$key];
        }

        $image = new stdclass();

        $image_id = $block->attrs['id'];

        $image->src = wp_get_attachment_url($image_id);
        $image->alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true);
        $image->title_text = get_post_meta( $image_id, '_wp_attachment_image_title', true);

        // Create the focus point data
        // $focus_string = isset($image_info[1]) ? $image_info[1] : '0.5,0.5';
        // $focus_info = explode(',', $focus_string);
        //$image->focus = new stdclass();
        //$image->focus->x = floatval($focus_info[0]);
        //$image->focus->y = floatval($focus_info[1]);

        if (!property_exists($image, 'alt_text') || empty($image->alt_text)) {
            $image->alt_text = get_the_title($image_id);
        }

        $image->image_id = $image_id;

        if(class_exists('tcS3')){
            $tcs3_options = get_option("tcS3_options");
            $image->service = 'aws-s3';
            $image->aws_bucket = $tcs3_options['bucket'];

        } else {
            $image->service = 'local';
        }

        $parsed_block->content = $image;

        return $parsed_block;

    });
