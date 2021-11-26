<?php

/*
 * Create by Ton! 20131212
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class image_category extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("image_category_model", "imc");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "image_category";
    }

    public function index() {
        $this->image_category_list();
    }

    function image_category_list() {// Display List of ImageCategory.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('position','image_category/image_category_processec/','A','')\">";
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_image_category = $this->imc->get_ADM_M_ImageCategory_List();
        $r_image_category = $q_image_category->result();

        $column = array("ID", "ImageCategory Code", "ImageCategory Name EN", "ImageCategory Name TH", "ImageCategory Desc", "Active");
//        $action = array(VIEW, EDIT, DEL);// Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_image_category, $r_image_category, $column, "image_category/image_category_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of ImageCategory.'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('position','image_category/image_category_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function image_category_processec() {// select processec (Add, Edit, Delete) ImageCategory.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// ADD.
            $Id = "";
            $this->image_category_management($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// VIEW & EDIT.
            $Id = $data['id'];
            $this->image_category_management($mode, $Id);
        } elseif ($mode == "D") {// DELETE.
            $Id = $data['id'];
            $result_inactive = $this->image_category_set_inactive($Id);
            if ($result_inactive === TRUE):
                echo "<script type='text/javascript'>alert('Inactive data Image Category Success.')</script>";
                redirect('image_category', 'refresh');
            else:
                echo "<script type='text/javascript'>alert('Inactive data Image Category Unsuccess. Please check?')</script>";
                redirect('image_category', 'refresh');
            endif;
        }
    }

    function image_category_set_inactive($ImageCategory_Id) {// Set Inactive ImageCategory.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_IMC['ImageCategory_Id'] = $ImageCategory_Id;

        $data_IMC['Active'] = FALSE;

        $data_IMC['Modified_Date'] = $human;
        $data_IMC['Modified_By'] = $this->session->userdata('user_id');
        $this->transaction_db->transaction_start();
        $result_inactive = $this->imc->save_ADM_M_ImageCategory('upd', $data_IMC, $where_IMC);
        if ($result_inactive === TRUE):
            $this->transaction_db->transaction_commit();
            return TRUE;
        else:
            log_message('error', 'save ADM_M_ImageCategory Unsuccess.');
            $this->transaction_db->transaction_rollback();
            return FALSE;
        endif;
    }

    function image_category_management($mode, $ImageCategoryId) {// Add & Edit ImageCategory.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $ImageCategory_Id = '';
        $Parent_Id = '';
        $ImageCategory_Code = '';
        $ImageCategory_NameEN = '';
        $ImageCategory_NameTH = '';
        $ImageCategory_Desc = '';
        $Active = '';

        if (!empty($ImageCategoryId)):
            $q_image_category = $this->imc->get_ADM_M_ImageCategory($ImageCategoryId, NULL, NULL, FALSE);
            $r_image_category = $q_image_category->result();
            if (count($r_image_category) > 0):
                foreach ($r_image_category as $value) :
                    $ImageCategory_Id = $ImageCategoryId;
                    $Parent_Id = $value->Parent_Id;
                    $ImageCategory_Code = $value->ImageCategory_Code;
                    $ImageCategory_NameEN = $value->ImageCategory_NameEN;
                    $ImageCategory_NameTH = $value->ImageCategory_NameTH;
                    $ImageCategory_Desc = $value->ImageCategory_Desc;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('ImageCategory.');
        $str_form.=$this->parser->parse('form/image_category_master', array("mode" => $mode
            , "ImageCategory_Id" => $ImageCategory_Id, "Parent_Id" => $Parent_Id, "ImageCategory_Code" => $ImageCategory_Code
            , "ImageCategory_NameEN" => $ImageCategory_NameEN, "ImageCategory_NameTH" => $ImageCategory_NameTH
            , "ImageCategory_Desc" => $ImageCategory_Desc, "Active" => $Active), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit ImageCategory.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function save_image_category() {
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $where_IMC['ImageCategory_Id'] = ($data['ImageCategory_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_Id']);

        $data_IMC['Parent_Id'] = ($data['Parent_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Parent_Id']);
        $data_IMC['ImageCategory_Code'] = ($data['ImageCategory_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_Code']);
        $data_IMC['ImageCategory_NameEN'] = ($data['ImageCategory_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_NameEN']);
        $data_IMC['ImageCategory_NameTH'] = ($data['ImageCategory_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_NameTH']);
        $data_IMC['ImageCategory_Desc'] = ($data['ImageCategory_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_Desc']);

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data_IMC['Active'] = TRUE;
        else:
            $data_IMC['Active'] = FALSE;
        endif;

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $data_IMC['Created_Date'] = $human;
                    $data_IMC['Created_By'] = $this->session->userdata('user_id');
                    $data_IMC['Modified_Date'] = $human;
                    $data_IMC['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->imc->save_ADM_M_ImageCategory('ist', $data_IMC);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $data_IMC['Modified_Date'] = $human;
                    $data_IMC['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->imc->save_ADM_M_ImageCategory('upd', $data_IMC, $where_IMC);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save ADM_M_ImageCategory Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
    }

    function check_image_category() {// Check ImageCategory_Code Already.
        $data = $this->input->post();

        $q_image_category = $this->imc->get_ADM_M_ImageCategory(NULL, $data['ImageCategory_Code'], NULL, FALSE);
        $r_image_category = $q_image_category->result();
        if (count($r_image_category) > 0):
            echo TRUE;
        else:
            echo FALSE;
        endif;
    }

}
