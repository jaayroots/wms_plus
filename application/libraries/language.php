<?php
#add by kik 
#23-09-2013

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class language {
    function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        $CI->lang->load('standard', $CI->config->item('lang3digit'));
        $CI->lang->load($CI->config->item('language_config'), $CI->config->item('lang3digit'));
    }

}