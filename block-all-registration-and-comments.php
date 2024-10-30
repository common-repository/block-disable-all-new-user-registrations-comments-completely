<?php
/*
Plugin Name: Block & Disable All User Registrations & Comments Completely
Plugin URI: http://whoischris/wordpress-DABARACC.zip
Description:  This simple plugin blocks all users from being able to register no matter what, this also blocks comments
			  from being able to be inserted into the database.
Author: Chris Flannagan
Version: 2.0
Author URI: http://whoischris.com/
*/


/**
 * WordPress Disable & Block All Registration And Comments Completely (DABARACC) core file
 *
 * @link        http://whoischris.com
 *
 * @package    WordPress DABARACC
 * @copyright    Copyright (c) 2016, Chris Flannagan
 * @license        http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since        WordPress DABARACC 1.0
 *
 *
 */

if ( ! class_exists( 'BlockAllStuff' ) ) {
    class BlockAllStuff
    {

        public function __construct() {

            if ( get_option( "dabaracc_users" ) !== false && get_option( "dabaracc_users" ) ) {
                add_action( 'register_post', array( &$this, 'prevent_any_registration' ), 10, 3 );
            }

            if ( get_option( "dabaracc_comments" ) !== false && get_option( "dabaracc_comments" ) ) {
                add_action( 'admin_init', array( &$this, 'df_disable_comments_post_types_support' ) );
                add_action( 'admin_init', array( &$this, 'df_disable_comments_admin_menu_redirect' ) );
                add_action( 'admin_menu', array( &$this, 'df_disable_comments_admin_menu' ) );
                add_action( 'init', array( &$this, 'remove_comment_support' ), 100);
                add_action( 'comment_post', array( &$this, 'remove_any_new_comments' ), 10, 2);

                add_filter( 'comments_open', array( &$this, 'df_disable_comments_status' ), 20, 2 );
                add_filter( 'pings_open', array( &$this, 'df_disable_comments_status' ), 20, 2 );
                add_filter( 'comments_array', array( &$this, 'df_disable_comments_hide_existing_comments' ), 10, 2 );
            }

            add_action( 'admin_menu', array( &$this, 'create_menu_item' ) );
        }

        public static function activate() {
            update_option( 'dabaracc_comments', true );
            update_option( 'dabaracc_users', true );
        }

        public static function deactivate() {
         //do nothing
        }

        public function create_menu_item() {
            //Place a link to our settings page under the Wordpress "Settings" menu
            add_menu_page( 'WP Block Comments & Users', 'Block \'Em All!', 'manage_options', 'dabaracc-page', array( &$this, 'template_page' ) );
        }

        public function template_page() {
            //Include our settings page template
            include( sprintf( "%s/dabaracc.php", dirname(__FILE__) ) );
        }
        
        /* All hooks below are the primary plugin logic for blocking/disabling comments and user registration */

        function prevent_any_registration( $user_login, $user_email, $errors )
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                $errors->add( 'no_registration_allowed', '<strong>ERROR</strong>: Registration is disabled for this website.' );
            }
        }

        //when a comment is added if through some back door this will immediately delete it
        function remove_any_new_comments( $comment_ID, $comment_approved )
        {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->comments
		    WHERE comment_ID = %d",
                    $comment_ID
                )
            );
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->commentmeta
		    WHERE comment_id = %d",
                    $comment_ID
                )
            );
        }

        // Disable support for comments and trackbacks in post types
        function df_disable_comments_post_types_support()
        {
            $post_types = get_post_types();
            foreach ( $post_types as $post_type ) {
                if ( post_type_supports( $post_type, 'comments' ) ) {
                    remove_post_type_support( $post_type, 'comments' );
                    remove_post_type_support( $post_type, 'trackbacks' );
                }
            }
        }

        function remove_comment_support()
        {
            remove_post_type_support( 'post', 'comments' );
            remove_post_type_support( 'page', 'comments' );
        }

        // Close comments on the front-end
        function df_disable_comments_status()
        {
            return false;
        }

        // Hide existing comments
        function df_disable_comments_hide_existing_comments( $comments )
        {
            $comments = array();
            return $comments;
        }

        // Remove comments page in menu
        function df_disable_comments_admin_menu()
        {
            remove_menu_page( 'edit-comments.php' );
        }

        // Redirect any user trying to access comments page
        function df_disable_comments_admin_menu_redirect()
        {
            global $pagenow;
            if ( $pagenow === 'edit-comments.php' ) {
                wp_redirect( admin_url() );
                exit;
            }
        }

        // Remove comments metabox from dashboard
        function df_disable_comments_dashboard()
        {
            remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        }

        // Remove comments links from admin bar
        function df_disable_comments_admin_bar()
        {
            if (is_admin_bar_showing()) {
                remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60) ;
            }
        }

    }
}

if ( class_exists( 'BlockAllStuff' ) ) {
    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'BlockAllStuff', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'BlockAllStuff', 'deactivate' ) );

    // instantiate the plugin class
    $BlockAllStuff = new BlockAllStuff();
}
