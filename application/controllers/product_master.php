<?php

// Create by Ton! 20130704
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class product_master extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.
    public $settings;

    function __construct() {
        parent::__construct();
        $this->load->model("location_model", "loc");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("zone_model", "zone");
        $this->load->model("storage_model", "stor");
        $this->load->model("putaway_model", "put");
        $this->load->model("product_model", "prod");
        $this->load->model("product_status_model", "prodS");
        $this->load->model("Company_model", "com");
        $this->load->model("encoding_conversion", "encode");
        $this->load->model('authen_model', 'atn'); // Add by Ton! 20131104
        $this->load->model("product_group_model", "gro"); // Add by Ton! 20131209
        $this->load->model("product_brand_model", "brn"); // Add by Ton! 20131209
        $this->load->model("uom_model", "uom");
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("system_management_model", "sys");

        $this->mnu_NavigationUri = "product_master";

        $this->settings = native_session::retrieve(); // for use config from XML.
    }

    function export_excel_all_product() {
        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->removeSheetByIndex(0);
        $product_sheet = $objPHPExcel->createSheet();
        $product_sheet->setTitle("Product_All");

        # Header
        $product_sheet->getStyle('A1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('A1', "No.");

        $product_sheet->getStyle('B1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('B1', "Product Code");

        $product_sheet->getStyle('C1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('C1', "Product Name");

        $product_sheet->getStyle('D1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('D1', "UOM");

        $product_sheet->getStyle('E1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('E1', "Unit / Pallet");

        $product_sheet->getStyle('F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('F1', "KG / Unit");

        $product_sheet->getStyle('G1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('G1', "Net Weight / Pallet");

        $product_sheet->getStyle('H1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('H1', "Gross Weight / Pallet");

        $product_sheet->getStyle('I1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('I1', "Width");

        $product_sheet->getStyle('J1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('J1', "Length");

        $product_sheet->getStyle('K1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('K1', "Height");

        $product_sheet->getStyle('L1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('L1', "CBM");

        $product_sheet->getStyle('M1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('M1', "Type");

        $product_sheet->getStyle('N1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('N1', "Min Aging");

        $product_sheet->getStyle('O1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('O1', "Min ShelfLife");

        $product_sheet->getStyle('P1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('P1', "Product Category");

        $product_sheet->getStyle('Q1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('Q1', "Product Group");

        $product_sheet->getStyle('R1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('R1', "Case/Layer");

        $product_sheet->getStyle('S1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('S1', "Layer/Pallet");

        $product_sheet->getStyle('T1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('T1', "Individual Max. Stacking");

        $product_sheet->getStyle('U1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('U1', "Pickup Rule");

        $product_sheet->getStyle('V1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $product_sheet->SetCellValue('V1', "Active/InActive");

        $product_sheet->getStyle('A1:V1')->getFont()->setBold(true);

        # Detail
        $data_prod = $this->prod->getPrductMasterList()->result();
// p( $data_prod ); exit;
        $no = 1;
        $rows = 2;
        foreach ($data_prod as $key => $value) :
            $product_sheet->SetCellValue('A' . $rows, $no);
            $product_sheet->SetCellValue('B' . $rows, $value->Product_Code);
            $product_sheet->SetCellValue('C' . $rows, $value->Product_NameEN);
            $product_sheet->SetCellValue('D' . $rows, $value->UOM);
            $product_sheet->SetCellValue('E' . $rows, $value->PerPallet);
            $product_sheet->SetCellValue('F' . $rows, $value->STD_Weight);
            $product_sheet->SetCellValue('G' . $rows, $value->NetWeight);
            $product_sheet->SetCellValue('H' . $rows, ($value->GrossWeight == 20 ? 0 :$value->GrossWeight) );
            $product_sheet->SetCellValue('I' . $rows, $value->Width);
            $product_sheet->SetCellValue('J' . $rows, $value->Length);
	    $product_sheet->SetCellValue('K' . $rows, $value->Height);
  	    $product_sheet->SetCellValue('L' . $rows, $value->Cubic_Meters);
            $product_sheet->SetCellValue('M' . $rows, $value->pType);
            $product_sheet->SetCellValue('N' . $rows, $value->Min_Aging);
            $product_sheet->SetCellValue('O' . $rows, $value->Min_ShelfLife);
            $product_sheet->SetCellValue('P' . $rows, $value->prodcate);
	    $product_sheet->SetCellValue('Q' . $rows, $value->prodgroup);
            $product_sheet->SetCellValue('R' . $rows, $value->CL);
            $product_sheet->SetCellValue('S' . $rows, $value->LP);
            $product_sheet->SetCellValue('T' . $rows, $value->IMS);
            $product_sheet->SetCellValue('U' . $rows, $value->pickRule);
	    $product_sheet->SetCellValue('V' . $rows, $value->Active);

            ++$no;
            ++$rows;
        endforeach;

        # Save Excel file.
        $fileName = 'All_Product_Master_' . date('Ymd') . '_' . time();
        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        unset($data_prod);

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }


    public function index() {
        $this->productMasterList();
    }

    private function productMasterList() {// Display list of product. (Datatable)
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
             ONCLICK=\"openForm('product_master','product_master/productProcessec/','A','')\">";
        endif;

	$button_add .= ' <a href="product_master/export_excel_all_product"><button class="button dark_blue">Export</button></a>';

        $column = array(
            _lang('no'),
            _lang('product_code'),
            _lang('product_name'),
//            _lang('product_category'),
            'Material Group',
            _lang('UOM'),
            _lang('pickup_rule'),
            _lang('active')
        );
        if (!empty($action)):
            foreach ($action as $index => $value) :
                if ($value === "Action"):
                    $column[] = VIEW;
                endif;
                if ($value === "Edit"):
                    $column[] = EDIT;
                endif;
                if ($value === "Delete"):
                    $column[] = DEL;
                endif;
            endforeach;
        endif;
        ##### END Permission Button. by Ton! 20140130 #####        
        $r_prod = $this->prod->getPrductMasterList()->result();

        $parameter['show_column'] = $column;
        $parameter['prod_list'] = $r_prod;
        $parameter['action'] = $action;
        $str_form = $this->parser->parse('form/product_master_list_form', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

//        $q_prod = $this->prod->getPrductMasterList();
//        $r_prod = $q_prod->result();
//        $datatable = $this->datatable->genTableFixColumn($q_prod, $r_prod, $column, "product_master/productProcessec", $action);
        $this->parser->parse('list_template', array(
            'user_login' => $this->session->userdata('username')
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission.
            , 'menu_title' => 'List of Product'
//            , 'datatable' => $datatable
            , 'datatable' => $str_form
            , 'button_add' => $button_add
        ));
    }

    function get_product_list() {// Add by Ton! 20140320
        $data = $this->input->get();
        ##### Permission Button. #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        if (isset($action_parmission['action_button'])):
            $action = $action_parmission['action_button'];
        else:
            $action = array();
        endif;

        $column = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , 'Material Group'
	    , _lang('UOM')
            , _lang('pickup_rule')
            , _lang('active')
        );
        if (!empty($action)):
            foreach ($action as $index => $value) :
                if ($value === "Action"):
                    $column[] = VIEW;
                endif;
                if ($value === "Edit"):
                    $column[] = EDIT;
                endif;
                if ($value == "Delete"):
                    $column[] = DEL;
                endif;
            endforeach;
        endif;

        $limit_max = ""; //100;
        $limit_start = ""; //0;
        $total_product = count($this->prod->getPrductMasterList()->result());
        $total_filter_product = count($this->prod->getPrductMasterList($limit_start, $limit_max)->result());
        $r_prod = $this->prod->getPrductMasterList($limit_start, $limit_max)->result();
    
        $response = array();
        $i = 1;
        foreach ($r_prod as $index_prod => $value_prod) :
            $response[$index_prod] = array($i, $value_prod->Product_Code, thai_json_encode($value_prod->Product_NameEN)
                , thai_json_encode($value_prod->prodgroup), thai_json_encode($value_prod->UOM), thai_json_encode($value_prod->pickRule), thai_json_encode($value_prod->Active));
            if (!empty($action)):
                if (in_array("Action", $action)):
                    array_push($response[$index_prod], "<a ONCLICK=\"openForm('product_master', 'product_master/productProcessec/', 'V', '$value_prod->Id')\">" . img("css/images/icons/view.png") . "</a>");
                endif;
                if (in_array("Edit", $action)):
                    array_push($response[$index_prod], "<a ONCLICK=\"openForm('product_master', 'product_master/productProcessec/', 'E', '$value_prod->Id')\">" . img("css/images/icons/edit.png") . "</a>");
                endif;
                if (in_array("Delete", $action)):
                    array_push($response[$index_prod], "<a ONCLICK=\"openForm('product_master', 'product_master/productProcessec/', 'D', '$value_prod->Id')\">" . img("css/images/icons/del.png") . "</a>");
                endif;
            endif;

            $i++;
        endforeach;

        $output = array(
//            "sEcho" => (int) $data['sEcho'],
            "iTotalRecords" => $total_product,
            "iTotalDisplayRecords" => $total_filter_product,
            "aaData" => $response
        );
        echo json_encode($output);
    }

    function productProcessec() {// select processec (Add, Edit Delete) Zone.
        $data = $this->input->post();
        $mode = $data['mode'];
        if ($mode == "A") :// Add.
            $Id = "";
            $this->productForm($mode, $Id);
        elseif ($mode == "V" || $mode == "E") :// View & Edit.
            $Id = $data['id'];
            $this->productForm($mode, $Id);
        elseif ($mode == "D") :// Delete
            $Id = $data['id'];
            $ceheck_result = $this->check_delete_product($Id);
            if ($ceheck_result === TRUE):
                echo "<script type='text/javascript'>alert('Can not deleted. Because still have the inventories.')</script>";
            else:
                $delete_result = $this->delete_product($Id);
                if ($delete_result === TRUE):
                    echo "<script type='text/javascript'>alert('Delete data Product Master Success.')</script>";
                else:
                    echo "<script type='text/javascript'>alert('Delete data Product Master Unsuccess.')</script>";
                endif;
            endif;
            redirect('product_master', 'refresh');
        endif;
    }

    private function check_delete_product($prodId = NULL) {
        $result = FALSE;

        if (!empty($prodId)):
            $balQty = 0;
            $r_check_inbound = $this->prod->checkInbound($prodId)->result();
            if (count($r_check_inbound) > 0) :
                foreach ($r_check_inbound as $value) :
                    $balQty = $value->Balance_Qty;
                endforeach;
            endif;

            if ($balQty > 0) :
                $result = TRUE; // Not Delete Product.
            endif;
        else:
            log_message('error', 'Coa not check Product for InActive STK_M_Product by Product_Id = ' . $prodId . ' Unsuccess. Product_Id IS NULL.');
        endif;

        return $result;
    }

    private function delete_product($prodId = NULL) {// save Storage call Product_model\deleteProductMaster.
        $resultSave = FALSE;
        if (!empty($prodId)):
            $this->load->helper("date");
            $human = mdate("%Y-%m-%d %h:%i:%s", time());

            $dataProd['Modified_Date'] = $human;
            $dataProd['Modified_By'] = $this->session->userdata('user_id');

            $whereProd['Product_Id'] = $prodId;
            $dataProd['Active'] = INACTIVE;

            $this->transaction_db->transaction_start();
            $resultSave = $this->prod->saveProductMaster('upd', $dataProd, $whereProd);
            if ($resultSave === TRUE) :
                $this->transaction_db->transaction_commit();
            else:
                $this->transaction_db->transaction_rollback();
                log_message('error', 'Set InActive STK_M_Product by Product_Id = ' . $prodId . ' Unsuccess.');
            endif;
        else:
            log_message('error', 'Set InActive STK_M_Product by Product_Id = ' . $prodId . ' Unsuccess. Product_Id IS NULL.');
        endif;

        return $resultSave;
    }

    function productForm($mode, $prodId = NULL, $prodCode = NULL, $close = NULL, $prodName = NULL) {// Form Add&Edit&View Product. (call view/form/productMasterForm.php) // Last Edit by Ton! 20130827
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        // Add By Akkarapol, 20/02/2014, จัดการ Re Order Point ในกรณีที่เซ็ตค่า active เป็น TRUE เพื่อให้ไปแสดงค่าที่มี ในหน้า Product Master ตามที่ได้เพิ่มข้อมูลไว้
        $re_order_point = array();
        $type_of_re_order_point = array();
        if ($this->settings['re_order_point']['active']):

            $type_of_re_order_point = $this->settings['re_order_point']['type'];

            $filter = array(
                'column' => array(
                    '*'
                ),
                'where' => array(
                    'product_id' => $prodId
                )
            );

            $re_order_point_of_product = $this->prod->get_re_order_point($filter['column'], $filter['where'])->result_array();

            foreach ($re_order_point_of_product as $key_item => $item):
                $re_order_point[$item['alias']] = $item;
            endforeach;

            if (empty($re_order_point)):
                foreach ($this->settings['re_order_point']['object'] as $key_item => $item):
                    $re_order_point[$item['alias']] = $item;
                endforeach;
            endif;

            ksort($re_order_point);

        endif;
        // END Add By Akkarapol, 20/02/2014, จัดการ Re Order Point ในกรณีที่เซ็ตค่า active เป็น TRUE เพื่อให้ไปแสดงค่าที่มี ในหน้า Product Master ตามที่ได้เพิ่มข้อมูลไว้
        ##### Permission Button. by Ton! 20140130 #####
        # View = -1, Add = -2, Edit = -3, Delete = -4 #
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);

        $button_save = "";
        if (in_array("-2", $action_parmission) || in_array("-3", $action_parmission)):
            $button_save = '<INPUT TYPE="submit" class="button dark_blue" VALUE="' . SAVE . '" ONCLICK="validation()" ID="btn_save">';
        endif;

        ##### END Permission Button. by Ton! 20140130 #####
        // get all product_category for dispaly dropdown list.
        $r_prod_category = $this->prod->productCategory()->result();
        $optionProdCate = genOptionDropdown($r_prod_category, 'SYS', FALSE, TRUE, " :: Optional :: ");

        // get all product_unit for dispaly dropdown list.
        $r_prod_unit = $this->prod->getProductUnit()->result();
        $optionProdUnit = genOptionDropdown($r_prod_unit, 'SYS', FALSE, FALSE);

        // get all PickUp Rule for dispaly dropdown list.
        $r_pick_up_rule = $this->prod->getPickUpRule()->result();
        $optionPickRule = genOptionDropdown($r_pick_up_rule, 'SYS', TRUE, FALSE);

        // get all PutAway Rule for dispaly dropdown list.
        $r_put_away_rule = $this->prod->getPutAwayRule()->result();
        $optionPutRule = genOptionDropdown($r_put_away_rule, 'SYS', TRUE, FALSE);

        // get all STD_WeightUnit for dispaly dropdown list.
        $r_weight_unit = $this->prod->getSTDWeightUnit()->result();
        $optionWeightUnit = genOptionDropdown($r_weight_unit);

        // get all Dimension_Unit for dispaly dropdown list.
        $r_dimension_unit = $this->prod->getDimensionUnit()->result();
        $optionDimensionUnit = genOptionDropdown($r_dimension_unit, 'SYS', FALSE, FALSE);

//        ----- START ISSUE#2429 by Ton! 20130822 -----
        // get all Supplier for dispaly dropdown list.
        $r_supplier = $this->com->getSupplierAll()->result();
        $optionSupplier = genOptionDropdown($r_supplier, 'COMPANY');
//        ----- END by Ton! 20130822 -----
        //        ----- START ADD by Ton! 20130912 -----
        // get all FG_LICSE for dispaly dropdown list.
        $r_FG_LICSE = $this->prod->getFG_LICSE()->result();
        $optionFG_LICSE = genOptionDropdown($r_FG_LICSE);
        //        ----- END ADD by Ton! 20130912 -----

        $r_prod_group = $this->gro->get_CTL_M_ProductGroup(NULL, NULL, TRUE)->result();
        $optionProdGroup = genOptionDropdown($r_prod_group, 'PRODGROUP', FALSE, TRUE, " :: Optional :: ");

        $r_prod_brand = $this->brn->get_CTL_M_ProductBrand(NULL, NULL, TRUE)->result();
        $optionProdBrand = genOptionDropdown($r_prod_brand, 'PRODBRAND', FALSE, TRUE, " :: Optional :: ");

// get product by Product_Id for pass to form.
        $product_list = "";
        if ($prodId != "") :
            $product_list = $this->prod->getProductMaster($prodId)->result();
        endif;

        // define for pass to form. 
        $Product_Id = $prodId;
        $ProductCategory_Id = "";
        if ($prodCode != '') :
            $Product_Code = $prodCode;
        else :
            $Product_Code = "";
        endif;

        $Product_Barcode = (!empty($prodCode) ? $prodCode : '');
        $Product_NameEN = (!empty($prodName) ? $prodName : '');
        $Product_NameTH = (!empty($prodName) ? $prodName : '');

        $Product_Desc = "";
        $Standard_Unit_Id = "";
        $Standard_Price = "";
        $SafetyStock = "";
        $StockAge = "";
        $Min_Aging = "";
        $Min_ShelfLife = "";
        $Min_Temporary = "";
        $Max_Temporary = "";
        $Uni_Barcode = "";
        $Fact_Barcode = "";
        $Internal_Barcode1 = "";
        $Internal_Barcode2 = "";
        $FG_LICSE = "";
        $PickUp_Rule = "";
        $PutAway_Rule = "";
        $Product_Image_Id = "";
        $MustQC = "";
        $IsRawMat = "";
        $IsFG = "";
        $IsMachine = "";
        $ColorCode = "";
        $GroupCode = "";
        $ShapeCode = "";
        $BrandCode = "";
        $STD_Weight = "";
        $STD_WeightUnit = "";
        $STD_Unit_In_Id = "";
        $STD_Unit_Out_Id = "";
        $Width = "";
        $Length = "";
        $Height = "";
        $Dimension_Unit = "";
        $Cubic_Meters = ""; // Add by Ton! 20130729
//        ----- START ISSUE#2429 by Ton! 20130822 -----
        $Supplier_Id = "";
//        ----- END ISSUE#2429 by Ton! 20130822 -----

        $HS_Code = ""; // Add by Ton! 20131126 พิกัดศุลกากร
        $ProductGroup_Id = ""; // Add by Ton! 20131209
        $ProductBrand_Id = ""; // Add by Ton! 20131209

        $Re_Order_Point = "";
        $prodActive = ACTIVE;

        //Add By Joke 16/06/2016
        $Proper_Shipping_Name = "";
        $UN_No = "";
        $IMO_Class = "";
        $Flashpoint = "";
        $PKG = "";
        $DIWclass = "";
        $TypeLicense = "";
        $LinkSafetyDataSheet = "";
        $SafetyDataSheet = "";
        $EmergencyContact = "";
        //END Add By Joke 16/06/2016
        # config variables.
        if (!empty($product_list)) :
//            p($product_list);exit;
            foreach ($product_list as $productList) :
                $ProductCategory_Id = $productList->ProductCategory_Id;
                $Product_Code = $productList->Product_Code;
                $Product_NameEN = $productList->Product_NameEN;
                $Product_NameTH = $productList->Product_NameTH;
                $Product_Desc = $productList->Product_Desc;
                $Product_Barcode = $productList->Product_Barcode;
                $Standard_Unit_Id = $productList->Standard_Unit_Id;
                $Standard_Price = $productList->Standard_Price;
                $SafetyStock = $productList->SafetyStock;
                $StockAge = $productList->StockAge;
                $Min_Aging = $productList->Min_Aging;
                $Min_ShelfLife = $productList->Min_ShelfLife;
                $Min_Temporary = $productList->Min_Temporary;
                $Max_Temporary = $productList->Max_Temporary;
                $Uni_Barcode = $productList->Uni_Barcode;
                $Fact_Barcode = $productList->Fact_Barcode;
                $Internal_Barcode1 = $productList->Internal_Barcode1;
                $Internal_Barcode2 = $productList->Internal_Barcode2;
                $FG_LICSE = $productList->FG_LICSE;
                $PickUp_Rule = $productList->PickUp_Rule;
                $PutAway_Rule = $productList->PutAway_Rule;
                $Product_Image_Id = $productList->Product_Image_Id;
                $MustQC = $productList->MustQC;
                $IsRawMat = $productList->IsRawMat;
                $IsFG = $productList->IsFG;
                $IsMachine = $productList->IsMachine;
                $ColorCode = $productList->ColorCode;
                $GroupCode = $productList->GroupCode;
                $ShapeCode = $productList->ShapeCode;
                $BrandCode = $productList->BrandCode;
                $STD_Weight = $productList->STD_Weight;
                $STD_WeightUnit = $productList->STD_WeightUnit;
                $Width = $productList->Width;
                $Length = $productList->Length;
                $Height = $productList->Height;
                $Dimension_Unit = $productList->Dimension_Unit;
                $Cubic_Meters = $productList->Cubic_Meters; // Add by Ton! 20130729
//        ----- START ISSUE#2429 by Ton! 20130822 -----
                $Supplier_Id = $productList->Supplier_Id;
//        ----- END ISSUE#2429 by Ton! 20130822 -----
                $HS_Code = $productList->HS_Code; // Add by Ton! 20131126 พิกัดศุลกากร
                $ProductGroup_Id = $productList->ProductGroup_Id; // Add by Ton! 20131209
                $ProductBrand_Id = $productList->ProductBrand_Id; // Add by Ton! 20131209
                $STD_Unit_In_Id = $productList->Standard_Unit_In_Id; // Add by Ton! 20131209
                $STD_Unit_Out_Id = $productList->Standard_Unit_Out_Id; // Add by Ton! 20131209
                $Re_Order_Point = $productList->Re_Order_Point;
                $prodActive = $productList->Active; // Add by Ton! 20140227
                // Add By Joke 16/06/2016
                $Proper_Shipping_Name = $productList->ProperShippingName;
                $UN_No = $productList->UNno;
                $IMO_Class = $productList->IMOclass;
                $Flashpoint = $productList->Flashpoint;
                $PKG = $productList->PKG;
                $DIWclass = $productList->DIWclass;
                $TypeLicense = $productList->TypeLicense;
                $LinkSafetyDataSheet = $productList->SafetyDataSheet;
                $SafetyDataSheet = $productList->SafetyDataSheet;
                $EmergencyContact = $productList->EmergencyContact;
                $case_per_layer = $productList->User_Defined_1;
                $layer_per_pallet = $productList->User_Defined_2;
                $individual_max_stacking = $productList->User_Defined_3;
                $product_type = $productList->User_Defined_4;
                $unit_per_pallet = $productList->Unit_Per_Pallet;                
                //END Add By Joke 16/06/2016
            endforeach;
        endif;
        $Re_Order_Point_List = $re_order_point;

        $hs_code_flag = $this->config->item("hs_code"); // Add by Ton! 20131126 พิกัดศุลกากร Show Or Hide.

        if ($SafetyDataSheet == "" || $SafetyDataSheet == "NULL") {
            $SafetyDataSheet = "Not_File";
        } else {
            $SafetyDataSheet = substr($SafetyDataSheet, 24);
        }
        $this->load->helper('form');
        $str_form = form_fieldset('Product Master Info.');

        // Add By Akkarapol, 15,16/01/2014, เพิ่มการจัดการกับ UOM สำหรับ Product ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้ เพื่อนำไปแสดงเป็น DropDownList ให้เลือก
        $filter_query = array(
            'column' => array(
                'CTL_M_UOM_Template_Of_Product.id',
                'TLIN.public_name AS Unit_Value_In',
                'TLOUT.public_name AS Unit_Value_Out',
                'TLIN.CTL_M_UOM_Template_id AS Unit_Id_In',
                'TLOUT.CTL_M_UOM_Template_id AS Unit_Id_Out'
            ),
            'where' => '1=1',
            'order' => '
                Unit_Value_In ASC ,
                Unit_Value_Out ASC '
        );

//        if (!empty($ProductGroupId)):
//            $filter_query['order'] = '
//                Unit_Value_In ASC ,
//                Unit_Value_Out ASC ,
//                
//                CASE 
//                WHEN ProductGroup_Id = ' . $ProductGroupId . ' then 1
//                else 5
//                END
//                ';
//        endif;

        $all_uoms = $this->uom->get_uom_of_product($filter_query['column'], $filter_query['where'], $filter_query['order'])->result();

//        p($all_uoms);exit;

        $tmp_in_out = '';

        $tmp_uom['custom'] = 'Custom UOM';
        foreach ($all_uoms as $key_all_uom => $all_uom):
            $in_out_tmp = 'IN(' . $all_uom->Unit_Value_In . ') | OUT(' . $all_uom->Unit_Value_Out . ')';
            if ($tmp_in_out != $in_out_tmp):
//                $tmp_uom[$key_all_uom] = $all_uom;
                $tmp_uom[$all_uom->Unit_Id_In . SEPARATOR . $all_uom->Unit_Id_Out] = $in_out_tmp;
            endif;
            $tmp_in_out = $in_out_tmp;
        endforeach;

        $all_uoms = object_to_array(@$tmp_uom);
        // END Add By Akkarapol, 15,16/01/2014, เพิ่มการจัดการกับ UOM สำหรับ Product ทั้งการ query ในส่วนของ Template ทั้งหมดที่จะให้เลือกใช้ และที่ Group นี้เลือกใช้ เพื่อนำไปแสดงเป็น DropDownList ให้เลือก
        $selected_template_uom = 'custom';

        // pass parameter to form
        $str_form.=$this->parser->parse('form/product_master_form', array("prodCateList" => $optionProdCate
            , "prodUnitList" => $optionProdUnit, "supplierList" => $optionSupplier, "pickUpRuleList" => $optionPickRule
            , "putAwayRuleList" => $optionPutRule, "ProdGroupList" => $optionProdGroup, "ProdBrandList" => $optionProdBrand
            , "weightUnitList" => $optionWeightUnit, "DimensionUnitList" => $optionDimensionUnit, "mode" => $mode
            , "Product_Id" => $Product_Id, "ProductCategory_Id" => $ProductCategory_Id, "Product_Code" => $Product_Code
            , "Product_NameEN" => $Product_NameEN, "Product_NameTH" => $Product_NameTH, "Product_Desc" => $Product_Desc
            , "Product_Barcode" => $Product_Barcode, "Standard_Unit_Id" => $Standard_Unit_Id, "Standard_Price" => $Standard_Price
            , "SafetyStock" => $SafetyStock, "StockAge" => $StockAge, "Min_Aging" => $Min_Aging, "Min_ShelfLife" => $Min_ShelfLife
            , "Min_Temporary" => $Min_Temporary, "Max_Temporary" => $Max_Temporary, "Uni_Barcode" => $Uni_Barcode
            , "Fact_Barcode" => $Fact_Barcode, "Internal_Barcode1" => $Internal_Barcode1, "Internal_Barcode2" => $Internal_Barcode2
            , "FG_LICSE" => $FG_LICSE, "PickUp_Rule" => $PickUp_Rule, "PutAway_Rule" => $PutAway_Rule, "Product_Image_Id" => $Product_Image_Id
            , "MustQC" => $MustQC, "IsRawMat" => $IsRawMat, "IsFG" => $IsFG, "IsMachine" => $IsMachine, "ColorCode" => $ColorCode
            , "GroupCode" => $GroupCode, "ShapeCode" => $ShapeCode, "BrandCode" => $BrandCode, "STD_Weight" => $STD_Weight
            , "STD_WeightUnit" => $STD_WeightUnit, "Width" => $Width, "Length" => $Length, "Height" => $Height, "Dimension_Unit" => $Dimension_Unit
            , "Cubic_Meters" => $Cubic_Meters, "Supplier_Id" => $Supplier_Id, "close" => $close, "FG_LICSE_List" => $optionFG_LICSE
            , "HS_Code" => $HS_Code, "hs_code_flag" => $hs_code_flag, "ProductGroup_Id" => $ProductGroup_Id, "ProductBrand_Id" => $ProductBrand_Id
            , 'all_uoms' => $all_uoms, 'selected_template_uom' => $selected_template_uom, 'STD_Unit_In_Id' => $STD_Unit_In_Id
            , 'STD_Unit_Out_Id' => $STD_Unit_Out_Id, 'Re_Order_Point' => $Re_Order_Point, 'Re_Order_Point_List' => $Re_Order_Point_List
            , 'type_of_re_order_point' => $type_of_re_order_point, 'prodActive' => $prodActive, "UN_No" => $UN_No, "IMO_Class" => $IMO_Class, "Proper_Shipping_Name" => $Proper_Shipping_Name
            , 'Flashpoint' => $Flashpoint, 'PKG' => $PKG, 'DIW_Class' => $DIWclass, 'Type_of_License' => $TypeLicense, 'SafetyDataSheet' => $SafetyDataSheet, 'Emergency_Contact' => $EmergencyContact
            , 'case_per_layer' => $case_per_layer
            , 'layer_per_pallet' => $layer_per_pallet
            , 'unit_per_pallet' => $unit_per_pallet
            , 'product_type' => $product_type
            , 'individual_max_stacking' => $individual_max_stacking
            , "LinkSafetyDataSheet" => $LinkSafetyDataSheet), TRUE);

        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Product Master Info.'
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
            $Active = $this->input->post("pro_active");
            if ($Active === "N"):// InActive
                $result_check = $this->check_delete_product($data['Product_Id']);
                if ($result_check === TRUE):
                    $result['result'] = 0;
                    $result['note'] = "PROD_DEL";
                    echo json_encode($result);
                    return;
                endif;
            endif;
        endif;
        
        # check Product Code already.
        if ($data['type'] === "A"):
            $product_list = $this->prod->getProductMaster("", $data['Product_Code'])->result();
            if (count($product_list) > 0) :
                $result['result'] = 0;
                $result['note'] = "PROD_CODE_ALREADY";
                echo json_encode($result);
                return;
            endif;
        endif;

        // Add By Wannaporn 10-20-2020
        # check Internal Barcode1,2 already.
        if($data['type'] === "A"){
            $c1 = $this->prod->check_alternate_barcode_v1($data['Product_Barcode']);
            $c2 = $this->prod->check_alternate_barcode_v1($data['Internal_Barcode1']);
            $c3 = $this->prod->check_alternate_barcode_v1($data['Internal_Barcode2']);
            $check = (int) $c1 + (int) $c2 + (int) $c3;
            if ($check == 0) {
                // PASS
                $result['result'] = 1;
            } else {
                // NOT PASS
                $result['result'] = 0;
                if($c1 != 0):
                    $result['note1'] = "PRODUCT_BARCODE_ALREADY";
                endif;
                if($c2 != 0):
                    $result['note2'] = "INTERNAL_BARCODE1_ALREADY";
                endif;
                if($c3 != 0):
                    $result['note3'] = "INTERNAL_BARCODE2_ALREADY";
                endif;
            }
            echo json_encode($result);
            return;
        }
        if($data['type'] === "E"){
            $c1 = $this->prod->check_alternate_barcode_v1($data['Product_Barcode'],$data['Product_Id']);
            $c2 = $this->prod->check_alternate_barcode_v1($data['Internal_Barcode1'],$data['Product_Id']);
            $c3 = $this->prod->check_alternate_barcode_v1($data['Internal_Barcode2'],$data['Product_Id']);
            $check = (int) $c1 + (int) $c2 + (int) $c3;
            // p($c1);
            // p($c2);
            // p($c3);
            // exit;
            if ($check == 0) {
                // PASS
                $result['result'] = 1;
            } else {
                // NOT PASS
                $result['result'] = 0;
                if($c1 != 0):
                    $result['note1'] = "PRODUCT_BARCODE_ALREADY";
                endif;
                if($c2 != 0):
                    $result['note2'] = "INTERNAL_BARCODE1_ALREADY";
                endif;
                if($c3 != 0):
                    $result['note3'] = "INTERNAL_BARCODE2_ALREADY";
                endif;
            }
            echo json_encode($result);
            return;
        }
        // End Add

        echo json_encode($result);
        return;
    }

    function saveProduct() {// save Product call Product_model/saveProductMaster.
        //ADD BY POR 2014-02-25 START TRANSACTION
        $this->sys->transaction_start();
        $data = $this->input->post();

        $FileName = $_FILES["Safety_Data_Sheet"]["name"];
        $FileSize = $_FILES["Safety_Data_Sheet"]["size"];

        $uploaddir = 'uploads/default/files/';
        $uploadfile = $uploaddir . basename($FileName);
        $FileType = pathinfo($uploadfile, PATHINFO_EXTENSION);

//        p($this->settings['uploads']['upload_path'] . $FileName);exit;

        $status = 0; // Count Save False.
        //END ADD

        $this->load->helper("date");
        $human = mdate("%Y-%m-%d %h:%i:%s", time());

        // Add By Akkarapol, 20/01/2013, เพิ่มการเช็คว่า ถ้า $data['select_template_uom'] = 'custom' แล้ว ให้ทำการเพิ่ม Template นี้เข้าไปใน CTL_M_UOM_Template_Of_Product ด้วย
        if ($data['select_template_uom'] = 'custom'):
            $save_uom_of_product[] = array(
                'Product_Code' => strtoupper($data['Product_Code']),
                'standard_unit_in_id' => $data['Standard_Unit_In_Id'],
                'standard_unit_out_id' => $data['Standard_Unit_Out_Id']
            );
            $result_UOM = $this->uom->save_uom_of_product('ist', $save_uom_of_product);
            if ($result_UOM <= 0):
                log_message('error', 'Insert CTL_M_UOM_Template_Of_Product Unsuccess.');
                $status++;
            endif;
//            //ADD BY POR 2014-02-25
//            if ($this->transaction_db->transaction_status() === FALSE):
//                $status++;
//            endif;product_type
        //END ADD
        endif;
        // END product_typeBy Akkarapol, 20/01/2013, เพิ่มการเช็คว่า ถ้า $data['select_template_uom'] = 'custom' แล้ว ให้ทำการเพิ่ม Template นี้เข้าไปใน CTL_M_UOM_Template_Of_Product ด้วย

        $data['Standard_Unit_Id'] = $data['Standard_Unit_In_Id'];
        $rProduct['ProductCategory_Id'] = ($data['ProductCategory_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductCategory_Id']);
        $rProduct['Product_Code'] = ($data['Product_Code'] == "") ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $data['Product_Code']));
        $rProduct['Product_NameEN'] = ($data['Product_NameEN'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Product_NameEN']);
        $rProduct['Product_NameTH'] = ($data['Product_NameTH'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Product_NameTH']);
        $rProduct['Product_Desc'] = ($data['Product_Desc'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Product_Desc']);
        $rProduct['Product_Barcode'] = ($data['Product_Barcode'] == "") ? NULL : strtoupper(iconv("UTF-8", "TIS-620", $data['Product_Barcode']));
        $rProduct['Standard_Unit_Id'] = ($data['Standard_Unit_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Standard_Unit_Id']);
        $rProduct['Standard_Price'] = ($data['Standard_Price'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Standard_Price']);
        $rProduct['Min_Aging'] = ($data['Min_Aging'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Min_Aging']);
        $rProduct['Min_ShelfLife'] = ($data['Min_ShelfLife'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Min_ShelfLife']);
        $rProduct['Min_Temporary'] = ($data['Min_Temporary'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Min_Temporary']);
        $rProduct['Max_Temporary'] = ($data['Max_Temporary'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Max_Temporary']);
        $rProduct['FG_LICSE'] = ($data['FG_LICSE'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['FG_LICSE']);
        $rProduct['PickUp_Rule'] = ($data['PickUp_Rule'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['PickUp_Rule']);
        $rProduct['PutAway_Rule'] = ($data['PutAway_Rule'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['PutAway_Rule']);

        $rProduct['Internal_Barcode1'] = ($data['Internal_Barcode1'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Internal_Barcode1']);
        $rProduct['Internal_Barcode2'] = ($data['Internal_Barcode2'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Internal_Barcode2']);
        
        // Add By Jok 16/06/2016
        $rProduct['ProperShippingName'] = ($data['Proper_Shipping_Name'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Proper_Shipping_Name']);
        $rProduct['UNno'] = ($data['UN_No'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['UN_No']);
        $rProduct['IMOclass'] = ($data['IMO_Class'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['IMO_Class']);
        $rProduct['Flashpoint'] = ($data['Flashpoint'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Flashpoint']);
        $rProduct['PKG'] = ($data['PKG'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['PKG']);
        $rProduct['DIWclass'] = ($data['DIW_Class'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['DIW_Class']);
        $rProduct['TypeLicense'] = ($data['Type_of_License'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Type_of_License']);
        $rProduct['EmergencyContact'] = ($data['Emergency_Contact'] == "") ? NULL : utf8_to_tis620($data['Emergency_Contact']);
        $rProduct['User_Defined_1'] = ($data['case_per_layer'] == "") ? NULL : utf8_to_tis620($data['case_per_layer']);
        $rProduct['User_Defined_2'] = ($data['layer_per_pallet'] == "") ? NULL : utf8_to_tis620($data['layer_per_pallet']);
        $rProduct['User_Defined_3'] = ($data['individual_max_stacking'] == "") ? NULL : utf8_to_tis620($data['individual_max_stacking']);
        $rProduct['User_Defined_4'] = ($data['product_type'] == "") ? NULL : utf8_to_tis620($data['product_type']);
        $rProduct['Unit_Per_Pallet'] = ($data['unit_per_pallet'] == "") ? NULL : utf8_to_tis620($data['unit_per_pallet']);
        // End Add By Jok 16/06/2016
        // 
//        ----- START ISSUE#2429 by Ton! 20130822 -----
        $rProduct['Supplier_Id'] = ($data['Supplier_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Supplier_Id']);
//        ----- END by Ton! 20130822 -----

        $rProduct['ProductGroup_Id'] = ($data['ProductGroup_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductGroup_Id']); // Add by Ton! 20131209
//        $rProduct['ProductBrand_Id'] = ($data['ProductBrand_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_Id']); // Add by Ton! 20131209
        if (empty($data['ProductBrand_Id'])): // Edit By Akkarapol, 11/02/2014, check variable $data['ProductBrand_Id'] if empty set NULL
            $rProduct['ProductBrand_Id'] = NULL;
        else:
            $rProduct['ProductBrand_Id'] = ($data['ProductBrand_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ProductBrand_Id']);
        endif;

        $rProduct['Standard_Unit_In_Id'] = ($data['Standard_Unit_In_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Standard_Unit_In_Id']); // Add by Akkarapol, 16/01/2014, เพิ่มการเซฟ Standard_Unit_In_Id เข้าสู่ DB
        $rProduct['Standard_Unit_Out_Id'] = ($data['Standard_Unit_Out_Id'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Standard_Unit_Out_Id']); // Add by Akkarapol, 16/01/2014, เพิ่มการเซฟ Standard_Unit_Out_Id เข้าสู่ DB

        if ($this->settings['re_order_point']['active']):
            $rProduct['Re_Order_Point'] = ($data['Re_Order_Point'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Re_Order_Point']); // Add by Akkarapol, 29/01/2014, เพิ่มการเซฟ Re_Order_Point เข้าสู่ DB
        endif;

        $MustQC = $this->input->post("MustQC");
        if ($MustQC != 1) :
            $MustQC = 0;
        endif;
        $rProduct['MustQC'] = $MustQC;

        $IsRawMat = $this->input->post("IsRawMat");
        if ($IsRawMat != 1) :
            $IsRawMat = 0;
        endif;
        $rProduct['IsRawMat'] = $IsRawMat;

        $IsFG = $this->input->post("IsFG");
        if ($IsFG != 1) :
            $IsFG = 0;
        endif;
        $rProduct['IsFG'] = $IsFG;

        $IsMachine = $this->input->post("IsMachine");
        if ($IsMachine != 1) :
            $IsMachine = 0;
        endif;
        $rProduct['IsMachine'] = $IsMachine;

        $rProduct['ShapeCode'] = ($data['ShapeCode'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['ShapeCode']);
        $rProduct['STD_Weight'] = ($data['STD_Weight'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['STD_Weight']);
        $rProduct['STD_WeightUnit'] = ($data['STD_WeightUnit'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['STD_WeightUnit']);
        $rProduct['Width'] = ($data['Width'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Width']);
        $rProduct['Length'] = ($data['Length'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Length']);
        $rProduct['Height'] = ($data['Height'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Height']);
        $rProduct['Dimension_Unit'] = ($data['Dimension_Unit'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Dimension_Unit']);
        $rProduct['Cubic_Meters'] = ($data['Cubic_Meters'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['Cubic_Meters']); // Add by Ton! 20130729
        $rProduct['Active'] = $this->input->post("pro_active");
        $rProduct['HS_Code'] = ($data['HS_Code'] == "") ? NULL : iconv("UTF-8", "TIS-620", $data['HS_Code']); // Add by Ton! 20131126 พิกัดศุลกากร

        $whereProduct['Product_Id'] = ($data['Product_Id'] == "") ? "" : $data['Product_Id'];

        $result = FALSE;
        $resultFile = FALSE;
        $type = $data['type']; // Add, Edit
        $prod_Id = "";

        switch ($type) :
            case "A" : {
                    $rProduct['Created_Date'] = $human;
                    $rProduct['Created_By'] = $this->session->userdata('user_id');
                    $rProduct['Modified_Date'] = $human;
                    $rProduct['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->prod->saveProductMaster('ist', $rProduct, $whereProduct, @$data['Re_Order_Point_List']);

                    if ($result === FALSE):
                        log_message('error', 'Insert STK_M_Product Unsuccess.');
                        $status++;
                    else:
                        $prod_Id = $result;

                        $config['upload_path'] = $this->settings['uploads']['upload_path'];
                        $config['allowed_types'] = '*';
                        $config['file_name'] = $FileName;
                        $this->load->library('upload', $config);
                        $field_name = "Safety_Data_Sheet";
                        if ($FileName != "") {
                            if ($this->upload->do_upload($field_name)) {
                                $data = array('upload_data' => $this->upload->data());
                                $pathFile = $this->settings['uploads']['upload_path'] . $data["upload_data"]["file_name"];
                                $resultFile = $this->prod->updateFileProduct(iconv("UTF-8", "TIS-620", $pathFile), $prod_Id);
                                if ($resultFile == TRUE) {
                                    $result = TRUE;
                                } else {
                                    log_message('error', 'IMPORT Safety Data Sheet Not Success');
                                    $status++;
                                }
                            } else {
                                log_message('error', 'IMPORT Safety Data Sheet Not Success');
                                $status++;
                            }
                        }

                        if (isset($data['Re_Order_Point'])):
                            if ($data['Re_Order_Point'] == "Y"):
                                $result = $this->prod->_save_re_order_point('ist', @$data['Re_Order_Point_List'], $result);
                                if ($result === FALSE):
                                    log_message('error', 'Insert STK_M_Re_Order_Point Unsuccess.');
                                    $status++;
                                endif;
                            endif;
                        endif;
                    endif;
                }break;
            case "E" : {
                    $rProduct['Modified_Date'] = $human;
                    $rProduct['Modified_By'] = $this->session->userdata('user_id');
                    $result = $this->prod->saveProductMaster('upd', $rProduct, $whereProduct, @$data['Re_Order_Point_List']);
                    if ($result === FALSE):
                        $status++;
                    else:
                        if ($data["Delete_Logfile"] != "DeleteFile") {
                            if ($FileName != "") {
//                                if ($_FILES["Safety_Data_Sheet"]["name"] != substr($data['Log_Data_Sheet'], 24)) {
                                $config['upload_path'] = $this->settings['uploads']['upload_path'];
                                $config['allowed_types'] = '*';
                                $config['file_name'] = $FileName;
                                $this->load->library('upload', $config);
                                $field_name = "Safety_Data_Sheet";
                                if ($this->upload->do_upload($field_name)) {
                                    $dataFile = array('upload_data' => $this->upload->data());
                                    $pathFile = $this->settings['uploads']['upload_path'] . $dataFile["upload_data"]["file_name"];
                                    $resultFile = $this->prod->updateFileProduct(iconv("UTF-8", "TIS-620", $pathFile), $whereProduct["Product_Id"]);
                                    if ($resultFile == TRUE) {
                                        $base_directory = $dataFile["upload_data"]["file_path"] . substr($data['Log_Data_Sheet'], 24);
                                        if (unlink($base_directory)) {
                                            $result = TRUE;
                                        }
                                    } else {
                                        log_message('error', 'IMPORT Safety Data Sheet Not Success');
                                        $status++;
                                    }
                                } else {
                                    log_message('error', 'IMPORT Proper Shipping Name Not Success');
                                    $status++;
                                }
//                                }
                            }
                        } else {
                            $subStr = substr($data["Log_Data_Sheet"], 1);
                            $path = str_replace('\\', '/', getcwd()) . $subStr;
                            if (unlink($path)) {
                                $result = $this->prod->DeleteFileProduct($whereProduct);
                                if ($result == FALSE) {
                                    log_message('error', 'Not update query file.');
                                    $status++;
                                } else {
                                    $result = TRUE;
                                }
                            } else {
                                log_message('error', 'Error Delete File');
                                $status++;
                            }
                        }


                        if (isset($data['Re_Order_Point'])):
                            if ($data['Re_Order_Point'] == "Y"):
                                $result = $this->prod->_save_re_order_point('upd', @$data['Re_Order_Point_List'], $whereProduct['Product_Id']);
                                if ($result === FALSE):
                                    log_message('error', 'Update STK_M_Re_Order_Point Unsuccess.');
                                    $status++;
                                endif;
                            else:
                                $result = $this->prod->_save_re_order_point('del', NULL, $whereProduct['Product_Id']);
                                if ($result === FALSE):
                                    log_message('error', 'Set InActive STK_M_Re_Order_Point Unsuccess.');
                                    $status++;
                                endif;
                            endif;
                            $result = $this->prod->_save_re_order_point('del', NULL, $whereProduct['Product_Id']);
                            if ($result === FALSE):
                                log_message('error', 'Set InActive STK_M_Re_Order_Point Unsuccess.');
                                $status++;
                            endif;
                        endif;
                    endif;
                }break;
        endswitch;

        // Commit or Rollback
        $save_result = TRUE;

        if ($status <= 0):
            $this->transaction_db->transaction_commit();
            redirect('product_master');
//            if ($data['type'] === "A"):
//                $result = $this->saveProductToLocation($data);
//                if ($result === TRUE):
//                    $this->transaction_db->transaction_commit();
//                else:
//                    $save_result = FALSE;
//                    log_message('error', 'Save STK_M_Product_Location Unsuccess.');
//                    $this->transaction_db->transaction_rollback();
//                endif;
//            else:
//                $this->transaction_db->transaction_commit();
//            endif;
        else:
            $save_result = FALSE;
            log_message('error', 'Save Data Product Master Unsuccess.');
            $this->transaction_db->transaction_rollback();
        endif;

        echo $save_result;
        //echo $prod_Id;  // Change return Product ID By Joke 16/01/2016
    }

    // Add 
    function loadFileProduct() {
        $data = $this->input->get();
        $fileName = substr($data["FileName"], 24);
        $view = array(
            'file_name' => $fileName
            , 'path_file' => $data["FileName"]
        );
        $this->load->view("load_file.php", $view);
    }

//    ----- START #2429 Add by Ton! 20130821 -----
    function saveProductToLocation($data) {
        $resultSave = TRUE;

        $prodId = $data["Product_Id"]; //$this->input->post("Product_Id");
        $prodCode = $data["Product_Code"]; //$this->input->post("Product_Code");

        $prodStatus = array();
        $Normal = $data["prodStatus_Normal"]; //$this->input->post("prodStatus_Normal");
        if ($Normal == 1) :
            array_push($prodStatus, "NORMAL");
        endif;
        $Pending = $data["prodStatus_Pending"]; //$this->input->post("prodStatus_Pending");
        if ($Pending == 1) :
            array_push($prodStatus, "PENDING");
        endif;
        $CreditNote = $data["prodStatus_CreditNote"]; //$this->input->post("prodStatus_CreditNote");
        if ($CreditNote == 1) :
            array_push($prodStatus, "CREDIT NOTE");
        endif;
        $NonConform = $data["prodStatus_NonConform"]; //$this->input->post("prodStatus_NonConform");
        if ($NonConform == 1) :
            array_push($prodStatus, "NON CONFORM");
        endif;
        $Damage = $data["prodStatus_Damage"]; //$this->input->post("prodStatus_Damage");
        if ($Damage == 1) :
            array_push($prodStatus, "DAMAGE");
        endif;
        $Grade = $data["prodStatus_Grade"]; //$this->input->post("prodStatus_Grade");
        if ($Grade == 1) :
            array_push($prodStatus, "GRADE");
        endif;
        $Shortage = $data["prodStatus_Shortage"]; //$this->input->post("prodStatus_Shortage");
        if ($Shortage == 1) :
            array_push($prodStatus, "SHORTAGE");
        endif;
        # No use now.
//        $RePack = $data["prodStatus_RePack"]; //$this->input->post("prodStatus_RePack");
//        if ($RePack == 1) {
//            array_push($prodStatus, "RE-PACK");
//        }

        if (count($prodStatus > 0)) :// Check $prodStatus
            foreach ($prodStatus as $prodStatusList) :
                $locateId = array();
                $prodLocate = $this->loc->getProductLocation($prodStatusList); //Get Location
                if (count($prodLocate) > 0) :
                    foreach ($prodLocate as $prodLocateList) :
                        array_push($locateId, $prodLocateList->Location_Id);
                    endforeach;
                endif;
                $data = array();
                if (count($locateId > 0)) :
                    foreach ($locateId as $locateIdList) :
                        $dataDetail = array();
                        $dataDetail['Product_Id'] = $prodId;
                        $dataDetail['Product_Code'] = $prodCode;
                        $dataDetail['Product_status'] = $prodStatusList;
                        $dataDetail['Location_Id'] = $locateIdList;
                        $dataDetail['Active'] = ACTIVE;
                        $data[] = $dataDetail;
                        unset($dataDetail);
                    endforeach;
                endif;
                if (count($data > 0)) :
                    foreach ($data as $dataList) :
                        $result = $this->prod->saveProdToLocate($dataList); // Insert Product to Location [Table STK_M_Product_Location]
                        if ($result === FALSE) :
                            $resultSave = FALSE;
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $resultSave;
    }

//    ----- END Add by Ton! 20130821 -----

    public function search_product_code() {
        $criteria = $this->input->post("criteria");
        $response = $this->prod->find_product($criteria);
        if (is_null($response)) {
            echo json_encode(array("OK", $this->prod->generate_product_code()));
        } else {
            echo json_encode(array("NO", $response));
        }
    }

    public function generate_product_code() {
        echo json_encode($this->prod->generate_product_code());
    }

}

?>
