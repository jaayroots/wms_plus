<?php

#putaway master
#1.Show product on location - putawayList
#2.Add product to location - form()-> getZoneSelect()-> getZoneCategory() -> getProductList() -> saveProductLocation()
#3.Edit Product location - editProductToLocation
#  3.1.Add product to this location - addProductByLocation() -> getProductListByLocation() -> saveProductByLocation()
#  3.2.Delete (see 4)
#4.Delete Product location - deleteProductToLocation()
#5.putaction() for check action is View,Edit or Delete
#6.freeLocation  for search free location
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class putaway extends CI_Controller {

    public $settings;
    public $mnu_NavigationUri_PARule; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model("warehouse_model", "wh");
        $this->load->model("putaway_model", "put");
        $this->load->model("zone_model", "zone");
        $this->load->model("location_model", "loc");
        $this->load->model("product_model", "prod");
        $this->load->model("order_movement_model", "order_movement");

        $this->settings = native_session::retrieve();
        $this->load->model("encoding_conversion", "encode");

        $this->mnu_NavigationUri_PARule = "putaway/freeLocationList";
    }

    public function index() {
        //$this->putawayList(); // <<<<<------ COMMENT OUT by Ton! 20130903 ------>>>>>
        //$this->putawayList_V2();
    }

    // <<<<<------ START Add function putawayList_V2() by Ton! 20130902 ------>>>>>
//    function putawayList_V2() {
//        $column = array("Running No.", "Warehouse", "Zone", "Location", "Category", "Code", "Product Name", "Status", EDIT, DEL);
//        $parameter['show_column'] = $column;
//
//        // get all warehouse for dispaly dropdown list.
//        $q_WH = $this->wh->getWarehouseList();
//        $r_WH = $q_WH->result();
//        $optionWH = genOptionDropdown($r_WH, "WH");
//        $WHList = form_dropdown('Warehouse_Id', $optionWH, '', 'id=Warehouse_Id onChange="setZone()"');
//
//        // get all zone for dispaly dropdown list.
//        $q_Zone = $this->zone->getZoneListByWarehouseID();
//        $r_Zone = $q_Zone->result();
//        $optionZONE = genOptionDropdown($r_Zone, "ZONE");
//        $ZoneList = form_dropdown('Zone_Id', $optionZONE, '', 'id=Zone_Id onChange="setCategory()"');
//
//        // get all category for dispaly dropdown list.
//        $q_Cate = $this->zone->getZoneCategoryList();
//        $r_Cate = $q_Cate->result();
//        if (empty($r_Cate)) {
//            $queryCateAll = $this->zone->getAllCategoryList();
//            $r_Cate = $queryCateAll->result();
//        }
//        $optionCate = genOptionDropdown($r_Cate, "SYS");
//        $CateList = form_dropdown('Category_Id', $optionCate, '', 'id=Category_Id');
//
//        // get all product status for dispaly dropdown list.
//        $q_Status = $this->put->getProductStatus();
//        $r_Status = $q_Status->result();
//        $optionStatus = genOptionDropdown($r_Status, "SYS");
//        $StatusList = form_dropdown('Status_Id', $optionStatus, '', 'id=Status_Id');
//
//        $str_form = $this->parser->parse('form/product_location_list_V2', array("data" => $parameter, "test_parse" => "teat pass by parse"
//            , "WHList" => $WHList, "ZONEList" => $ZoneList, "CateList" => $CateList, "StatusList" => $StatusList), TRUE);
//        $this->parser->parse('form_template', array(
//            'user_login' => $this->session->userdata('user_id')
//            , 'copyright' => COPYRIGHT
//            , 'menu_title' => 'List of Product in Location'
////            , 'menu' => $this->menu->loadMenuAuth()
//            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
//            , 'form' => $str_form
//            , 'button_back' => ''
//            , 'button_clear' => ''
//            , 'button_save' => ''
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('user','putaway/form','A','')\">"
//        ));
//    }
    // <<<<<------ END Add function putawayList_V2() by Ton! 20130902 ------>>>>>
    //
    // <<<<<------ START Add function paDataList() by Ton! 20130902 ------>>>>>
    function paDataList() {
        $WarehouseId = $this->input->get_post("Warehouse_Id");
        $ZoneId = $this->input->get_post("Zone_Id");
        $CategoryId = $this->input->get_post("Category_Id");
        $prodCode = $this->input->get_post("Product_Code");
        $Status = $this->input->get_post("Status_Id");

        $prodId = '';
        if ($prodCode != '') {
            $q_Product = $this->prod->getProductMaster('', $prodCode);
            $r_Product = $q_Product->result();
            if (count($r_Product) > 0) {
                foreach ($r_Product as $value) {
                    $prodId = $value->Id;
                }
            }
        }

        $q_data = $this->put->getPutawayList($WarehouseId, $ZoneId, $CategoryId, $prodId, $Status);
        $r_data = $q_data->result();
        if (count($r_data) > 0) {
            $view['paRlueList'] = $r_data;
        } else {
            $view['paRlueList'] = NULL;
        }

        $this->load->view("pa_rlue_product_form", $view);
    }

    function freeLocationList() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        ##### Permission Button. by Ton! 20140213 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri_PARule);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        if (in_array("-3", $action_parmission) && !in_array("-4", $action_parmission)):
            $column = array("No.", "PutAway Name", "Product Status", "Product Sub Status", "DIW Class", "Remarks", EDIT);
        elseif (in_array("-4", $action_parmission) && !in_array("-3", $action_parmission)):
            $column = array("No.", "PutAway Name", "Product Status", "Product Sub Status", "DIW Class", "Remarks", DEL);
        elseif ((in_array("-3", $action_parmission)) && in_array("-4", $action_parmission)):
            $column = array("No.", "PutAway Name", "Product Status", "Product Sub Status", "DIW Class", "Remarks", EDIT, DEL);
        else:
            $column = array("No.", "PutAway Name", "Product Status", "Product Sub Status", "DIW Class", "Remarks");
        endif;

        $button_add = "";
        if (in_array("-2", $action_parmission)):
            $button_add = "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('user','putaway/freeLocation','A','')\">";
        endif;
        ##### END Permission Button. by Ton! 20140213 #####
