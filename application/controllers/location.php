<?php

// Create by Ton! 20130422
/* Location: ./application/controllers/location.php */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class location extends CI_Controller {

    public $mnu_NavigationUri_locate; // NavigationUri @Table ADM_M_MenuBar.
    public $mnu_NavigationUri_edit_locate;

    function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('validate_data');
        $this->load->helper('form');
        $this->load->helper('util_helper'); //add by kik : 06-09-2013
        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");

        $this->mnu_NavigationUri_locate = "location";
        $this->mnu_NavigationUri_edit_locate = "location/edit_location_code_list";
    }

    public function index() {
        $this->locationList();
    }

    function locationList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_locate);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
             ONCLICK=\"openForm('location','location/add_location_to_zone/','A','')\">";
        endif;

        ##### END Permission Button. by Ton! 20140130 #####

        $q_locate = $this->loc->getLocationAll();
        $r_locate = $q_locate->result();

        // Button View, Edit, Delete in Datatable.
        $column = array("ID", "Warehouse Code", "Zone Code", "Capacity", "Storage Name", "Storage Code", "Location Code");
//        $action = array(VIEW, DEL); // Comment Out by Ton! 20140130
        $datatable = $this->datatable->genTableFixColumn($q_locate, $r_locate, $column, "location/locationProcessec", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'List of Location'
            , 'datatable' => $datatable
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
//             ONCLICK=\"openForm('location','location/add_location_to_zone/','A','')\">"
            , 'button_add' => $button_add
        ));
    }

    function locationProcessec() {// select processec (Add, Edit Delete) Location.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") {// Add.
            $Id = "";
            $this->locationForm($mode, $Id);
        } elseif ($mode == "V" || $mode == "E") {// View & Edit.
            $Id = $data['id'];
            $this->locationForm($mode, $Id);
        } elseif ($mode == "D") {// Delete
            $Id = $data['id'];
            $result = $this->deleteLocation($Id);
            if ($result == 1) {
                redirect('location', 'refresh');
            } else {
                P("Can not deleted. Because location have product is placed.");
                redirect('location', 'refresh');
            }
        }
    }

    function locationForm($mode, $Id) {// Form Add&Edit Location. (call view/form/location.php)
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_locate);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. by Ton! 20140130 #####
        // get location by id for pass to form.
        $location_list = "";
        if ($Id != "") :
            $location_list = $this->loc->getLocationByID($Id)->result();
        endif;

        // define for pass to form.
        $Warehouse_Id = "";
        $Zone_Id = "";
        $Category_Id = "";
        $Storage_Id = "";
        $Storage_Detail_Id = "";
        $Location_Code = "";

        // Initial define for pass to form edit.
        if ($location_list != "") :
            foreach ($location_list as $locationlist) :
                $Warehouse_Id = $locationlist->Warehouse_Id;
                $Zone_Id = $locationlist->Zone_Id;
                $Category_Id = $locationlist->Category_Id;
                $Storage_Id = $locationlist->Storage_Id;
                $Storage_Detail_Id = $locationlist->Storage_Detail_Id;
                $Location_Code = $locationlist->Location_Code;
            endforeach;
        endif;

        // get all warehouse for dispaly dropdown list.
        $warehouse_list = $this->wh->getWarehouseList()->result();
        $optionWH = genOptionDropdown($warehouse_list, "WH");
        $WHList = form_dropdown('Warehouse_Id', $optionWH, $Warehouse_Id, 'id=Warehouse_Id disabled="disabled"');

        // get all zone for dispaly dropdown list.
        $zone_list = $this->zone->getZoneListByWarehouseID($Warehouse_Id)->result();
        $optionZONE = genOptionDropdown($zone_list, "ZONE");
        $ZoneList = form_dropdown('Zone_Id', $optionZONE, $Zone_Id, 'id=Zone_Id disabled="disabled"');

        // get all category for dispaly dropdown list.
        $cate_list = $this->zone->getZoneCategoryList($Zone_Id)->result();
        if (empty($cate_list)) :// Add by Ton! 20130514
            $cate_list = $this->zone->getAllCategoryList()->result();
        endif;
        $optionCate = genOptionDropdown($cate_list, "SYS");
        $CateList = form_dropdown('Category_Id', $optionCate, $Category_Id, 'id=Category_Id disabled="disabled"');

        // get all storage for dispaly dropdown list.
        $stor_list = $this->stor->getStorageList($Warehouse_Id)->result();
        $optionSTOR = genOptionDropdown($stor_list, "STOR");
        $StorList = form_dropdown('Storage_Id', $optionSTOR, $Storage_Id, 'id=Storage_Id disabled="disabled"');

        // get all storage_detail for dispaly dropdown list.
        $storDetail_list = $this->stor->getStorageDetail($Storage_Id)->result();
        $optionSTORDetail = genOptionDropdown($storDetail_list, "STORDetail");
        $StorDetailList = form_dropdown('Storage_Detail_Id', $optionSTORDetail, $Storage_Detail_Id, 'id=Storage_Detail_Id disabled="disabled"');

        $this->load->helper('form');
        $str_form = form_fieldset('Location Information');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/location', array("mode" => $mode, "Id" => $Id, "WHList" => $WHList, "ZONEList" => $ZoneList,
            "CateList" => $CateList, "STORList" => $StorList, "StorDetailList" => $StorDetailList, "Location_Code" => $Location_Code), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Location'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
