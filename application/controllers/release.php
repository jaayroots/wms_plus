<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Release extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        //p($this->session->all_userdata());
        $this->parser->parse('welcome_message', array(
            'user_login' => ''
            , 'menu_title' => 'WMS+ <i class="icon-list icon-white"></i>'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'content' => img(array(
                'src' => 'css/images/release_note.png',
                'alt' => 'Release Note',
                'class' => 'post_images',
                'title' => 'Release Note',
                'rel' => 'lightbox',
            ))
            , 'button_add' => ''
        ));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */