<?php

/*
 * Create by Ton! 20131212
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class image_gallery extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.
    public $settings;

    function __construct() {
        parent::__construct();
        $this->load->model('authen_model', 'atn');
        $this->load->model("image_category_model", "imc");
        $this->load->model("image_gallery_model", "img");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "image_gallery";
        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->image_gallery_list();
    }

    function image_gallery_list() {// Display List of ImageGallery.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
//        if (isset($action_parmission['action_button'])):
//            $action = $action_parmission['action_button'];
//        else:
//            $action = array();
//        endif;

        if (in_array("-3", $action_parmission) && !in_array("-4", $action_parmission)):
            $action = array(EDIT);
        elseif (in_array("-4", $action_parmission) && !in_array("-3", $action_parmission)):
            $action = array(DEL);
        elseif ((in_array("-3", $action_parmission)) && in_array("-4", $action_parmission)):
            $action = array(EDIT, DEL);
        else:
            $action = array();
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_image_gallery = $this->img->get_ADM_M_ImageCategory_Gallery_List();
        $r_image_gallery = $q_image_gallery->result();

        $column = array("ID", "ImageCategory Code", "ImageCategory Name EN", "ImageCategory Name TH", "ImageCategory Desc");
//        $action = array(EDIT); // Comment Out by Ton! 20140130

        $datatable = $this->datatable->genTableFixColumn($q_image_gallery, $r_image_gallery, $column, "image_gallery/image_category_processec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of ImageGallery.'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

    function image_category_processec() {// select processec (Edit) ImageCategory.
        $data = $this->input->post();
        if ($data['mode'] == "E") :// EDIT.
            $this->image_category_management($data);
        endif;
    }

    function image_category_management($params) {// Edit ImageCategory.
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        ##### END Permission Button. by Ton! 20140130 #####

        $display_path = $this->session->userdata('path_images');

        $ImageCategory_Id = '';
        $ImageCategory_Code = '';
        $ImageCategory_NameEN = '';
        $Active = '';
        if (!empty($params['id'])):
            $q_image_category = $this->imc->get_ADM_M_ImageCategory($params['id'], NULL, NULL, FALSE);
            $r_image_category = $q_image_category->result();
            if (count($r_image_category) > 0):
                foreach ($r_image_category as $value) :
                    $ImageCategory_Id = $params['id'];
                    $ImageCategory_Code = $value->ImageCategory_Code;
                    $ImageCategory_NameEN = $value->ImageCategory_NameEN;
                    $Active = $value->Active;
                endforeach;
            endif;
        endif;

        $response = (isset($params['response']) ? $params['response'] : '');
        $q_image_item = $this->img->get_ADM_M_ImageItem_List($params['id']);
        $r_image_item = $q_image_item->result();

        $this->load->helper('form');
        $str_form = form_fieldset('ImageGallery.');
        $str_form.=$this->parser->parse('form/image_gallery_master_list', array("mode" => $params['mode']
            , "ImageCategory_Id" => $ImageCategory_Id, "ImageCategory_Code" => $ImageCategory_Code
            , "ImageCategory_NameEN" => $ImageCategory_NameEN, "Active" => $Active
            , "image_item_list" => $r_image_item, "display_path" => $display_path, "response" => $response), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add/Edit ImageGallery.'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function get_image_itme_detail() {
        $ImageItem_Id = $this->input->post('ImageItem_Id');

        $q_image_item = $this->atn->get_ADM_M_ImageItem($ImageItem_Id, NULL, NULL, FALSE);
        $r_image_item = $q_image_item->result();

        echo json_encode($r_image_item);
    }

    function uploadImage() {
        $data = $this->input->post();
        $data['mode'] = $data['type'];
        $data['id'] = $data['ImageCategory_Id'];
        $path = $this->settings["path"]["images"];

        $Active = $this->input->post("Active");
        if ($Active == 'on'):
            $data['Active'] = TRUE;
        else:
            $data['Active'] = FALSE;
        endif;

        $result = FALSE;
        if ($_FILES['BrowseItem']['error'] == 4):// Not Upload File.
            $result = $this->saveImageItem($data);
        elseif ($_FILES['BrowseItem']['error'] == 0):// Upload File.
            $_FILES['BrowseItem']['name'] = $data['ImageName'] . $data['ImageExt'];
            $config['upload_path'] = $path;
            $config['allowed_types'] = '*';
            $this->load->library('upload', $config);
            $field_name = "BrowseItem";

            if ($this->upload->do_upload($field_name)) :
                $up = $this->upload->data();
                $data['ImageName'] = $up['raw_name']; // Edit by Ton! 201400318
                $result = $this->saveImageItem($data);
            else:
                $data['response'] = 'ERROR';
            endif;
        endif;

        if ($result == FALSE):
            $data['response'] = 'ERROR';
        else:
            $data['response'] = 'OK';
        endif;

        $this->image_category_management($data);
    }

    function saveImageItem($data) {
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $rItem['ImageCategory_Id'] = ($data['ImageCategory_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageCategory_Id']);
        $rItem['ImageName'] = ($data['ImageName'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageName']);
        $rItem['ImageExt'] = ($data['ImageExt'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageExt']);
        $rItem['ImageDesc'] = ($data['ImageDesc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ImageDesc']);
        $rItem['ImageStream'] = NULL;
        $rItem['Active'] = $data['Active'];

        $whereItem['ImageItem_Id'] = ($data['ImageItem_Id'] == "") ? "" : $data['ImageItem_Id'];

        $this->transaction_db->transaction_start();
        $result = FALSE;
        $type = $data['type'];
        switch ($type) :
            case "A" : {
                    $rItem['Created_Date'] = $human;
                    $rItem['Created_By'] = $this->session->userdata('user_id');
                    $rItem['Modified_Date'] = $human;
                    $rItem['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->img->save_ADM_M_ImageItem('ist', $rItem);
                    if ($result > 0):
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $rItem['Modified_Date'] = $human;
                    $rItem['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->img->save_ADM_M_ImageItem('upd', $rItem, $whereItem);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'save ADM_M_ImageItem Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        return $result;
    }

}
