<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name:	Keyphrase Auto Link Manager
Plugin URI: 	https:/github.com/timbral/
Description: 	Management tool with filtering feature that finds phrases in content and replaces them with user defined links.
Author: 		Parker Westfall
Author URI: 	https:/github.com/timbral/
Text Domain:	keyphrase-auto-link-manager
Domain Path:	/languages
Version: 		1.0
License:		GPL2
*/

/*  Copyright 2018 Parker Westfall  (timbralpw@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/
//enqueue script and styles
function enqueue_keyphrase_auto_link_manager_scripts() {
}

add_action('admin_enqueue_scripts', 'enqueue_keyphrase_auto_link_manager_scripts');

require_once( plugin_dir_path( __FILE__ ) . '/includes/options-keyphrase-auto-link-manager.php');
