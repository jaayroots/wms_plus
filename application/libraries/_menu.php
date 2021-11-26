<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Menu {

    function __construct() {
        $CI = & get_instance();
        //$CI->output->enable_profiler(FALSE);
        $CI->load->library('session');
        $controller = $CI->uri->segment(1);
        $user_id = $CI->session->userdata("user_id");
        $router = $CI->router->fetch_class();
        if (!$user_id && $router != "authen") :
            if (!$_COOKIE['connection']) : // check $_COOKIE if have connection from HH allow connection if not reject : by ball : 2013-01-10
                redirect('authen/login');
            endif;
        endif;
    }

    function genMenu($menu_list) {
        usleep(10000); // Set sleep for some bug menu disapear (micro second)		
        if (count($menu_list) <= 0)
            return;
        $str_menu = "<ul id=\"menu\"> ";
        $str_menu .= "<li><a href='" . site_url() . "' >HOME <i class='icon-white icon-home'></i></a></li>";
        foreach ($menu_list as $menu) {
            if ("0" == $menu->lavel) {
                $str_menu .= "<li><a href=\"" . site_url() . "/" . $menu->NavigationUri . "\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
            } else if (("1" == $menu->lavel) && ($menu->count_sub > 0)) {  # Case : Sub Menu  1 Lavel
                $str_menu .= "<li><a href=\"#\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
                $str_menu .= "<div class=\"dropdown_1column\">";
                $str_menu .= "<div class=\"col_1\">";
                $str_menu .= "<ul class=\"simple\">";
                foreach ($menu->sub_menu as $sub_menu) {
                    $str_menu .= "<li><a href=\"" . site_url() . "/" . $sub_menu->NavigationUri . "\">" . $sub_menu->MenuBar_NameEn . "</a></li>";
                }
                $str_menu .= "</ul>";
                $str_menu .= "</div>";
                $str_menu .= "</div>";
                $str_menu .= "</li>";
            } else if (("2" == $menu->lavel) && ($menu->count_sub > 0)) {
                $str_menu .= "<li><a href=\"#\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
                $str_menu .= "<div class=\"dropdown_" . $menu->count_sub . "columns\">";
                foreach ($menu->sub_menu as $sub_menu) {
                    $str_menu .= "<div class=\"col_1\">";
                    $str_menu .= "<h3>" . $sub_menu->MenuBar_NameEn . "</h3>";
                    $str_menu .= "<ul>";
                    foreach ($sub_menu->sub_menu as $last_menu) {
                        $str_menu .= "<li><a href=\"" . site_url() . "/" . $last_menu->NavigationUri . "\">" . $last_menu->MenuBar_NameEn . "</a></li>";
                    }
                    $str_menu .= "</ul>";
                    $str_menu .= "</div>";
                }
                $str_menu .= "</div>";
                $str_menu .= "</li>";
            }
        }

        $str_menu .= "<li><a href='#myModal' data-toggle='modal' id='EditPass' ONCLICK='showModalPassword()'>Change Password</a></li>"; // Add by Ton! 20131225 For user can edit password by myself.
        $str_menu .= "<li class=\"menu_right\" ><a href='" . base_url() . "index.php/authen/logout'>Logout<i class='icon-white icon-user'></i></a></li>";
        $str_menu .= "</ul>";
        return $str_menu;
    }

    function loadMenuAuth() {// Add by Ton! 20131108 Auth Menu. New Version.
        $CI = & get_instance();
        $CI->load->model("menu_model", "mnu");
        $CI->load->model("authen_model", "atn");

        $main_menu = NULL;

        $r_menu_auth_group = $CI->mnu->getMenuAuthGroup($CI->session->userdata('user_id'));
        if (count($r_menu_auth_group) == 0) :
            $CI->session->sess_destroy();
            redirect('authen');
            exit();
        endif;

        if (count($r_menu_auth_group) > 0):
            $menu_bar_id = array();
            foreach ($r_menu_auth_group as $value) :
                $menu_bar_id[] = $value->MenuBar_Id;
            endforeach;
            $r_menu_auth = $CI->mnu->getMenuAuthParent(implode(",", $menu_bar_id));
            
            if (count($r_menu_auth) > 0):
                $main_menu = $r_menu_auth;
                foreach ($main_menu as $rows):
                    $lavel = 0;
                    $q_sub_menu = $CI->mnu->getMenuAuthChild($rows->MenuBar_Id);
                    $r_sub_menu = $q_sub_menu;
                    if (count($r_sub_menu) > 0):
                        $lavel = 1;
                        foreach ($r_sub_menu as $sub_rows):
                            $q_last_menu = $CI->mnu->getMenuAuthChild($sub_rows->MenuBar_Id);
                            $r_last_menu = $q_last_menu;
                            if (count($r_last_menu) > 0):
                                $lavel = 2;
                            endif;
                            $sub_rows->count_sub = count($r_last_menu);
                            $sub_rows->sub_menu = $r_last_menu;
                        endforeach;
                    endif;
                    $rows->lavel = $lavel;
                    $rows->count_sub = count($r_sub_menu);
                    $rows->sub_menu = $r_sub_menu;
                endforeach;
            endif;
        endif;
        return $this->genMenu($main_menu);
    }

    # Not Used. Old Version
//    function loadMenu() {
//        $CI = & get_instance();
//        $CI->load->model("menu_model", "mnu");
//        $user_id = $CI->session->userdata('user_id');
//        $main_menu = $CI->mnu->getMenuParent($user_id);
//        if (count($main_menu) > 0) {
//            foreach ($main_menu as $rows) {
//                $lavel = 0;
//                $sub_menu = $CI->mnu->getMenuChild($rows->MenuBar_Id);
//                if (count($sub_menu) > 0) {
//                    $lavel = 1;
//                    foreach ($sub_menu as $sub_rows) {
//                        $last_menu = $CI->mnu->getMenuChild($sub_rows->MenuBar_Id);
//                        if (count($last_menu) > 0) {
//                            $lavel = 2;
//                        }
//                        $sub_rows->count_sub = count($last_menu);
//                        $sub_rows->sub_menu = $last_menu;
//                    }
//                }
//                $rows->lavel = $lavel;
//                $rows->count_sub = count($sub_menu);
//                $rows->sub_menu = $sub_menu;
//            }
//        }
//        return $this->genMenu($main_menu);
//    }
}
