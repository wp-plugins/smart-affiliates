<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Docs')) {

    class Saff_Admin_Docs {

        function __construct() {
            
        }
        
        function saff_docs() {
            global $wpdb;
            include 'about-smart-affiliates.php';
        }
    }   

        
}

return new Saff_Admin_Docs();
