<?php

//Create By Joke 16-12-07 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report_checkLocationBalance
 *
 * @author SaLoveBy
 */
class report_checkLocationBalance extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140114
        $this->load->library('cryptography');
        $this->load->library('form_validation');
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('pagination_ajax');
        $this->load->helper('form');
        $this->load->model("report_model", "r");
        $this->load->model("warehouse_model", "wh");
        $this->load->model("location_model", "location");
    }

    public function index() {
        $this->LocationRemain();
    }

    public function LocationRemain() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit
        $conf = $this->config->item('_xml');

        $conf_customer = empty($conf['conf_customer']) ? false : @$conf['conf_customer'];

        $parameter = array();

        $parameter['renter_id'] = $this->config->item('renter_id');
        $parameter['conf_customer'] = $conf_customer;

        $str_form = $this->parser->parse('form/location_remain', array("data" => $parameter, "test_parse" => "teat pass by parse"), TRUE);

//ADD BY POR 2013-11-05 แก้ไขให้เรียกใช้ workflow_template แทน
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Location Remain Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class = "icon-minus-sign icon-white toggleForm" data-target = "frmStockMove"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type = "button" value = "Export To PDF" class = "button dark_blue" id = "pdfshow" onClick = "exportFileLocation(' . "'PDF'" . ')" />'
            , 'button_action' => '<input type = "button" value = "Export To Excel" class = "button dark_blue" id = "excelshow"  onClick = "exportFileLocation(' . "'EXCEL'" . ')" />'
        ));
