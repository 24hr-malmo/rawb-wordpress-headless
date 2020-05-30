<?php

    /*
    Plugin Name: RAWB Headless Helper
    Plugin URI: http://24hr.se
    Description: Saves content to a Draft Content Service and gives the possibility to push the content to live
    Version: 0.8.9
    Author: Camilo Tapia <camilo.tapia@24hr.se>
    */

    require_once('block-parsers/core-image.php');
    require_once('block-parsers/core-heading.php');
    require_once('block-parsers/core-paragraph.php');
    require_once('block-parsers/core-columns.php');
    require_once('block-parsers/core-column.php');
    require_once('block-parsers/core-list.php');
    require_once('block-parsers/core-reusable-block.php');

    class RAWBHeadless {

        private $ID;
        private $post;
        private $data;
        private $template;
        private $publish_date;
        private $skip_guid_validation;

        function __construct($ID, $post, $document_type, $publish_date) {

            $this->ID = $ID;
            $this->post = $post;
            $this->template = $document_type;
            $this->data = new stdclass();
            $this->publish_date = $publish_date;
            $this->skip_guid_validation = false;

            header("x-content-id: $ID");
            header("x-content-document-type: $document_type");
            header("x-content-parent: $post->post_parent");
            header("x-content-order: $post->menu_order");
            header("x-content-resource-last-updated: " . date('Y-m-d H:i:s.u +00:00', $publish_date));
            header("x-site-hostname:" . $_SERVER['SERVER_NAME']);

            // We have this as part of the data, as well as part of the meta, since we want to use it as content as well
            $this->data->id = $ID;

        }

        public function get() {
            return $this->data;
        }

        private function new_block($name) {
            $block = new stdclass();
            $block->blockName = $name;
            $block->blocks = array();
            return $block;
        }

        public function format_blocks($blocks, $parentBlock = 'none') {

            global $wp_filter;

            $block_list = array();

            for ($index = 0; $index < sizeof($blocks); $index++) {
                $blocks[$index]['index'] = $index;
            }

            $virtual_row = $this->new_block('virtual/row');
            $virtual_column = $this->new_block('core/column');
            array_push($virtual_row->blocks, $virtual_column);

            foreach ($blocks as $block_as_array) {

                $block = (object) $block_as_array;

                $parsed_block = new stdclass();

                $filter_name = 'headless_helper__' . $block->blockName;
                if (isset($wp_filter[$filter_name])) {
                    $parsed_block = apply_filters($filter_name, $block, array(&$this, 'format_blocks'));
                } else {
                    $parsed_block = $block;
                    $parsed_block->_unparsed = true;
                }

                $parsed_block->index = $block->index;

                if (!isset($parsed_block->blockName)) {
                    $parsed_block->blockName = $block->blockName;
                }

                if ($block->blockName) {
                    array_push($block_list, $parsed_block);
                }

            }

            return $block_list;

        }

        public function populate_page() {

            $this->data->post_title = $this->post->post_title;
            $this->data->permalink = rtrim(get_permalink($this->ID), '/');
            $this->data->guid = sprintf('%s-%d', $this->template, $this->ID);

            $post_content = str_replace('&nbsp;', ' ', $this->post->post_content);

            $blocks = parse_blocks( $post_content );

            $this->data->blocks = $this->format_blocks($blocks);

            return $this->data;

        }

        public function parseBlockCoreImage($block) {
            return wp_get_attachment_url($block->attrs['id']);
        }


        /* Function to build det correct response, convert it to json, replace all permalink host to get clean urls */
        /* publish_date should be unix timestamp */
        public function send() {
            $data = $this->data;
            return RAWBHeadless::send_json($data);
        }

        // Append data to your final object
        public function append_data($data) {
            $this->data = (object) array_merge((array) $this->data, (array) $data);
        }

        static function send_json($data, $skip_guid_validation = false) {

            if($skip_guid_validation !== true) {
                // Validate that data contains a guid!
                if (!property_exists($data, 'guid')) {
                    http_response_code(422);
                    echo json_encode(array(
                        'error' => 1,
                        'error_message' => 'Wordpress resource is missing a GUID, please implement'
                    ));
                    die();
                }
            }

            $json_string = json_encode($data);

            // $json_string = replace_hosts($json_string);

            $json_string = apply_filters('dls_replace_hosts', $json_string);
            $json_string = apply_filters('rawb_send_json_data', $json_string);

            header("content-type: application/json");

            echo $json_string;

        }





    }