//            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
            , 'button_save' => $button_save
        ));
    }

    function saveLocation() {// save location call location/saveDataLocation.
        $data = $this->input->post();
        $result = 1;

        $resultCheck = $this->loc->checkLocation($data['Warehouse_Id'], $data['Zone_Id'], $data['Category_Id'], $data['Storage_Id'], $data['Location_Code']);
        if ($resultCheck > 0) {
            echo $result = 2;
            return;
        }

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %H:%i:%s", time());

        $rLocation['Warehouse_Id'] = ($data['Warehouse_Id'] == "") ? NULL : $data['Warehouse_Id'];
        $rLocation['Zone_Id'] = ($data['Zone_Id'] == "") ? NULL : $data['Zone_Id'];
        $rLocation['Category_Id'] = ($data['Category_Id'] == "") ? NULL : $data['Category_Id'];
        $rLocation['Storage_Id'] = ($data['Storage_Id'] == "") ? NULL : $data['Storage_Id'];
        $rLocation['Storage_Detail_Id'] = ($data['Storage_Detail_Id'] == "") ? NULL : $data['Storage_Detail_Id'];
        $rLocation['Location_Code'] = ($data['Location_Code'] == "") ? "" : $data['Location_Code'];

        $whereLocation['Location_Id'] = ($data['Location_Id'] == "") ? "" : $data['Location_Id'];

        $type = $data['type']; // Add, Edit, Delete
        switch ($type) {
            case "A" : {
//                $rLocation['Create_Date']=$human;
//                $rLocation['Create_By']=$this->session->userdata('user_id');
                    $rLocation['Active'] = ACTIVE;
                    $result = $this->loc->saveDataLocation('ist', $rLocation, $whereLocation);
                }break;
            case "E" : {
//                $rLocation['Modified_Date']=$human;
//                $rLocation['Modified_By']=$this->session->userdata('user_id');
                    $rLocation['Active'] = ACTIVE;
                    $result = $this->loc->saveDataLocation('upd', $rLocation, $whereLocation);
                }break;
        }
        echo $result;
    }

    function deleteLocation($Location_Id) {// save Location call location_model\deleteDataLocation.
        $resultChk = $this->put->checkProductInLocation($Location_Id);
        if ($resultChk <= 0):
            $whereLocation['Location_Id'] = ($Location_Id == "") ? "" : $Location_Id;
            $rLocation['Active'] = INACTIVE;

            $result = $this->loc->saveDataLocation('upd', $rLocation, $whereLocation);
            return $result;
        endif;
    }

    function add_location_to_zone() {// Add by Ton! 20130430
        // get all warehouse for dispaly dropdown list.
        $queryWH = $this->wh->getWarehouseList();
        $warehouse_list = $queryWH->result();
        $optionWH = genOptionDropdown($warehouse_list, "WH");
        $WHList = form_dropdown('Warehouse_Id', $optionWH, '0', 'id=Warehouse_Id onChange="setZone()"');

        // get all zone for dispaly dropdown list.
        $zone_list = array();
        $optionZONE = genOptionDropdown($zone_list, "ZONE");
        $ZoneList = form_dropdown('Zone_Id', $optionZONE, '0', 'id=Zone_Id onChange="setCategory()"');

        // get all category for dispaly dropdown list.
        $cate_list = array();
        $optionCate = genOptionDropdown($cate_list, "SYS");
        $CateList = form_dropdown('Category_Id', $optionCate, '0', 'id=Category_Id');

        // get all storage for dispaly dropdown list.
        $stor_list = array();
        $optionSTOR = genOptionDropdown($stor_list, "STOR");
        $StorList = form_dropdown('Storage_Id', $optionSTOR, '0', 'id=Storage_Id onChange="showStorageDetailList()"');

        $this->load->helper('form');
        $str_form = form_fieldset('Add Location to Zone');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/add_Location_to_zone', array("WHList" => $WHList, "ZONEList" => $ZoneList, "CATEList" => $CateList, "STORList" => $StorList), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add Location to Zone'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToMenu()" ID="btn_back">'
            , 'button_clear' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . CLEAR . '" ONCLICK="clearData()" ID="btn_clear">'
            , 'button_save' => '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">'
        ));
    }

    function showListStorageDetail() {// Add by Ton! 20130430 // Edit by Ton! 20130814
        $data = $this->input->post();

        if ($data['Warehouse_Id'] == "" || $data['Zone_Id'] == "" || $data['Storage_Id'] == "") {
            $view['location_already'] = '0';
        } else {
            $resultCheck = $this->loc->checkLocation($data['Warehouse_Id'], $data['Zone_Id'], '', $data['Storage_Id'], '');
            if ($resultCheck > 0) {
                $view['location_already'] = '1';
            } else {
                $view['location_already'] = '0';
            }
        }

        if ($data['Warehouse_Id'] == "" || $data['Zone_Id'] == "" || $data['Storage_Id'] == "") {
            $view['storage_detailList'] = NULL;
        } else {
//            $querySTORDetail = $this->stor->_get_storage_detail_by_stor_id($data['Warehouse_Id'], $data['Zone_Id'], $data['Storage_Id']);
            $querySTORDetail = $this->stor->_get_storage_detail_by_stor_id($data['Warehouse_Id'], $data['Storage_Id']); // Edit by Ton! 20131031
            $stor_detail_list = $querySTORDetail->result();
            if (count($stor_detail_list) > 0) {
                $view['storage_detailList'] = $stor_detail_list;
            } else {
                $view['storage_detailList'] = NULL;
            }
        }

        $this->load->view("storage_detail_table_form", $view);
    }

    // ---------------------------------------- dropdown ----------------------------------------
    function get_zone_by_warehouse_ID() {// Add by Ton! 20130430
        $data = $this->input->post();
        $Warehouse_Id = $data['Warehouse_Id'];

        $queryZone = $this->zone->getZoneListByWarehouseID($Warehouse_Id);
        $zone_list = $queryZone->result();
        $optionZONE = genOptionDropdown($zone_list, "ZONE");
        $ZoneList = form_dropdown('Zone_Id', $optionZONE, '0', 'id=Zone_Id onChange="setCategory()"');

        echo $ZoneList;
    }

    function getStorageByWarehouseID() {// Add by Ton! 20130430
        $data = $this->input->post();
        $Warehouse_Id = $data['Warehouse_Id'];

        $querySTOR = $this->stor->getStorageList($Warehouse_Id);
        $stor_list = $querySTOR->result();
        $optionSTOR = genOptionDropdown($stor_list, "STOR");
        $StorList = form_dropdown('Storage_Id', $optionSTOR, '0', 'id=Storage_Id onChange="showStorageDetailList()"');

        echo $StorList;
    }

    function getCategoryByZoneID() {// Add by Ton! 20130430
        $data = $this->input->post();
        $Zone_Id = $data['Zone_Id'];

        $queryCate = $this->zone->getZoneCategoryList($Zone_Id);
        $cate_list = $queryCate->result();
        if ($cate_list->num_rows <= 0) {// Add by Ton! 20130514
            $queryCateAll = $this->zone->getAllCategoryList();
            $cate_list = $queryCateAll->result();
        }
        $optionCate = genOptionDropdown($cate_list, "SYS");
        $CateList = form_dropdown('Category_Id', $optionCate, '0', 'id=Category_Id');

        echo $CateList;
    }

    function getStorageDetailByStorageID() {// Add by Ton! 20130503
        $data = $this->input->post();
        $Storage_Id = $data['Storage_Id'];

        $querySTORDetail = $this->stor->getStorageDetail($Storage_Id);
        $storDetail_list = $querySTORDetail->result();
        $optionSTORDetail = genOptionDropdown($storDetail_list, "STORDetail");
        $StorDetailList = form_dropdown('Storage_Detail_Id', $optionSTORDetail, '0', 'id=Storage_Detail_Id onChange=""');

        echo $StorDetailList;
    }

    // ---------------------------------------- dropdown ----------------------------------------

    function saveLocationToZone() {// Add by Ton! 20130430
        $result = 1;
        $data = $this->input->post();

        $queryWH = $this->wh->getWarehouseByID($data['Warehouse_Id']);
        $warehouse_list = $queryWH->result();
        $Warehouse_Code = "";
        if ($warehouse_list != "") {
            foreach ($warehouse_list as $whList) {
                $Warehouse_Code = $whList->Warehouse_Code;
            }
        }

        $queryZone = $this->zone->getZone($data['Zone_Id']);
        $zone_list = $queryZone->result();
        $Zone_Code = "";
        if ($zone_list != "") {
            foreach ($zone_list as $zoneList) {
                $Zone_Code = $zoneList->Zone_Code;
            }
        }

        $storage_detail_id = array_unique($data['storage_detail_id']);
        if (!empty($storage_detail_id)) {
            foreach ($storage_detail_id as $StorDetailID) {

                $querySTORDetail = $this->stor->get_storage_code_by_stor_detail_id($StorDetailID);
                $stor_detail_list = $querySTORDetail->result();

                $Storage_Code = "";
                if ($stor_detail_list != "") {
                    foreach ($stor_detail_list as $storList) {
                        $Storage_Code = $storList->Storage_Code;
                    }
                }

                $rLOCZONE['Warehouse_Id'] = ($data['Warehouse_Id'] == "") ? "" : $this->encode->utf8_to_tis620($data['Warehouse_Id']);
                $rLOCZONE['Zone_Id'] = ($data['Zone_Id'] == "") ? "" : $this->encode->utf8_to_tis620($data['Zone_Id']);
                $rLOCZONE['Category_Id'] = ($data['Category_Id'] == "") ? "" : $this->encode->utf8_to_tis620($data['Category_Id']);
                $rLOCZONE['Storage_Id'] = ($data['Storage_Id'] == "") ? "" : $this->encode->utf8_to_tis620($data['Storage_Id']);

                $rLOCZONE['Storage_Detail_Id'] = ($StorDetailID == "") ? "" : $this->encode->utf8_to_tis620($StorDetailID);
                $rLOCZONE['Location_Code'] = $this->encode->utf8_to_tis620($Warehouse_Code . "-" . $Zone_Code . "-" . $Storage_Code);

                $rLOCZONE['Active'] = ACTIVE;
                $result = $this->loc->saveDataLocation('ist', $rLOCZONE, '');
                if ($result == 0) {
                    echo $result;
                }
            }
        }
        echo $result;
    }

    public function getFreeLocation() {// Add by Ton! 20130516
        $type = $this->input->get_post('type');

        $prodCode = $this->input->get_post('prodCode');
        $prodStatus = $this->input->get_post('prodStatus');
        $used_rule = $this->input->get_post('used_rule');

        //<<<<<----- START - ADD by Ton! 20130913 ----->>>>>
        $prodCate = $this->input->get_post('prodCate');
        $prodSubStatus = $this->input->get_post('prodSubStatus');
        $prodQty = $this->input->get_post('prodQty');
        //<<<<<----- END - ADD by Ton! 20130913 ----->>>>>

        if ($used_rule == '') {
            $used_rule = 1; // 1 : Used, 0 : Not Used
        }


        //EDIT BY POR 2014-05-27 เปลี่ยนการส่งค่ามาเพื่อรองรับ stored ใหม่
        $id = $this->input->get_post('id');
        $type_item = $this->input->get_post('type_item');

//        $suggestLoc = $this->suggest_location->getSuggestLocationArray($type, $prodCode, $prodStatus, $used_rule);// Comment Out by Ton! 20130913

        //<<<<<----- START - EDIT CODE by Ton! 20130913 ----->>>>>
        //EDTIT BY POR 2014-05-27 ส่งตัวแปรเพิ่ม itemId,type_item
		$suggestLoc = $this->suggest_location->getSuggestLocationArray($type, $prodCode, $prodStatus, $used_rule, $prodCate, $prodSubStatus, $prodQty, $id, $type_item); // Edit by Ton! 20130913
        //<<<<<----- END - EDIT CODE by Ton! 20130913 ----->>>>>

        echo json_encode($suggestLoc);
    }

    #add function getFreeLocationForPicking for edit again : by kik : 2013-12-09

    public function getFreeLocationForPicking() {
        $type = $this->input->get_post('type');
        $prodCode = $this->input->get_post('prodCode');
        $prodStatus = $this->input->get_post('prodStatus');
        $locateId = $this->input->get_post('locateId');
        $prodSubStatus = $this->input->get_post('product_sub_status');  #add for defect 518 : by kik : 20131209
        $qty = $this->input->get_post('qty');                           #add for defect 518 : by kik : 20131209
//        $lotSerial = ($this->input->get_post('lot_serial') == "" ? '0' : $this->input->get_post('lot_serial'));    #add for defect 518 : by kik : 20131209
        $lot = ($this->input->get_post('lot') == "" ? '0' : $this->input->get_post('lot'));    #add for defect 518 : by kik : 20131209
        $serial = ($this->input->get_post('serial') == "" ? '0' : $this->input->get_post('serial'));    #add for defect 518 : by kik : 20131209
        $limit = $this->input->get_post('limit');                        #add for defect 518 : by kik : 20131209
        $inbound_id = $this->input->get_post('inbound_id');              #add for defect 518 : by kik : 20131209
        $item_id = $this->input->get_post('item_id');                    #add for defect 518 : by kik : 20131209

//        $suggestLoc = $this->suggest_location->getFindLocationArray($type, $prodCode, $prodStatus, $prodSubStatus, $lotSerial, $locateId, $qty, $limit, $inbound_id, $item_id); #add $limit for defect 518 : by kik : 20131204
        $suggestLoc = $this->suggest_location->getFindLocationArray($type, $prodCode, $prodStatus, $prodSubStatus, $lot, $serial, $locateId, $qty, $limit, $inbound_id, $item_id); #add $limit for defect 518 : by kik : 20131204
        $return_val = array();

        #convent lot/sel thai lang
        foreach ($suggestLoc as $suggestLo):
            $suggestLo['Product_Lot'] = iconv("TIS-620", "UTF-8", $suggestLo['Product_Lot']);
            $suggestLo['Product_Serial'] = iconv("TIS-620", "UTF-8", $suggestLo['Product_Serial']);
            $return_val[] = $suggestLo;
        endforeach;

        echo json_encode($return_val);
    }

    #Start comment for defect 518 : by kik : 20131209