//END ADD
    }

    function getDataTable() {

        //set_time_limit(7200);
        //ini_set('memory_limit', '-1');
        
        $conf = $this->config->item('_xml');
        $show = $this->input->post("show_type");
/*
        $conf_customer = empty($conf['conf_customer']) ? false : @$conf['conf_customer'];

        $db_connect_mysql = array(
            'username' => "joke",
            'password' => "password",
            'hostname' => "172.16.38.17",
            'database' => "db_wmsplus"
        );

        $db_mysql = $this->location->get_db_from_mysql($db_connect_mysql); // Get Name and DB Name 
        // Create array put data
        $reultData = array();

        $db_connect_mssql = array(
            'server_name' => "172.16.38.18",
            'database' => "WMSP_TALESUN",
            'username' => "sa",
            'password' => 'azsxdcfv'
        );

        $dbname = $this->location->get_db_mssql_all($db_connect_mssql); // Get Name All Database      
  
        $database = array();
        foreach ($dbname as $key => $value) {
            if (array_key_exists($value->DATABASE_NAME, $db_mysql)) { // เช็คที่ไปดึงชื่อดาต้าเบสทั้งหมด มาเทียบกับ ชื่อดาต้าเบสที่เก็บใน Locahost        
                $database[$value->DATABASE_NAME] = $db_mysql[$value->DATABASE_NAME];
            }
        }
*/

//        error_reporting(E_ALL);
        $db_connect_mssql = array(
            'server_name' => "ecolab.cmozpodty13y.ap-southeast-1.rds.amazonaws.com,1433",
            'database' => "ECOLAB_PRODUCTION",
            'username' => "ditsadmin",
            'password' => 'azsxdcfv'
        );
        $database = array("ECOLAB_PRODUCTION");

        $location_list = $this->location->get_location_remain($db_connect_mssql, $database, $show);

        $reultData = $location_list;
        $parameter['data_detail'] = $reultData;
        echo json_encode($parameter);
    }

    function exportLocationRemain() {
        $type = $this->input->get("FileType");
        $show = $this->input->get("show_type");
/*
        $db_connect_mysql = array(
            'username' => "joke",
            'password' => "password",
            'hostname' => "172.16.38.17",
            'database' => "db_wmsplus"
        );

        $db_mysql = $this->location->get_db_from_mysql($db_connect_mysql); // Get Name and DB Name 

        $db_connect_mssql = array(
            'server_name' => "ecolab.cmozpodty13y.ap-southeast-1.rds.amazonaws.com",
            'database' => "ECOLAB_PRODUCTION",
            'username' => "ditsadmin",
            'password' => 'azsxdcfv'
        );

        $dbname = $this->location->get_db_mssql_all($db_connect_mssql); // Get Name All Database       
        $database = array();

        foreach ($dbname as $key => $value) {
            if (array_key_exists($value->DATABASE_NAME, $db_mysql)) { // เช็คที่ไปดึงชื่อดาต้าเบสทั้งหมด มาเทียบกับ ชื่อดาต้าเบสที่เก็บใน Locahost    
                $database[$value->DATABASE_NAME] = $db_mysql[$value->DATABASE_NAME];
            }
        }
*/
//	error_reporting(E_ALL);
        $db_connect_mssql = array(
            'server_name' => "ecolab.cmozpodty13y.ap-southeast-1.rds.amazonaws.com",
            'database' => "ECOLAB_PRODUCTION",
            'username' => "ditsadmin",
            'password' => 'azsxdcfv'
        );
	$database = array("ECOLAB_PRODUCTION");
        $location_list = $this->location->get_location_remain($db_connect_mssql, $database, $show);

        foreach ($location_list as $key => $value) {
           
            if (isset($value["Detail"])) {
                $UsedPallet = 0;
                $RemainPallet = 0;
                $UsedItem = 0;
                $RemainItem = 0;
                $Balance_Qty_total = 0;
                $array_company_name = array();

                // foreach ($value["Detail"] as $key1 => $value1) {
                   
                //     if ($value1["Pallet_Code"] != "") {
                //         $UsedPallet += intval($value["Capacity_Max_Pallet"]);
                //         $RemainPallet = 0;
                //         $UsedItem = 0;
                //         $RemainItem = $value["Max_Capacity"];
                //     } else {
                //         $UsedPallet += 0;
                //         $RemainPallet = $value["Capacity_Max_Pallet"];
                //         $Balance_Qty_total += intval($value1["Balance_Qty"]);
                //         $UsedItem = $Balance_Qty_total;
                //         $RemainItem = (intval($value["Max_Capacity"]) - intval($UsedItem));
                //     }
                //     $RemainPallet = $value["Capacity_Max_Pallet"] -  $UsedPallet;

                //     
                //     $array_company_name[] = $value1["Company"];
                // }
                $PalletCode = array();
                $Palletunique;
                foreach ($value["Detail"] as $key1 => $value1) {
                    if($value1["Pallet_Code"] != ""){
                        $PalletCode[] = $value1["Pallet_Code"];
                        $RemainItem = $value["Max_Capacity"];
                    }else{
                        $UsedPallet += 0;
                        $RemainPallet = $value["Capacity_Max_Pallet"];

                        $Balance_Qty_total = floatval($Balance_Qty_total) + floatval($value1["Balance_Qty"]);
                        $UsedItem = $Balance_Qty_total;
                        // $RemainItem = (floatval($value["Max_Capacity"]) - floatval($Balance_Qty_total));
                        // $RemainItem = floatval($RemainItem);
                        
                    }
                    $array_company_name[] = $value1["Company"];
                }
                $Palletunique =  array_unique($PalletCode);
                $UsedPallet = sizeof($Palletunique);
                $RemainPallet = $value["Capacity_Max_Pallet"] -  $UsedPallet;

                $RemainItem = (floatval($value["Max_Capacity"]) - floatval($UsedItem));
                $RemainItem = floatval($RemainItem);
                
            
                $location_list[$value["Location_Code"]]["UsedPallet"] = $UsedPallet;
                $location_list[$value["Location_Code"]]["RemainPallet"] = $RemainPallet;
                $location_list[$value["Location_Code"]]["UsedItem"] = $UsedItem;
                $location_list[$value["Location_Code"]]["RemainItem"] = $RemainItem;

                $unique_company = array_unique($array_company_name);
                $location_list[$value["Location_Code"]]["Company"] = $value1["Company"] != "" ? implode(",", $unique_company) : "";
            } else {
                $location_list[$value["Location_Code"]]["UsedPallet"] = 0;
                $location_list[$value["Location_Code"]]["RemainPallet"] = $value["Capacity_Max_Pallet"];
                $location_list[$value["Location_Code"]]["UsedItem"] = 0;
                $location_list[$value["Location_Code"]]["RemainItem"] = $value["Max_Capacity"];
                $location_list[$value["Location_Code"]]["Company"] = "";
            }
        }
        ksort($location_list);

        if ($type == "EXCEL") {
            $this->exportLocationRemainToExcel($location_list);
        } else {
            $this->exportLocationRemainToPDF($location_list);
        }
    }

    function exportLocationRemainToExcel($location_list) {
        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Location Remain");

        $styleArray = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FE0606')
        ));