//        $column = array("No.", "PutAway Name", "Product Status", "Product Sub Status", "Product Category", "Remarks", EDIT, DEL);

        $putaway_list = $this->put->get_putaway_list()->result();
        $parameter['show_column'] = $column;
        $parameter['plist'] = $putaway_list;
        $parameter['action'] = $action; // Add by Ton! For set Permission Button.
        $str_form = $this->parser->parse('form/product_location_list', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        // EDIT By Akkarapol, 14/11/2013, เปลี่ยนการ parse จากที่เรียก form_template เป็นเรียก list_template เพื่อให้การแสดงผลของหน้า list เป็นเหมือนๆกันทุกหน้า
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'List of Putaway Rule'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'datatable' => $str_form
//            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('user','putaway/freeLocation','A','')\">"
            , 'button_add' => $button_add # Permission Button. by Ton! 20140213
            , 'response' => $this->session->flashdata('response')
        ));
    }

    // <<<<<------ END Add function paDataList() by Ton! 20130902 ------>>>>>
    //
    // <<<<<------ START Comment Out by Ton! 20130903 ------>>>>>
    /* function putawayList() {
      #show Product on Location From STK_M_Product_Location
      $query = $this->put->getPutawayList();
      $putaway_list = $query->result();
      p($putaway_list);
      $column = array("Running No.", "Warehouse", "Zone", "Location", "Category", "Code", "Product Name", "Status", EDIT, DEL);
      $action = array(EDIT, DEL);

      //        $datatable = $this->datatable->genTableFixColumn($query, $putaway_list, $column, "putaway/putaction", $action);
      //        $module = "";
      //        $sub_module = "";
      //        $this->parser->parse('list_template', array(
      //            'menu' => $this->menu->loadMenu()
      //            , 'menu_title' => 'List of Product in Location'
      //            , 'datatable' => $datatable
      //            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "'
      //          ONCLICK=\"openForm('user','putaway/form','A','')\">"
      //        ));

      $parameter['show_column'] = $column;
      $parameter['plist'] = $putaway_list;
      $str_form = $this->parser->parse('form/product_location_list', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

      $this->parser->parse('form_template', array(
      'user_login' => $this->session->userdata('user_id')
      , 'copyright' => COPYRIGHT
      , 'menu_title' => 'List of Product in Location'
      //            , 'menu' => $this->menu->loadMenuAuth()
      , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
      , 'form' => $str_form
      , 'button_back' => ''
      , 'button_clear' => ''
      , 'button_save' => ''
      , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . ADD . "' ONCLICK=\"openForm('user','putaway/form','A','')\">"
      ));
      } */
    // <<<<<------ END Comment Out by Ton! 20130903 ------>>>>>

    function form() {
        #Form for add Product to Location by Zone
        $product_query = $this->put->getProductStatus();
        $pd_status_list = $product_query->result();
        $optionPS = genOptionDropdown($pd_status_list, "SYS");

        $category_query = $this->put->getProductCategoryList();
        $category_list = $category_query->result();
        $optionCategory = genOptionDropdown($category_list, "SYS");

        $parameter = array(
            "selectWarehouse" => $this->put->getWarehouseList(),
            "selectCategory" => $optionCategory,
            "selectProductStatus" => $optionPS,
            "code" => '',
            "action" => '',
            "values" => ''
        );

        $str_form = $this->parser->parse('form/product_to_location', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => 'Wenuka'
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'List of Product in Location : Add Product to Location'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function getZoneSelect() {
        #for jquery receive warehouse_id and get zone
        $w = $this->input->get('w');
        $z = $this->input->get('z'); //zoneid กรณีที่เรา edit zone จะมี zoneid ส่งเข้ามาด้วย

        $list = $this->put->getZoneList($w);
        $option = '<SELECT NAME="zone_list" id="zone_list">';
        $option.='<OPTION VALUE="">Please select Zone</OPTION>';
        if (count($list) != 0) {
            foreach ($list as $zone) {
                $chk = "";
                if ($z == $zone['id']):
                    $chk = "selected";
                endif;
                $option.='<OPTION VALUE="' . $zone['id'] . '"' . $chk . '>' . $zone['name'] . '</OPTION>';
            }
        }

        $option.='</SELECT>';
        echo $option;
    }

    function getZoneCategory() {
        #for jquery receive zone_id and get Category of Zone
        $zone = $this->input->get('zone');
        $warehouse = $this->input->get('w');
        $cate_id = $this->input->get('cate'); //ADD BY POR 2013-11-11 เพิ่มเติมรหัส category
        $this->load->model("zone_model");
        $count_location = $this->zone_model->checkZoneLocation($warehouse, $zone);
        if (count($count_location) == 0) {
            $option = 0;
        } else {
            $zone_cate_query = $this->zone_model->getZoneCategoryList($zone);
            $zone_list = $zone_cate_query->result();
            //print_r($zone_list);
            $option = '<SELECT NAME="zone_cate" id="zone_cate">';
            $option.='<OPTION VALUE="">Please select Zone Category</OPTION>';
            if (count($zone_list) != 0) {
                foreach ($zone_list as $z) {
                    //ADD BY POR 2013-11-11 เพิ่มเติมให้เลือก category ที่ต้องการแก้ไข
                    $chk = "";
                    if ($cate_id == $z->Dom_ID):
                        $chk = "selected";
                    endif;
                    //END ADD
                    $option.='<OPTION VALUE="' . $z->Dom_ID . '" ' . $chk . '>' . $z->Dom_EN_Desc . '</OPTION>';
                }
            }

            $option.='</SELECT>';
        }
        echo $option;
    }

    function check_sub_zone() {
        #for jquery receive zone_id and get Category of Zone
        $zone = $this->input->get('zone');
        $warehouse = $this->input->get('w');
        $zone_cate = $this->input->get('zone_cate');
        $this->load->model("zone_model", "z");
        $location = $this->z->checkZoneCateLocation($warehouse, $zone, $zone_cate);
        //$count_location=$this->zone_model->checkZoneLocation($warehouse,$zone);
        if (count($location) == 0) {
            $option = 0;
        } else {
            $option = 1;
        }
        echo $option;
    }

    function getProductList() {
        # for jquery
        # 1.receive warehouse_id,zone_id,zone_cate and product_status
        # 2.and get all Product (select product not in STK_Product_Location
        $category = $this->input->get('cate');
        $warehouse = $this->input->get('warehouse');
        $zone = $this->input->get('zone');
        $zone_cate = $this->input->get('zone_cate');
        $status = $this->input->get('status');
        if ($category != "") {
            //echo ' Product Category : '.$category;
        } else {
            // echo ' All Product';
        }
        # 2
        $product = $this->put->getProductFromCategory($category, $warehouse, $zone, $zone_cate, $status);
        /*
          p($product);
          [id] => 1
          [code] => 0010900200599
          [name] => JSU_������ MAKARON  �ժ���

         */
        $rows = array();
        foreach ($product as $p) {
            $row['id'] = $p['id'];
            $row['code'] = $p['code'];
            $row['name'] = $p['name'];
            $rows[] = thai_json_encode($row);
        }

        //$view['data']=$product;
        # show product style table
        //$this->load->view("putaway_table_form",$view);
        echo json_encode($rows);
    }

    function putaction() {
        # receive action Edit, Delete from page putawayList
        $id = $this->input->post('id');
        $mode = $this->input->post('mode');

        if ($mode == "E") {
            redirect('/putaway/editProductToLocation?id=' . $id, 'refresh');
        } else if ($mode == "D") {
            redirect('/putaway/deleteProductToLocation?id=' . $id, 'refresh');
        } else {
            redirect('/putaway', 'refresh');
        }
    }

    function saveProductLocation() {
        # receive Zone and add product to location (STK_Product_Location)
        # save product list to all location in Zone
        $data = $this->input->post();
        //p($data);
        //exit();
        $status = $this->put->saveProductToLocation($data);
        //echo ' status = '.$status;
        if ($status == 1) {
            //redirect('/putaway', 'refresh');
            $url = 'putaway';
            $new_status = "C001";
        } else if ($status == 0) {
            $url = 'putaway/form';
            $new_status = "C000";
        } else {
            //echo ' error ';
            $new_status = "C002";
            $url = 'putaway/form';
        }
        $json['status'] = $new_status;
        $json['url'] = $url;
        echo json_encode($json);
    }

    function editProductToLocation() {
        # for Edit Location receive Id(STK_Product_location)
        $id = $this->input->get('id');

        //$location=$this->put->getLocationFromProductLocation($id);
        $location = $this->put->locationInfo($id);
        # get Product and Quantity(STK_T_Inbound) put in this Location
        $product = $this->put->getProductInbound('Edit', $id, '');

        $product_list = $product->result();
        $column = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_status')
            , _lang('product_name')
            , _lang('qty')
            , _lang('del')
        );
        $action = array(DEL);

        /* $datatable=$this->datatable->genTableFixColumn($product,$product_list,$column,"putaway/putaction",$action);
          $module = "";
          $sub_module = "";
          $this->parser->parse('list_template', array(
          'menu' => $this->menu->loadMenu()
          ,'menu_title' => 'List of Product in Location : '.$location['location_code']
          ,'datatable'  => $datatable
          ,'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='".BACK."'	 ONCLICK=\"openForm('user','putaway','','')\"> <INPUT TYPE='button' class='button dark_blue' VALUE='Add Product to Location Code : ".$location['location_code']."'	 ONCLICK=\"openForm('user','putaway/addProductByLocation?id=".$location['id']."&pid=".$id."','A','')\">"
          ));
         */

        $parameter['show_column'] = $column;
        $parameter['plist'] = $product_list;
        $parameter['location_id'] = $id;
        $parameter['location_code'] = $location;
        $str_form = $this->parser->parse('form/product_location_edit', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);


        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'List of Product in Location : ' . $location
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function addProductByLocation() {
        #Save many Product to 1 location From page edit location
        $id = $this->input->get('id');   // Location Id
        //$pid=$this->input->get('pid'); // Id of table STK_M_Product_location
        //$location=$this->put->getLocationFromProductLocation($id);
        $pid = '';
        $location_info = $this->put->getWarehouseFromLocation($id);

        $product_query = $this->put->getProductStatus();
        $pd_status_list = $product_query->result();
        $optionPS = genOptionDropdown($pd_status_list, "SYS");

        $category_query = $this->put->getProductCategoryList();
        $category_list = $category_query->result();
        $optionCategory = genOptionDropdown($category_list, "SYS");

        //p($location_info);
        $parameter = array(
            "location_info" => $location_info,
            "Location_Code" => $this->put->getLocationCode($id),
            "Location_Id" => $id,
            "Product_Location_Id" => $pid,
            "selectCategory" => $optionCategory,
            "selectProductStatus" => $optionPS,
            "action" => '',
            "values" => ''
        );

        $str_form = $this->parser->parse('form/product_by_location', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => 'Wenuka'
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Add Product to Location : ' . $this->put->getLocationCode($id)
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function getProductListByLocation() {
        #for jquery from page addProductByLocation
        #show product not in this Location
        $location_id = $this->input->get('location_id');
        $status = $this->input->get('status');
        $cate = $this->input->get('cate');
        $product = $this->put->getProductOnLocation($location_id, $status, $cate);
        //$view['data']=$product;
        //$this->load->view("form/product_table_form",$view);
        echo json_encode($product);
    }

    function saveProductByLocation() {
        #Save many product on 1 Location
        $data = $this->input->post();
        //p($data);
        //exit();
        //location_id
        $status = $this->put->saveToLocation($data);
        //echo 'status='.$status;
        if ($status == 0) {
            //redirect('/putaway/freeLocation','refresh');
            $url = "putaway/freeLocation";
            $nstatus = "C000";
        } else {
            /*
              if($data['product_location_id']!=""){
              //redirect('/putaway/editProductToLocation?id='.$data['product_location_id'], 'refresh');
              $url="putaway/editProductToLocation?id=".$data['product_location_id'];
              }
              else{
              //redirect('/putaway/editProductToLocation?id='.$status, 'refresh');
             */
            $url = "putaway/editProductToLocation?id=" . $data['location_id'];
            //}
            $nstatus = "C001";
        }

        $json['status'] = $nstatus;
        $json['url'] = $url;
        echo json_encode($json);
    }

    function deleteProductToLocation() {
        #delete product location
        $id = $this->input->post('id');
        $location_id = $this->input->post('location_id');
        $result = $this->put->deleteProductLocation($id, $location_id);
        if ($result === FALSE) :
            #show product quantity
//            $location = $this->put->getLocationFromProductLocation($id);
//            $product_list = $this->put->getProductInbound("DEL", $location['id'], $location['product_code'])->result();
//            $column = array('Process Id', 'Product Code', 'Product Status', 'Quantity');
//            $action = array();
//            $datatable = 'Can not delete this location : ' . $location['id'];
            $json['status'] = "C001";
            $json['error_msg'] = "Can not delete this Product";
        else :
            $json['status'] = "C000";
            $json['error_msg'] = "";
        endif;

        echo json_encode($json);
    }

    function freeLocation() {// Edit by Ton! 20131024
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        //ADD BY POR 2013-11-11
        $idedit = (int) $this->input->get("id"); //==รับค่าที่ต้องการแก้ไขโดยแปลงให้เป็นตัวเลข
        //==ดึงข้อมูลที่ต้องการแก้ไขออกมาโดยเรียกใช้ function getAllDataEdit ที่ได้สร้างไว้สำหรับดึงข้อมูลจาก id putaway
        $para = array();
        if ($idedit > 0):
            /* $data_edit = $this->put->getAllDataEdit($idedit)->result();
              $para['Warehouse_Id'] = $data_edit[0]->Warehouse_Id;
              $para['Zone_Id'] = $data_edit[0]->Zone_Id;
              $para['Product_Status_Id'] = $data_edit[0]->Product_Status_Id;
              $para['pro_status'] = $data_edit[0]->pro_status;
              $para['Product_Sub_Status_Id'] = $data_edit[0]->Product_Sub_Status_Id;
              $para['pro_substatus'] = $data_edit[0]->pro_substatus;
              $para['Product_Category_Id'] = $data_edit[0]->Product_Category_Id;
              $prat['pro_catestatus'] = $data_edit[0]->pro_catestatus;
              $para['Active'] = $data_edit[0]->Active;
              $para['Remarks'] = $data_edit[0]->Remarks;
              $para['putaway_name'] = $data_edit[0]->PutAway_Name; */

            # Edit by Ton! 20140129
            $para['Warehouse_Id'] = "";
            $para['Zone_Id'] = "";
            $para['Product_Status_Id'] = "";
            $para['pro_status'] = "";
            $para['Product_Sub_Status_Id'] = "";
            $para['pro_substatus'] = "";
            $para['Product_Category_Id'] = "";
            $para['pro_catestatus'] = "";
            $para['Active'] = "";
            $para['Remarks'] = "";
            $para['putaway_name'] = "";

            $data_edit = $this->put->get_putaway_list($idedit)->result();
            if (count($data_edit) > 0):

                $para['Warehouse_Id'] = $data_edit[0]->Warehouse_Id;  // Add By Akkarapol, 07032014, เพิ่มไปเพราะว่า มันไม่มีค่านี้ส่งไปให้ view เพราะงั้นแล้ว view ก็เลยไม่สามารถแสดงค่าที่เลือกแล้วได้นั่นเอง
                $para['Zone_Id'] = $data_edit[0]->Zone_Id;  // Add By Akkarapol, 07032014, เพิ่มไปเพราะว่า มันไม่มีค่านี้ส่งไปให้ view เพราะงั้นแล้ว view ก็เลยไม่สามารถแสดงค่าที่เลือกแล้วได้นั่นเอง

                $para['Product_Status_Id'] = $data_edit[0]->Product_Status_Id;
                $para['pro_status'] = strtoupper($data_edit[0]->Product_Status);
                $para['Product_Sub_Status_Id'] = $data_edit[0]->Product_Sub_Status_Id;
                $para['pro_substatus'] = $data_edit[0]->Product_Sub_Status;
                $para['Product_Category_Id'] = $data_edit[0]->Product_Category_Id;
                $para['pro_catestatus'] = $data_edit[0]->Product_Category;
                $para['Active'] = $data_edit[0]->Active_Status;
                $para['Remarks'] = $data_edit[0]->Remarks;
                $para['putaway_name'] = $data_edit[0]->PutAway_Name;
            endif;
        endif;
        //END ADD
        #search location not have product
        $category_list = $this->put->getProductCategoryList()->result();
        $optionCategory = genOptionDropdown($category_list, "SYS_ID");

        #Get Product Status //Add by Ton! 20131021
        $r_product_status = $this->prod->selectProductStatus(INACTIVE)->result();
        $product_status_list = genOptionDropdown($r_product_status, "SYS_ID");

        #Get Product Sub Status //Add by Ton! 20131021
        $r_product_Substatus = $this->prod->selectSubStatus()->result();
        $product_Substatus_list = genOptionDropdown($r_product_Substatus, "SYS_ID");

        $parameter = array(
            "selectWarehouse" => $this->put->getWarehouseList(),
            "selectCategory" => $optionCategory,
            "selectStatus" => $product_status_list,
            "selectSubstatus" => $product_Substatus_list,
            "putaway_name" => '',
            "putaway_id" => $idedit//For Edit.
        );
        $str_form = $this->parser->parse('form/free_location', array("data" => $parameter, "data_edit" => $para), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('user_id')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'List of Free Location :'
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function showFreeLocation() {
        $warehouse = ($this->input->get('warehouse') == '' ? NULL : $this->input->get('warehouse')); //$this->input->get('warehouse');
        $zone = ($this->input->get('zone') == '' ? NULL : $this->input->get('zone')); //$this->input->get('zone');
        $zone_cate = ($this->input->get('zone_cate') == '' ? NULL : $this->input->get('zone_cate')); //$this->input->get('zone_cate');
        $putaway_id = (int) $this->input->get('putaway_id');
        $limit_start = (int) $this->input->get('limit_start');
        $limit_max = (int) $this->input->get('limit_max');

        $selected_location = array();
        $data_location = $this->put->getLocationByPutaway($putaway_id)->result_array();
        foreach ($data_location as $k => $v) :
            $selected_location[] = $v['Location_Id'];
        endforeach;

        // Add by Ton! 20131030
        $zone_cate_id = NULL;
        if (!empty($zone_cate)): //ADD BY POR 2013-11-07 ถ้า $zone_cate เป็นค่าว่าง ไม่ต้องนำไปค้นหา $zone_cate_id อีก
            $q_zone_cate = $this->zone->getAllCategoryList($zone_cate);
            $r_zone_cate = $q_zone_cate->result();
            if (count($r_zone_cate) > 0):
                foreach ($r_zone_cate as $value) :
                    $zone_cate_id = $value->Dom_ID;
                endforeach;
            endif;
        endif;
        $location = $this->put->searchFreeLocation($warehouse, $zone, $zone_cate_id, $selected_location, $putaway_id, $limit_start, $limit_max);
        echo json_encode($location);
        exit();
    }

#  Open Form With Data

    /**
     * function openActionForm use for open putaway
     *
     * modified : kik  20140708 : for add column invoice and contianer and Tuning code
     *
     */
    function openActionForm() {


        $this->output->enable_profiler($this->config->item('set_debug'));

        $flow_id = $this->input->post("id");

        $this->load->model("workflow_model", "flow");
        $this->load->model("contact_model", "contact");
        $this->load->model("company_model", "company");
        $this->load->model("system_management_model", "sys");
        $this->load->model("stock_model", "stock");

        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        #Retrive Data from Table
        $flow_detail = $this->flow->getFlowDetail($flow_id, 'STK_T_Order');
        $process_id = $flow_detail[0]->Process_Id;
        $order_id = $flow_detail[0]->Order_Id;
        $present_state = $flow_detail[0]->Present_State;
        $module = $flow_detail[0]->Module;

        // validate document exist state
        $valid_state = validate_state($module);
        if ($valid_state) :
            redirect($valid_state);
        endif;

        // register token
        $parameter['token'] = register_token($flow_id, $present_state, $process_id);

        $parameter['process_type'] = $flow_detail[0]->Process_Type;
        $parameter['document_no'] = $flow_detail[0]->Document_No;
        $parameter['doc_refer_int'] = $flow_detail[0]->Doc_Refer_Int;
        $parameter['doc_refer_ext'] = $flow_detail[0]->Doc_Refer_Ext;
        $parameter['doc_refer_inv'] = $flow_detail[0]->Doc_Refer_Inv;
        $parameter['doc_refer_ce'] = $flow_detail[0]->Doc_Refer_CE;
        $parameter['doc_refer_bl'] = $flow_detail[0]->Doc_Refer_BL;
        $parameter['owner_id'] = $flow_detail[0]->Owner_Id;
        $parameter['renter_id'] = $flow_detail[0]->Renter_Id;
        $parameter['shipper_id'] = $flow_detail[0]->Source_Id;
        $parameter['consignee_id'] = $flow_detail[0]->Destination_Id;
        $parameter['receive_type'] = $flow_detail[0]->Doc_Type;
        $parameter['est_receive_date'] = $flow_detail[0]->Est_Action_Date;
        $parameter['remark'] = $flow_detail[0]->Remark;
        $parameter['vendor_id'] = $flow_detail[0]->Vendor_Id;
        $parameter['driver_name'] = $flow_detail[0]->Vendor_Driver_Name;
        $parameter['car_no'] = $flow_detail[0]->Vendor_Car_No;
        $parameter['receive_date'] = $flow_detail[0]->Action_Date;
        $parameter['is_pending'] = $flow_detail[0]->Is_Pending;
        $parameter['is_repackage'] = $flow_detail[0]->Is_Repackage;
        $parameter['is_urgent'] = $flow_detail[0]->Is_urgent;

        /**
         * GET Activity By  : show pick by
         * @add code by kik : get sub_module for show activity by
         */
        $module_activity = 'putaway';
        $sub_module_activity = $this->order_movement->get_submodule_activity($order_id, $module_activity, 'cf_putaway_by');
        //end of get activity by

        /**
         * GET Order Detail
         */
        $order_by = "STK_T_Order_Detail.Item_Id ASC";
        $order_deatil = $this->stock->getOrderDetail($order_id, true, $order_by, $sub_module_activity, $module_activity); //ADD BY BALL 2013-12-06 เพิ่มรับ parameter ตัวสุดท้าย เพื่อบอกว่าต้องการให้แสดงใครทำรายการ  //add parameter ตัวที่ 3 by kik เพื่อ query confirm qty มากกว่า 0 ขึ้นมาแสดงผล 20140814
//        p($order_deatil);exit();


        $parameter['order_id'] = $order_id;
        $parameter['order_deatil'] = $order_deatil;

        #Get Renter [Company Renter] list
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY");
        $parameter['renter_list'] = $renter_list;

        #Get Shipper[Company Supplier] list
        $q_shipper = $this->company->getSupplierAll();
        $r_shipper = $q_shipper->result();
        $shipper_list = genOptionDropdown($r_shipper, "COMPANY");
        $parameter['shipper_list'] = $shipper_list;

        #Get Consignee[Company Owner]  list
        $q_consignee = $this->company->getOwnerAll();
        $r_consignee = $q_consignee->result();
        $consignee_list = genOptionDropdown($r_consignee, "COMPANY");
        $parameter['consignee_list'] = $consignee_list;

        #Get Receive Type list
        $r_receive_type = $this->sys->getReceiveType();
        $receive_list = genOptionDropdown($r_receive_type, "SYS");
        $parameter['receive_list'] = $receive_list;

        #Get Vendor [Company Vendor] list
        $q_vendor = $this->company->getVendorAll();
        $r_vendoe = $q_vendor->result();
        $vendor_list = genOptionDropdown($r_vendoe, "COMPANY");
        $parameter['vendor_list'] = $vendor_list;

//        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state);
        $data_form = $this->workflow->openWorkflowForm($process_id, $present_state, $this->session->userdata('user_id'), "flow/flowPutawayList"); // Button Permission. Add by Ton! 20140131

        $parameter['user_id'] = $this->session->userdata('user_id');
        $parameter['flow_id'] = $flow_id;
        $parameter['process_id'] = $process_id;
        $parameter['present_state'] = $present_state;
        $parameter['data_form'] = (array) $data_form;
        $parameter['from_State'] = $data_form->from_State; //BY POR 2013-10-03 เพิ่มให้ส่งค่ากลับว่าถึง step ไหนแล้ว
        $parameter['statusprice'] = $conf_price_per_unit;
        $parameter['conf_inv'] = $conf_inv;
        $parameter['conf_cont'] = $conf_cont;

        // Add By Akkarapol, 11/03/2014, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        $where['Dom_Host_Code'] = 'SUB_STATUS';
        $sub_status_no_specefied = $this->sys->getDomCodeByDomENDesc('No Specified', $where)->row_array();
        $parameter['sub_status_no_specefied'] = $sub_status_no_specefied['Dom_Code'];
        $sub_status_return = $this->sys->getDomCodeByDomENDesc('Return', $where)->row_array();
        $parameter['sub_status_return'] = $sub_status_return['Dom_Code'];
        $sub_status_repackage = $this->sys->getDomCodeByDomENDesc('Repackage', $where)->row_array();
        $parameter['sub_status_repackage'] = $sub_status_repackage['Dom_Code'];
        // END Add By Akkarapol, 11/03/2014, เพิ่มการ query ค่า sub status จาก db เพื่อให้ค่าของ dom_code ที่ได้ออกมานั้น ถูกต้องทั้งหมด และไม่ต้องไปตามแก้ในโค๊ดอีก
        # LOAD FORM
        //p($data_form->form_name);
        $str_form = $this->parser->parse("form/" . $data_form->form_name, $parameter, TRUE);

        $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportFile(\'PDF\')" value="PDF">'; // Add By Akkarapol, 20/11/2013, เพิ่มปุ่ม สำหรับ Generate PDF เพื่อออกใบ Putaway Job
        // List pallet Sent To RePrint Pallet Tag
        foreach ($parameter['order_deatil'] as $key => $value) {
            $pallet_list .= $value->Pallet_Code;
        }
	    $data_form->str_buttun .= '<input class="button dark_blue" type="button" onclick="exportPallet(\''.$pallet_list.'\')" value="Pallet">';
        //ADD BY POR 2014-01-14 เพิ่มให้ตรวจสอบ config ว่าถ้ามี price_per_unit เป็น TRUE จะให้ระบุราคาต่อหน่วยด้วย

        $priceperunit = '';
        $unitofprice = '';


        #ตรวจสอบว่า config price per unit เปิดใช้งานและ present state เท่ากับ 5 จะสามารถแก้ไข ราคาได้
        if ($conf_price_per_unit && $present_state == 6):

            // comment out by kik : 20140708 : for can edit price per unit in approve putaway
            /* comment ไว้ก่อน หากต้องการให้คีย์ได้ก็ให้เอาส่วนนี้ออก */

            $priceperunit = " ,{
                    sSortDataType: \"dom-text\",
                    sType: \"numeric\",
                    type: 'text',
                    onblur: \"submit\",
                    event: 'click keyup',
                    loadfirst: true,
                    cssclass: \"required number\",
                    fnOnCellUpdated: function(sStatus, sValue, settings) {
                    calculate_qty();
                    }
              }";

            $unitofprice = ",{
                    loadurl: '" . site_url() . '/pre_receive/getPriceUnit' . "',
                    loadtype: 'POST',
                    type: 'select',
                    event: 'click',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_unit_price);
                    return value;
                    }
              }";

//            comment by kik : 20140708 : for open edit price per unit in putaway
//            $priceperunit = ' ,null';
//            $unitofprice = ',null';


        else:

            $priceperunit = ' ,null';
            $unitofprice = ',null';

        endif; // end of set edit price per unit

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => $data_form->from_state_name
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'priceperunit' => $priceperunit //ADD BY POR 2014-01-14 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            , 'unitofprice' => $unitofprice //ADD BY POR 2014-01-14 ให้ส่งตัวแปรเกี่ยวกับการแสดงราคาต่อหน่วย
            // Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="form_receive"></i>'
            // END Add By Akkarapol, 21/09/2013, เพิ่ม Class toggle เข้าไป ให้รองรับกับที่ K.Krip เขียนโค๊ดไว้
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_cancel' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '"	VALUE="' . CANCEL . '"    ONCLICK="cancel()">'
            , 'button_action' => $data_form->str_buttun
        ));
    }

    function confirmAction() {
        $respond = $this->_updateProcess($this->input->post());
        if ("C001" == $respond) {
            $json['status'] = "C002";
            $json['error_msg'] = "";
        } else {
            $json['status'] = "E001";
            $json['error_msg'] = "Incomplete Data";
        }
        $json['respond'] = "$respond";
        echo json_encode($json);
    }

    function approveAction() {
        $this->transaction_db->transaction_start(); //ADD BY POR 2014-03-10
        $check_not_err = TRUE;

        $respond = $this->_updateProcess($this->input->post());
        if (!empty($respond['critical'])) :
            $check_not_err = FALSE;

            /**
             * Set Alert Zone (set Error Code, Message, etc.)
             */
            $return['critical'][]['message'] = "Can not update Process.";
            $return = array_merge_recursive($return, $respond);

        endif;

        if ($check_not_err):
            $this->transaction_db->transaction_end();

            $json['status'] = "save";
            $set_return['message'] = "Approve Putaway Complete.";
            $return['success'][] = $set_return;
            $json['return_val'] = $return;
        else:
            $this->transaction_db->transaction_rollback();

            $set_return['critical'][]['message'] = "Approve Putaway Incomplete.";
            $json['status'] = "save";
            $json['return_val'] = array_merge_recursive($set_return, $return);
        endif;


        echo json_encode($json);
    }

    function _updateProcess() {
        #Load config
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $flow_id = $this->input->post("flow_id");
        $order_id = $this->input->post("order_id");
        $process_id = $this->input->post("process_id");
        $present_state = $this->input->post("present_state");
        $process_type = $this->input->post("process_type");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $user_id = $this->input->post("user_id");

        # Parameter of document number
        $document_no = $this->input->post("document_no");
        $doc_refer_int = $this->input->post("doc_refer_int");
        $doc_refer_ext = $this->input->post("doc_refer_ext");
        $doc_refer_inv = $this->input->post("doc_refer_inv");
        $doc_refer_ce = $this->input->post("doc_refer_ce");
        $doc_refer_bl = $this->input->post("doc_refer_bl");

        # Parameter Order Document
        $owner_id = $this->input->post("owner_id");
        $renter_id = $this->input->post("renter_id");
        $shipper_id = $this->input->post("shipper_id");
        $consignee_id = $this->input->post("consignee_id");
        $est_receive_date = $this->input->post("est_receive_date");
        $receive_type = $this->input->post("receive_type");
        $is_pending = $this->input->post("is_pending");
        $is_repackage = $this->input->post("is_repackage");
        $is_urgent = $this->input->post("is_urgent");
        $remark = $this->input->post("remark");

        $vendor_id = $this->input->post("vendor_id");
        $driver_name = $this->input->post("driver_name");
        $car_no = $this->input->post("car_no");
        $receive_date = $this->input->post("receive_date");

        # Parameter Order Detail
        $prod_list = $this->input->post("prod_list");

        $prod_del_list = $this->input->post("prod_del_list");

        # Parameter Index Datatable
        $ci_prod_code = $this->input->post("ci_prod_code");
        $ci_lot = $this->input->post("ci_lot");
        $ci_serial = $this->input->post("ci_serial");
        $ci_mfd = $this->input->post("ci_mfd");
        $ci_exp = $this->input->post("ci_exp");
        $ci_reserv_qty = $this->input->post("ci_reserv_qty");
        $ci_remark = $this->input->post("ci_remark");
        $ci_prod_id = $this->input->post("ci_prod_id");
        $ci_prod_status = $this->input->post("ci_prod_status");
        $ci_unit_id = $this->input->post("ci_unit_id");
        $ci_item_id = $this->input->post("ci_item_id");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_confirm_qty = $this->input->post("ci_confirm_qty");
        $ci_suggest_loc = $this->input->post("ci_suggest_loc");
        $ci_actual_loc = $this->input->post("ci_actual_loc");

        $ci_putaway_by = $this->input->post("ci_putaway_by");
        $ci_putaway_date = $this->input->post("ci_putaway_date");

        //ADD BY POR 2014-01-10 เพิ่มเกี่ยวกับราคา
        if ($conf_price_per_unit) {
            $ci_price_per_unit = $this->input->post("ci_price_per_unit");
            $ci_unit_price = $this->input->post("ci_unit_price");
            $ci_all_price = $this->input->post("ci_all_price");
            $ci_unit_price_id = $this->input->post("ci_unit_price_id");
        }
        //END ADD



        $pending_status_code = "";
        if ($is_pending != ACTIVE) {
            $is_pending = INACTIVE;
        } else {
            $this->load->model("system_management_model", "sys");
            $result = $this->sys->getPendingStatus();
            if (!empty($result)) {
                foreach ($result as $rows) {
                    $pending_status_code = $rows->Dom_Code;
                }
            }
        }

        if ($is_repackage != ACTIVE) {
            $is_repackage = INACTIVE;
        }

        //add Urgent for ISSUE 3312 : by kik : 20140120
        if ($is_urgent != ACTIVE) {
            $is_urgent = INACTIVE;
        }

        # Update Order and Order Detail
        $this->load->model("stock_model", "stock");
        $this->load->model("workflow_model", "flow");

        $data['Document_No'] = $document_no;
        $return = array();
        $check_not_err = TRUE;

        /**
         * update Order
         */
        if ($check_not_err):
            $order = array(
                'Doc_Refer_Int' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_int))
                , 'Doc_Refer_Ext' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ext))
                , 'Doc_Refer_Inv' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_inv))
                , 'Doc_Refer_CE' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_ce))
                , 'Doc_Refer_BL' => strtoupper(iconv("UTF-8", "TIS-620", $doc_refer_bl))
                , 'Doc_Type' => $receive_type
                , 'Owner_Id' => $owner_id
                , 'Renter_Id' => $renter_id
                , 'Estimate_Action_Date' => $est_receive_date
                , 'Source_Id' => $shipper_id
                , 'Destination_Id' => $consignee_id
                , 'Is_Pending' => $is_pending
                , 'Is_Repackage' => $is_repackage
                , 'Is_urgent' => $is_urgent
                , 'Remark' => iconv("UTF-8", "TIS-620", $remark)
                , 'Modified_By' => $user_id
                , 'Modified_Date' => date("Y-m-d H:i:s")
                , 'Vendor_Id' => $vendor_id
                , 'Vendor_Driver_Name' => iconv("UTF-8", "TIS-620", $driver_name)
                , 'Vendor_Car_No' => iconv("UTF-8", "TIS-620", $car_no)
                    //, 'Actual_Action_Date' => $receive_date
            );
            $where['Flow_Id'] = $flow_id;
            $where['Order_Id'] = $order_id;

            $result_updateOrder = $this->stock->updateOrder($order, $where);
            if (!$result_updateOrder):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not update Order.";
            endif;
        endif;


        /**
         * update Order Detail
         */
        if ($check_not_err):
            $order_detail = array();
            if (!empty($prod_list)) {
                foreach ($prod_list as $rows) {
                    $a_data = explode(SEPARATOR, $rows);
                    $is_new = $a_data[$ci_item_id];
                    $detail = array();
                    $detail['Product_Id'] = $a_data[$ci_prod_id];
                    $detail['Product_Code'] = $a_data[$ci_prod_code];
                    $detail['Product_Status'] = $a_data[$ci_prod_status];
                    $detail['Product_Sub_Status'] = $a_data[$ci_prod_sub_status];
                    //                $detail['Reserv_Qty'] = $a_data[$ci_reserv_qty]; // Comment By Akkarapol, 09/09/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องไปอัพเดทใน order detail เนื่องจากถ้าไปอัพเดทที่ order detail ด้วยแล้วนั้น ค่าที่ต้องการรับ กับค่าที่รับจริง เมื่อเอาไปคำนวนส่วนที่เหลืออยู่ จะผิด
                    $detail['Confirm_Qty'] = str_replace(",", "", $a_data[$ci_confirm_qty]); //+++++Edit BY POR 2013-11-28 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    $detail['Unit_Id'] = $a_data[$ci_unit_id];
                    //ADD BY POR 2014-01-14 เพิ่มเกี่ยวกับบันทึกราคาด้วย
                    if ($conf_price_per_unit) {
                        $detail['Price_Per_Unit'] = str_replace(",", "", $a_data[$ci_price_per_unit]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                        $detail['Unit_Price_Id'] = $a_data[$ci_unit_price_id];
                        $detail['All_Price'] = str_replace(",", "", $a_data[$ci_all_price]); //+++++ADD BY POR 2014-01-10 แปลง qty ให้อยู่ในรูปแบบ float โดยตัด comma ออก
                    }
                    //END ADD
//                    $detail['Product_Lot'] = $a_data[$ci_lot];
//                    $detail['Product_Serial'] = $a_data[$ci_serial];
                    $detail['Product_Lot'] = iconv("UTF-8", "TIS-620", $a_data[$ci_lot]);
                    $detail['Product_Serial'] = iconv("UTF-8", "TIS-620", $a_data[$ci_serial]);
                    $detail['Suggest_Location_Id'] = $a_data[$ci_suggest_loc];
                    $detail['Actual_Location_Id'] = $a_data[$ci_actual_loc];
                    $detail['Remark'] = iconv("UTF-8", "TIS-620", $a_data[$ci_remark]);

                    if ($a_data[$ci_mfd] != "") {
                        $detail['Product_Mfd'] = convertDate($a_data[$ci_mfd], "eng", "iso", "-");
                    } else {
                        $detail['Product_Mfd'] = null;
                    }

                    if ($a_data[$ci_exp] != "") {
                        $detail['Product_Exp'] = convertDate($a_data[$ci_exp], "eng", "iso", "-");
                    } else {
                        $detail['Product_Exp'] = null;
                    }

                    if ($is_pending == ACTIVE) {
                        $detail['Product_Status'] = $pending_status_code;
                    }

                    if ("new" != $is_new) {
                        unset($where);
                        $where['Item_Id'] = $is_new;
                        $where['Order_Id'] = $order_id;
                        $where['Product_Code'] = $detail['Product_Code'];
                        $statusupdate = $this->stock->updateOrderDetail($detail, $where);
                        if (!$statusupdate):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not Update STK_T_Order_Detail.";
                            break;
                        endif;
                        //            Add By Akkarapol, 29/08/2013, เพิ่มเข้าไปเพื่ออัพเดทข้อมูลในตาราง Inbound ด้วย

                        $setDataForUpdateInboundDetail = array();
//                        $setDataForUpdateInboundDetail['Product_Lot'] = $detail['Product_Lot']; //comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                        $setDataForUpdateInboundDetail['Product_Serial'] = $detail['Product_Serial'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                        $setDataForUpdateInboundDetail['Product_Mfd'] = @$detail['Product_Mfd'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                        $setDataForUpdateInboundDetail['Product_Exp'] = @$detail['Product_Exp'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                        $setDataForUpdateInboundDetail['Receive_Qty'] = $detail['Confirm_Qty']; // Edit by kik change from Reserv_Qty to Confirm_Qty for insert to Inbound Table : 04-09-2013
//                        $setDataForUpdateInboundDetail['Unit_Id'] = $detail['Unit_Id'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
                        //ADD BY POR 2014-01-09 เพิ่มเกี่ยวกับบันทึกราคาด้วย
//                        if ($this->settings['price_per_unit'] == TRUE) {//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                            $setDataForUpdateInboundDetail['Price_Per_Unit'] = $detail['Price_Per_Unit'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                            $setDataForUpdateInboundDetail['Unit_Price_Id'] = $detail['Unit_Price_Id'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                            $setDataForUpdateInboundDetail['All_Price'] = $detail['All_Price'];//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
//                        }//comment by kik : 20140721 ไม่ต้อง update ค่านี้แล้วเพราะใน step putaway ไม่มีการเปลี่ยนแปลง
                        //END ADD
                        $setDataForUpdateInboundDetail['Suggest_Location_Id'] = $detail['Suggest_Location_Id'];
                        $setDataForUpdateInboundDetail['Actual_Location_Id'] = $detail['Actual_Location_Id'];
                        $setDataForUpdateInboundDetail['Active'] = 'Y';
                        $setDataForUpdateInboundDetail['Putaway_By'] = $a_data[$ci_putaway_by]; //ADD BY POR 2013-11-08 เพิ่มเพื่อให้ไป update ตาราง inbound โดยให้นำค่ามาจาก detail
                        $setDataForUpdateInboundDetail['Putaway_Date'] = $a_data[$ci_putaway_date]; //ADD BY POR 2013-11-08 เพิ่มเพื่อให้ไป update ตาราง inbound โดยให้นำค่ามาจาก detail

                        $setDataWhere = array();
                        // Comment By Akkarapol, 11/09/2013, คอมเม้นต์ทิ้งเพราะว่า ใช้ Inbound_Id ในการ Where แล้ว เพราะอันเก่านี้มันมีปัญหาจากตอนที่ทำ Sprit
                        //                    $setDataWhere['Flow_Id'] = $flow_id;
                        //                    $setDataWhere['Product_Code'] = $detail['Product_Code'];
                        // END Comment By Akkarapol, 11/09/2013, คอมเม้นต์ทิ้งเพราะว่า ใช้ Inbound_Id ในการ Where แล้ว เพราะอันเก่านี้มันมีปัญหาจากตอนที่ทำ Sprit
                        // Add By Akkarapol, 11/09/2013, เพิ่มฟังก์ชั่น query เอาค่าของ Inbound_Item_Id ออกมาจาก Order_Detail แล้วเอามา Where ในขั้นตอนที่จะทำการ Update Inbound
                        $getOrderDetailByItemId = $this->stock->getOrderDetailByItemId($is_new);
                        $setDataWhere['Inbound_Id'] = $getOrderDetailByItemId['Inbound_Item_Id'];
                        // END Add By Akkarapol, 11/09/2013, เพิ่มฟังก์ชั่น query เอาค่าของ Inbound_Item_Id ออกมาจาก Order_Detail แล้วเอามา Where ในขั้นตอนที่จะทำการ Update Inbound

                        $statusupdateinbound = $this->stock->updateInboundDetail($setDataForUpdateInboundDetail, $setDataWhere);
                        if (!$statusupdateinbound):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not Update STK_T_Inbound.";
                            break;
                        endif;

                        unset($setDataForUpdateInboundDetail);
                        unset($setDataWhere);
                        //            END Add By Akkarapol, 29/08/2013, เพิ่มเข้าไปเพื่ออัพเดทข้อมูลในตาราง Inbound ด้วย
                    } else {
                        $detail['Order_Id'] = $order_id;
                        $detail['Confirm_Qty'] = 0;
                        $order_detail[] = $detail;
                    }
                }
                if ($check_not_err):
                    if (!empty($order_detail)) {
                        $afftectedRows = $this->stock->addOrderDetail($order_detail);

                        if (empty($afftectedRows) || $afftectedRows <= 0): //ถ้ามากกว่าถือว่ามีการ insert ข้อมูล
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not insert STK_T_Order_Detail.";
                        endif;
                    }
                endif;
            }
        endif;


        if ($check_not_err):
            if (is_array($prod_del_list) && (count($prod_del_list) > 0)) {
                unset($rows);
                unset($detail);
                $item_delete = array();
                foreach ($prod_del_list as $rows) {
                    $a_data = explode(SEPARATOR, $rows);
                    $item_delete[] = $a_data[$ci_item_id];  /* Item_Id for Delete in STK_T_Order_Detail  */
                }
                $statusremove = $this->stock->removeOrderDetail($item_delete);
                if (!$statusremove):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not remove Order Detail.";
                endif;
            }
        endif;

        $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data); // Edit by Ton! 20131021
        if (empty($action_id) || $action_id == ''):
            $check_not_err = FALSE;
            $return['critical'][]['message'] = "Can not update Workflow.";
        endif;


        //logOrderDetail($order_id, 'putaway', $action_id, $action_type);   //COMMENT BY POR 2014-07-07
        //return "C001";
        return $return;
    }

    // <<<<<------ START ADD DROPDOWN by Ton! 20130903 ------>>>>>
    function getZoneByWarehouseID() {//Add by Ton! 20130903
        $data = $this->input->post();
        $Warehouse_Id = $data['Warehouse_Id'];

        $q_Zone = $this->zone->getZoneListByWarehouseID($Warehouse_Id);
        $r_Zone = $q_Zone->result();
        $optionZONE = genOptionDropdown($r_Zone, "ZONE");
        $ZoneList = form_dropdown('Zone_Id', $optionZONE, '', 'id=Zone_Id onChange="setCategory()"');

        echo $ZoneList;
    }

    function getCategoryByZoneID() {//Add by Ton! 20130903
        $data = $this->input->post();
        $Zone_Id = $data['Zone_Id'];
        $cate_Id = $data['cate'];

        $q_Cate = $this->zone->getZoneCategoryList($Zone_Id);
        $r_Cate = $q_Cate->result();
        if (count($r_Cate) <= 0) {
            $queryCateAll = $this->zone->getAllCategoryList();
            $r_Cate = $queryCateAll->result();
        }
        $optionCate = genOptionDropdown($r_Cate, "SYS");
        $CateList = form_dropdown('Category_Id', $optionCate, $cate_Id, 'id=Category_Id');

        echo $CateList;
    }

    // <<<<<------ END ADD DROPDOWN by Ton! 20130903 ------>>>>>

    function savePutAwayRule() {//Add by Ton! 20131022
        $data = $this->input->post();

//        1. insert STK_M_PUTAWAY
//        2. update STK_M_Location

        if ($data['putaway_id'] != '' && $data['putaway_id'] != 0) :
            $type = 'upd';
        else:
            $type = 'ist';
        endif;

        $dataPutAway['PutAway_Name'] = ($data['putaway_name'] == "") ? "" : $data['putaway_name'];
        $dataPutAway['Zone_Id'] = ($data['zone_list'] == "" || $data['zone_list'] == 0) ? NULL : $data['zone_list'];

        $dataPutAway['Product_Status_Id'] = ($data['product_status'] == "") ? 0 : $data['product_status']; // Edit by Ton! 20140129
        $dataPutAway['Product_Sub_Status_Id'] = ($data['product_sub_status'] == "") ? 0 : $data['product_sub_status']; // Edit by Ton! 20140129
        $dataPutAway['Product_Category_Id'] = ($data['product_cate'] == "") ? -1 : $data['product_cate']; // Edit by Ton! 20140129

        /* Comment Out by Ton! 20140129
         * $product_status = NULL;
          if ($data['product_status'] != ''):
          $q_product_status = $this->prod->selectProductStatus(null, $data['product_status']);
          $r_product_status = $q_product_status->result();
          foreach ($r_product_status as $value) :
          $product_status = $value->Dom_ID;
          endforeach;
          $dataPutAway['Product_Status_Id'] = $product_status;
          endif;

          $product_sub_status = NULL;
          if ($data['product_sub_status'] != ''):
          $q_product_sub_status = $this->prod->selectSubStatus($data['product_sub_status']);
          $r_product_sub_status = $q_product_sub_status->result();
          foreach ($r_product_sub_status as $value) :
          $product_sub_status = $value->Dom_ID;
          endforeach;
          $dataPutAway['Product_Sub_Status_Id'] = $product_sub_status;
          endif;

          $product_category_id = NULL;
          if ($data['zone_cate'] != ''):
          $q_product_category_id = $this->put->getProductCategoryList($data['zone_cate']);
          $r_product_category_id = $q_product_category_id->result();
          foreach ($r_product_category_id as $value) :
          $product_category_id = $value->Dom_ID;
          endforeach;
          //$dataPutAway['Product_Category_Id'] = $product_category_id; COMMENT BY POR 2013-11-12 เนื่องจากค่าที่ส่งมาเป็น Dom_ID อยู่แล้ว ไม่จำเป็นต้องไปหาอีกรอบ
          $dataPutAway['Product_Category_Id'] = $data['zone_cate'];
          else:
          $dataPutAway['Product_Category_Id'] = -1;
          endif; */

        $dataPutAway['Active'] = ACTIVE;
        $dataPutAway['Remarks'] = ($data['remark'] == "") ? "" : $data['remark'];

        $wherePutAway['Id'] = ($data['putaway_id'] == "") ? "" : $data['putaway_id'];

        $this->transaction_db->transaction_start();
        $putaway_id = $this->put->save_STK_M_PUTAWAY($type, $dataPutAway, $wherePutAway);
        if ($putaway_id != FALSE) :
            $locate_id = $data['location_list']; //location ที่เลือกใหม่
            //กรณีเป็นการแก้ไขจะเคลียร์ location เดิมให้เป็น null ก่อนการ update แทนที่ด้วย location ใหม่
            if ($type == "upd") :
                $result_update = $this->put->update_to_null($data['putaway_id']);
                if ($result_update === FALSE):
                    log_message('error', 'Update STK_M_Location Putaway_Id = NULL Unsuccess.');
                    $this->transaction_db->transaction_rollback();
                    echo FALSE;
                    exit();
                endif;
                $putaway_id = $data['putaway_id'];
            endif;

            //update location ใหม่
            if (count($locate_id) > 0) :
                foreach ($locate_id as $value) :
                    $result = $this->put->update_PUTAWAY_ID($value, $putaway_id);
                    if ($result === FALSE):
                        log_message('error', 'Update STK_M_Location Putaway_Id Unsuccess.');
                        $this->transaction_db->transaction_rollback();
                        echo FALSE;
                        exit();
                    endif;
                endforeach;
            endif;

            $this->transaction_db->transaction_commit();
        else:
            log_message('error', 'Save STK_M_PUTAWAY Unsuccess.');
            $this->transaction_db->transaction_rollback();
            echo FALSE;
            exit();
        endif;

        redirect("putaway/freeLocationList"); //Add BY POR 2013-11-07 ถ้าเป็น true ให้ redirect กลับไปหน้าแรก
        //echo TRUE; COMMENT BY POR 2013-11-07 ถ้าเป็น true ให้ redirect กลับไปหน้าแรก
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-11-12
    #BY:KIK
    #เพิ่มในส่วนของ reject and (reject and return)
    #START New Comment Code #ISSUE 3034 Reject Document
    #add code for reject and return go to state 4(wait for Approve Receive Product)

    function rejectAndReturnAction() {

        $this->load->library('stock_lib');
        $this->load->model("stock_model", "stock");
        $this->load->model("pallet_model", "pallet");

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_item_id = $this->input->post("ci_item_id");

        $data['Document_No'] = $document_no;

        $this->transaction_db->transaction_start();

        $check_not_err = TRUE;

        if ($next_state == 7) {

            if ($check_not_err):
                $orderData = array(
                    'Actual_Action_Date' => NULL
                    , 'Real_Action_Date' => NULL
                );

                $orderWhere['Order_Id'] = $order_id;
                $result_updateOrder = $this->stock->_updateOrder($orderData, $orderWhere);
                if (!$result_updateOrder):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Order.";
                endif;
            endif;


            /**
             * update inbound and onhand , pallet
             */
            if ($check_not_err):
                // update detail when have inbound
                $result_order_details = $this->stock->getOrderDetailByOrderId($order_id);

                $inbound_arr = array();
                $pallet_arr = array();
                if (!empty($result_order_details)):
                    foreach ($result_order_details as $result_order_detail):
                        if ($result_order_detail->Inbound_Item_Id != "" && $result_order_detail->Inbound_Item_Id != NULL):
                            array_push($inbound_arr, $result_order_detail->Inbound_Item_Id);
                        endif;

                        if ($result_order_detail->Pallet_Id != "" && $result_order_detail->Pallet_Id != NULL):
                            array_push($pallet_arr, $result_order_detail->Pallet_Id);
                        endif;
                    endforeach;
                endif; //end find inbound_Id
                #update inbound inactive
                if (!empty($inbound_arr)):
                    if (!empty($result_order_details)):
                        $result_del_onhand = $this->stock->removeInbound($inbound_arr);
                        if (!$result_del_onhand) {
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete inbound in STK_T_Inbound table";
                        }
//                            foreach ($inbound_arr as $inb_id):
//                                    $setDataWhereInb['Inbound_Id'] = $inb_id;
//                                    $setDataForUpdateInb['Active'] = 'N';
//                                    $result_upd_pallet = $this->stock->updateInboundDetail($setDataForUpdateInb, $setDataWhereInb);
//                                    if (!$result_upd_pallet) {
//                                        $check_not_err = FALSE;
//                                        $return['critical'][]['message'] = "Can not update Inbound table";
//                                    }
//                            endforeach;

                    endif;

                endif; //end update inbound
                #delete onhand
                if ($check_not_err) :
                    if (!empty($inbound_arr)):
                        $result_del_onhand = $this->stock->removeOnhandHistory($inbound_arr);
                        if (!$result_del_onhand) {
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete Onhand History table";
                        }
                    endif;

                endif; //end delete onhand
                #clear location in pallet
                if ($check_not_err) :

                    if (!empty($pallet_arr)):

                        foreach ($pallet_arr as $pallet_id):

                            $setDataWherePallet['Pallet_Id'] = $pallet_id;
                            $setDataForUpdatePallet['Suggest_Location_Id'] = NULL;
                            $setDataForUpdatePallet['Actual_Location_Id'] = NULL;
//                            $setDataForUpdatePallet['Is_Full'] = NULL;
                            $result_upd_pallet = $this->pallet->update_pallet_colunm($setDataForUpdatePallet, $setDataWherePallet);
                            if (!$result_upd_pallet) {
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Pallet table";
                            }

                        endforeach;

                    endif;

                endif; //end delete onhand



            endif; // end update inbound , onhand
        }//end update data when next state = 7

        if ($check_not_err):
            //update order detail = N
            $detail['Suggest_Location_Id'] = NULL;
            $detail['Actual_Location_Id'] = NULL;
            $detail['Reason_Code'] = NULL;
            $detail['Reason_Remark'] = NULL;
            $detail['Remark'] = NULL;
            $detail['Activity_Date'] = NULL;
            $detail['Activity_Code'] = 'RECEIVE';    // #defect 515 ,add for reset Activity_Code = RECEIVE : by kik : 20131203
            #reject return to receive again
            if ($next_state == 7) {
                $detail['Inbound_Item_Id'] = NULL;
            }

            $where['Order_Id'] = $order_id;

            $result = $this->stock->updateOrderDetail($detail, $where);
            if (!$result):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order Detail.";
            endif;

            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if ($check_not_err):
                if (empty($action_id) || $action_id == '') :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Workflow.";
                endif;
            endif;

            if ($check_not_err):

                #if insert data done. when return commit for runing database again
                $this->transaction_db->transaction_end();
                if ($present_state == 6 && $next_state == 4) {
                    //$json['status'] = "C006";
                    $set_return['message'] = "Reject Putaway Complete.";
                } else {
                    //$json['status'] = "C005";
                    $set_return['message'] = "Reject and Return Putaway Complete.";
                }

                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            else:
                #if insert data not done . when return rollback and not use database
                $this->transaction_db->transaction_rollback();
                $set_return['critical'][]['message'] = "Save Putaway Incomplete";
                $return = array_merge_recursive($set_return, $return);
                $json['status'] = "save";
                $json['return_val'] = $return;
            endif;
        else:
            #if insert data not done . when return rollback and not use database
            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Save Putaway Incomplete";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    function rejectAction() {

        $this->load->library('stock_lib');
        $this->load->model("stock_model", "stock");
        $this->load->model("pallet_model", "pallet");

        $process_id = $this->input->post("process_id");
        $flow_id = $this->input->post("flow_id");
        $present_state = $this->input->post("present_state");
        $action_type = $this->input->post("action_type");
        $next_state = $this->input->post("next_state");
        $document_no = $this->input->post("document_no");
        $order_id = $this->input->post("order_id");
        $prod_list = $this->input->post("prod_list");
        $ci_prod_sub_status = $this->input->post("ci_prod_sub_status");
        $ci_item_id = $this->input->post("ci_item_id");

        $data['Document_No'] = $document_no;

        $this->transaction_db->transaction_start();

        $check_not_err = TRUE;

        if ($next_state == -1) {

            if ($check_not_err):
                $orderData = array(
                    'Actual_Action_Date' => NULL
                    , 'Real_Action_Date' => NULL
                );

                $orderWhere['Order_Id'] = $order_id;
                $result_updateOrder = $this->stock->_updateOrder($orderData, $orderWhere);
                if (!$result_updateOrder):
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Order.";
                endif;
            endif;


            /**
             * update inbound and onhand , pallet
             */
            if ($check_not_err):
                // update detail when have inbound
                $result_order_details = $this->stock->getOrderDetailByOrderId($order_id);

                $inbound_arr = array();
                $pallet_arr = array();
                if (!empty($result_order_details)):
                    foreach ($result_order_details as $result_order_detail):
                        if ($result_order_detail->Inbound_Item_Id != "" && $result_order_detail->Inbound_Item_Id != NULL):
                            array_push($inbound_arr, $result_order_detail->Inbound_Item_Id);
                        endif;

                        if ($result_order_detail->Pallet_Id != "" && $result_order_detail->Pallet_Id != NULL):
                            array_push($pallet_arr, $result_order_detail->Pallet_Id);
                        endif;
                    endforeach;
                endif; //end find inbound_Id
                #update inbound inactive
                if (!empty($inbound_arr)):
                    if (!empty($result_order_details)):
                        foreach ($inbound_arr as $inb_id):
                            $setDataWhereInb['Inbound_Id'] = $inb_id;
                            $setDataForUpdateInb['Active'] = 'N';
                            $result_upd_pallet = $this->stock->updateInboundDetail($setDataForUpdateInb, $setDataWhereInb);
                            if (!$result_upd_pallet) {
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Inbound table";
                            }
                        endforeach;

                    endif;

                endif; //end update inbound
                #delete onhand
                if ($check_not_err) :
                    if (!empty($inbound_arr)):
                        $result_del_onhand = $this->stock->removeOnhandHistory($inbound_arr);
                        if (!$result_del_onhand) {
                            $check_not_err = FALSE;
                            $return['critical'][]['message'] = "Can not delete Onhand History table";
                        }
                    endif;

                endif; //end delete onhand
                #clear location in pallet
                if ($check_not_err) :

                    if (!empty($pallet_arr)):

                        foreach ($pallet_arr as $pallet_id):

                            $setDataWherePallet['Pallet_Id'] = $pallet_id;
                            $setDataForUpdatePallet['Suggest_Location_Id'] = NULL;
                            $setDataForUpdatePallet['Actual_Location_Id'] = NULL;
                            $setDataForUpdatePallet['Is_Full'] = NULL;
                            $result_upd_pallet = $this->pallet->update_pallet_colunm($setDataForUpdatePallet, $setDataWherePallet);
                            if (!$result_upd_pallet) {
                                $check_not_err = FALSE;
                                $return['critical'][]['message'] = "Can not update Pallet table";
                            }

                        endforeach;

                    endif;

                endif; //end delete onhand



            endif; // end update inbound , onhand
        }//end update data when next state = -1

        if ($check_not_err):
            //update order detail = N
            $detail['Suggest_Location_Id'] = NULL;
            $detail['Actual_Location_Id'] = NULL;
            $detail['Reason_Code'] = NULL;
            $detail['Reason_Remark'] = NULL;
            $detail['Remark'] = NULL;
            $detail['Activity_Date'] = NULL;
            $detail['Activity_Code'] = 'RECEIVE';

            if ($next_state == -1) {
                $detail['Inbound_Item_Id'] = NULL;
            }

            $where['Order_Id'] = $order_id;

            $result = $this->stock->updateOrderDetail($detail, $where);
            if (!$result):
                $check_not_err = FALSE;
                $return['critical'][]['message'] = "Can not update Order Detail.";
            endif;

            $action_id = $this->workflow->updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data);
            if ($check_not_err):
                if (empty($action_id) || $action_id == '') :
                    $check_not_err = FALSE;
                    $return['critical'][]['message'] = "Can not update Workflow.";
                endif;
            endif;

            if ($check_not_err):

                #if insert data done. when return commit for runing database again
                $this->transaction_db->transaction_end();
                if ($present_state == 6 && $next_state == -1) {
                    $set_return['message'] = "Reject Putaway Complete.";
                } else {
                    $set_return['message'] = "Reject and Return Putaway Complete.";
                }

                $return['success'][] = $set_return;
                $json['status'] = "save";
                $json['return_val'] = $return;
            else:
                #if insert data not done . when return rollback and not use database
                $this->transaction_db->transaction_rollback();
                $set_return['critical'][]['message'] = "Save Putaway Incomplete";
                $return = array_merge_recursive($set_return, $return);
                $json['status'] = "save";
                $json['return_val'] = $return;
            endif;
        else:
            #if insert data not done . when return rollback and not use database
            $this->transaction_db->transaction_rollback();
            $set_return['critical'][]['message'] = "Save Putaway Incomplete";
            $return = array_merge_recursive($set_return, $return);
            $json['status'] = "save";
            $json['return_val'] = $return;
        endif;

        echo json_encode($json);
    }

    #End New Comment Code #ISSUE 3034 Reject Document
    //ADD BY POR 2013-11-12 function สำหรับ update location ให้เป็น null โดยอ้างอิงจาก putaway_id

    function flagInactiveLocation() {
        $putaway_id = $this->input->get('id');
        $response = $this->put->check_before_delete($putaway_id)->result();
        if ($response[0]->countlocation == 0) :
            $this->transaction_db->transaction_start();
            $location_affected = $this->put->update_to_null($putaway_id);
            if ($location_affected === FALSE):
                log_message('error', 'Set STK_M_Location Putaway_Id IS NULL Where Putaway_Id = ' . $putaway_id . ' Unsuccess.');
            endif;
            $putaway_affected = $this->put->update_putaway_null($putaway_id);
            if ($putaway_affected === FALSE):
                log_message('error', 'Set InActive STK_M_Putaway Where Putaway_Id = ' . $putaway_id . ' Unsuccess.');
            endif;
            if ($location_affected && $putaway_affected) :
                $this->transaction_db->transaction_commit();
                $this->session->set_flashdata("response", "success");
            else:
                $this->transaction_db->transaction_rollback();
                $this->session->set_flashdata("response", "failed");
            endif;
        else:
            $this->session->set_flashdata("response", "failed");
        endif;
        redirect('putaway/freeLocationList');
        //END ADD
    }

    public function auto_print () {
	$view['d'] = $_GET['d'];

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
	$mpdf = new mPDF('th', 'A5-L', '11', '', 5, 5, $margin_top, 10, 5, 5);
	$mpdf->SetImportUse();

	$pagecount = $mpdf->SetSourceFile('/var/www/WMS/WebAppForPC/' . $_GET['d']);

	// Import the last page of the source PDF file
	$tplId = $mpdf->ImportPage($pagecount);
	$mpdf->UseTemplate($tplId);
	$mpdf->SetJS('print();');
	$mpdf->Output();
    }

    public function sp_putaway () {
	$this->put->rePallet();
    }

}
