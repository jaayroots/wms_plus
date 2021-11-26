<?php

// Create by Ton! 20130422
/* Location: ./application/controllers/storage.php */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class storage extends CI_Controller {

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

        $this->mnu_NavigationUri = "storage";
    }

    public function index() {
        $this->storage_list();
    }

    function storage_list() {
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
             ONCLICK=\"openForm('storage','storage/storage_processec/','A','')\">";
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_storage = $this->stor->getStorageAll(TRUE);
        $r_storage = $q_storage->result();

        $column = array("ID", "Warehouse Code", "Storage Type", "Storage Name", "Storage Height", "Storage Width", "Storage Lenght",
            "Storage Row", "Storage Column", "Storage Level", "Location Height", "Location Width", "Location Lenght", "Max Capacity", "Active");
//        $action = array(VIEW, DEL); // Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_storage, $r_storage, $column, "storage/storage_processec", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Storage'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('storage','storage/storage_processec/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function storage_processec() {// select processec (Add, Edit Delete) Storage.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->storage_form($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->storage_form($mode, $Id);
        elseif ($mode == "D") :// Delete
            $Id = $data['id'];
            $result_check = $this->check_storage_delete($Id);
            if ($result_check["result"] == FALSE):
                if ($result_check['note'] === "PROD"):
                    echo "<script type='text/javascript'>alert('Can not deleted. Because location have product is placed.')</script>";
                elseif ($result_check['note'] === "LOCATE"):
                    echo "<script type='text/javascript'>alert('Can not deleted. Because location not found.')</script>";
                endif;
            else:
                $result_delete = $this->delete_storage($Id);
                if ($result_delete == TRUE):
                    echo "<script type='text/javascript'>alert('Delete data Storage Success.')</script>";
                else:
                    echo "<script type='text/javascript'>alert('Delete data Storage Unsuccess. Please check?')</script>";
                endif;
            endif;

            redirect('storage', 'refresh');
        endif;
    }

    function storage_form($mode, $Id) {// Form Add&Edit Storage. (call view/form/storage.php)
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        #Load config
        $conf = $this->config->item('_xml'); // By POR : 20140722
        $max_row = (empty($conf['storage']['storage_row']) || $conf['storage']['storage_row']==0)?25:@$conf['storage']['storage_row'];   //config max_row
        $max_column = (empty($conf['storage']['storage_column']) || $conf['storage']['storage_column']==0)?25:@$conf['storage']['storage_column'];  //config max_column
        $max_level = (empty($conf['storage']['storage_level']) || $conf['storage']['storage_level']==0)?25:@$conf['storage']['storage_level']; //config max_level

        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="button" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####
        // get all warehouse for dispaly dropdown list.
        $warehouse_list = $this->wh->getWarehouseList()->result();
        $optionWH = genOptionDropdown($warehouse_list, "WH");

        // get all storageType for dispaly dropdown list.
        $storageType_list = $this->storType->getstorageTypeList()->result();
        $optionStorType = genOptionDropdown($storageType_list, "StorType");

        // get storage by id for pass to form.
        if ($Id != "") :
            $storage_list = $this->stor->getStorageByID($Id)->result();
        else:
            $storage_list = NULL;
        endif;

        // define for pass to form.
        $Warehouse_Id = "";
        $StorageType_Id = "";
        $Storage_NameTh = "";
        $Storage_NameEn = "";
        $Storage_Height = "";
        $Storage_Width = "";
        $Storage_Lenght = "";
        $Storage_Row = "";
        $Storage_Column = "";
        $Storage_Level = "";
        $Location_Width = "";
        $Location_Lenght = "";
        $Location_Height = "";
        $Max_Capacity = "";
        $Capacity_Max_Pallet = "";
        $Suggest_Allow_Merge = "";
        $Zone_Id = "";
        $Active = "";

        // Initial define for pass to form edit.
        if (!empty($storage_list)) :
            foreach ($storage_list as $storageList) :
                $Warehouse_Id = $storageList->Warehouse_Id;
                $StorageType_Id = $storageList->StorageType_Id;
                $Storage_NameTh = $storageList->Storage_NameTh;
                $Storage_NameEn = $storageList->Storage_NameEn;
                $Storage_Height = $storageList->Storage_Height;
                $Storage_Width = $storageList->Storage_Width;
                $Storage_Lenght = $storageList->Storage_Lenght;
                $Storage_Row = $storageList->Storage_Row;
                $Storage_Column = $storageList->Storage_Column;
                $Storage_Level = $storageList->Storage_Level;
                $Location_Height = $storageList->Location_Height;
                $Location_Width = $storageList->Location_Width;
                $Location_Lenght = $storageList->Location_Lenght;
                $Max_Capacity = $storageList->Max_Capacity;
                $Capacity_Max_Pallet = $storageList->Capacity_Max_Pallet;
                $Suggest_Allow_Merge = $storageList->Suggest_Allow_Merge;
                $Zone_Id = $storageList->Zone_Id; // Add by Ton! 20140108
                if ($storageList->Active === 'Y' || $storageList->Active === '1' || $storageList->Active === 1):
                    $Active = TRUE;
                else:
                    $Active = FALSE;
                endif;
            endforeach;
        endif;

        // get all zone for dispaly dropdown list.
        $zone_list = $this->zone->getZoneListByWarehouseID($Warehouse_Id)->result();
        $optionZONE = genOptionDropdown($zone_list, "ZONE");
        $ZoneList = form_dropdown('Zone_Id', $optionZONE, $Zone_Id, 'id="Zone_Id" style="width: auto;" class="required"'); // disabled="disabled"

        $this->load->helper('form');
//        $str_form=form_open('storage/save_storage');
        $str_form = form_fieldset('Storage Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/storage_master', array("WHList" => $optionWH, "StorTypeList" => $optionStorType
            , "mode" => $mode, "Id" => $Id, "Warehouse_Id" => $Warehouse_Id, "StorageType_Id" => $StorageType_Id
            , "Storage_NameTh" => $Storage_NameTh, "Storage_NameEn" => $Storage_NameEn, "Storage_Height" => $Storage_Height
            , "Storage_Width" => $Storage_Width, "Storage_Lenght" => $Storage_Lenght, "Storage_Row" => $Storage_Row
            , "Storage_Column" => $Storage_Column, "Storage_Level" => $Storage_Level, "Max_Capacity" => $Max_Capacity
            , "Location_Height" => $Location_Height, "Location_Width" => $Location_Width, "Location_Lenght" => $Location_Lenght
            , "ZONEList" => $ZoneList, "Zone_Id" => $Zone_Id, "Active" => $Active,"max_row" => $max_row,"max_column"=>$max_column
            , "Capacity_Max_Pallet" => $Capacity_Max_Pallet, "Suggest_Allow_Merge" => $Suggest_Allow_Merge
            , "max_level" =>$max_level), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Storage'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function validation() {// Add by Ton! 20140303
        $data = $this->input->post();

        $result = array();
        $result['result'] = TRUE;
        $result['note'] = NULL;

        if ($data['type'] === "E"):
            if (!isset($data['Active'])):
                $result_check = $this->check_storage_delete($data['Storage_Id']);
                if ($result_check['result'] == TRUE):
                    if ($result_check['note'] === "PROD"):
                        $result['result'] = FALSE;
                        $result['note'] = "STOR_DEL_PROD";
                    elseif ($result_check['note'] === "LOCATE"):
                        $result['result'] = FALSE;
                        $result['note'] = "STOR_DEL_LOCATE";
                    endif;
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;

        echo json_encode($result);
    }

    function save_storage() {// save Zone call storage/saveDataStorage.
        $this->transaction_db->transaction_start();
        $data = $this->input->post();

        $rStorage['Warehouse_Id'] = ($data['wh_id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['wh_id']);
        $rStorage['StorageType_Id'] = ($data['st_id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['st_id']);
        $rStorage['Storage_NameTh'] = ($data['Storage_NameTh'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Storage_NameTh']);
        $rStorage['Storage_NameEn'] = ($data['Storage_NameEn'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Storage_NameEn']);

        if (trim($data['Storage_Height']) !== "" && trim($data['Storage_Height']) !== NULL):
            $rStorage['Storage_Height'] = trim($data['Storage_Height']);
        else:
            $rStorage['Storage_Height'] = NULL;
        endif;
        if (trim($data['Storage_Width']) !== "" && trim($data['Storage_Width']) !== NULL):
            $rStorage['Storage_Width'] = trim($data['Storage_Width']);
        else:
            $rStorage['Storage_Width'] = NULL;
        endif;
        if (trim($data['Storage_Lenght']) !== "" && trim($data['Storage_Lenght']) !== NULL):
            $rStorage['Storage_Lenght'] = trim($data['Storage_Lenght']);
        else:
            $rStorage['Storage_Lenght'] = NULL;
        endif;

        if (trim($data['Storage_Row']) !== "" && trim($data['Storage_Row']) !== NULL):
            $rStorage['Storage_Row'] = trim($data['Storage_Row']);
        else:
            $rStorage['Storage_Row'] = NULL;
        endif;
        if (trim($data['Storage_Column']) !== "" && trim($data['Storage_Column']) !== NULL):
            $rStorage['Storage_Column'] = trim($data['Storage_Column']);
        else:
            $rStorage['Storage_Column'] = NULL;
        endif;
        if (trim($data['Storage_Level']) !== "" && trim($data['Storage_Level']) !== NULL):
            $rStorage['Storage_Level'] = trim($data['Storage_Level']);
        else:
            $rStorage['Storage_Level'] = NULL;
        endif;

        if (trim($data['Location_Width']) !== "" && trim($data['Location_Width']) !== NULL):
            $rStorage['Location_Width'] = trim($data['Location_Width']);
        else:
            $rStorage['Location_Width'] = NULL;
        endif;
        if (trim($data['Location_Lenght']) !== "" && trim($data['Location_Lenght']) !== NULL):
            $rStorage['Location_Lenght'] = trim($data['Location_Lenght']);
        else:
            $rStorage['Location_Lenght'] = NULL;
        endif;
        if (trim($data['Location_Height']) !== "" && trim($data['Location_Height']) !== NULL):
            $rStorage['Location_Height'] = trim($data['Location_Height']);
        else:
            $rStorage['Location_Height'] = NULL;
        endif;

        $rStorage['Max_Capacity'] = ($data['Max_Capacity'] == "") ? NULL : $data['Max_Capacity'];
        $rStorage['Suggest_Allow_Merge'] = ($data['Suggest_Allow_Merge'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Suggest_Allow_Merge']);
        $rStorage['Capacity_Max_Pallet'] = ($data['Capacity_Max_Pallet'] == "") ? NULL : $data['Capacity_Max_Pallet'];

        if (isset($data['Zone_Id'])):
            $rStorage['Zone_Id'] = ($data['z_id'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['z_id']); // Add by Ton! 20140107
        endif;

        if (isset($data['Active'])):
            $rStorage['Active'] = ACTIVE;
        else:
            $rStorage['Active'] = INACTIVE;
        endif;

        $whereStorage['Storage_Id'] = ($data['Storage_Id'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Storage_Id']);

        $result = TRUE;
        $type = $data['type']; // Add, Edit, Delete

        switch ($type) :
            case "A" : {
                    $result = $this->stor->saveDataStorage('ist', $rStorage, $whereStorage);
                    if ($result <= 0):
                        $result = FALSE;
                        $this->transaction_db->transaction_rollback(); // Save Not Ok.
                    endif;
                }break;
            case "E" : {
                    $result = $this->stor->saveDataStorage('upd', $rStorage, $whereStorage);
                }break;
        endswitch;

//        // Save Storage Success.
        if ($result == TRUE):
            if ($type == 'A') :
                $result_stor_detail = $this->auto_create_storage_detail($result, $rStorage, $data['check_WH']); // Edit by Ton! 20140108
                if ($result_stor_detail === TRUE) :
                    $result = TRUE;
                    $this->transaction_db->transaction_commit(); // Save Ok.
                else:
                    $result = FALSE;
                    $this->transaction_db->transaction_rollback(); // Save Not Ok.
                endif;
            elseif ($type == 'E'):

                //$result_stor_detail = $this->auto_create_storage_detail($result, $rStorage, $data['check_WH']); // Edit by Ton! 20140108

                if (trim($data['Storage_Height']) !== "" && trim($data['Storage_Height']) !== NULL):
                    $data_storage_detail['Storage_Height'] = trim($data['Storage_Height']);
                else:
                    $data_storage_detail['Storage_Height'] = NULL;
                endif;
                if (trim($data['Storage_Width']) !== "" && trim($data['Storage_Width']) !== NULL):
                    $data_storage_detail['Storage_Width'] = trim($data['Storage_Width']);
                else:
                    $data_storage_detail['Storage_Width'] = NULL;
                endif;
                if (trim($data['Storage_Lenght']) !== "" && trim($data['Storage_Lenght']) !== NULL):
                    $data_storage_detail['Storage_Lenght'] = trim($data['Storage_Lenght']);
                else:
                    $data_storage_detail['Storage_Lenght'] = NULL;
                endif;

                $rStorage['Capacity_Max'] = ($data['Max_Capacity'] == "") ? NULL : $data['Max_Capacity'];
                $rStorage['Capacity_Remain'] = ($data['Max_Capacity'] == "") ? NULL : $data['Max_Capacity'];
                $rStorage['Capacity_Max_Pallet'] = ($data['Capacity_Max_Pallet'] == "") ? NULL : $data['Capacity_Max_Pallet'];
                $data_storage_detail['Suggest_Allow_Merge'] = ($data['Suggest_Allow_Merge'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Suggest_Allow_Merge']);
                $data_storage_detail['Capacity_Remain'] = ($data['Max_Capacity'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Max_Capacity']);

                $where_storage_detail['Storage_Id'] = ($data['Storage_Id'] == "") ? "" : iconv("UTF-8", "TIS-620", $data['Storage_Id']);

                $result_save_stor_detail = $this->stor->save_STK_M_Storage_Detail("upd", $data_storage_detail, $where_storage_detail);
                if ($result_save_stor_detail === TRUE):
                    $result = TRUE;
                    $this->transaction_db->transaction_commit(); // Save Ok.
                else:
                    $result = FALSE;
                    $this->transaction_db->transaction_rollback(); // Save Not Ok.
                endif;
            else:
                $this->transaction_db->transaction_commit(); // Save Ok.
            endif;
        else:
            $this->transaction_db->transaction_rollback(); // Save Not Ok.
        endif;

        echo $result;
    }

    private function auto_create_storage_detail($StorID = "", $rStorage = "", $chkWH = 0) {// Add by Ton! 20130429
        $storCodeAll = array(); // array of Storage_Code will create.
        // Add by Ton! 20131024
        $warehouse_code = NULL;
        $r_warehouse = $this->wh->getWarehouseByID($rStorage["Warehouse_Id"])->result();
        foreach ($r_warehouse as $value):
            $warehouse_code = $value->Warehouse_Code;
        endforeach;

        // Add by Ton! 20140108
        $Zone_Id = NULL;
        $Zone_Code = NULL;
        if (isset($rStorage["Zone_Id"])):
            $Zone_Id = $rStorage["Zone_Id"];
            $r_zone = $this->zone->getZone($rStorage["Zone_Id"])->result();
            foreach ($r_zone as $value):
                $Zone_Code = $value->Zone_Code;
            endforeach;
        endif;

        // Create array of row.
        $aRow = array();
        $row = intval($rStorage["Storage_Row"]);
        for ($r = 1; $r <= $row; $r++) :
            if (strlen(strval($r)) < 2) :
                $aRow[] = "0" . strval($r);
            else :
                $aRow[] = strval($r);
            endif;
        endfor;

        // Create array of column.
        $aCol = array();
        $col = intval($rStorage["Storage_Column"]);
        for ($c = 1; $c <= $col; $c++) :
            if (strlen(strval($c)) < 2) :
                $aCol[] = "0" . strval($c);
            else :
                $aCol[] = strval($c);
            endif;
        endfor;

        // Create array of level.
        $aLev = array();
        $lev = intval($rStorage["Storage_Level"]);
        for ($l = 1; $l <= $lev; $l++) :
            if (strlen(strval($l)) < 2) :
                $aLev[] = "0" . strval($l);
            else :
                $aLev[] = strval($l);
            endif;
        endfor;

        // Loop & Add to array $storCodeAll.
        for ($r = 0; $r < sizeof($aRow); $r++) :
            for ($c = 0; $c < sizeof($aCol); $c++) :
                for ($l = 0; $l < sizeof($aLev); $l++) :
//                    $storCodeAll[] = array('Storage_Code' => $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"], 'Storage_Row' => intval($aRow["$r"])
//                        , 'Storage_Column' => intval($aCol["$c"]), 'Storage_Level' => intval($aLev["$l"])
//                        , 'Location_Code' => $warehouse_code . "-" . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]//Add by Ton! 20131024
//                    );


					// Hard Code Mapping for Cold Room
					if ($rStorage['StorageType_Id'] == 11) :
						$Zone_Code = $Zone_Code;
						$row = "";
                        if ($chkWH == 1):// Edit by Ton! 20140108
                            $storCodeAll[] = array('Storage_Code' => $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"], 'Storage_Row' => intval($aRow["$r"])
                                , 'Storage_Column' => intval($aCol["$c"]), 'Storage_Level' => intval($aLev["$l"])
                                , 'Location_Code' => $warehouse_code . $Zone_Code . $row . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                                , 'Location_Physical_Code' => $warehouse_code . $Zone_Code . $row . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                            );
                        else:
                            $storCodeAll[] = array('Storage_Code' => $row . "-" . $aCol["$c"] . "-" . $aLev["$l"], 'Storage_Row' => intval($aRow["$r"])
                                , 'Storage_Column' => intval($aCol["$c"]), 'Storage_Level' => intval($aLev["$l"])
                                , 'Location_Code' => $warehouse_code . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                                , 'Location_Physical_Code' => $warehouse_code . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]);
                        endif;
					else :
                        if ($chkWH == 1):// Edit by Ton! 20140108
                            $storCodeAll[] = array('Storage_Code' => $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"], 'Storage_Row' => intval($aRow["$r"])
                                , 'Storage_Column' => intval($aCol["$c"]), 'Storage_Level' => intval($aLev["$l"])
                                , 'Location_Code' => $warehouse_code . "-" . $Zone_Code . "-" . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                                , 'Location_Physical_Code' => $warehouse_code . "-" . $Zone_Code . "-" . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                            );
                        else:
                            $storCodeAll[] = array('Storage_Code' => $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"], 'Storage_Row' => intval($aRow["$r"])
                                , 'Storage_Column' => intval($aCol["$c"]), 'Storage_Level' => intval($aLev["$l"])
                                , 'Location_Code' => $warehouse_code . "-" . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                                , 'Location_Physical_Code' => $warehouse_code . "-" . $aRow["$r"] . "-" . $aCol["$c"] . "-" . $aLev["$l"]
                            );
                        endif;
					endif;

					// End Mapping By Ball

                endfor;
            endfor;
        endfor;

        //--------------------------------------- save to STK_M_Storage_Detail. -------------------------------------------------
        foreach ($storCodeAll as $stor_code) :
            $rStorDetail['Warehouse_Id'] = ($rStorage["Warehouse_Id"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Warehouse_Id"]);
            $rStorDetail['Storage_Id'] = ($StorID == "") ? NULL : $this->encode->utf8_to_tis620($StorID);
            $rStorDetail['Storage_Code'] = ($stor_code == "") ? NULL : $this->encode->utf8_to_tis620($stor_code['Storage_Code']);
            $rStorDetail['Storage_Row'] = ($rStorage["Storage_Row"] == "") ? NULL : $this->encode->utf8_to_tis620($stor_code['Storage_Row']);
            $rStorDetail['Storage_Column'] = ($rStorage["Storage_Column"] == "") ? NULL : $this->encode->utf8_to_tis620($stor_code['Storage_Column']);
            $rStorDetail['Storage_Level'] = ($rStorage["Storage_Level"] == "") ? NULL : $this->encode->utf8_to_tis620($stor_code['Storage_Level']);
            $rStorDetail['Storage_Width'] = ($rStorage["Storage_Width"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Storage_Width"]);
            $rStorDetail['Storage_Lenght'] = ($rStorage["Storage_Lenght"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Storage_Lenght"]);
            $rStorDetail['Storage_Height'] = ($rStorage["Storage_Height"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Storage_Height"]);
            $rStorDetail['Capacity_Max'] = ($rStorage["Max_Capacity"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Max_Capacity"]);
            $rStorDetail['Capacity_Max_Pallet'] = ($rStorage["Capacity_Max_Pallet"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Capacity_Max_Pallet"]);
            $rStorDetail['Suggest_Allow_Merge'] = ($rStorage["Suggest_Allow_Merge"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Suggest_Allow_Merge"]);
            $rStorDetail['Capacity_Remain'] = ($rStorage["Max_Capacity"] == "") ? NULL : $this->encode->utf8_to_tis620($rStorage["Max_Capacity"]);
            $rStorDetail['Unit_Id'] = NULL;
            $rStorDetail['Is_Full'] = 'N';
            $rStorDetail['Active'] = ACTIVE;

            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %H:%i:%s", time());

            $rStorDetail['Create_Date'] = $human;
            $rStorDetail['Create_By'] = $this->session->userdata('user_id');
            $storage_detail_id = $this->stor->save_create_storage_detail($rStorDetail);

            if (!$storage_detail_id)://storage_detail_id
                return FALSE; // Not Ok.
            else:
                // Add by Ton! 20131024 Create Location.
                $rLocation['Putaway_Id'] = NULL;
                $rLocation['Warehouse_Id'] = $rStorDetail['Warehouse_Id'];
                $rLocation['Storage_Id'] = $rStorDetail['Storage_Id'];
                $rLocation['Storage_Detail_Id'] = $storage_detail_id;
                $rLocation['Location_Code'] = $stor_code['Location_Code'];
                $rLocation['Location_Physical_Code'] = $stor_code['Location_Physical_Code']; //NULL Edit by Ton! 20130108
                $rLocation['Remarks'] = NULL;
                $rLocation['Zone_Id'] = $Zone_Id; //$rStorage["Zone_Id"]; // Add by Ton! 20140108

                $chkHW = $this->stor->getStorageList($rStorDetail["Warehouse_Id"], $Zone_Id)->result(); // Add by Ton! 20140110
                if (count($chkHW) > 2):
                    $rLocation['Active'] = INACTIVE;
                else:
                    $rLocation['Active'] = ACTIVE;
                endif;

                $result_locate = $this->loc->saveDataLocation("ist", $rLocation);
                if ($result_locate === FALSE):
                    return FALSE; // Not Ok.
                endif;
            endif;
        endforeach;
        return TRUE; // Ok.
    }

    private function check_storage_delete($Storage_Id) {// Add by Ton! 20140228
        $result = array();
        $result['result'] = TRUE;
        $result['note'] = NULL;

        $resultLocate = $this->loc->getLocationbyStorID($Storage_Id, TRUE)->result();
        if (count($resultLocate) > 0) :
            $aLocateID = array();
            foreach ($resultLocate as $value) :
                $aLocateID[] = $value->Location_Id;
            endforeach;

            if (count($aLocateID) > 0) :
                $resultChk = $this->put->checkProductInLocation($aLocateID);
                if ($resultChk > 0):
                    $result['result'] = FALSE;
                    $result['note'] = 'PROD';

                    return $result;
                endif;
            endif;
        else:
            $result['result'] = FALSE;
            $result['note'] = 'LOCATE';

            return $result;
        endif;
    }

    private function delete_storage($Storage_Id) {// save Storage call storage_model\deleteDataStorage.
        $this->transaction_db->transaction_start();
        $whereStorage['Storage_Id'] = ($Storage_Id == "") ? "" : $Storage_Id;
        $rStorage['Active'] = INACTIVE;
        $result_del = $this->stor->saveDataStorage("upd", $rStorage, $whereStorage);
        if ($result_del === TRUE):
            $this->transaction_db->transaction_commit(); // save Ok.
        else:
            $this->transaction_db->transaction_rollback(); // Save Not Ok.
        endif;

        return $result_del;
    }

    function checkStorage() {// Add by Ton! 20140107
        $data = $this->input->post();

        if (isset($data["Warehouse_Id"])):
            $WH = ($data["Warehouse_Id"] == "") ? NULL : $data["Warehouse_Id"];
        else:
            $WH = NULL;
        endif;

        if (isset($data["Zone_Id"])):
            $Zone = ($data["Zone_Id"] == "") ? NULL : $data["Zone_Id"];
        else:
            $Zone = NULL;
        endif;

        $r_chkHW = $this->stor->getStorageList($WH, $Zone)->result();
        echo count($r_chkHW);
    }

    function ajax_check_item_in_storage() {
        $data = $this->input->post();

        $res = array();
        $res["result"] = TRUE;
        $res["note"] = "";

        if (ISSET($data["Storage_Id"])):
            $locate_id = array();

            $result_locate = $this->loc->getLocationbyStorID($data["Storage_Id"], TRUE)->result();
            foreach ($result_locate as $value) :
                $locate_id[] = $value->Location_Id;
            endforeach;

            if (!empty($locate_id)):
                $result_chk = $this->put->checkProductInLocation($locate_id);
                if ($result_chk > 0):
                    $res["result"] = FALSE;
                    $res["note"] = "Have items in Storage.";
                endif;
            endif;
        endif;
        
        echo json_encode($res);
    }

}

?>
