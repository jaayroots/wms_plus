<?php

// Create by Ton! 20130422 
//Location: ./application/controllers/warehouse.php 
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class warehouse extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("Storagetype_model", "storType");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model("city_model", "city");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri = "warehouse";
    }

    public function index() {
        $this->warehouseList();
    }

    function warehouseList() {// display list of warehouse(datatable).
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $action = $action_parmission['action_button'];
        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button button dark_blue' VALUE='ADD'
             ONCLICK=\"openForm('warehouse','warehouse/warehouseProcessec/','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140130 #####

        $q_warehouse = $this->wh->getWarehouseAll();
        $r_warehouse = $q_warehouse->result();

        // Button View, Edit, Delete in Datatable.
        $column = array("ID", "Warehouse Code", "Warehouse Name EN", "Warehouse Name TH", "Warehouse Desc", "Address", "City", "Zip Code", "Warehouse Type");
//        $action = array(VIEW, EDIT, DEL); // Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_warehouse, $r_warehouse, $column, "warehouse/warehouseProcessec", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Warehouse'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('warehouse','warehouse/warehouseProcessec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function warehouseProcessec() {// select processec (Add, Edit Delete) Warehouse.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->warehouseForm($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->warehouseForm($mode, $Id);
        elseif ($mode == "D") :// Delete.
            $Id = $data['id'];
            $result_check = $this->check_delete_warehouse($Id);
            if ($result_check === TRUE):
                echo "<script type='text/javascript'>alert('Can not be deleted Warehouse. Warehouse is already in used. Do not delete!')</script>";
                redirect('warehouse', 'refresh');
            else:
                $result_delete = $this->delete_warehouse($Id);
                if ($result_delete === TRUE) :
                    echo "<script type='text/javascript'>alert('Delete data Warehouse Success.')</script>";
                    redirect('warehouse', 'refresh');
                else:
                    echo "<script type='text/javascript'>alert('Can not be deleted Warehouse. Do not delete!')</script>";
                    redirect('warehouse', 'refresh');
                endif;
            endif;
        endif;
    }

    function warehouseForm($mode, $Id) {// Form Add&Edit Zone. (call view/form/warehouseForm.php)
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . 'SAVE' . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####
        // get all city for dispaly dropdown list.
        $city_list = $this->city->get_CTL_M_City(NULL, NULL, NULL, TRUE)->result();
        $optionCity = genOptionDropdown($city_list, "CITY");

        $r_WH_Type = $this->wh->get_warehouse_type()->result();
        p($r_WH_Type);
        exit;
        $optionWH_Type = genOptionDropdown($r_WH_Type, "SYS_ID");
        // define for pass to form.
        $Warehouse_Id = "";
        $Warehouse_Code = "";
        $Warehouse_NameEN = "";
        $Warehouse_NameTH = "";
        $Warehouse_Desc = "";
        $Address = "";
        $City_id = "";
        $ZipCode = "";
        $Warehouse_Type = "";
        $Active = "";

        // get warehouse by id for pass to form.
        if ($Id != NULL):
            $Warehouse_Id = $Id;
            $warehouse_list = $this->wh->getWarehouseByID($Id)->result();
        else:
            $warehouse_list = NULL;
        endif;
        // Initial define for pass to form edit.
        if (!empty($warehouse_list)) :
            foreach ($warehouse_list as $whList) :
                $Warehouse_Code = $whList->Warehouse_Code;
                $Warehouse_NameEN = $whList->Warehouse_NameEN;
                $Warehouse_NameTH = $whList->Warehouse_NameTH;
                $Warehouse_Desc = $whList->Warehouse_Desc;
                $Address = $whList->Address;
                $City_id = $whList->City_Id;
                $ZipCode = $whList->ZipCode;
                $Warehouse_Type = $whList->Warehouse_Type;
                if ($whList->Active === 'Y'):
                    $Active = TRUE;
                else:
                    $Active = FALSE;
                endif;
            endforeach;
        endif;

        $this->load->helper('form');
        $str_form = form_fieldset('Warehouse Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/warehouse_master', array("cityList" => $optionCity, "mode" => $mode
            , "Warehouse_Id" => $Warehouse_Id, "Warehouse_Code" => $Warehouse_Code, "Warehouse_NameEN" => $Warehouse_NameEN
            , "Warehouse_NameTH" => $Warehouse_NameTH, "Warehouse_Desc" => $Warehouse_Desc, "Warehouse_Desc" => $Warehouse_Desc
            , "Address" => $Address, "City_id" => $City_id, "ZipCode" => $ZipCode
            , "Warehouse_Type" => $Warehouse_Type, "Active" => $Active, "optionWH_Type" => $optionWH_Type), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => 'COPYRIGHT'
            , 'menu_title' => 'Warehouse'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . 'BACK' . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . 'CLEAR' . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140228
        $data = $this->input->post();

        $result = array();
        $result['result'] = 1;
        $result['note'] = NULL;

        # Check Delete Warehouse.
        if ($data['type'] === "E"):
            if (!isset($data['Active'])):// InActive
                $result_check = $this->check_delete_warehouse($data['Warehouse_Id']);
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "WH_DEL";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        # Check Warehouse Code already exists.
        $warehouse_list = $this->wh->getWarehouseByCode($data['Warehouse_Id'], $data['Warehouse_Code'])->result();
        if (count($warehouse_list) > 0) :
            $result['result'] = 0;
            $result['note'] = "WH_CODE_ALREADY";
            echo json_encode($result);
            return;
        endif;

        echo json_encode($result);
        return;
    }

    function saveWarehouse() {// save Warehouse call warehouse_model\saveDataWarehouse.
        $data = $this->input->post();

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $rWH['Warehouse_Code'] = ($data['Warehouse_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_Code']);
        $rWH['Warehouse_NameEN'] = ($data['Warehouse_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_NameEN']);
        $rWH['Warehouse_NameTH'] = ($data['Warehouse_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_NameTH']);
        $rWH['Warehouse_Desc'] = ($data['Warehouse_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_Desc']);
        $rWH['Address'] = ($data['Address'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Address']);
        $rWH['City_Id'] = ($data['City_id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['City_id']);
        $rWH['ZipCode'] = ($data['ZipCode'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ZipCode']);
        $rWH['Warehouse_Type'] = ($data['Warehouse_Type'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_Type']);

        if (isset($data['Active'])):
            $rWH['Active'] = ACTIVE;
        else:
            $rWH['Active'] = INACTIVE;
        endif;

        $whereWH['Warehouse_Id'] = ($data['Warehouse_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Warehouse_Id']);

        $this->transaction_db->transaction_start();
        $type = $data['type']; // Add, Edit, Delete
        $result = TRUE;
        switch ($type) :
            case "A" : {
                    $rWH['Created_Date'] = $human;
                    $rWH['Created_By'] = $this->session->userdata('user_id');
                    $result = $this->wh->saveDataWarehouse('ist', $rWH, $whereWH);
                    if ($result <= 0):
                        $result = FALSE;
                    else:
                        $result = TRUE;
                    endif;
                }break;
            case "E" : {
                    $rWH['Modified_Date'] = $human;
                    $rWH['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->wh->saveDataWarehouse('upd', $rWH, $whereWH);
                }break;
        endswitch;

        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        echo $result;
        return;
    }

    function check_delete_warehouse($WH_Id) {
        $result = $this->zone->check_zone(NULL, NULL, $WH_Id, TRUE);
        return $result;
    }

    function delete_warehouse($WH_Id) {// delete Warehouse call warehouse_model\deleteDataWarehouse.
        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        $whereWH['Warehouse_Id'] = ($WH_Id == "") ? "" : $WH_Id;

        $rWH['Modified_Date'] = $human;
        $rWH['Modified_By'] = $this->session->userdata('user_id');
        $rWH['Active'] = INACTIVE;

        $this->transaction_db->transaction_start();
        $result = $this->wh->saveDataWarehouse('upd', $rWH, $whereWH);
        if ($result === TRUE):
            $this->transaction_db->transaction_commit();
        else:
            $this->transaction_db->transaction_rollback();
        endif;

        return $result;
    }

}

?>
