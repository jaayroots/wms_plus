<?php

/*
 * Create by Ton! 20131115
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class menu_bar extends CI_Controller {

    public $mnu_NavigationUri_PC; // NavigationUri @Table ADM_M_MenuBar.
    public $mnu_NavigationUri_HH; // NavigationUri @T able ADM_M_MenuBar.

    public function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("workflow_model", "flow");
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri_PC = "menu_bar/get_menu_bar_pc_list";
        $this->mnu_NavigationUri_HH = "menu_bar/get_menu_bar_hh_list";
    }

    # PC ---------------------------------------------------------------------------------------------------------------------------------------------
    # ------------------------------------------------------------------------------------------------------------------------------------------------

    function get_menu_bar_pc_list() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $this->cache->memcached->clean();
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_PC);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_menu = $this->atn->get_ADM_M_MenuBar_List('PC');
        $r_menu = $q_menu->result();

        $column = array("ID", "Menu Bar Code", "Menu Name EN", "Menu Name TH", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_menu, $r_menu, $column, "menu_bar/menu_pc_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth();
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Menu PC'
            , 'datatable' => $datatable
            , 'button_add' => ''
        ));
    }

    function menu_pc_processec() {// select processec (Add, Edit Delete) Menu PC.
        $data = $this->input->post();
        $this->cache->memcached->clean();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->menu_pc_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->menu_pc_management($mode, $Id);
        elseif ($mode == "D"): // DELETE.
            $Id = $data['id'];
            $result_inactive = $this->menu_set_active($Id, FALSE, TRUE);
            if ($result_inactive === TRUE):
                $this->cache->memcached->clean();
                echo "<script type='text/javascript'>alert('Inactive Menu Success.')</script>";
                redirect('menu_bar/get_menu_bar_pc_list', 'refresh');
            else:
                $this->cache->memcached->clean();
                echo "<script type='text/javascript'>alert('Inactive Menu Unsuccess.')</script>";
                redirect('menu_bar/get_menu_bar_pc_list', 'refresh');
            endif;
        endif;
    }

    function menu_pc_management($mode, $MenuBarId) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_PC);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $MenuBar_Id = $MenuBarId;
        $Parent_Id = '';
        $MenuBar_Code = '';
        $MenuBar_NameEn = '';
        $MenuBar_NameTh = '';
        $Menu_Type = '';
        $Active = '';

        $MenuBar_List = NULL;
        if (!empty($MenuBar_Id)):
            $r_menu_parent = $this->atn->get_ADM_M_MenuBar(TRUE, NULL, $MenuBar_Id, NULL, FALSE, 'PC')->result();
            if (count($r_menu_parent) > 0):
                foreach ($r_menu_parent as $value) :
                    $MenuBar_Code = $value->MenuBar_Code;
                    $Parent_Id = $value->Parent_Id;
                    $MenuBar_NameEn = $value->MenuBar_NameEn;
                    $MenuBar_NameTh = $value->MenuBar_NameTh;
                    $Menu_Type = $value->Menu_Type;
                    $Active = $value->Active;
                endforeach;

                $r_menu_child = $this->atn->get_ADM_M_MenuBar(FALSE, $MenuBar_Id, NULL, NULL, FALSE, 'PC')->result();
                if (count($r_menu_child) > 0):
                    $MenuBar_List = $r_menu_child;
                endif;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Menu PC.');

        $str_form.=$this->parser->parse('form/menu_pc_master', array("mode" => $mode, "MenuBar_Id" => $MenuBar_Id
            , "MenuBar_Code" => $MenuBar_Code, "MenuBar_NameEn" => $MenuBar_NameEn, "MenuBar_NameTh" => $MenuBar_NameTh
            , "Menu_Type" => $Menu_Type, "Active" => $Active, "MenuBar_List" => $MenuBar_List), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Edit Menu PC'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => ''//'<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    # PC ---------------------------------------------------------------------------------------------------------------------------------------------
    # END --------------------------------------------------------------------------------------------------------------------------------------------
    # 
    # 
    # 
    # HH ---------------------------------------------------------------------------------------------------------------------------------------------
    # ------------------------------------------------------------------------------------------------------------------------------------------------

    function get_menu_bar_hh_list() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $this->cache->memcached->clean();
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_HH);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_menu = $this->atn->get_ADM_M_MenuBar_List('HH');
        $r_menu = $q_menu->result();

        $column = array("ID", "Menu Bar Code", "Menu Name EN", "Menu Name TH", "Action");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_menu, $r_menu, $column, "menu_bar/menu_hh_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Menu HH'
            , 'datatable' => $datatable
            , 'button_add' => ''
        ));
    }

    function menu_hh_processec() {// select processec (Add, Edit Delete) Menu HH.
        $data = $this->input->post();
        $this->cache->memcached->clean();
        $mode = $data['mode'];
        if ($mode == "A") :// ADD.
            $Id = "";
            $this->menu_hh_management($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// VIEW & EDIT.
            $Id = $data['id'];
            $this->menu_hh_management($mode, $Id);
        elseif ($mode == "D") :// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->menu_set_active($Id, FALSE, FALSE);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Inactive Menu Success.')</script>";
                redirect('menu_bar/get_menu_bar_hh_list', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Inactive Menu Unsuccess.')</script>";
                redirect('menu_bar/get_menu_bar_hh_list', 'refresh');
            endif;
        endif;
    }

    function menu_hh_management($mode, $MenuBarId) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_HH);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $MenuBar_Id = '';
        $Parent_Id = '';
        $MenuBar_Code = '';
        $MenuBar_NameEn = '';
        $MenuBar_NameTh = '';
        $MenuBar_Desc = '';
        $NavigationUri = '';
        $Sequence = '';
        $Icon_Image_Id = '';
        $IsUri = '';
        $Menu_Type = '';
        $Active = '';
        $ImageName = '';

        $Image_List = NULL;

        if (!empty($MenuBarId)):
            $r_menu_parent = $this->atn->get_ADM_M_MenuBar(TRUE, NULL, $MenuBarId, NULL, FALSE, 'HH')->result();
            if (count($r_menu_parent) > 0):
                foreach ($r_menu_parent as $value) :
                    $MenuBar_Id = $MenuBarId;
                    $Parent_Id = $value->Parent_Id;
                    $MenuBar_Code = $value->MenuBar_Code;
                    $MenuBar_NameEn = $value->MenuBar_NameEn;
                    $MenuBar_NameTh = $value->MenuBar_NameTh;
                    $MenuBar_Desc = $value->MenuBar_Desc;
                    $NavigationUri = $value->NavigationUri;
                    $Sequence = $value->Sequence;
                    $Icon_Image_Id = $value->Icon_Image_Id;
                    $IsUri = $value->IsUri;
                    $Menu_Type = $value->Menu_Type;
                    $Active = $value->Active;
                    $ImageName = $value->ImageName;
                endforeach;
            endif;
        endif;

        $r_Image = $this->atn->get_ADM_M_ImageItem()->result();
        $optionImage = genOptionDropdown($r_Image, "IMAGE");
        if (count($r_Image) > 0):
            $Image_List = $optionImage;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Menu HH.');

        $str_form.=$this->parser->parse('form/menu_hh_master', array("mode" => $mode, "MenuBar_Id" => $MenuBar_Id
            , "Parent_Id" => $Parent_Id, "MenuBar_Code" => $MenuBar_Code
            , "MenuBar_NameEn" => $MenuBar_NameEn, "MenuBar_NameTh" => $MenuBar_NameTh
            , "MenuBar_Desc" => $MenuBar_Desc, "NavigationUri" => $NavigationUri, "Sequence" => $Sequence
            , "Icon_Image_Id" => $Icon_Image_Id, "IsUri" => $IsUri, "Active" => $Active
            , "Menu_Type" => $Menu_Type, "Image_List" => $Image_List, "ImageName" => $ImageName), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Edit Menu HH'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    # HH ---------------------------------------------------------------------------------------------------------------------------------------------
    # END --------------------------------------------------------------------------------------------------------------------------------------------
    # 
    # 
    # 
    # GET --------------------------------------------------------------------------------------------------------------------------------------------
    # ------------------------------------------------------------------------------------------------------------------------------------------------

    function get_menu_detail() {
        $MenuBar_Id = $this->input->post("MenuBarId");
        $Menu_Type = $this->input->post("MenuType");

        $r_menu = $this->atn->get_ADM_M_MenuBar(FALSE, NULL, $MenuBar_Id, NULL, FALSE, $Menu_Type)->result();

        $new_list = array();
        foreach ($r_menu as $rows) :
            $new_list['menu'][] = thai_json_encode((array) $rows);
        endforeach;

        $r_Image = $this->atn->get_ADM_M_ImageItem()->result(); // Dropdown List Image.
        $optionImage = genOptionDropdown($r_Image, "IMAGE");
        if (count($r_Image) > 0):
            $new_list['image'][] = $optionImage;
        endif;
        $json['menu_detail'] = $new_list;

        $r_Module = $this->flow->get_SYS_M_Stateedge_Module()->result(); // Dropdown List Module.
        $optionModule = genOptionDropdown($r_Module, "MODULE");
        if (count($r_Module) > 0):
            $new_list['module'][] = $optionModule;
        endif;
        $json['menu_detail'] = $new_list;

        echo json_encode($json);
    }

    # GET --------------------------------------------------------------------------------------------------------------------------------------------
    # END --------------------------------------------------------------------------------------------------------------------------------------------
    # 
    # 
    # 
    # SAVE -------------------------------------------------------------------------------------------------------------------------------------------
    # ------------------------------------------------------------------------------------------------------------------------------------------------

    private function menu_set_active($MenuBar_Id, $active, $Parent) {
        $this->transaction_db->transaction_start();
        $result_inactive_parent = $this->atn->set_active_ADM_M_MenuBar($MenuBar_Id, $active, $Parent);
        if ($result_inactive_parent === TRUE):
            $this->transaction_db->transaction_commit();
            $this->cache->memcached->clean();
            return TRUE;
        else:
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function save_menu_parent() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Menu['MenuBar_Id'] = $data['MenuBar_Id_p'];

        $data_Menu['Parent_Id'] = 0;
        $data_Menu['MenuBar_Code'] = ($data['MenuBar_Code_p'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_Code_p']);
        $data_Menu['MenuBar_NameEn'] = ($data['MenuBar_NameEn_p'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_NameEn_p']);
        $data_Menu['MenuBar_NameTh'] = ($data['MenuBar_NameTh_p'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_NameTh_p']);

        $Active = $this->input->post("Active_p");
        if ($Active != 1) :
            $Active = FALSE;
        else:
            $Active = TRUE;
        endif;
//        $data_Menu['Active'] = $Active;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type_p'];
        switch ($type) :
            case "A" : {
                    $data_Menu['Created_Date'] = $human;
                    $data_Menu['Created_By'] = $this->session->userdata('user_id');
                    $data_Menu['Modified_Date'] = $human;
                    $data_Menu['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_MenuBar('ist', $data_Menu, $where_Menu);
                    if ($result > 0):
                        $result = TRUE;
                    else:
                        $this->transaction_db->transaction_rollback();
                    endif;
                }break;
            case "E" : {
                    $data_Menu['Modified_Date'] = $human;
                    $data_Menu['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_MenuBar('upd', $data_Menu, $where_Menu);
                }break;
        endswitch;

        if ($result === TRUE):
            if ($Active === TRUE):
                $result = $this->atn->set_active_ADM_M_MenuBar($data['MenuBar_Id_p'], TRUE, TRUE);
            else:
                $result = $this->atn->set_active_ADM_M_MenuBar($data['MenuBar_Id_p'], FALSE, TRUE);
            endif;
        endif;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
            $this->cache->memcached->clean();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function save_menu_child() {
        $data = $this->input->post();

        $MenuBarId = NULL;

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_Menu['MenuBar_Id'] = $data['MenuBar_Id'];

        $data_Menu['Parent_Id'] = ($data['Parent_Id'] == "") ? 0 : iconv("UTF-8", "TIS-620", $data['Parent_Id']);
        $data_Menu['MenuBar_Code'] = ($data['MenuBar_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_Code']);
        $data_Menu['MenuBar_NameEn'] = ($data['MenuBar_NameEn'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_NameEn']);
        $data_Menu['MenuBar_NameTh'] = ($data['MenuBar_NameTh'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_NameTh']);
        $data_Menu['MenuBar_Desc'] = ($data['MenuBar_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['MenuBar_Desc']);
        $data_Menu['NavigationUri'] = ($data['NavigationUri'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['NavigationUri']);
        $data_Menu['Sequence'] = ($data['Sequence'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Sequence']);
        $data_Menu['Icon_Image_Id'] = ($data['Icon_Image_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Icon_Image_Id']);
        $data_Menu['Menu_Type'] = ($data['Menu_Type'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Menu_Type']);
        if (isset($data['Module'])):
            $data_Menu['Module'] = ($data['Module'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Module']);
        else:
            $data_Menu['Module'] = NULL;
        endif;

        $Active = $this->input->post("Active");
        if ($Active != 1) :
            $Active = 0;
        endif;
        $data_Menu['Active'] = $Active;

        $IsUri = $this->input->post("IsUri");
        if ($IsUri != 1) :
            $IsUri = 0;
        endif;
        $data_Menu['IsUri'] = $IsUri;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_Menu['Created_Date'] = $human;
                    $data_Menu['Created_By'] = $this->session->userdata('user_id');
                    $data_Menu['Modified_Date'] = $human;
                    $data_Menu['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_MenuBar('ist', $data_Menu);
                    if ($result <= 0):
                        $this->transaction_db->transaction_rollback();
                    else:
                        $MenuBarId = $result;
                    endif;
                }break;
            case "E" : {
                    $data_Menu['Modified_Date'] = $human;
                    $data_Menu['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->atn->save_ADM_M_MenuBar('upd', $data_Menu, $where_Menu);

                    $MenuBarId = $where_Menu['MenuBar_Id'];
                }break;
        endswitch;

        # Save SYS_M_Action_Menu
        if ($result != NULL && $result != FALSE):
            if (empty($data_Menu['Module']) && !empty($MenuBarId)):
                $where_Act_Menu['MenuBar_Id'] = $MenuBarId;
                $data_Act_Menu['MenuBar_Id'] = $MenuBarId;

                $data_Act_Menu['IsView'] = 1;

                $IsAdd = $this->input->post("IsAdd");
                if ($IsAdd !== "1") :
                    $IsAdd = FALSE;
                else:
                    $IsAdd = TRUE;
                endif;
                $data_Act_Menu['IsAdd'] = $IsAdd;

                $IsEdit = $this->input->post("IsEdit");
                if ($IsEdit !== "1") :
                    $IsEdit = FALSE;
                else:
                    $IsEdit = TRUE;
                endif;
                $data_Act_Menu['IsEdit'] = $IsEdit;

                $IsDelete = $this->input->post("IsDelete");
                if ($IsDelete !== "1") :
                    $IsDelete = FALSE;
                else:
                    $IsDelete = TRUE;
                endif;
                $data_Act_Menu['IsDelete'] = $IsDelete;

                $r_Action_Menu = $this->atn->get_SYS_M_Action_Menu($MenuBarId)->result();
                if (count($r_Action_Menu) > 0):
                    # Update
                    $result = $this->atn->save_SYS_M_Action_Menu("upd", $data_Act_Menu, $where_Act_Menu);
                else:
                    # Insert
                    $result = $this->atn->save_SYS_M_Action_Menu("ist", $data_Act_Menu, $where_Act_Menu);
                endif;

//                if ($result > 0):
//                    $get_act_mnu = $this->atn->get_SYS_M_Action_Menu($MenuBarId)->result();
//
//                    $act_Id = array();
//                    # View = -1, Add = -2, Edit = -3, Delete = -4 #
//                    foreach ($get_act_mnu as $value) :
//                        if ($value->IsView === TRUE):
//                            $act_Id[] = "-1";
//                        endif;
//                        if ($value->IsAdd === TRUE):
//                            $act_Id[] = "-2";
//                        endif;
//                        if ($value->IsEdit === TRUE):
//                            $act_Id[] = "-3";
//                        endif;
//                        if ($value->IsDelete === TRUE):
//                            $act_Id[] = "-4";
//                        endif;
//                    endforeach;
//
//                    if (!empty($act_Id)):
//                        # Update ADM_M_User_Permission
//                        $sql_user_permission = "DELETE ADM_M_User_Permission 
//                            WHERE ADM_M_User_Permission.MenuBar_Id = '" . $MenuBarId . "'
//                            AND ADM_M_User_Permission.Action_Id NOT IN (" . implode(",", $act_Id) . ")";
//                        $this->atn->save_ADM_M_User_Permission(NULL, NULL, NULL, $sql_user_permission);
//
//                        # Update ADM_M_Role_Permission
//                        $sql_role_permission = "DELETE ADM_M_Role_Permission 
//                            WHERE ADM_M_Role_Permission.MenuBar_Id = '" . $MenuBarId . "'
//                            AND ADM_M_Role_Permission.Action_Id NOT IN (" . implode(",", $act_Id) . ")";
//                        $this->atn->save_ADM_M_Role_Permission(NULL, NULL, NULL, $sql_role_permission);
//
//                        # Update ADM_M_Group=_Permission                          
//                        $sql_group_permission = "DELETE ADM_M_Group_Permission 
//                            WHERE ADM_M_Group_Permission.MenuBar_Id = '" . $MenuBarId . "'
//                            AND ADM_M_Group_Permission.Action_Id NOT IN (" . implode(",", $act_Id) . ")";
//                        $this->atn->save_ADM_M_Group_Permission(NULL, NULL, NULL, $sql_group_permission);
//                    endif;
//                endif;
            elseif (!empty($data_Menu['Module'])):
                $result = TRUE;
            endif;
        endif;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
            $this->cache->memcached->clean();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    # SAVE -------------------------------------------------------------------------------------------------------------------------------------------
    # END --------------------------------------------------------------------------------------------------------------------------------------------

    function check_menu() {// Check MenuBar_Code Already.
        $data = $this->input->post();

        $r_menu_parent = $this->atn->get_ADM_M_MenuBar(TRUE, NULL, NULL, $data['MenuBar_Code'], FALSE)->result();

        $r_menu_child = $this->atn->get_ADM_M_MenuBar(FALSE, NULL, NULL, $data['MenuBar_Code'], FALSE)->result();

        if (count($r_menu_parent) > 0 || count($r_menu_child) > 0):
            echo TRUE;
        else:
            echo FALSE;
        endif;
    }

}
