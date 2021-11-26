<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class menu_auth {

    function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        $controller = $CI->uri->segment(1);
        $user_id = $CI->session->userdata("user_id");
        $router = $CI->router->fetch_class();
        if (!$user_id && (!in_array($router, array("authen", "sendmail")))) :
            if (!$_COOKIE['connection']) : // check $_COOKIE if have connection from HH allow connection if not reject : by ball : 2013-01-10
                redirect('authen/login');
            endif;
        endif;
    }

    public function genMenu($menu_list) {
        usleep(10000); // Set sleep for some bug menu disapear (micro second)		
        if (count($menu_list) <= 0)
            return;
        $str_menu = "<ul id=\"menu\"> ";
        $str_menu .= "<li><a href='" . site_url() . "' >HOME <i class='icon-white icon-home'></i></a></li>";
        foreach ($menu_list as $menu) :
            if ("0" == $menu->lavel) :
                $str_menu .= "<li><a href=\"" . site_url() . "/" . $menu->NavigationUri . "\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
            elseif (("1" == $menu->lavel) && ($menu->count_sub > 0)) :  # Case : Sub Menu  1 Lavel
                $str_menu .= "<li class='head_menu' title='{$menu->MenuBar_NameTh}'><a href=\"#\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
                $str_menu .= "<div class=\"dropdown_1column\">";
                $str_menu .= "<div class=\"col_1\">";
                $str_menu .= "<ul class=\"simple\">";
                $flag = 0;
                foreach ($menu->sub_menu as $sub_menu) :
                    if ($flag == 0) :
                        $str_menu .= "<li title='{$sub_menu->MenuBar_NameTh}'><a href=\"" . site_url() . "/" . $sub_menu->NavigationUri . "\">" . $sub_menu->MenuBar_NameEn . "</a></li>";
                    endif;
                    if ($sub_menu->MenuBar_NameEn == "<hr/>") :
                        $flag = 1;
                    else :
                        $flag = 0;
                    endif;
                endforeach;
                $str_menu .= "</ul>";
                $str_menu .= "</div>";
                $str_menu .= "</div>";
                $str_menu .= "</li>";
            elseif (("2" == $menu->lavel) && ($menu->count_sub > 0)) :
                $str_menu .= "<li><a href=\"#\" class=\"drop\">" . $menu->MenuBar_NameEn . "</a>";
                $str_menu .= "<div class=\"dropdown_" . $menu->count_sub . "columns\">";
                foreach ($menu->sub_menu as $sub_menu) :
                    $str_menu .= "<div class=\"col_1\">";
                    $str_menu .= "<h3>" . $sub_menu->MenuBar_NameEn . "</h3>";
                    $str_menu .= "<ul>";
                    $flag = 0;
                    foreach ($sub_menu->sub_menu as $last_menu) :
                        $str_menu .= "<li><a href=\"" . site_url() . "/" . $last_menu->NavigationUri . "\">" . $last_menu->MenuBar_NameEn . "</a></li>";
                    endforeach;
                    $str_menu .= "</ul>";
                    $str_menu .= "</div>";
                endforeach;
                $str_menu .= "</div>";
                $str_menu .= "</li>";
            endif;
        endforeach;

        //$str_menu .= "<li><a href='#myModal' data-toggle='modal' id='EditPass' ONCLICK='showModalPassword()'>Change Password</a></li>"; // Add by Ton! 20131225 For user can edit password by myself.
        //$str_menu .= "<li class=\"menu_right\" ><a href='" . base_url() . "index.php/authen/logout'>Logout<i class='icon-white icon-user'></i></a></li>";
        $str_menu .= "</ul>";
        return $str_menu;
    }

    public function loadMenuAuth() {// Add by Ton! 20131108 Auth Menu. New Version.
        $CI = & get_instance();
        $CI->load->model("menu_model", "mnu");
        $CI->load->model("authen_model", "atn");

        $main_menu = NULL;
        $mnu_Id = array();
        # Get User Permission Parent Menu.
        $r_mnu_auth_user = $CI->mnu->get_Parent_Menu_ADM_M_User_Permission($CI->session->userdata('user_id'));
        if (count($r_mnu_auth_user) > 0) :
            foreach ($r_mnu_auth_user as $value) :
                // Add menu_id to $mnu_Id[];
                $mnu_Id[] = (in_array($value->Parent_Id, $mnu_Id) ? 0 : $value->Parent_Id);
            endforeach;
        endif;
        # Get Role of User
        $role_Id = $this->get_RoleId_by_User($CI->session->userdata('user_id'));
        # Get Role Permission Parent Menu.
        if (count($role_Id) > 0):
            $r_mnu_auth_role = $CI->mnu->get_Parent_Menu_ADM_M_Role_Permission(implode(",", $role_Id));
            if (count($r_mnu_auth_role) > 0):
                // Add menu_id to $mnu_Id[];
                foreach ($r_mnu_auth_role as $value) :
                    $mnu_Id[] = (in_array($value->Parent_Id, $mnu_Id) ? 0 : $value->Parent_Id);
                endforeach;
            endif;
        endif;

        # Get Group of User        
        $group_Id = $this->get_GroupId_by_User($CI->session->userdata('user_id'));

        # Get Group Permission Parent Menu.
        if (count($group_Id) > 0):
            $r_mnu_auth_group = $CI->mnu->get_Parent_Menu_ADM_M_Group_Permission(implode(",", $group_Id));
            if (count($r_mnu_auth_group) > 0):
                // Add menu_id to $mnu_Id[];
                foreach ($r_mnu_auth_group as $value) :
                    $mnu_Id[] = (in_array($value->Parent_Id, $mnu_Id) ? 0 : $value->Parent_Id);
                endforeach;
            endif;
        endif;

        foreach (array_keys($mnu_Id, 0, true) as $key) :// Remove Value = 0
            unset($mnu_Id[$key]);
        endforeach;

        # Get Menu Permission.
        $mnu_permission = array();
        if (count($mnu_Id) > 0):
            # User
            $r_user_permission = $CI->atn->get_ADM_M_User_Permission($CI->session->userdata('user_id'))->result();
            if (count($r_user_permission) > 0):
                foreach ($r_user_permission as $value) :
                    $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                endforeach;
            endif;

            # Role    
            if (!empty($role_Id)):
                $r_role_permission = $CI->atn->get_ADM_M_Role_Permission(implode(",", $role_Id))->result();
                if (count($r_role_permission) > 0):
                    foreach ($r_role_permission as $value) :
                        $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                    endforeach;
                endif;
            endif;

            # Group
            if (!empty($group_Id)):
                $r_group_permission = $CI->atn->get_ADM_M_Group_Permission(implode(",", $group_Id))->result();
                if (count($r_group_permission) > 0):
                    foreach ($r_group_permission as $value) :
                        $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                    endforeach;
                endif;
            endif;
        endif;

        foreach (array_keys($mnu_permission, 0, true) as $key) :// Remove Value = 0
            unset($mnu_permission[$key]);
        endforeach;

        if (count($mnu_Id) <= 0 || count($mnu_permission) <= 0):
            $datestring = "%Y-%m-%d %h:%i:%s";
            $time = time();
            $CI->load->helper("date");
            $human = mdate($datestring, $time);

            $whereUser['Log_Id'] = $CI->session->userdata('log_id');

            $dataUser['User_Login'] = FALSE;
            $dataUser['Logout_Time'] = time();
            $dataUser['Logout_Date'] = $human;

            if (!empty($whereUser['Log_Id'])) :
                $CI->atn->save_SYS_L_UserLogin('upd', $dataUser, $whereUser);
            endif;

            $CI->session->sess_destroy();
            redirect('authen?e=Permission Denied');
            exit();
        elseif (count($mnu_Id) > 0 || count($mnu_permission) > 0):
            $r_menu_auth = $CI->mnu->getMenuAuthParent(implode(",", $mnu_Id));
            if (count($r_menu_auth) > 0):
                $main_menu = $r_menu_auth;
                foreach ($main_menu as $rows):
                    $lavel = 0;

                    $r_sub_menu = $CI->mnu->getMenuAuthChild($rows->MenuBar_Id, implode(",", $mnu_permission));
                    if (count($r_sub_menu) > 0):
                        $lavel = 1;
                        foreach ($r_sub_menu as $sub_rows):
                            $r_last_menu = $CI->mnu->getMenuAuthChild($sub_rows->MenuBar_Id, implode(",", $mnu_permission));
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

    public function get_RoleId_by_User($UserLogin_Id) {// Add by Ton! 20140124
        $CI = & get_instance();
        $CI->load->model("authen_model", "atn");

        $role_Id = array();
        if (!empty($UserLogin_Id)):
            $r_user_role_members = $CI->atn->get_ADM_R_UserRoleMembers(NULL, NULL, NULL, $UserLogin_Id, TRUE)->result();
            if (count($r_user_role_members) > 0):
                foreach ($r_user_role_members as $value) :
                    $role_Id[] = (in_array($value->UserRole_Id, $role_Id) ? 0 : $value->UserRole_Id);
                endforeach;
            endif;
        endif;

        foreach (array_keys($role_Id, 0, true) as $key) :// Remove Value = 0
            unset($role_Id[$key]);
        endforeach;

        return $role_Id;
    }

    public function get_GroupId_by_User($UserLogin_Id) {// Add by Ton! 20140124
        $CI = & get_instance();
        $CI->load->model("authen_model", "atn");

        $group_Id = array();
        if (!empty($UserLogin_Id)):
            $r_user_group_members = $CI->atn->get_ADM_R_UserGroupMembers(NULL, NULL, NULL, $UserLogin_Id, TRUE)->result();
            if (count($r_user_group_members) > 0):
                foreach ($r_user_group_members as $value) :
                    $group_Id[] = (in_array($value->UserGroup_Id, $group_Id) ? 0 : $value->UserGroup_Id);
                endforeach;
            endif;
        endif;

        foreach (array_keys($group_Id, 0, true) as $key) :// Remove Value = 0
            unset($group_Id[$key]);
        endforeach;

        return $group_Id;
    }

    function get_action_parmission($UserLogin_Id = NULL, $mnu_NavigationUri = NULL) {// Edit by Ton! 20140131
        $CI = & get_instance();
        $CI->load->model("authen_model", "atn");
        $CI->load->model("menu_model", "mnu");

        $action_parmission = array();
        if (!empty($UserLogin_Id) && !empty($mnu_NavigationUri)):
            $role_Id = $this->get_RoleId_by_User($UserLogin_Id); # Get Role by User. #
            $group_Id = $this->get_GroupId_by_User($UserLogin_Id); # Get Group by User. #
            $r_MenuBarId = $CI->mnu->get_menu_id_by_uri($mnu_NavigationUri); # Get MenuBar_Id #
            $MenuBarId = NULL;
            foreach ($r_MenuBarId as $value) :
                $MenuBarId = $value->MenuBar_Id;
            endforeach;
            if (!empty($MenuBarId)):
                # Parameter Optional : UserLogin_Id, UserRole_Id, UserGroup_Id, MenuBar_Id # 
//                $result_permission = $CI->atn->get_permission_menu($UserLogin_Id, implode(",", $role_Id), implode(",", $group_Id), $MenuBarId)->result();
                $result_permission = $CI->atn->get_permission_menu($UserLogin_Id, implode(",", $role_Id), implode(",", $group_Id), $MenuBarId);
                if (count($result_permission) > 0):
                    # View = -1, Add = -2, Edit = -3, Delete = -4 #
                    foreach ($result_permission as $value) :
                        if (!empty($value->Edge_Id)):
                            $action_parmission[] = $value->Edge_Id;
                        else:
                            $action_parmission[] = $value->Action_Id;
                        endif;
                    endforeach;
                endif;
            endif;

            $action = array(); # View = -1, Add = -2, Edit = -3, Delete = -4 #
            if (!empty($action_parmission)):
                if (in_array("-1", $action_parmission) && in_array("-3", $action_parmission) && in_array("-4", $action_parmission)):
                    $action = array('VIEW', 'EDIT', 'DEL');
                elseif (in_array("-1", $action_parmission) && in_array("-3", $action_parmission)):
                    $action = array('VIEW', 'EDIT');
                elseif (in_array("-1", $action_parmission)):
                    $action = array('VIEW');
                elseif (in_array("-3", $action_parmission)):
                    $action = array('EDIT');
                elseif (in_array("-4", $action_parmission)):
                    $action = array('DEL');
                endif;
            endif;
            $action_parmission['action_button'] = $action;
        endif;

        return $action_parmission; // action of menu.
    }

}