//    public function getFreeLocationForPicking() {
//        $type = $this->input->get_post('type');
//        $prodCode = $this->input->get_post('prodCode');
//        $prodStatus = $this->input->get_post('prodStatus');
//        $locateId = $this->input->get_post('locateId');
//        $orderId = $this->input->get_post('orderId');
//        $itemId = $this->input->get_post('itemId');
//        $limit = $this->input->get_post('limit');   #add for defect 518 : by kik : 20131204
////        if ($prodStatus=='NORMAL') {
////            $suggestLoc=$this->suggest_location->getFindLocationArray($type, $prodCode, $prodStatus, $locateId);
////            echo json_encode($suggestLoc);
////        }
//        //comment by por #1672 ส่งตัวแปร $orderId เพิ่ม 2013-09-17
////        $suggestLoc = $this->suggest_location->getFindLocationArray($type, $prodCode, $prodStatus, $locateId, $orderId, $itemId,$limit); #add $limit for defect 518 : by kik : 20131204
//        $suggestLoc = $this->suggest_location->getFindLocationArray($type, $prodCode, $prodStatus, $locateId, $orderId, $itemId,$limit); #add $limit for defect 518 : by kik : 20131204
//        echo json_encode($suggestLoc);
//    }
    #End comment for defect 518 : by kik : 20131209
    // Add By Akkarapol, 02/10/2013, เพิ่มเพื่อใช้สำหรับ Auto Complete ของ Suggest & Actual Location

    function autoCompleteSuggestLocation() {
        $text_search = $this->input->post('text_search');
        $tr_data_no = $this->input->post('tr_data_no');
        $prodCode = $this->input->post('prodCode');
        $prodStatus = $this->input->post('prodStatus');
        $prodSubStatus = $this->input->post('prodSubStatus');
        $Qty = $this->input->post('Qty');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("location_model", "location");
        $this->load->model("system_management_model", "sys");

        $prodSubStatus = $this->sys->getDomCodeByDomENDesc($prodSubStatus)->row_array(); // Edit By Akkarapol, 06/11/2013, เพิ่ม ->row_array(); เข้าไปเนื่องจากได้ไปแก้ให้ฟังก์ชั่นที่เรียกใช้นี้ return ค่ากลับมาแบบเดิมๆเลยคือค่าแค่ตอน db->get เพราะงั้นจึงต้องเพิ่มส่วนนี้เข้าไป ให้เรียกใช้งานได้เหมือนเดิม        $prodSubStatus = $prodSubStatus['Dom_Code'];
        $this->load->model("product_model", "product");
        $ProductCategory_Id = $this->product->getProductDetailByProductCode($prodCode, 'ProductCategory_Id');
        $ProductCategory_Id = $ProductCategory_Id->row_array();
        if ($ProductCategory_Id['ProductCategory_Id'] == ''):
            $ProductCategory_Id['ProductCategory_Id'] = NULL;
        endif;

        $result = $this->suggest_location->getSuggestLocationArray('suggestLocation', $prodCode, $prodStatus, 1, $ProductCategory_Id['ProductCategory_Id'], $prodSubStatus, $Qty);

        $list = '';
        $results = array();
        foreach ($result as $idx => $data) {
            // Add By Akkarapol, 09/10/2013, เพิ่มการตรวจสอบค่าว่า มีค่าเหมือนกับที่ คีย์ เข้ามาหรือไม่ ถ้า มีก็ให้เก็บค่าเข้าตัวแปรเพื่อส่งกลับไปแสดงใน suggestBox
            if (strpos($data['Location_Code'], strtoupper($text_search)) !== false) {
                $results[$idx]['location_id'] = $data['Location_Id'];
                $results[$idx]['location_code'] = $data['Location_Code'];
            }
            // END Add By Akkarapol, 09/10/2013, เพิ่มการตรวจสอบค่าว่า มีค่าเหมือนกับที่ คีย์ เข้ามาหรือไม่ ถ้า มีก็ให้เก็บค่าเข้าตัวแปรเพื่อส่งกลับไปแสดงใน suggestBox
        }
        echo json_encode($results);
    }

    function autoCompleteActualLocation() {
        $text_search = $this->input->post('text_search');
        $tr_data_no = $this->input->post('tr_data_no');
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("location_model", "location");

        $result = $this->location->getLocationByLikeCode($text_search);

        $results = array();
        foreach ($result as $idx => $data) {
            $results[$idx]['location_id'] = $data->Location_Id;
            $results[$idx]['location_code'] = $data->Location_Code;
        }
        echo json_encode($results);
    }

    // END Add By Akkarapol, 02/10/2013, เพิ่มเพื่อใช้สำหรับ Auto Complete ของ Suggest & Actual Location

    /**
     * check existing location by location name
     * @author BALL
     * @return TRUE if exist
     */
    public function check_exist_location() {
        $params = $this->input->get();

        if (empty($params))
            $params = $this->input->post();

        $location_code = (ISSET($params['location_code']) ? $params['location_code'] : NULL);
        $location_id = (ISSET($params['location_id']) ? $params['location_id'] : NULL);

        $result = NULL;
        if (!empty($location_code)):
            $result = $this->loc->getLocationIdByCode(strtoupper($location_code), $location_id);
        endif;

        echo json_encode($result);
    }

    function edit_location_code_list() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        ##### Permission Button. #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_edit_locate);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;
        if (!empty($action)):
            $action = array(EDIT);
        endif;
        ##### END Permission Button. #####

        $qet_locate = $this->loc->get_edit_location_code_list();
        $result_locate = $qet_locate->result();

        $column = array("ID", "Location Code", "Warehouse Code", "Zone Code", "Storage Name", "Storage Code", "Items", "Active");
        $datatable = $this->datatable->genTableFixColumn($qet_locate, $result_locate, $column, "location/edit_location_code_processec", $action);

        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'List of Location for Edit Location Code.'
            , 'datatable' => $datatable
            , 'button_add' => ''
        ));
    }

    function edit_location_code_processec() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $data = $this->input->post();
        
        ##### Permission Button. #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_edit_locate);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="Save" ONCLICK="validation()" ID="btn_save">';
        endif;
        ##### END Permission Button. #####

        $parameter = array();
        $data_locate = $this->loc->get_edit_location_code($data['id'])->result();
        foreach ($data_locate as $Locate) :
            $parameter["Location_Id"] = $data['id'];
            $parameter["Warehouse_Code"] = $Locate->Warehouse_Code;
            $parameter["Zone_Code"] = $Locate->Zone_Code;
            $parameter["Storage_NameEn"] = $Locate->Storage_NameEn;
            $parameter["Storage_Code"] = $Locate->Storage_Code;
            $parameter["Location_Code"] = $Locate->Location_Code;
            $parameter["Active"] = $Locate->Active;
        endforeach;

        $this->load->helper('form');
        $str_form = form_fieldset('Location Detail');

        // pass parameter to form
        $str_form.=$this->parser->parse('form/edit_location_code', array("parameter" => $parameter), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Edit Location Code.'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => '<INPUT TYPE="button" class="button dark_blue" VALUE="' . BACK . '" ONCLICK="backToList()" ID="btn_back">'
            , 'button_clear' => ''
            , 'button_save' => $button_save
        ));
    }

    function save_edit_location_code() {// Add by Ton! 20140110
        $data = $this->input->post();
        $result = FALSE;

        if (!empty($data['Location_Id'])):
            $rLocation['Location_Code'] = ($data['Location_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Location_Code']);

            $Active = $this->input->post("Active");
            if ($Active == 'true'):
                $rLocation['Active'] = ACTIVE;
            else:
                $rLocation['Active'] = INACTIVE;
            endif;

            $whereLocation['Location_Id'] = ($data['Location_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Location_Id']);

            $this->transaction_db->transaction_start();
            $result = $this->loc->saveDataLocation('upd', $rLocation, $whereLocation);
            if ($result === TRUE) :
                $this->transaction_db->transaction_commit();
            else:
                log_message('error', 'Save Edit Location Code[STK_M_Location] Unsuccess.');
                $this->transaction_db->transaction_rollback();
            endif;
        else:
            log_message('error', 'Save Edit Location Code[STK_M_Location] Unsuccess. Location_Id IS NULL');
        endif;

        echo $result;
    }

    function get_location_cross_dock() { // For HH Add by Ton! 20150603
        $locate_result = $this->loc->getLocationCrossDock(FALSE, TRUE);
        echo json_encode($locate_result);
    }

    function ajax_check_item_in_location() {
        $data = $this->input->post();

        $res = array();
        $res["result"] = TRUE;
        $res["note"] = "";

        if (ISSET($data["Location_Id"])):
            $resultChk = $this->put->checkProductInLocation($data["Location_Id"]);
            if ($resultChk > 0):
                $res["result"] = FALSE;
                $res["note"] = "Have items in Location.";
            endif;
        endif;

        echo json_encode($res);
    }

}