########### Header ###############
#กำหนด column
        $pallet_sheet->mergeCells('A1:A2');
        $pallet_sheet->SetCellValue('A1', "No");
        $pallet_sheet->mergeCells('B1:B2');
        $pallet_sheet->SetCellValue('B1', "Location Code");
        $pallet_sheet->mergeCells('C1:E1');
        $pallet_sheet->SetCellValue('C1', "Pallet");
        $pallet_sheet->SetCellValue('C2', "Max Capacity");
        $pallet_sheet->SetCellValue('D2', "Used");
        $pallet_sheet->SetCellValue('E2', "Remain");
        $pallet_sheet->mergeCells('F1:H1');
        $pallet_sheet->SetCellValue('F1', "Item");
        $pallet_sheet->SetCellValue('F2', "Max Capacity");
        $pallet_sheet->SetCellValue('G2', "Used");
        $pallet_sheet->SetCellValue('H2', "Remain");
        $pallet_sheet->mergeCells('I1:I2');
        $pallet_sheet->SetCellValue('I1', "Comapny");

        #กำหนดให้ column เป็นตัวหนา 
        $pallet_sheet->getStyle('A')->getFont()->setBold(true);
        $pallet_sheet->getStyle('B')->getFont()->setBold(true);
        $pallet_sheet->getStyle('C')->getFont()->setBold(true);
        $pallet_sheet->getStyle('D')->getFont()->setBold(true);
        $pallet_sheet->getStyle('E')->getFont()->setBold(true);
        $pallet_sheet->getStyle('F')->getFont()->setBold(true);
        $pallet_sheet->getStyle('G')->getFont()->setBold(true);
        $pallet_sheet->getStyle('H')->getFont()->setBold(true);
        $pallet_sheet->getStyle('I')->getFont()->setBold(true);

        #กำหนดให้ column อยู่กึ่งกลาง
        $pallet_sheet->getStyle('A1:A2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $pallet_sheet->getStyle('B1:B2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));
        $pallet_sheet->getStyle('C1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('C2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('D2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('E2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('F2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('G2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('H2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $pallet_sheet->getStyle('I1:I1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::VERTICAL_CENTER,));

        #กำหนดขนาดให้ column
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14.57);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9.71);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14.57);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9.71);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        ######### End header #################
        #ใส่ขอบให้ column header
        $objPHPExcel->getActiveSheet()->getStyle('A1' . ':I1')->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
        $objPHPExcel->getActiveSheet()->getStyle('A2' . ':I2')->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));

        ######### Set Color header #################
        $objPHPExcel->getActiveSheet()->getStyle('C1:E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ADFF2F');
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ADFF2F');
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ADFF2F');
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ADFF2F');
        $objPHPExcel->getActiveSheet()->getStyle('F1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD700');
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD700');
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD700');
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD700');

        $i = 1;
        $index_detail = 3;

        foreach ($location_list as $key => $value) {
            // p($value);exit;
            #ใส่ขอบให้ column detail
            $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':I' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
            $pallet_sheet->SetCellValue('A' . $index_detail, $i);
            $pallet_sheet->getStyle('B' . $index_detail)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $pallet_sheet->SetCellValue('B' . $index_detail, $value["Location_Code"]);

            $objPHPExcel->getActiveSheet()->getStyle('C' . $index_detail . ':E' . $index_detail)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ADFF2F');
            $pallet_sheet->SetCellValue('C' . $index_detail, $value["Capacity_Max_Pallet"]);

            if ($value["UsedPallet"] > $value["Capacity_Max_Pallet"]) {
                $objPHPExcel->getActiveSheet()->getStyle('D' . $index_detail)->applyFromArray($styleArray);
                $pallet_sheet->SetCellValue('D' . $index_detail, $value["UsedPallet"]);
            } else {
                $pallet_sheet->SetCellValue('D' . $index_detail, $value["UsedPallet"]);
            }
            $pallet_sheet->SetCellValue('E' . $index_detail, $value["RemainPallet"]);

            $objPHPExcel->getActiveSheet()->getStyle('F' . $index_detail . ':H' . $index_detail)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFD700');
            $pallet_sheet->SetCellValue('F' . $index_detail, $value["Max_Capacity"]);

            if ($value["UsedItem"] > $value["Max_Capacity"]) {
                $objPHPExcel->getActiveSheet()->getStyle('G' . $index_detail)->applyFromArray($styleArray);
                $pallet_sheet->SetCellValue('G' . $index_detail, $value["UsedItem"]);
            } else {
                $pallet_sheet->SetCellValue('G' . $index_detail, $value["UsedItem"]);
            }
            $pallet_sheet->SetCellValue('H' . $index_detail, $value["RemainItem"]);

            $pallet_sheet->getStyle('I' . $index_detail)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $pallet_sheet->SetCellValue('I' . $index_detail, $value["Company"]);

            $i++;
            $index_detail++;
        }

        # Save Excel file.
        $fileName = 'Location remain report -' . date('Ymd-His');

        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset = UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

    function exportLocationRemainToPDF($location_list) {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');

        $view["Header"] = array(
            "No" => "No",
            "Location_Code" => "Location Code",
            "Pallet" => "Pallet",
            "Item" => "Item",
            "Company" => "Company"
        );

        $view["Header_Detail"] = array(
            "Max_Capacity" => "Max_Capacity",
            "Used" => "Used",
            "Remain" => "Remain"
        );

        $view['from_date'] = $this->input->post("fdate");
        $view['to_date'] = $this->input->post("tdate");

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;
        $view['revision'] = $this->revision;

        $view["Body"] = $location_list;

        $this->load->view("report/exportLocationRemainToPDF", $view);
    }

}
