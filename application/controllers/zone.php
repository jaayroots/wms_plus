<?php

// Create by Ton! 20130422 
//Location: ./application/controllers/zone.php 
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class zone extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("location_model", "locate");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("Storagetype_model", "storType");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "zone";
    }

    public function index() {
        $this->zoneList();
    }

    function zoneList() {// Display list of zone. (Datatable)
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('zone','zone/zoneProcessec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_zone = $this->zone->getZoneAll();
        $r_zone = $q_zone->result();

        // Button View, Edit, Delete in Datatable.
        $column = array("ID", "Warehouse Code", "Zone Code", "Zone Name En", "Zone Name Th", "Zone Desc", "Active");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_zone, $r_zone, $column, "zone/zoneProcessec", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Zone'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('zone','zone/zoneProcessec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function zoneProcessec() {// select processec (Add, Edit Delete) Zone.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->zoneForm($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->zoneForm($mode, $Id);
        elseif ($mode == "D") :// Delete
            $Id = $data['id'];

            $result_check = $this->check_delete_zone($Id);
            if ($result_check === TRUE):
                echo "<script type='text/javascript'>alert('Can not be deleted Zone. Zone is already in used. Do not delete!')</script>";
                redirect('zone', 'refresh');
            else:
                $result_delete = $this->delete_zone($Id);
                if ($result_delete === TRUE) :
                    echo "<script type='text/javascript'>alert('Delete data Zone Success.')</script>";
                    redirect('zone', 'refresh');
                else:
                    echo "<script type='text/javascript'>alert('Delete data Zone Unsuccess. Please check?')</script>";
                    redirect('zone', 'refresh');
                endif;
            endif;


        endif;
    }

    function getCategoryList() {// get Category 20130426
        $data = $this->input->post();
        $zone_id = $data['Zone_Id'];
        $mode = $data['type'];

        //get all Category for display multiple select list.
        $queryCategory = $this->zone->getAllCategoryList();
        $category_list = $queryCategory->result();

        $cateIDList = array();
        if ($zone_id != '') :// get Category by zone_id for edit
            $queryZoneCategory = $this->zone->getZoneCategoryList($zone_id);
            $zone_cateList = $queryZoneCategory->result();

            $i = 0;
            foreach ($zone_cateList as $list) :
//                $cateIDList[$i] = $list->Dom_Code;
                $cateIDList[$i] = $list->Dom_ID;
                $i++;
            endforeach;
        endif;

        $view['cateList'] = $category_list;
        $view['cateIDList'] = $cateIDList;
        $view['mode'] = $mode;
        $this->load->view("category_table_form", $view);
    }

    function zoneForm($mode, $Id) {// Form Add&Edit Zone. (call view/form/zone.php)
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####
        // get all warehouse for dispaly dropdown list.
        $r_WH = $this->wh->getWarehouseList()->result();
        $optionWH = genOptionDropdown($r_WH, "WH");

        // get zone by id for pass to form.
        if ($Id != NULL):
            $r_Zone = $this->zone->getZone($Id)->result();
        else:
            $r_Zone = NULL;
        endif;

        // define for pass to form.
        $Warehouse_Id = "";
        $Zone_Code = "";
        $Zone_NameEn = "";
        $Zone_NameTh = "";
        $Zone_Desc = "";
        $Active = "";

        // Initial define for pass to form edit.
        if (!empty($r_Zone)) :
            foreach ($r_Zone as $zoneList) :
                $Warehouse_Id = $zoneList->Warehouse_Id;
                $Zone_Code = $zoneList->Zone_Code;
                $Zone_NameEn = $zoneList->Zone_NameEn;
                $Zone_NameTh = $zoneList->Zone_NameTh;
                $Zone_Desc = $zoneList->Zone_Desc;
                if ($zoneList->Active === 'Y' || $zoneList->Active === '1' || $zoneList->Active === 1):
                    $Active = TRUE;
                else:
                    $Active = FALSE;
                endif;
            endforeach;
        endif;

        $this->load->helper('form');
//        $str_form=form_open('zone/saveZone');
        $str_form = form_fieldset('Zone Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/zone_master', array("WHList" => $optionWH, "mode" => $mode, "Id" => $Id,
            "WH_Id" => $Warehouse_Id, "Zone_Code" => $Zone_Code, "Zone_NameEn" => $Zone_NameEn,
            "Zone_NameTh" => $Zone_NameTh, "Zone_Desc" => $Zone_Desc, "Active" => $Active), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Zone'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140228
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check Delete Zone.
        if ($data['type'] === "E"):
            if (!isset($data['Active'])):// InActive
                $result_check = $this->check_delete_zone($data['Zone_Id']);
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "ZONE_DEL";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        # Check Zone Code already exists.
        if ($this->zone->check_zone($data['Zone_Code'], $data['Zone_Id'], $data['Warehouse_Id']) === TRUE) :
            $result['result'] = 0;
            $result['note'] = "ZONE_CODE_ALREADY";
            echo json_encode($result);
            return;
        endif;

        echo json_encode($result);
        return;
    }

    function saveZone() {// save Zone call zone_model\saveDataZone.
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $rZone['Warehouse_Id'] = ($data['Warehouse_Id'] == "") ? NULL : $data['Warehouse_Id'];
        $rZone['Zone_Code'] = ($data['Zone_Code'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Zone_Code']);
        $rZone['Zone_NameEn'] = ($data['Zone_NameEn'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Zone_NameEn']);
        $rZone['Zone_NameTh'] = ($data['Zone_NameTh'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Zone_NameTh']);
        $rZone['Zone_Desc'] = ($data['Zone_Desc'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Zone_Desc']);

        $whereZone['Zone_Id'] = ($data['Zone_Id'] == "") ? "" : $data['Zone_Id'];

        if (isset($data['Active'])):
            $rZone['Active'] = ACTIVE;
        else:
            $rZone['Active'] = INACTIVE;
        endif;

        $type = $data['type']; // Add, Edit, Delete
        switch ($type) :
            case "A" : {
                    $rZone['Create_Date'] = $human;
                    $rZone['Create_By'] = $this->session->userdata('user_id');
                    $this->transaction_db->transaction_start();
                    $result = $this->zone->saveDataZone('ist', $rZone, $whereZone);
                    if ($result > 0):
                        $this->transaction_db->transaction_commit();
                    else:
                        $this->transaction_db->transaction_rollback();
                    endif;
                }break;
            case "E" : {
                    $rZone['Modified_Date'] = $human;
                    $rZone['Modified_By'] = $this->session->userdata('user_id');
                    $this->transaction_db->transaction_start();
                    $result = $this->zone->saveDataZone('upd', $rZone, $whereZone);
                    if ($result === TRUE):
                        $this->transaction_db->transaction_commit();
                    else:
                        $this->transaction_db->transaction_rollback();
                    endif;
                }break;
        endswitch;

        if ($type == 'A') :// Edit by Ton! 20130429
            if ($result != '') :
                $id = $result;
                $this->transaction_db->transaction_start();
                $result = $this->zone->saveDataZoneCategory($id, $data);
                if ($result === TRUE):
                    $this->transaction_db->transaction_commit();
                else:
                    $this->transaction_db->transaction_rollback();
                endif;
            else :
                $result = FALSE;
            endif;
        elseif ($type == 'E') :
            $id = $data['Zone_Id'];
            $this->transaction_db->transaction_start();
            $result = $this->zone->saveDataZoneCategory($id, $data);
            if ($result === TRUE):
                $this->transaction_db->transaction_commit();
            else:
                $this->transaction_db->transaction_rollback();
            endif;
        endif;

        echo $result;
    }

    function check_delete_zone($Zone_Id) {
        $result = $this->locate->checkLocation(NULL, $Zone_Id, NULL, NULL, NULL, NULL, TRUE);
        return $result;
    }

    function delete_zone($Zone_Id) {
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $whereZone['Zone_Id'] = ($Zone_Id == "") ? "" : $Zone_Id;
        $rZone['Modified_Date'] = $human;
        $rZone['Modified_By'] = $this->session->userdata('user_id');
        $rZone['Active'] = INACTIVE;

        $this->transaction_db->transaction_start();
        $result = $this->zone->saveDataZone('upd', $rZone, $whereZone);
        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        return $result;
    }

}

?>