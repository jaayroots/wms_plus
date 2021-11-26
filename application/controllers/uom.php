<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Uom extends CI_Controller {

    public $mnu_NavigationUri_UOM; // NavigationUri @Table ADM_M_MenuBar.
    public $mnu_NavigationUri_TMP; // NavigationUri @Table ADM_M_MenuBar.

    public function __construct() {
        parent::__construct();
        $this->load->model("uom_model", "uom");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("product_model", "prod");

        $isUserLogin = $this->session->userdata("user_id");
        if ($isUserLogin == "") :
            echo "<script>alert('Your session was expired. Please Log-in!')</script>";
            echo "<script>window.location.replace('" . site_url() . "');</script>";
        endif;

        $this->settings = native_session::retrieve();
        $this->mnu_NavigationUri_UOM = "uom";
        $this->mnu_NavigationUri_TMP = "uom/template_master";
    }

    public function index() {
        $this->uom_master();
    }

    public function uom_master() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_UOM);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('uom','uom/uom_process/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $uom_query = $this->uom->get_uom_all();
        $uom_list = $uom_query->result();

        $column = array("ID", "UOM Code", "Name", "Active");
//        $action = array(VIEW, EDIT); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($uom_query, $uom_list, $column, "uom/uom_process", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of UOM'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('uom','uom/uom_process/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    public function template_master() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_TMP);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' 
                ONCLICK=\"openForm('uom','uom/template_process/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $uom_query = $this->uom->get_template_all();
        $uom_list = $uom_query->result();

        $column = array("ID", "Name", "Description", "Active");
//        $action = array(VIEW, EDIT); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($uom_query, $uom_list, $column, "uom/template_process", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of UOM Template'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('uom','uom/template_process/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function uom_process() {
        $data = $this->input->post();

        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->uom_form($mode, $Id, NULL);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->uom_form($mode, $Id, NULL);
        elseif ($mode == "D") :// Delete
            $Id = $data['id'];
            $result = $this->delete_uom($Id);
            if ($result === TRUE) :
                echo "<script type='text/javascript'>alert('Set Inactive UOM Success.')</script>";
                redirect('uom/uom_master', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Set Inactive UOM Unsuccess.')</script>";
                redirect('uom/uom_master', 'refresh');
            endif;
        endif;
    }

    function template_process() {
        $data = $this->input->post();

        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->template_form($mode, $Id, '', '');
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->template_form($mode, $Id, '', '');
        elseif ($mode == "D") :// Delete
            $Id = $data['id'];
            $result = $this->delete_uom_template($Id);
            if ($result === TRUE) :
                echo "<script type='text/javascript'>alert('Set Inactive UOM Template Success.')</script>";
                redirect('uom/template_master', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Set Inactive UOM Template Unsuccess.')</script>";
                redirect('uom/template_master', 'refresh');
            endif;
        endif;
    }

    function uom_form($mode, $uom_id = NULL, $uom_code = NULL) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_UOM);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save" data-dialog="Save UOM">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $r_uom_master = $this->uom->get_uom_master_list()->result();
        $uom_master_list = genOptionDropdown($r_uom_master, 'UOM');
        
        // Add By Akkarapol, 14/01/2013, เพิ่มการเซ็ตค่าตัวแปร $languages โดยนำค่ามาจาก XML เพื่อนำค่า ภาษาต่างๆไปใช้
        foreach ($this->settings['languages']['object'] as $key_lang => $langs):
            if ($langs['active']):
                $languages[$key_lang] = $langs['name'];
            endif;
        endforeach;
        // END Add By Akkarapol, 14/01/2013, เพิ่มการเซ็ตค่าตัวแปร $languages โดยนำค่ามาจาก XML เพื่อนำค่า ภาษาต่างๆไปใช้
        // Comment By Akkarapol, 14/01/2013, คอมเม้นต์การเซ็ตค่าตัวแปร $languages แบบฝังโค๊ดทิ้งเพราะตอนนี้เซ็ตค่าของตัวแปร $languages ที่ทำมาจาก XML แทน
        //        $languages = array(
        //            'eng' => 'English',
        //            'tha' => 'Thai'
        //        );
        // END Comment By Akkarapol, 14/01/2013, คอมเม้นต์การเซ็ตค่าตัวแปร $languages แบบฝังโค๊ดทิ้งเพราะตอนนี้เซ็ตค่าของตัวแปร $languages ที่ทำมาจาก XML แทน

        $this->load->helper('form');
        $str_form = form_fieldset('UOM Master Info.');

        $data_uom = array();
        if ($mode === 'E'):
            $find_column = 'CTL_M_UOM.*';
            $find_where['CTL_M_UOM.id'] = $uom_id;
            $data_uom = $this->uom->get_uom_detail($find_column, $find_where)->row_array();

            $data_uom_langs = $this->uom->get_uom_detail_all_lang('CTL_M_UOM_Language.*', $find_where)->result_array();
            foreach ($data_uom_langs as $key_lang => $lang):
                $data_uom['data'][$lang['language']] = $lang;
            endforeach;
        elseif ($mode === 'V'):
            $find_column = 'CTL_M_UOM.*';
            $find_where['CTL_M_UOM.id'] = $uom_id;
            $data_uom = $this->uom->get_uom_detail($find_column, $find_where)->row_array();

            $data_uom_langs = $this->uom->get_uom_detail_all_lang('CTL_M_UOM_Language.*', $find_where)->result_array();
            foreach ($data_uom_langs as $key_lang => $lang):
                $data_uom['data'][$lang['language']] = $lang;
            endforeach;
        endif;

        // pass parameter to form
        $str_form.=$this->parser->parse('form/uom_form', array(
            'mode' => $mode,
//            'uom_id' => $uom_id,
//            'uom_code' => $uom_code,
            'data_uom' => $data_uom,
//            'close' => $close,
            'uom_master_list' => $uom_master_list,
            'languages' => $languages
                ), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'UOM Master Info.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function template_form($mode, $uom_id = NULL, $uom_code = NULL) {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_TMP);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save" data-dialog="Save UOM Template">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####


        $uom_master_query = $this->uom->get_uom_master_list();
        $uom_type_list = $uom_master_query->result();
        $uom_type_list = genOptionDropdown($uom_type_list, 'UOM');

        $uom_unit_query = $this->uom->get_uom_slave_list();
        $uom_unit_list = $uom_unit_query->result();
        $uom_unit_list = genOptionDropdown($uom_unit_list, 'UOM');

        $uom_template_query = $this->uom->get_template_all();
        $uom_template_list = $uom_template_query->result();
        $uom_template_list = genOptionDropdown($uom_template_list, 'UOM_TEMPLATE');
        
        // get all Dimension_Unit for dispaly dropdown list.
        $r_dimension_unit = $this->prod->getDimensionUnit()->result();
        $optionDimensionUnit = genOptionDropdown($r_dimension_unit, 'SYS', FALSE, FALSE);
        

        // Add By Akkarapol, 14/01/2013, เพิ่มการเซ็ตค่าตัวแปร $languages โดยนำค่ามาจาก XML เพื่อนำค่า ภาษาต่างๆไปใช้
        foreach ($this->settings['languages']['object'] as $key_lang => $langs):
            if ($langs['active']):
                $languages[$key_lang] = $langs['name'];
            endif;
        endforeach;
        // END Add By Akkarapol, 14/01/2013, เพิ่มการเซ็ตค่าตัวแปร $languages โดยนำค่ามาจาก XML เพื่อนำค่า ภาษาต่างๆไปใช้
        // Comment By Akkarapol, 14/01/2013, คอมเม้นต์การเซ็ตค่าตัวแปร $languages แบบฝังโค๊ดทิ้งเพราะตอนนี้เซ็ตค่าของตัวแปร $languages ที่ทำมาจาก XML แทน
        //        $languages = array(
        //            'eng' => 'English',
        //            'tha' => 'Thai'
        //        );
        // END Comment By Akkarapol, 14/01/2013, คอมเม้นต์การเซ็ตค่าตัวแปร $languages แบบฝังโค๊ดทิ้งเพราะตอนนี้เซ็ตค่าของตัวแปร $languages ที่ทำมาจาก XML แทน

        $this->load->helper('form');
        $str_form = form_fieldset('UOM Template Master Info.');

        $data_template = array();
        if ($mode === 'E'):
            $find_column = 'CTL_M_UOM_Template.*,CTL_M_UOM_Select.type_id,CTL_M_UOM_Select.unit_id';
            $find_where['CTL_M_UOM_Template.id'] = $uom_id;
            $data_template = $this->uom->get_template_detail($find_column, $find_where)->row_array();
            $data_template_langs = $this->uom->get_template_detail_all_lang('CTL_M_UOM_Template_Language.*', $find_where)->result_array();

            foreach ($data_template_langs as $key_lang => $lang):
                $data_template['data'][$lang['language']] = $lang;
            endforeach;
        elseif ($mode === 'V'):
            $find_column = '*';
            $find_where['CTL_M_UOM_Template.id'] = $uom_id;
            $data_template = $this->uom->get_template_detail($find_column, $find_where)->row_array();

            $data_template_langs = $this->uom->get_template_detail_all_lang('CTL_M_UOM_Template_Language.*', $find_where)->result_array();
            foreach ($data_template_langs as $key_lang => $lang):
                $data_template['data'][$lang['language']] = $lang;
            endforeach;
        endif;
//p($data_template);exit();
        // pass parameter to form
        $str_form.=$this->parser->parse('form/template_form', array(
            'mode' => $mode,
//            'uom_id' => $uom_id,
//            'uom_code' => $uom_code,
            'data_template' => $data_template,
//            'close' => $close,
            'uom_type_list' => $uom_type_list,
            'uom_unit_list' => $uom_unit_list,
            'uom_template_list' => $uom_template_list,
            "DimensionUnitList" => $optionDimensionUnit,
            'languages' => $languages
                ), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'UOM Master Info.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function save_uom() {
        $this->transaction_db->transaction_start();
        $data = $this->input->post();

        $return = array();
        $check_not_err = TRUE;
        
        $return = array();
        $uom_data['uom']['where']['id'] = $data['uom_id'];
        $uom_data['uom']['data']['parent_id'] = $data['parent_id'];
        $uom_data['uom']['data']['code'] = $data['uom_code'];
        $uom_data['uom']['data']['active'] = $data['uom_active'];

        $result = TRUE;
        $type = $data['type']; // Add, Edit
        switch ($type) :
            case "A" : {
                $CTL_M_UOM_id = $this->uom->save_uom_master('ist', $uom_data['uom']['data'], $uom_data['uom']['where']);
                if ($CTL_M_UOM_id == FALSE):
                    log_message('error', 'Save CTL_M_UOM Unsuccess.');
                    $result = FALSE;
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Save CTL_M_UOM Unsuccess.";
                endif;
                foreach ($data['data'] as $key_lang => $lang):
                    $lang_data['CTL_M_UOM_id'] = $CTL_M_UOM_id;
                    $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
                    $lang_data['short_text'] = iconv("UTF-8", "TIS-620", $lang['short_text']);
                    $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
                    $lang_data['language'] = $lang['language'];
                    $lang_datas[] = $lang_data;
                endforeach;
                $result = $this->uom->save_uom_master_language_batch('ist', $lang_datas, $uom_data['uom']['where']);
                if ($result === FALSE):
                    log_message('error', 'Save CTL_M_UOM_Language Unsuccess.');
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Save CTL_M_UOM_Language Unsuccess.";
                endif;
            }break;
            case "E" : {
                    $this->uom->save_uom_master('upd', $uom_data['uom']['data'], $uom_data['uom']['where']);
                    foreach ($data['data'] as $key_lang => $lang):
                        $where['id'] = $lang['id'];
                        $lang_data['CTL_M_UOM_id'] = $data['uom_id'];
                        $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
                        $lang_data['short_text'] = iconv("UTF-8", "TIS-620", $lang['short_text']);
                        $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
                        $lang_data['language'] = $lang['language'];
//                        $lang_datas[] = $lang_data;
                        $rs = $this->uom->save_uom_master_language_batch('upd', $lang_data, $where);
                        if ($rs == FALSE):
                            log_message('error', 'Save CTL_M_UOM_Language Unsuccess.');
                            $result = FALSE;
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Save CTL_M_UOM_Language Unsuccess.";
                        endif;
                    endforeach;
                }break;
        endswitch;

        
        if($type=='E'):
             # Set Active UOM. Add by Ton! 20140217
            if ($data['uom_active'] === "Y"):
                $status = $this->uom_func->set_active_uom($data['uom_id'], TRUE);
                if (!empty($status['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update data.";
                    $return = array_merge_recursive($return, $status);
                endif;
//                if ($result === FALSE):
//                    log_message('error', 'Set Active CTL_M_UOM by uom_id = ' . $data['uom_id'] . ' Unsuccess.');
//                endif;
            else:
                $status = $this->uom_func->set_active_uom($data['uom_id'], FALSE);
                if (!empty($status['critical'])) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update data.";
                    $return = array_merge_recursive($return, $status);
                endif;
//                if ($result === FALSE):
//                    log_message('error', 'Set InActive CTL_M_UOM by uom_id = ' . $data['uom_id'] . ' Unsuccess.');
//                endif;
            endif;
        endif;
       
        
        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Save UOM Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Save UOM Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;
        
        echo json_encode($json);
        
//        if ($result === TRUE) :
//            $this->transaction_db->transaction_commit();
//        else:
//            $this->transaction_db->transaction_rollback();
//        endif;
//        
//        echo $result;
    }

    function save_template() {
        
        $this->transaction_db->transaction_start();
        $data = $this->input->post();
        $saveFlag = TRUE; // Add by Ton! 20140305

        $return = array();
        $check_not_err = TRUE;
        
        $select_uom['column'] = 'id';
        $select_uom['data']['type_id'] = $data['type_id'];
        $select_uom['data']['unit_id'] = $data['unit_id'];

        $select_uom_id = $this->uom->get_uom_select($select_uom['column'], $select_uom['data'])->row_array();

        if (empty($select_uom_id)):
            $select_uom['data']['active'] = ACTIVE;
            $select_uom_id['id'] = $this->uom->save_uom_select('ist', $select_uom['data'], '');
        endif;

        if ($select_uom_id['id'] === '' || empty($select_uom_id['id'])):
            log_message('error', 'Save CTL_M_UOM_Select Unsuccess.');
            $saveFlag = FALSE;
        endif;

        $template_data['template']['where']['id'] = $data['template_id'];
        $template_data['template']['data']['CTL_M_UOM_Select_id'] = $select_uom_id['id'];
        $template_data['template']['data']['child_id'] = ($data['child_id']=='*'?NULL:$data['child_id']);
        $template_data['template']['data']['quantity'] = $data['quantity'];
        $template_data['template']['data']['active'] = $data['template_active'];
        $template_data['template']['data']['tmp_active'] = $data['template_active'];
        $template_data['template']['data']['Width'] = (empty($data['width'])?0:$data['width']);
        $template_data['template']['data']['Length'] = (empty($data['length'])?0:$data['length']);
        $template_data['template']['data']['Height'] = (empty($data['height'])?0:$data['height']);
        $template_data['template']['data']['Dimension_Unit'] = $data['dimension_unit'];
        $template_data['template']['data']['Cubic_Meters'] = (empty($data['cbm'])?0:$data['cbm']);

        $type = $data['type']; // Add, Edit
        switch ($type) :
            case "A" : {
                    $CTL_M_UOM_Template_id = $this->uom->save_template_master('ist', $template_data['template']['data'], $template_data['template']['where']);
                    if ($CTL_M_UOM_Template_id === FALSE):
                        log_message('error', 'Save CTL_M_UOM_Template Unsuccess.');
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Save CTL_M_UOM_Template Unsuccess.";
                    endif;
                    foreach ($data['data'] as $key_lang => $lang):
//                        $lang_data['id'] = $lang['id'];
                        $lang_data['CTL_M_UOM_Template_id'] = $CTL_M_UOM_Template_id;
                        $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
                        $lang_data['public_name'] = iconv("UTF-8", "TIS-620", $lang['public_name']);
                        $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
                        $lang_data['language'] = $lang['language'];
                        $lang_datas[] = $lang_data;
                    endforeach;
                    $result = $this->uom->save_template_master_language_batch('ist', $lang_datas, $template_data['template']['where']);
                    if ($result === FALSE):
                        log_message('error', 'Save CTL_M_UOM_Template_Language Unsuccess.');
                        $check_not_err = FALSE;

                        /**
                         * Set Alert Zone (set Error Code, Message, etc.)
                         */
                        $return['critical'][]['message'] = "Save CTL_M_UOM_Template_Language Unsuccess.";
                    endif;
                }break;
            case "E" : {
                    $this->uom->save_template_master('upd', $template_data['template']['data'], $template_data['template']['where']);
                    $result = TRUE;
                    foreach ($data['data'] as $key_lang => $lang):
                        $where['id'] = $lang['id'];
                        $lang_data['CTL_M_UOM_Template_id'] = $data['template_id'];
                        $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
                        $lang_data['public_name'] = iconv("UTF-8", "TIS-620", $lang['public_name']);
                        $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
                        $lang_data['language'] = $lang['language'];
//                        $lang_datas[] = $lang_data;
                        $rs = $this->uom->save_template_master_language_batch('upd', $lang_data, $where);
                        if ($rs == FALSE):
                            log_message('error', 'Save CTL_M_UOM_Template_Language Unsuccess.');
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Save CTL_M_UOM_Template_Language Unsuccess.";
                        endif;
                    endforeach;
                }break;
        endswitch;

        
         /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Save UOM Template Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Save UOM Template Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
        endif;

        echo json_encode($json);
        
        
//        $template_data['template']['where']['id'] = $data['template_id'];
//        $template_data['template']['data']['child_id'] = $data['parent_code'];
//        $template_data['template']['data']['code'] = $data['uom_code'];
//        $template_data['template']['data']['active'] = $data['uom_active'];
//
//        $result = 0;
//        $type = $data['type']; // Add, Edit
//        switch ($type) {
//            case "A" : {
//                    $result = $this->uom->save_uom_master('ist', $uom_data['uom']['data'], $uom_data['uom']['where']);
//                    foreach ($data['data'] as $key_lang => $lang):
////                        $lang_data['id'] = $lang['id'];
//                        $lang_data['CTL_M_UOM_code'] = $result;
//                        $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
//                        $lang_data['short_text'] = iconv("UTF-8", "TIS-620", $lang['short_text']);
//                        $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
//                        $lang_data['language'] = $lang['language'];
//                        $lang_datas[] = $lang_data;
//                    endforeach;
//                    $result = $this->uom->save_uom_master_language_batch('ist', $lang_datas, $uom_data['uom']['where']);
//                }break;
//            case "E" : {
//                    $this->uom->save_uom_master('upd', $uom_data['uom']['data'], $uom_data['uom']['where']);
//                    $result = TRUE;
//                    foreach ($data['data'] as $key_lang => $lang):
//                        $where['id'] = $lang['id'];
//                        $lang_data['CTL_M_UOM_code'] = $data['uom_code'];
//                        $lang_data['name'] = iconv("UTF-8", "TIS-620", $lang['name']);
//                        $lang_data['short_text'] = iconv("UTF-8", "TIS-620", $lang['short_text']);
//                        $lang_data['description'] = iconv("UTF-8", "TIS-620", $lang['description']);
//                        $lang_data['language'] = $lang['language'];
////                        $lang_datas[] = $lang_data;
//                        $rs = $this->uom->save_uom_master_language_batch('upd', $lang_data, $where);
//                        if ($rs == FALSE):
//                            $result = FALSE;
//                        endif;
//                    endforeach;
////                    p($result);
//                }break;
//        }

//        if ($saveFlag === TRUE) :
//            $this->transaction_db->transaction_commit();
//        else:
//            $this->transaction_db->transaction_rollback();
//        endif;

//        echo $result;
//        echo $saveFlag;
    }

    // Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่น ajax สำหรับตรวจสอบว่า Custom UOM ที่เลือกใช้นั้น ซ้ำกับที่เคยมีอยู่แล้วหรือไม่ 
    public function ajax_chk_custom_uom() {
        $data = $this->input->post();
        $filter_query = array(
            'column' => array(
                'CTL_M_UOM_Template_Of_Product.id'
            ),
            'where' => array(
                'CTL_M_UOM_Template_Of_Product.standard_unit_in_id' => $data['in'],
                'CTL_M_UOM_Template_Of_Product.standard_unit_out_id' => $data['out'],
//                'CTL_M_UOM_Template_Of_Product.ProductGroup_Id' => $data['ProductGroup_Id']
            )
        );

        $chk = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'])->result();

        if (empty($chk)):
            $json['status'] = "C001";
            $json['error_msg'] = "";
        else:
            $json['status'] = "E001";
            $json['error_msg'] = "This Custom UOM is Duplicate.";
        endif;

        echo json_encode($json);
    }

    // END Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่น ajax สำหรับตรวจสอบว่า Custom UOM ที่เลือกใช้นั้น ซ้ำกับที่เคยมีอยู่แล้วหรือไม่ 
    // Add By Akkarapol, 20/01/2013, เพิ่มฟังก์ชั่น ajax สำหรับ return List ของ Standard Unit ตาม ProductGroup, ProductBrand และ ProductCode ที่ได้เลือกมา
    function ajax_list_of_standard_unit() {
        $data = $this->input->post();
        $str_where = array();
        if (!empty($data['ProductGroup_Id'])):
            $str_where[] = "CTL_M_UOM_Template_Of_Product.ProductGroup_Id = '{$data['ProductGroup_Id']}'";
        endif;
        if (!empty($data['ProductBrand_Id'])):
            $str_where[] = "CTL_M_UOM_Template_Of_Product.ProductBrand_Id = '{$data['ProductBrand_Id']}'";
        endif;
        if (!empty($data['Product_Code'])):
            $str_where[] = "CTL_M_UOM_Template_Of_Product.Product_Code = '{$data['Product_Code']}'";
        endif;

        $filter_where = '1=1';
        foreach ($str_where as $key_where => $where):
            if ($key_where == 0):
                $filter_where = $where;
            else:
                $filter_where = $filter_where . ' OR ' . $where;
            endif;
        endforeach;

        $filter_query = array(
            'column' => array(
                'CTL_M_UOM_Template_Of_Product.id',
                'TLIN.public_name AS Unit_Value_In',
                'TLOUT.public_name AS Unit_Value_Out',
                'TLIN.CTL_M_UOM_Template_id AS Unit_Id_In',
                'TLOUT.CTL_M_UOM_Template_id AS Unit_Id_Out'
            ),
            'where' => $filter_where,
            'order' => '
                Unit_Value_In ASC ,
                Unit_Value_Out ASC 
                '
        );

        $results = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'], $filter_query['order'])->result();

        $uom_list = array();
        foreach ($results as $key_result => $result):
            $uom_list[$key_result]['val'] = $result->Unit_Id_In . SEPARATOR . $result->Unit_Id_Out;
            $uom_list[$key_result]['show_text'] = 'IN(' . $result->Unit_Value_In . ') | OUT(' . $result->Unit_Value_Out . ')';
        endforeach;

        echo json_encode($uom_list);
    }

    // END Add By Akkarapol, 20/01/2013, เพิ่มฟังก์ชั่น ajax สำหรับ return List ของ Standard Unit ตาม ProductGroup, ProductBrand และ ProductCode ที่ได้เลือกมา

    function delete_uom($Id = NULL) {// Add by Ton! 20140212
//        $result = FALSE;
        $return = array();
        $check_not_err = TRUE;
        
        if (!empty($Id)):
            $result = $this->uom_func->set_active_uom($Id, FALSE);
            if (!empty($result['critical'])) :
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update data.";
                $return = array_merge_recursive($return, $result);
            endif;
        else:
            log_message('error', 'Inactive Id = ' . $Id . ' Table CTL_M_UOM Unsuccess. Id IS NULL.');
        endif;

        
            $rs = TRUE;
            
        /**
         * check if for return json and set transaction
         */
        if ($check_not_err):
            /**
             * ================== Auto End Transaction =========================
             */
            $this->transaction_db->transaction_end();

            $set_return['message'] = "Inactive UOM Complete.";
            $return['success'][] = $set_return;
            $json['status'] = "save";
            $json['return_val'] = $return;
            
            $rs = TRUE;
            
        else:
            /**
             * ================== Rollback Transaction =========================
             */
            $this->transaction_db->transaction_rollback();

            $array_return['critical'][]['message'] = "Inactive UOM Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($array_return, $return);
            
            $rs = FALSE;
            
        endif;
        
//        p($return);
//        exit();
//        if ($check_not_err === TRUE):
//            $this->transaction_db->transaction_commit();
//        else:
//            log_message('error', 'Inactive Id = ' . $Id . ' Table CTL_M_UOM Unsuccess.');
//            $this->transaction_db->transaction_rollback();
//        endif;

        return $rs;
    }

    function delete_uom_template($Id = NULL) {// Add by Ton! 20140212
        $result = FALSE;
        $uom_temp_id = array();
        if (!empty($Id)):
            $uom_temp_id[] = $Id;
            $result = $this->uom_func->set_active_uom_template($uom_temp_id, FALSE);
        else:
            log_message('error', 'Inactive Id = ' . $Id . ' Table CTL_M_UOM_Template Unsuccess. Id IS NULL.');
        endif;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'Inactive Id = ' . $Id . ' Table CTL_M_UOM_Template Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        return $result;
    }

}

?>
