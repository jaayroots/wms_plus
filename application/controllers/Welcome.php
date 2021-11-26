<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class welcome extends CI_Controller {

    public $settings;

    public function __construct() {
        parent::__construct();
        $this->load->model("product_model", "p");
        $this->load->model("menu_model", "mnu");
        $this->load->model("authen_model", "atn");
        $this->load->model("contact_model", "con");
        $this->load->model("workflow_model", "flow");

        $this->load->library('session');
        $this->load->library('encrypt');

        $isUserLogin = $this->session->userdata("user_id");

        // $this->settings = native_session::retrieve();
        if ($isUserLogin == "") {
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        }
    }

    public function index() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $this->load->library('dash_board');
        $index_url = 'index.php/';

        ### GET Dash Board by Permission. by Ton! 20140129 ###
        #
        
        $mnu_Id = array();

        # Get User Permission Parent Menu.
        $r_mnu_auth_user = $this->mnu->get_Parent_Menu_ADM_M_User_Permission($this->session->userdata('user_id'));
        if (count($r_mnu_auth_user) > 0) :
            foreach ($r_mnu_auth_user as $value) :
                // Add menu_id to $mnu_Id[];
                $mnu_Id[] = (in_array($value->Parent_Id, $mnu_Id) ? 0 : $value->Parent_Id);
            endforeach;
        endif;

        # Get Role of User
        $role_Id = array();
        $r_user_role_members = $this->atn->get_ADM_R_UserRoleMembers(NULL, NULL, NULL, $this->session->userdata('user_id'), TRUE)->result();
        // p($r_user_role_members);
        // exit;
        if (count($r_user_role_members) > 0):
            foreach ($r_user_role_members as $value) :
                $role_Id[] = (in_array($value->UserRole_Id, $role_Id) ? 0 : $value->UserRole_Id);
            endforeach;
        endif;

        foreach (array_keys($role_Id, 0, true) as $key) :// Remove Value = 0
            unset($role_Id[$key]);
        endforeach;
        # Get Role Permission Parent Menu.
        if (!empty($role_Id)):
            $r_mnu_auth_role = $this->mnu->get_Parent_Menu_ADM_M_Role_Permission(implode(",", $role_Id));
            if (count($r_mnu_auth_role) > 0):
                // Add menu_id to $mnu_Id[];
                foreach ($r_mnu_auth_role as $value) :
                    $mnu_Id[] = (in_array($value->Parent_Id, $mnu_Id) ? 0 : $value->Parent_Id);
                endforeach;
            endif;
        endif;

        # Get Group of User
        $group_Id = array();
        $r_user_group_members = $this->atn->get_ADM_R_UserGroupMembers(NULL, NULL, NULL, $this->session->userdata('user_id'), TRUE)->result();
        if (count($r_user_group_members) > 0):
            foreach ($r_user_group_members as $value) :
                $group_Id[] = (in_array($value->UserGroup_Id, $group_Id) ? 0 : $value->UserGroup_Id);
            endforeach;
        endif;

        foreach (array_keys($group_Id, 0, true) as $key) :// Remove Value = 0
            unset($group_Id[$key]);
        endforeach;

        # Get Group Permission Parent Menu.
        if (!empty($group_Id)):
            $r_mnu_auth_group = $this->mnu->get_Parent_Menu_ADM_M_Group_Permission(implode(",", $group_Id));
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
        $data_dash_board = array();
        $url = array();
        $mnu_permission = array();
        if (!empty($mnu_Id) > 0):
            # User
            $r_user_permission = $this->atn->get_ADM_M_User_Permission($this->session->userdata('user_id'))->result();
            if (count($r_user_permission) > 0):
                foreach ($r_user_permission as $value) :
                    $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                endforeach;
            endif;

            # Role
            if (!empty($role_Id)):
                $r_role_permission = $this->atn->get_ADM_M_Role_Permission(implode(",", $role_Id))->result();
                if (count($r_role_permission) > 0):
                    foreach ($r_role_permission as $value) :
                        $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                    endforeach;
                endif;
            endif;

            # Group
            if (!empty($group_Id)):
                $r_group_permission = $this->atn->get_ADM_M_Group_Permission(implode(",", $group_Id))->result();
                if (count($r_group_permission) > 0):
                    foreach ($r_group_permission as $value) :
                        $mnu_permission[] = (in_array($value->MenuBar_Id, $mnu_permission) ? 0 : $value->MenuBar_Id);
                    endforeach;
                endif;
            endif;

            foreach (array_keys($mnu_permission, 0, true) as $key) :// Remove Value = 0
                unset($mnu_permission[$key]);
            endforeach;

            if (!empty($mnu_permission)):
                $r_Dash_Parent = $this->atn->get_Dash_Board_by_Permission(implode(",", $mnu_Id), NULL, TRUE)->result();
                if (count($r_Dash_Parent) > 0):
                    foreach ($r_Dash_Parent as $index_Dash_Parent => $value_Dash_Parent) :
                        $data_dash_board[$index_Dash_Parent]['name'] = $value_Dash_Parent->MenuBar_NameEn;
                        $data_dash_board[$index_Dash_Parent]['sum_count'] = 0;
                        $r_Dash_Child = $this->atn->get_Dash_Board_by_Permission(NULL, $value_Dash_Parent->MenuBar_Id, FALSE)->result();
                        foreach ($r_Dash_Child as $index_Dash_Child => $value_Dash_Child) :
                            $url[$value_Dash_Child->Module] = $index_url . $value_Dash_Child->NavigationUri;
                            $data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['Module'] = $value_Dash_Child->Module;

                            $str_function = $value_Dash_Child->Dash_Board_Function;
                            $r_Dash_Func = $this->dash_board->$str_function($value_Dash_Child->Module);
                            $num = count($r_Dash_Func);

                            $data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['name'] = $value_Dash_Child->MenuBar_NameEn;
                            $data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['sum_count'] = $num;
                            $data_dash_board[$index_Dash_Parent]['sum_count'] = $data_dash_board[$index_Dash_Parent]['sum_count'] + $num;
                            foreach ($r_Dash_Func as $index_Dash_Func => $value_Dash_Func):
                                if (@empty($data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['count'][$value_Dash_Func['State_NameEn']])):
                                    @$data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['count'][$value_Dash_Func['State_NameEn']] = 0;
                                endif;
                                @$data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['count'][$value_Dash_Func['State_NameEn']] = @$data_dash_board[$index_Dash_Parent]['state'][$index_Dash_Child]['count'][$value_Dash_Func['State_NameEn']] + 1;
                            endforeach;
                        endforeach;
                    endforeach;
                endif;
            endif;
        endif;
        #
        ### END GET Dash Board by Permission. by Ton! 20140129 ###
        #
        
        /* # DashBoard show Re-Location In HH
          $re_location_hh = $this->dash_board->get_workflow_re_location_in_HH();

          $set_data_for_push = array();
          $set_data_for_push['name'] = 'Re-Location In HH';
          $set_data_for_push['sum_count'] = count($re_location_hh);
          $data_dash_board[] = $set_data_for_push;
         */

        if($this->session->userdata('user_group_code') != 'CUST'):
        
            $show_dashboard = TRUE;
            
            # DashBoard show Re-Location In HH by Ton! 20140508
            $push_data = array();

            $re_location_hh = $this->dash_board->get_workflow_re_location_in_HH();
            $push_data['name'] = 'Re-Location In HH';
            $push_data['sum_count'] = count($re_location_hh);

            $state_relocate = $this->flow->get_SYS_M_Stateedge_by_Module("relocate")->result();
            if (count($state_relocate) > 0):
                foreach ($state_relocate as $index => $value) :
                    $push_data['state'][$index]['Module'] = $value->Action_Type;
                    $push_data['state'][$index]['name'] = $value->Description;

                    $relocate_hh_sum_count = count($this->dash_board->get_workflow_re_location_in_HH($value->From_State));
                    $push_data['state'][$index]['sum_count'] = $relocate_hh_sum_count;
                endforeach;
            endif;

            $data_dash_board[] = $push_data;
            
        else:
        
            $show_dashboard = FALSE;
        
        endif;

        # Add by Ton! 20140425

        $suggest_change_password = FALSE;
        if (isset($this->settings['suggest_change_password'])):
            if (($this->settings['suggest_change_password'] == "TRUE" ? TRUE : FALSE)):
                $get_User = $this->con->getUserLogin(NULL, $this->session->userdata('user_id'), NULL, FALSE)->result();
                if (count($get_User) > 0):
                    $this->load->helper("date");
                    $date_now = mdate("%Y-%m-%d %h:%i:%s", time());
                    $current_month = date("m", strtotime($date_now));
                    $current_day = date("d", strtotime($date_now));

                    $last_edit_month = "";
                    $last_edit_day = "";
                    foreach ($get_User as $user_value) :
                        $last_edit_month = date("m", strtotime($user_value->Modified_Date));
                        $last_edit_day = date("d", strtotime($user_value->Modified_Date));
                    endforeach;
                    
                    if ((int) $current_month <> (int) $last_edit_month):
                        if ((int) $current_day >= (int) $last_edit_day):
                            $suggest_change_password = TRUE;
                        endif;
                    endif;

                endif;
            endif;
        endif;
        
        $this->parser->parse('welcome_message', array(
            'user_login' => $this->session->userdata('user_id')
            , 'menu_title' => 'WMS+ <i class="icon-list icon-white"></i>'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'content' => img(array(
                'src' => 'css/images/WMSPlus.png',
                'alt' => 'Under construction!',
                'class' => 'post_images',
                'width' => '1024',
                'height' => '400',
                'title' => 'Under construction!',
                'rel' => 'lightbox',
            ))
            , 'button_add' => ''
            , 'dash_boards' => $data_dash_board
            , 'show_dashboard' => $show_dashboard
            , 'url' => $url
            , 'suggest_change_password' => $suggest_change_password
        ));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */