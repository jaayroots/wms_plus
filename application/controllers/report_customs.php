<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class report_customs extends CI_Controller {

    public $settings;       //add by kik : 20140114
    public $revision; //กำหนดตัวแปรสำหรับเรียก revision ของ SVN

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();   //add by kik : 20140114
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('pagination_ajax');
        $this->load->helper('form');
        $this->load->model("report_customs_model", "r");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("re_location_model", "reLocation");  // Add By Akkarapol, 16/10/2013, เพิ่มการ load model ชื่อ re_location_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("workflow_model", "flow");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ workflow_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("company_model", "company");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ company_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("system_management_model", "sys");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ system_management_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("pre_dispatch_model", "pre_dispatch");  // Add By Akkarapol, 21/10/2013, เพิ่มการ load model ชื่อ pre_dispatch_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("pending_model", "pending");  // Add By Akkarapol, 11/11/2013, เพิ่มการ load model ชื่อ pending_model ขึ้นมาเพื่อใช้ในฟังก์ชั่นต่างๆ
        $this->load->model("location_model", "lc");
        $this->load->model("product_model", "product");
        $this->load->model("order_movement_model", "order_movement");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));


        //ADD BY POR 2013-12-19 เพิ่ม revision สำหรับไปใช้ใน report
        //$this->config->set_item('svnversion', @shell_exec('svnversion '.realpath(__FILE__)));
        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
        //END ADD

        $this->load->model("location_history_model", "locationHis"); // ADD BY POR 2013-10-25
    }

    public function index() {
        
    }

    //=====Importation Report Index : BY POR 2014-10-28
    function importationReport() {
        $this->output->enable_profiler($this->config->item('set_debug')); //Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/customs/import_form_report", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Importation Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmImportReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
                // , 'button_action' => ''
        ));
    }

    //importationReport next search : BY POR 2014-10-28
    function importReportSearch() {
        $search = $this->input->get();

        ###################################################
        #	Pagination
        ###################################################
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->r->search_import_report($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying

        $pages->items_total = $num_rows;
        $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->r->search_import_report($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->search_import_report($search, null, null);
        endif;


        $view['pagination'] = $pages->display_pages();


        $view['search'] = $search;

        $this->load->view("report/customs/import_report.php", $view);
    }

    //=====แสดงรายงาน EXCEL รายงานการนำเข้า : ADD BY POR 2014-11-11
    function exportImportToExcel() {
        $search = $this->input->post();
        $datas = $this->r->search_import_report($search, null, null);

        $conf = $this->config->item('_xml');
        $record_per_sheet = (!empty($conf['record_per_sheet']) ? $conf['record_per_sheet'] : 60000);

        list($fdate, $fmonth, $fyear) = explode("/", $this->input->post("fdate"));
        list($tdate, $tmonth, $tyear) = explode("/", $this->input->post("tdate"));

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Sheet 1");

        $index = 7;
        $index_detail = 8;
        $count_data = 0;
        foreach ($datas as $key => $values):
            foreach ($values as $value):
                $count_data++;
                if ($count_data > $record_per_sheet):
                    $pallet_sheet = $objPHPExcel->createSheet();
                    $pallet_sheet->setTitle("Sheet " . $objPHPExcel->getSheetCount());

                    $count_data = 1;

                    #กรณีขึ้น sheet ใหม่ให้หัว column อยู่บรรทัดแรกเลย
                    $index = 7;
                    $index_detail = 8;
                endif;

                # Title        
                $pallet_sheet->mergeCells('A1:K1');
                $pallet_sheet->SetCellValue('A1', _lang("cus_renter_name") . ": " . $this->session->userdata('dept_name'));
                $pallet_sheet->getStyle("A1")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A2:K2');
                $pallet_sheet->SetCellValue('A2', _lang("cus_customs_entry") . ": " . $this->input->post("customs_entry"));
                $pallet_sheet->getStyle("A2")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A3:K3');
                $pallet_sheet->SetCellValue('A3', _lang("cus_ior") . ": " . $this->input->post("ior"));
                $pallet_sheet->getStyle("A3")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A4:K4');
                $pallet_sheet->SetCellValue('A4', _lang("cus_invoice") . ": " . $this->input->post("invoice"));
                $pallet_sheet->getStyle("A4")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A5:K5');
                $pallet_sheet->SetCellValue('A5', _lang("cus_from_date") . ": " . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) . "         " . _lang("cus_to_date") . " : " . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543));
                $pallet_sheet->getStyle("A5")->getFont()->setBold(true);


                ########### Header ###############
                #กำหนด column
                $pallet_sheet->SetCellValue('A' . $index, _lang('cus_renter_name'));
                $pallet_sheet->SetCellValue('B' . $index, _lang('cus_customs_entry'));
                $pallet_sheet->SetCellValue('C' . $index, _lang('cus_ior'));
                $pallet_sheet->SetCellValue('D' . $index, _lang('invoice_no'));
                $pallet_sheet->SetCellValue('E' . $index, _lang('receive_date'));
                $pallet_sheet->SetCellValue('F' . $index, _lang('product_code'));
                $pallet_sheet->SetCellValue('G' . $index, _lang('product_name'));
                $pallet_sheet->SetCellValue('H' . $index, _lang('receive_qty'));
                $pallet_sheet->SetCellValue('I' . $index, _lang('unit'));
                $pallet_sheet->SetCellValue('J' . $index, _lang('all_price'));
                $pallet_sheet->SetCellValue('K' . $index, _lang('unit_price'));

                #กำหนดให้ column เป็นตัวหนา 
                $pallet_sheet->getStyle('A' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('B' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('C' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('D' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('E' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('F' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('G' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('H' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('I' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('J' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('K' . $index)->getFont()->setBold(true);

                #กำหนดให้ column อยู่กึ่งกลาง
                $pallet_sheet->getStyle('A' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('B' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('C' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('D' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('E' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('F' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('G' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('H' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('I' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('J' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('K' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                ######### End header #################
                #ใส่ขอบให้ column header
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':K' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));

                #ใส่ขอบให้ column detail
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':K' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                $objPHPExcel->getActiveSheet()->getStyle('E' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');

                $pallet_sheet->SetCellValue('A' . $index_detail, tis620_to_utf8($value['Renter_Name']));
                $pallet_sheet->SetCellValue('B' . $index_detail, tis620_to_utf8($values[0]['Doc_Refer_CE']));
                $pallet_sheet->SetCellValue('C' . $index_detail, tis620_to_utf8($values[0]['Vendor_Name']));
                $pallet_sheet->SetCellValue('D' . $index_detail, tis620_to_utf8($value['Invoice_No']));
                $pallet_sheet->SetCellValue('E' . $index_detail, $value['Receive_Date']);
                //$pallet_sheet->setCellValueByColumnAndRow('E' . $index_detail, PHPExcel_Shared_Date::PHPToExcel( '2014-10-16' ));
               // PHPExcel_Shared_Date::PHPToExcel( '2014-10-16' )
                $pallet_sheet->SetCellValue('F' . $index_detail, $value['Product_Code']);
                $pallet_sheet->SetCellValue('G' . $index_detail, tis620_to_utf8($value['Product_NameEN']));
                $pallet_sheet->SetCellValue('H' . $index_detail, $value['Receive_Qty']);
                $pallet_sheet->getStyle('H' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('I' . $index_detail, tis620_to_utf8($value['unit']));
                $pallet_sheet->SetCellValue('J' . $index_detail, $value['price']);
                $pallet_sheet->getStyle('J' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('K' . $index_detail, tis620_to_utf8($value['unit_price']));

                $index_detail++;

            endforeach;
        endforeach;

        # Save Excel file.
        $fileName = "import_report";


        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

    public function export() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $this->load->model("company_model", "company");
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/customs/export_form", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Customs Export '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmExport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    public function export_search() {
        $search = $this->input->get();
        $num_rows = sizeof($this->r->search_export_report($search, null, null));

        $pages = new Pagination_ajax;
        $pages->items_total = $num_rows;
        $pages->mid_range = 5;
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;

        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->r->search_export_report($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->search_export_report($search, null, null);
        endif;


        $view['pagination'] = $pages->display_pages();

        $view['search'] = $search;

        $this->load->view("report/customs/export_report.php", $view);
    }

    public function export_to_excel() {
        $search = $this->input->post();
        $datas = $this->r->search_export_report($search, null, null);

        $conf = $this->config->item('_xml');
        $record_per_sheet = (!empty($conf['record_per_sheet']) ? $conf['record_per_sheet'] : 60000);

        list($fdate, $fmonth, $fyear) = explode("/", $this->input->post("fdate"));
        list($tdate, $tmonth, $tyear) = explode("/", $this->input->post("tdate"));

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Sheet 1");

        $index = 7;
        $index_detail = 8;
        $count_data = 0;
        foreach ($datas as $key => $values):
            foreach ($values as $value):
                $count_data++;
                if ($count_data > $record_per_sheet):
                    $pallet_sheet = $objPHPExcel->createSheet();
                    $pallet_sheet->setTitle("Sheet " . $objPHPExcel->getSheetCount());

                    $count_data = 1;

                    #กรณีขึ้น sheet ใหม่ให้หัว column อยู่บรรทัดแรกเลย
                    $index = 7;
                    $index_detail = 8;
                endif;

                # Title        
                $pallet_sheet->mergeCells('A1:K1');
                $pallet_sheet->SetCellValue('A1', _lang("cus_renter_name") . ": " . $this->session->userdata('dept_name'));
                $pallet_sheet->getStyle("A1")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A2:K2');
                $pallet_sheet->SetCellValue('A2', _lang("cus_customs_entry") . ": " . $this->input->post("customs_entry"));
                $pallet_sheet->getStyle("A2")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A3:K3');
                $pallet_sheet->SetCellValue('A3', _lang("cus_eor") . ": " . $this->input->post("eor"));
                $pallet_sheet->getStyle("A3")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A4:K4');
                $pallet_sheet->SetCellValue('A4', _lang("cus_invoice") . ": " . $this->input->post("invoice"));
                $pallet_sheet->getStyle("A4")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A5:K5');
                $pallet_sheet->SetCellValue('A5', _lang("cus_from_date") . ": " . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) . "         " . _lang("cus_to_date") . " : " . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543));
                $pallet_sheet->getStyle("A5")->getFont()->setBold(true);


                ########### Header ###############
                #กำหนด column
                $pallet_sheet->SetCellValue('A' . $index, _lang('cus_renter_name'));
                $pallet_sheet->SetCellValue('B' . $index, _lang('customs_entry'));
                $pallet_sheet->SetCellValue('C' . $index, _lang('cus_eor'));
                $pallet_sheet->SetCellValue('D' . $index, _lang('invoice_no'));
                $pallet_sheet->SetCellValue('E' . $index, _lang('dispatch_date'));
                $pallet_sheet->SetCellValue('F' . $index, _lang('product_code'));
                $pallet_sheet->SetCellValue('G' . $index, _lang('product_name'));
                $pallet_sheet->SetCellValue('H' . $index, _lang('dispatch_qty'));
                $pallet_sheet->SetCellValue('I' . $index, _lang('unit'));
                $pallet_sheet->SetCellValue('J' . $index, _lang('all_price'));
                $pallet_sheet->SetCellValue('K' . $index, _lang('unit_price'));

                #กำหนดให้ column เป็นตัวหนา 
                $pallet_sheet->getStyle('A' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('B' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('C' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('D' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('E' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('F' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('G' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('H' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('I' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('J' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('K' . $index)->getFont()->setBold(true);

                #กำหนดให้ column อยู่กึ่งกลาง
                $pallet_sheet->getStyle('A' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('B' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('C' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('D' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('E' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('F' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('G' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('H' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('I' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('J' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('K' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                ######### End header #################
                #ใส่ขอบให้ column header
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':K' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));

                #ใส่ขอบให้ column detail
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':K' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                $objPHPExcel->getActiveSheet()->getStyle('E' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');
          

                $pallet_sheet->SetCellValue('A' . $index_detail, tis620_to_utf8($value['Renter_Name']));
                $pallet_sheet->SetCellValue('B' . $index_detail, tis620_to_utf8($value['Doc_Refer_CE']));
                $pallet_sheet->SetCellValue('C' . $index_detail, tis620_to_utf8($value['EOR_Name']));
                $pallet_sheet->SetCellValue('D' . $index_detail, tis620_to_utf8($value['Invoice_No']));

                // $myDateString = str_replace('/', '-', $value['Dispatch_Date']);
                // $pallet_sheet->getStyle('E' . $index_detail)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DMYSLASH);
                $pallet_sheet->SetCellValue('E' . $index_detail, $value['Dispatch_Date']);
                
                $pallet_sheet->SetCellValue('F' . $index_detail, $value['Product_Code']);
                $pallet_sheet->SetCellValue('G' . $index_detail, tis620_to_utf8($value['Product_NameEN']));
                $pallet_sheet->SetCellValue('H' . $index_detail, $value['Dispatch_Qty']);
                $pallet_sheet->getStyle('H' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('I' . $index_detail, tis620_to_utf8($value['unit']));
                $pallet_sheet->SetCellValue('J' . $index_detail, $value['price']);
                $pallet_sheet->getStyle('J' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('K' . $index_detail, tis620_to_utf8($value['unit_price']));

                $index_detail++;

            endforeach;
        endforeach;

        # Save Excel file.
        $fileName = "export_report";


        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

    public function export_to_pdf() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->model("company_model", "company");
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();

        $report = $this->r->search_export_report($search);
        $view['datas'] = $report;

        $view['header_table'] = array('ลำดับที่'
            , 'เลขที่ใบขน / ใบโอน'
            , 'EOR'
            , 'เลขที่ Invoice หรือ เลขที่เอกสารประกอบ'
            , 'วันที่ส่งออก / วันที่โอน'
            , 'รหัสสินค้า / วัตถุดิบ'
            , 'รายละเอียดสินค้า'
            , 'ปริมาณ'
            , 'หน่วยนับ'
            , 'มูลค่า');

        $view['from_date'] = $this->input->post("fdate");
        $view['to_date'] = $this->input->post("tdate");

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;
        $view['revision'] = $this->revision;

        $this->load->view("report/customs/export_pdf", $view);
    }

    //=====แสดงรายงาน PDF รายการการนำเข้า : ADD BY POR 2014-11-12
    function exportImportToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();

        $report = $this->r->search_import_report($search);
        $view['datas'] = $report;


        $view['header_table'] = array('ลำดับที่'
            , 'เลขที่ใบขน/ใบโอน'
            , 'เลขที่ INVOICE หรือเลขที่เอกสารประกอบ'
            , 'วันนำเข้า/วันรับโอน'
            , 'รหัสสินค้า/วัตถุดิบ'
            , 'รายละเอียดสินค้า'
            , 'ปริมาณ'
            , 'หน่วยนับ'
            , 'มูลค่า');


        //เรียกวันที่ของข้อมูลจากที่เลือก
        $view['from_date'] = $this->input->post("fdate"); //วันที่เริ่มค้น
        $view['to_date'] = $this->input->post("tdate"); //วันที่สิ้นสุด
        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $revision = $this->revision;
        $view['revision'] = $revision;

        $this->load->view("report/customs/import_pdf", $view);
    }

    //remaining report : ADD BY POR 2014-11-12
    //=====remaining Report Index : BY POR 2014-11-12
    function remaining_report() {
        $this->output->enable_profiler($this->config->item('set_debug')); //Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/customs/remain_form_report", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Remaining Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmRemainReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    //remainReport next search : BY POR 2014-11-12
    function remainReportSearch() {
        $search = $this->input->get();

        ###################################################
        #	Pagination
        ###################################################
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->r->search_remain_report($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying

        $pages->items_total = $num_rows;
        $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->r->search_remain_report($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->search_remain_report($search, null, null);
        endif;


        $view['pagination'] = $pages->display_pages();


        $view['search'] = $search;

        $this->load->view("report/customs/remain_report.php", $view);
    }

    //=====แสดงรายงาน PDF รายงานของคงเหลือ : ADD BY POR 2014-11-12
    function exportRemainToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $search = $this->input->post();

        $report = $this->r->search_remain_report($search);
        $view['datas'] = $report;


        $view['header_table'] = array('ลำดับที่'
            , 'เลขที่ใบขน/ใบโอน'
            , 'รหัสสินค้า/วัตถุดิบ'
            , 'ชนิดของ'
            , 'ปริมาณ'
            , 'หน่วยนับ'
            , 'มูลค่า');


        //เรียกชื่อของคนที่ออกรายงาน
        $view['from_date'] = $this->input->post("fdate"); //วันที่เริ่มค้น
        $view['to_date'] = $this->input->post("tdate"); //วันที่สิ้นสุด
        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $revision = $this->revision;
        $view['revision'] = $revision;

        $this->load->view("report/customs/remain_pdf", $view);
    }

    //=====แสดงรายงาน EXCEL รายงานสินค้าคงเหลือ : ADD BY POR 2014-11-12
    function exportRemainToExcel() {
        $search = $this->input->post();
        $datas = $this->r->search_remain_report($search, null, null);

        $conf = $this->config->item('_xml');
        $record_per_sheet = (!empty($conf['record_per_sheet']) ? $conf['record_per_sheet'] : 60000);

        list($fdate, $fmonth, $fyear) = explode("/", $this->input->post("fdate"));
        //list($tdate, $tmonth, $tyear) = explode("/", $this->input->post("tdate"));

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Sheet 1");

        $index = 6;
        $index_detail = 7;
        $count_data = 0;
        foreach ($datas as $key => $values):
            foreach ($values as $value):
                $count_data++;
                if ($count_data > $record_per_sheet):
                    $pallet_sheet = $objPHPExcel->createSheet();
                    $pallet_sheet->setTitle("Sheet " . $objPHPExcel->getSheetCount());

                    $count_data = 1;

                    #กรณีขึ้น sheet ใหม่ให้หัว column อยู่บรรทัดแรกเลย
                    $index = 6;
                    $index_detail = 7;
                endif;

                # Title 
                $pallet_sheet->mergeCells('A1:H1');
                $pallet_sheet->SetCellValue('A1', _lang("cus_renter_name") . ": " . $this->session->userdata('dept_name'));
                $pallet_sheet->getStyle("A1")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A2:H2');
                $pallet_sheet->SetCellValue('A2', _lang("cus_customs_entry") . ": " . $this->input->post("customs_entry"));
                $pallet_sheet->getStyle("A2")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A3:H3');
                $pallet_sheet->SetCellValue('A3', _lang("cus_ior") . ": " . $this->input->post("ior"));
                $pallet_sheet->getStyle("A3")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A4:H4');
                $pallet_sheet->SetCellValue('A4', _lang("date") . ": " . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543));
                $pallet_sheet->getStyle("A4")->getFont()->setBold(true);


                ########### Header ###############
                #กำหนด column
                $pallet_sheet->SetCellValue('A' . $index, _lang('customs_entry'));
                $pallet_sheet->SetCellValue('B' . $index, _lang('cus_ior'));
                $pallet_sheet->SetCellValue('C' . $index, _lang('product_code'));
                $pallet_sheet->SetCellValue('D' . $index, _lang('product_name'));
                $pallet_sheet->SetCellValue('E' . $index, _lang('balance'));
                $pallet_sheet->SetCellValue('F' . $index, _lang('unit'));
                $pallet_sheet->SetCellValue('G' . $index, _lang('all_price'));
                $pallet_sheet->SetCellValue('H' . $index, _lang('unit_price'));

                #กำหนดให้ column เป็นตัวหนา 
                $pallet_sheet->getStyle('A' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('B' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('C' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('D' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('E' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('F' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('G' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('H' . $index)->getFont()->setBold(true);

                #กำหนดให้ column อยู่กึ่งกลาง
                $pallet_sheet->getStyle('A' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('B' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('C' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('D' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('E' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('F' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('G' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('H' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                ######### End header #################
                #ใส่ขอบให้ column header
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':H' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));

                #ใส่ขอบให้ column detail
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':H' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));

                $pallet_sheet->SetCellValue('A' . $index_detail, tis620_to_utf8($values[0]['Doc_Refer_CE']));
                $pallet_sheet->SetCellValue('B' . $index_detail, tis620_to_utf8($values[0]['Vendor_Name']));
                $pallet_sheet->SetCellValue('C' . $index_detail, tis620_to_utf8($value['Product_Code']));
                $pallet_sheet->SetCellValue('D' . $index_detail, iconv("TIS-620", "UTF-8", $value["Product_NameEN"]));
                $pallet_sheet->SetCellValue('E' . $index_detail, $value['Balance_Qty']);
                $pallet_sheet->getStyle('E' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('F' . $index_detail, tis620_to_utf8($value['unit']));
                $pallet_sheet->SetCellValue('G' . $index_detail, $value['Price']);
                $pallet_sheet->getStyle('G' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('H' . $index_detail, tis620_to_utf8($value['unit_price']));

                $index_detail++;

            endforeach;
        endforeach;

        # Save Excel file.
        $fileName = "remain_excel";


        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

    public function movement() {
        $this->output->enable_profiler($this->config->item('set_debug'));

        #Get renter list
        $this->load->model("company_model", "company");
        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;
        $parameter['renter_id'] = $r_renter['0']->Company_Id;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/customs/movement_form", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Customs Stock Movement '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmImportReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    public function movement_search() {

        $search = $this->input->get();

        $result = $this->r->searchProductMovementAllProduct_showItem($search);
        $view['datas'] = $result;
        $view['form_value'] = $search;
        $this->load->view("report/customs/movement_by_item_report.php", $view);
    }

    public function export_movement_by_item() {
        $search = $this->input->post();

        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        $revision = $this->revision;
        $view['revision'] = $revision;

        $this->load->library('pdf/mpdf');
        $result = $this->r->searchProductMovementAllProduct_showItem($search);
        $view['datas'] = $result;
        $view['form_value'] = $search;
        $this->load->view("report/customs/export_movement_by_item_pdf.php", $view);
    }

    public function exportExcelMovementAll_showItem() {
        $search = $this->input->post();
        $datas = $this->r->searchProductMovementAllProduct_showItem($search);

        $conf = $this->config->item('_xml');
        $build_pallet = (!empty($conf['build_pallet']) ? $conf['build_pallet'] : false);
        $record_per_sheet = (!empty($conf['record_per_sheet']) ? $conf['record_per_sheet'] : 60000);

        list($fdate, $fmonth, $fyear) = explode("/", $this->input->post("fdate"));
        list($tdate, $tmonth, $tyear) = explode("/", $this->input->post("tdate"));

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Sheet 1");

        $index = 9;
        $index_detail = 10;
        $count_data = 0;
        foreach ($datas as $key => $values):
            foreach ($values as $value):

                $count_data++;
                if ($count_data > $record_per_sheet):
                    $pallet_sheet = $objPHPExcel->createSheet();
                    $pallet_sheet->setTitle("Sheet " . $objPHPExcel->getSheetCount());

                    $count_data = 1;

                    #กรณีขึ้น sheet ใหม่ให้หัว column อยู่บรรทัดแรกเลย
                    $index = 9;
                    $index_detail = 10;
                endif;

                # Title 
                if ($build_pallet):
                    $pallet_sheet->mergeCells('A1:U1');
                    $pallet_sheet->mergeCells('A2:U2');
                    $pallet_sheet->mergeCells('A3:U3');
                    $pallet_sheet->mergeCells('A4:U4');
                    $pallet_sheet->mergeCells('A5:U5');
                    $pallet_sheet->mergeCells('A6:U6');
                    $pallet_sheet->mergeCells('A7:U7');
                else:
                    $pallet_sheet->mergeCells('A1:T1');
                    $pallet_sheet->mergeCells('A2:T2');
                    $pallet_sheet->mergeCells('A3:T3');
                    $pallet_sheet->mergeCells('A4:T4');
                    $pallet_sheet->mergeCells('A5:T5');
                    $pallet_sheet->mergeCells('A6:T6');
                    $pallet_sheet->mergeCells('A7:T7');
                endif;
                $pallet_sheet->SetCellValue('A1', _lang("cus_renter_name") . ": " . $this->session->userdata('dept_name'));
                $pallet_sheet->getStyle("A1")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A2', _lang("cus_ior") . ": " . $this->input->post("ior"));
                $pallet_sheet->getStyle("A2")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A3', _lang("cus_eor") . ": " . $this->input->post("eor"));
                $pallet_sheet->getStyle("A3")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A4', _lang("cus_customs_entry_inbound") . ": " . $this->input->post("ce_in"));
                $pallet_sheet->getStyle("A4")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A5', _lang("cus_customs_entry_outbound") . ": " . $this->input->post("ce_out"));
                $pallet_sheet->getStyle("A5")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A6', _lang("product_name") . ": " . $this->input->post("product_name"));
                $pallet_sheet->getStyle("A6")->getFont()->setBold(true);

                $pallet_sheet->SetCellValue('A7', _lang("cus_from_date") . ": " . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) . "         " . _lang("cus_to_date") . " : " . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543));
                $pallet_sheet->getStyle("A7")->getFont()->setBold(true);


                ########### Header ###############
                #กำหนด column
                $pallet_sheet->SetCellValue('A' . $index, _lang('product_code'));
                $pallet_sheet->SetCellValue('B' . $index, _lang('product_name'));
                $pallet_sheet->SetCellValue('C' . $index, _lang('date'));
                $pallet_sheet->SetCellValue('D' . $index, _lang('number_received'));
                $pallet_sheet->SetCellValue('E' . $index, _lang('reference_received'));
                $pallet_sheet->SetCellValue('F' . $index, _lang('cus_customs_entry_inbound'));
                $pallet_sheet->SetCellValue('G' . $index, _lang('cus_ior'));
                //$pallet_sheet->SetCellValue('H'.$index, _lang('dispatch_date'));
                $pallet_sheet->SetCellValue('I' . $index, _lang('number_dispatch'));
                $pallet_sheet->SetCellValue('J' . $index, _lang('reference_dispatch'));
                $pallet_sheet->SetCellValue('K' . $index, _lang('cus_customs_entry_outbound'));
                $pallet_sheet->SetCellValue('L' . $index, _lang('cus_eor'));
                $pallet_sheet->SetCellValue('M' . $index, _lang('receive_qty'));
                $pallet_sheet->SetCellValue('N' . $index, _lang('dispatch_qty'));
                $pallet_sheet->SetCellValue('O' . $index, _lang('serial') . "/" . _lang('lot'));
                $pallet_sheet->SetCellValue('P' . $index, _lang('product_mfd'));
                $pallet_sheet->SetCellValue('Q' . $index, _lang('product_exp'));
                $pallet_sheet->SetCellValue('R' . $index, _lang('from_to'));
                $pallet_sheet->SetCellValue('S' . $index, _lang('location'));
                if ($build_pallet):
                    $pallet_sheet->SetCellValue('T' . $index, _lang('pallet_code'));
                    $pallet_sheet->SetCellValue('U' . $index, _lang('balance'));
                else:
                    $pallet_sheet->SetCellValue('T' . $index, _lang('balance'));
                endif;

                #กำหนดให้ column เป็นตัวหนา 
                $pallet_sheet->getStyle('A' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('B' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('C' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('D' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('E' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('F' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('G' . $index)->getFont()->setBold(true);
                //$pallet_sheet->getStyle('H'.$index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('I' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('J' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('K' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('L' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('M' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('N' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('O' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('P' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('Q' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('R' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('S' . $index)->getFont()->setBold(true);
                if ($build_pallet):
                    $pallet_sheet->getStyle('T' . $index)->getFont()->setBold(true);
                    $pallet_sheet->getStyle('U' . $index)->getFont()->setBold(true);
                else:
                    $pallet_sheet->getStyle('T' . $index)->getFont()->setBold(true);
                endif;

                #กำหนดให้ column อยู่กึ่งกลาง
                $pallet_sheet->getStyle('A' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('B' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('C' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('D' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('E' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('F' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('G' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                //$pallet_sheet->getStyle('H'.$index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('I' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('J' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('K' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('L' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('M' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('N' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('O' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('P' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('Q' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('R' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('S' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                if ($build_pallet):
                    $pallet_sheet->getStyle('T' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                    $pallet_sheet->getStyle('U' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                else:
                    $pallet_sheet->getStyle('T' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                endif;
                ######### End header #################


                if ($build_pallet):
                    #ใส่ขอบให้ column header
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':U' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                    #ใส่ขอบให้ column detail
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':U' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                else:
                    #ใส่ขอบให้ column header
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':T' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                    #ใส่ขอบให้ column detail
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':T' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                endif;

                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setVisible(false);

                //echo "R = " . $value['receive_date'] . " ; D = " . $value['receive_date'];
                //echo "<br/>";

                $objPHPExcel->getActiveSheet()->getStyle('C' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');
                $objPHPExcel->getActiveSheet()->getStyle('P' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');
                $objPHPExcel->getActiveSheet()->getStyle('Q' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');

                $pallet_sheet->SetCellValue('A' . $index_detail, $value['Product_Code']);
                $pallet_sheet->SetCellValue('B' . $index_detail, tis620_to_utf8($value['Product_NameEN']));
                $pallet_sheet->SetCellValue('C' . $index_detail, $value['receive_date_excel'] . $value['dispatch_date_excel']);
                $pallet_sheet->SetCellValue('D' . $index_detail, $value['receive_doc_no']);
                $pallet_sheet->SetCellValue('E' . $index_detail, tis620_to_utf8($value['receive_refer_ext']));
                $pallet_sheet->SetCellValue('F' . $index_detail, $value['CE_IN']);
                $pallet_sheet->SetCellValue('G' . $index_detail, tis620_to_utf8($value['IOR_Name']));
                //$pallet_sheet->SetCellValue('H'.$index_detail, $value['dispatch_date_excel']);
                $pallet_sheet->SetCellValue('I' . $index_detail, $value['pay_doc_no']);
                $pallet_sheet->SetCellValue('J' . $index_detail, tis620_to_utf8($value['pay_refer_ext']));
                $pallet_sheet->SetCellValue('K' . $index_detail, $value['CE_OUT']);
                $pallet_sheet->SetCellValue('L' . $index_detail, tis620_to_utf8($value['EOR_Name']));
                $pallet_sheet->SetCellValue('M' . $index_detail, $value['r_qty']);
                $pallet_sheet->getStyle('M' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('N' . $index_detail, $value['p_qty']);
                $pallet_sheet->getStyle('N' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('O' . $index_detail, tis620_to_utf8($value['Product_SerLot']));
                $pallet_sheet->SetCellValue('P' . $index_detail, $value['Product_Mfd']);
                $pallet_sheet->SetCellValue('Q' . $index_detail, $value['Product_Exp']);
                $pallet_sheet->SetCellValue('R' . $index_detail, tis620_to_utf8($value['branch']));
                $pallet_sheet->SetCellValue('S' . $index_detail, $value['Location_Code']);
                if ($build_pallet):
                    $pallet_sheet->SetCellValue('T' . $index_detail, $value['Pallet_Code']);
                    $pallet_sheet->SetCellValue('U' . $index_detail, $value['Balance_Qty']);
                    $pallet_sheet->getStyle('U' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                else:
                    $pallet_sheet->SetCellValue('T' . $index_detail, $value['Balance_Qty']);
                    $pallet_sheet->getStyle('T' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                endif;
                $index_detail++;

            endforeach;
        endforeach;

        //p($pallet_sheet);
        //exit;
        # Save Excel file.
        $fileName = "stockmovement";


        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

    public function export_movement_by_total() {
        $search = $this->input->post();
        $result = $this->r->searchProductMovementAllProduct($search);
        $view['datas'] = $result;
        $view['form_value'] = $search;
        $this->load->view("report/customs/export_movement_by_total_pdf.php", $view);
    }

    //borrow report : ADD BY POR 2014-11-17
    //=====borrow Report Index : BY POR 2014-11-17
    function borrow_report() {
        $this->output->enable_profiler($this->config->item('set_debug')); //Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/customs/borrow_form_report", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Borrow Report '
//            , 'menu' => $this->menu->loadMenuAuth()
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmBorrowReport"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    //borrowReport next search : BY POR 2014-11-17
    function borrowReportSearch() {
        $search = $this->input->get();

        ###################################################
        #	Pagination
        ###################################################
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->r->search_borrow_report($search, null, null)); // this is the COUNT(*) query that gets the total record count from the table you are querying

        $pages->items_total = $num_rows;
        $pages->mid_range = 5; // number of links you want to show in the pagination before the "..."
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->r->search_borrow_report($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->r->search_borrow_report($search, null, null);
        endif;


        $view['pagination'] = $pages->display_pages();


        $view['search'] = $search;

        $this->load->view("report/customs/borrow_report.php", $view);
    }

    public function exportBorrowToPDF() {
        $search = $this->input->post();
        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        $revision = $this->revision;
        $view['revision'] = $revision;

        $this->load->library('pdf/mpdf');
        $result = $this->r->search_borrow_report($search);
        $view['datas'] = $result;
        $view['form_value'] = $search;
        $this->load->view("report/customs/borrow_pdf.php", $view);
    }

    //=====แสดงรายงาน EXCEL รายงานสินค้าคงเหลือ : ADD BY POR 2014-11-12
    function exportBorrowToExcel() {
        $search = $this->input->post();
        $datas = $this->r->search_borrow_report($search, 'EXCEL');

        $conf = $this->config->item('_xml');
        $record_per_sheet = (!empty($conf['record_per_sheet']) ? $conf['record_per_sheet'] : 60000);

        list($fdate, $fmonth, $fyear) = explode("/", $this->input->post("fdate"));
        list($tdate, $tmonth, $tyear) = explode("/", $this->input->post("tdate"));

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->removeSheetByIndex(0);
        $pallet_sheet = $objPHPExcel->createSheet();
        $pallet_sheet->setTitle("Sheet 1");

        $index = 5;
        $index_detail = 6;
        $count_data = 0;
        foreach ($datas as $key => $values):
            foreach ($values as $value):
                $count_data++;
                if ($count_data > $record_per_sheet):
                    $pallet_sheet = $objPHPExcel->createSheet();
                    $pallet_sheet->setTitle("Sheet " . $objPHPExcel->getSheetCount());

                    $count_data = 1;

                    #กรณีขึ้น sheet ใหม่ให้หัว column อยู่บรรทัดแรกเลย
                    $index = 5;
                    $index_detail = 6;
                endif;

                # Title
                $pallet_sheet->mergeCells('A1:L1');
                $pallet_sheet->SetCellValue('A1', _lang("cus_renter_name") . ": " . $this->session->userdata('dept_name'));
                $pallet_sheet->getStyle("A1")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A2:L2');
                $pallet_sheet->SetCellValue('A2', _lang("cus_custom_doc_ref") . ": " . $this->input->post("custom_doc_ref"));
                $pallet_sheet->getStyle("A2")->getFont()->setBold(true);

                $pallet_sheet->mergeCells('A3:L3');
                $pallet_sheet->SetCellValue('A3', _lang("cus_from_date") . ": " . $fdate . " " . nameMonthENG($fmonth) . " " . ($fyear + 543) . "         " . _lang("cus_to_date") . " : " . $tdate . " " . nameMonthENG($tmonth) . " " . ($tyear + 543));
                $pallet_sheet->getStyle("A3")->getFont()->setBold(true);

                ########### Header ###############
                #กำหนด column
                $pallet_sheet->SetCellValue('A' . $index, _lang('borrow_date'));
                $pallet_sheet->SetCellValue('B' . $index, _lang('return_date'));
                $pallet_sheet->SetCellValue('C' . $index, _lang('cus_date_diff'));
                $pallet_sheet->SetCellValue('D' . $index, _lang('cus_custom_doc_ref'));
                $pallet_sheet->SetCellValue('E' . $index, _lang('product_code'));
                $pallet_sheet->SetCellValue('F' . $index, _lang('product_name'));
                $pallet_sheet->SetCellValue('G' . $index, _lang('export_qty'));
                $pallet_sheet->SetCellValue('H' . $index, _lang('Import Qty'));
                $pallet_sheet->SetCellValue('I' . $index, _lang('remain_qty'));
                $pallet_sheet->SetCellValue('J' . $index, _lang('cus_all_price_borrow'));
                $pallet_sheet->SetCellValue('K' . $index, _lang('cus_all_price_return'));
                $pallet_sheet->SetCellValue('L' . $index, _lang('remark'));

                #กำหนดสีให้ column
//              $pallet_sheet->getStyle('A'.$index.':L'.$index)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '8B8B7A'))));
                #ใส่ขอบให้ header
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index . ':L' . $index)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));


                #กำหนดให้ column เป็นตัวหนา ;
                $pallet_sheet->getStyle('A' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('B' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('C' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('D' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('E' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('F' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('G' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('H' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('I' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('J' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('K' . $index)->getFont()->setBold(true);
                $pallet_sheet->getStyle('L' . $index)->getFont()->setBold(true);

                #กำหนดให้ column อยู่กึ่งกลาง
                $pallet_sheet->getStyle('A' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('B' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('C' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('D' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('E' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('F' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('G' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('H' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('I' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('J' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('K' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                $pallet_sheet->getStyle('L' . $index)->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                ######### End header #################
                #กำหนดสีให้ header
                /*
                  if(!empty($value['out_date'])):
                  $pallet_sheet->getStyle('A'.$index_detail.':L'.$index_detail)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'CDCDB4'))));
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$index_detail.':L'.$index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '808080'))));
                  else:
                  $pallet_sheet->getStyle('A'.$index_detail.':L'.$index_detail)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'EEEED1'))));
                  $objPHPExcel->getActiveSheet()->getStyle('A'.$index_detail.':L'.$index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('rgb' => '808080'))));
                  endif;
                 */
                #ใส่ขอบ
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail . ':L' . $index_detail)->getBorders()->applyFromArray(array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '000000'))));
                
                $objPHPExcel->getActiveSheet()->getStyle('A' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');
                $objPHPExcel->getActiveSheet()->getStyle('B' . $index_detail)->getNumberFormat()->setFormatCode('mm-dd-yy');

                $pallet_sheet->SetCellValue('A' . $index_detail, $value['out_date']);
                $pallet_sheet->SetCellValue('B' . $index_detail, $value['import_date']);
                $pallet_sheet->SetCellValue('C' . $index_detail, $value['date_diff']);
                $pallet_sheet->SetCellValue('D' . $index_detail, tis620_to_utf8($value['Custom_Doc_Ref']));
                $pallet_sheet->SetCellValue('E' . $index_detail, $value['Product_Code']);
                $pallet_sheet->SetCellValue('F' . $index_detail, tis620_to_utf8($value['Product_Name']));
                $pallet_sheet->SetCellValue('G' . $index_detail, $value['Confirm_Qty']);
                $pallet_sheet->getStyle('G' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('H' . $index_detail, $value['import_Receive_Qty']);
                $pallet_sheet->getStyle('H' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('I' . $index_detail, $value['remain_qty']);
                $pallet_sheet->getStyle('I' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('J' . $index_detail, $value['all_price']);
                $pallet_sheet->getStyle('J' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('K' . $index_detail, $value['import_All_Price']);
                $pallet_sheet->getStyle('K' . $index_detail)->getNumberFormat()->setFormatCode('0.00');
                $pallet_sheet->SetCellValue('L' . $index_detail, tis620_to_utf8($value['Remark']));

                $index_detail++;

            endforeach;
        endforeach;

        # Save Excel file.
        $fileName = "borrow_excel";


        $uploaddir = $this->settings['uploads']['upload_path'];

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($uploaddir . $fileName . '.xls');

        # Read Excel file.
        header('Content-Type: application/octet-stream charset=UTF-8');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $fileName . ".xls\"");
        readfile($uploaddir . $fileName . '.xls');
    }

}
