<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends CI_Controller {

    public function index() {
        $this->userList();
    }

//    Comment Out by Ton! Not Used. 20131212
//    function userList() {
//        $this->load->model("user_model", "usr");
//        $query = $this->usr->getUserAll();
//        $user_list = $query->result();
//        $this->parser->parse('list_template', array(
//            'user_login' => 'Wenuka'
////            , 'menu' => $this->menu->loadMenu()
//            , 'menu' => $this->menu->loadMenuAuth()// Edit by Ton! 20131111
//            , 'menu_title' => 'List of User'
//            , 'datatable' => $this->datatable->genTableByArray($query, $user_list)
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//			 ONCLICK=\"openForm('user','user/userForm/','A','')\">"
//        ));
//    }

    //    Comment Out by Ton! Not Used. 20131212
//    function userForm() {
//        $this->load->helper('form');
//        $str_form = form_open('email/send');
//        $str_form .= form_fieldset('User Information');
//        $str_form .= form_input('username', 'johndoe');
//        $str_form .= form_input('username', 'Test');
//
//        $test_parameter = array(
//            array("test" => "teat pass by parameter")
//        );
//        $str_form = $this->parser->parse('form/user', array("test_parameter" => $test_parameter, "test_parse" => "teat pass by parse"), TRUE);
//
//
//        $this->parser->parse('form_template', array(
//            'user_login' => 'Wenuka'
//            , 'copyright' => COPYRIGHT
//            , 'menu_title' => 'Test'
////            , 'menu' => $this->menu->loadMenu()
//            , 'menu' => $this->menu->loadMenuAuth()// Edit by Ton! 20131111
//            , 'form' => $str_form
//            , 'button_back' => '<INPUT TYPE="button" class="button orange"	VALUE="' . BACK . '" ONCLICK="">'
//            , 'button_clear' => '<INPUT TYPE="button" class="button orange" VALUE="' . CLEAR . '" ONCLICK="">'
//            , 'button_save' => '<INPUT TYPE="button" class="button orange"	VALUE="' . SAVE . '" ONCLICK="">'
//        ));
//    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
