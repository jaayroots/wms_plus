<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// Create by Ton! 20130521
/* Location: ./application/controllers/import_Stock_On_Hand.php */
/* Support file .csv only. Coding program convert excel is csv later. */

class import_Stock_On_Hand extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->library('excel');
        $this->load->library('validate_data');
        $this->load->model("onhand_R_To_R_model", "R_R_model");
        $this->mnu_NavigationUri = "import_Stock_On_Hand";

        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->importForm();
    }

    function importForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $this->load->helper('form');
        $str_form = form_fieldset('Import R To R On Hand');
        $str_form.=$this->parser->parse('form/import_Stock_On_Hand', array(), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Import R To R On Hand'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function upload() {
        $config['upload_path'] = $this->settings['uploads']['upload_path'];
        $config['allowed_types'] = '*';
        $config['file_name'] = 'Rec-' . date('Ymd') . '-' . generateRandomString(9);

        $this->load->library('upload', $config);
        $field_name = "xfile";
        if ($this->upload->do_upload($field_name)) {
            $up = $this->upload->data();
            $this->import($up["full_path"]);
        } else {
            p("Upload Unsuccessful !!");
            log_message('error', 'import_Stock_On_Hand Not Success');
            redirect('import_Stock_On_Hand', 'refresh');
        }
    }

    function import($path) {
       

        #Load config
        $conf = $this->config->item('_xml');
  

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // Add By Akkarapol, 10/02/2014, เพิ่มการจัดการ import ด้วยไฟล์ .xls ตั้งแต่การ get ค่าออกมาจากไฟล์ ในแต่ละ cell การแปลงค่าที่ได้รับมาให้ตรงกับการใช้งานในแต่ละ field จนถึงการ set ค่าเข้าตัวแปร เพื่อเตรียมข้อมูลนำไป import ต่อไป

        $excel = array('xls', 'xlsx'); // Add array for file Ms-Excel
        if (in_array($ext, $excel)){ // function import by XLS

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();
           
            $check_column_idx = $objPHPExcel->getActiveSheet()->getCell('A' . 1)->getValue();

            if ($check_column_idx != "ITEMNO") {
                p("Incorrect Template Format, Please recheck about master template from website or contact administrator.");
                redirect('import_Stock_On_Hand', 'refresh');
                exit();
            }

            $start_get_value = FALSE;
            $id = 0;
        
            foreach ($objWorksheet->getRowIterator() as $key_row => $row) {
              
                if ($start_get_value){
                    $set_data = array();

                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                //  p($value); exit;
                    if (empty($value)):
                        break;
                    endif;

                    $set_data['Item No'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(0, $key_row)->getValue()));
                    $set_data['Item Description'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    $set_data['Quantity On Hand'] = "";
                    $set_data['Stocking Unit']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(2, $key_row)->getFormattedValue()));
                    $set_data['20-JWD']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(13, $key_row)->getFormattedValue()));
                    $set_data['88-NEC-Bangpakong']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(6, $key_row)->getFormattedValue()));
                    $set_data['92-REWORK AND ON HOLD']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(7, $key_row)->getFormattedValue()));
                    $set_data['99-OBSOLETE']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(8, $key_row)->getFormattedValue()));
                    $set_data['LOC_94_ON_HAND']    = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(12, $key_row)->getFormattedValue()));
                    $set_data['JWD']    = "";
                    $set_data['Diff']    = "";
                
                    $Excel_data[$set_data['Item No']][] = $set_data;

                }else{
                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getValue()));
                    if ($value != ''):
                        $start_get_value = TRUE;
                    endif;
                }
               
                //p($set_data);
              
            }
           //  p('-------------');

            $BalanceJWD   = $this->R_R_model->getBalanceJWD();
         //p($BalanceJWD);
            foreach ($BalanceJWD as $key => $value) {
             
                //p($value);
                    $value2['Item No'] =  $value['Product_Code'];  //$value->Product_Code; 
                    $value2['Item Description'] =   $value['Product_NameEN']; //$value->Product_NameEN; 
                    $value2['Quantity On Hand'] = ""; 
                    $value2['Stocking Unit'] = $value['UOM']; //$value->UOM;  
                    $value2['20-JWD'] = ""; 
                    $value2['88-NEC-Bangpakong'] = "";
                    $value2['92-REWORK AND ON HOLD'] = "";
                    $value2['99-OBSOLETE'] = "";
                    $value2['LOC_94_ON_HAND'] = "";
                    $value2['JWD'] = $value['Balance_Qty']; //$value->AVAILABLE;   
                    $value2['Diff'] = "";
                 //p($value2);exit;

                    $JWD_data[$value2['Item No']][] = $value2;

            }
             //---------- key------------//
            foreach ($Excel_data as $key => $value) {
                $data_Excel =  $key;
                $key_excel[]=  $data_Excel;
                
            }
            foreach ($JWD_data as $key => $value) {
                $data_JWD =  $key;
                $key_jwd[]=  $data_JWD;
                
            }


            //  p($key_excel);
            //  p('------------');
            //  p($key_jwd);exit;
            //------------- sum ------------//
            
            $check = array_intersect($key_jwd,$key_excel);
            
            $sum   = array();
            foreach ($check as $key => $value) {
               
                   array_push($sum,$JWD_data[$value]);

                    // $sum[$key][0]['Item No']  = $excel_Item = $Excel_data[$value][0]['Item No'];
                    // $sum[$key][0]['Item Description']  = $excel_Des = $Excel_data[$value][0]['Item Description'];
                    // $sum[$key][0]['Quantity On Hand']  = "";
                    // $sum[$key][0]['Stocking Unit']  = $excel_unit = $Excel_data[$value][0]['Stocking Unit'];
                     $sum[$key][0]['20-JWD']  = $excel_20_JWD = $Excel_data[$value][0]['20-JWD'];
                     $sum[$key][0]['88-NEC-Bangpakong']  = $excel_88_NEC = $Excel_data[$value][0]['88-NEC-Bangpakong'];
                     $sum[$key][0]['92-REWORK AND ON HOLD']  = $excel_92_REWORK = $Excel_data[$value][0]['92-REWORK AND ON HOLD'];
                     $sum[$key][0]['99-OBSOLETE']  = $excel_99_OBSOLETE = $Excel_data[$value][0]['99-OBSOLETE'];
                     $sum[$key][0]['LOC_94_ON_HAND']  = $excel_99_OBSOLETE = $Excel_data[$value][0]['LOC_94_ON_HAND'];
                    // $sum[$key][0]['JWD']     = $jwd = $JWD_data[$value][0]['JWD'];
                    // $sum[$key][0]['Diff']    = $excel_20_JWD - $jwd;

                unset($Excel_data[$value]);
                unset($JWD_data[$value]);
               
            }
            // p($sum);
            // exit;

            $this->ExportToExcel($sum,$Excel_data,$JWD_data);
        }

    }


    function ExportToExcel($sum,$Excel_data,$JWD_data){
        $dupicate = $sum ;
        $excel =  $Excel_data;
        $jwd = $JWD_data;
            // p($dupicate);
            // p('------------------');
            // p($excel);
            // p('------------------');
            // p($jwd);
            // exit;

        $data = array();
        $i = 0;

        foreach ($dupicate as $key => $value) {
            
                    $data['Item No']                = $value[$i]['Item No'];
                    $data['Item Description']       = $value[$i]['Item Description'];
                    $data['Quantity On Hand']       = $value[$i]['Quantity On Hand'];
                    $data['Stocking Unit']          = $value[$i]['Stocking Unit'];
                    $data['20-JWD']                 = (float)$value[$i]['20-JWD'];
                    $data['88-NEC-Bangpakong']      = (float)$value[$i]['88-NEC-Bangpakong'];
                    $data['92-REWORK AND ON HOLD']  = (float)$value[$i]['92-REWORK AND ON HOLD'];
                    $data['99-OBSOLETE']            = (float)$value[$i]['99-OBSOLETE'];
                    $data['LOC_94_ON_HAND']         = (float)$value[$i]['LOC_94_ON_HAND'];
                    $data['JWD']                    = (float)$value[$i]['JWD'];
                    $data['Diff']                   = (float)$value[$i]['JWD'] - (float)$value[$i]['20-JWD'];
                    if (!empty( $data['Item No'])){
                        $tmp[] = $data;
                    }

        }

        foreach ($excel as $key => $value) {

                    $data['Item No']                = $value[$i]['Item No'];
                    $data['Item Description']       = $value[$i]['Item Description'];
                    $data['Quantity On Hand']       = $value[$i]['Quantity On Hand'];
                    $data['Stocking Unit']          = $value[$i]['Stocking Unit'];
                    $data['20-JWD']                 = (float)$value[$i]['20-JWD'];
                    $data['88-NEC-Bangpakong']      = (float)$value[$i]['88-NEC-Bangpakong'];
                    $data['92-REWORK AND ON HOLD']  = (float)$value[$i]['92-REWORK AND ON HOLD'];
                    $data['99-OBSOLETE']            = (float)$value[$i]['99-OBSOLETE'];
                    $data['LOC_94_ON_HAND']         = (float)$value[$i]['LOC_94_ON_HAND'];
                    $data['JWD']                    = (float)$value[$i]['JWD'];
                    $data['Diff']                   = (float)$value[$i]['JWD'] - (float)$value[$i]['20-JWD'];
                    if (!empty( $data['Item No'])){
                        $tmp[] = $data;
                    }
        }


        foreach ($jwd as $key => $value) {

                    $data['Item No']                = $value[$i]['Item No'];
                    $data['Item Description']       = $value[$i]['Item Description'];
                    $data['Quantity On Hand']       = $value[$i]['Quantity On Hand'];
                    $data['Stocking Unit']          = $value[$i]['Stocking Unit'];
                    $data['20-JWD']                 = (float)$value[$i]['20-JWD'];
                    $data['88-NEC-Bangpakong']      = (float)$value[$i]['88-NEC-Bangpakong'];
                    $data['92-REWORK AND ON HOLD']  = (float)$value[$i]['92-REWORK AND ON HOLD'];
                    $data['99-OBSOLETE']            = (float)$value[$i]['99-OBSOLETE'];
                    $data['LOC_94_ON_HAND']         = (float)$value[$i]['LOC_94_ON_HAND'];
                    $data['JWD']                    = (float)$value[$i]['JWD'];
                    $data['Diff']                   = (float)$value[$i]['JWD'] - (float)$value[$i]['20-JWD'];
                    if (!empty( $data['Item No'])){
                        $tmp[] = $data;
                    }

        }
        $i++;
        $view['file_name'] = 'R_To_R_Stock_On_Hand_'.date('Ymd-His');
        $view['header'] = array(
                                  _lang('Item No')
                                , _lang('Item Description')
                                , _lang('Quantity On Hand')
                                , _lang('Stocking Unit')
                                , _lang('20-JWD')
                                , _lang('88-NEC-Bangpakong')
                                , _lang('92-REWORK AND ON HOLD')
                                , _lang('99-OBSOLETE')
                                , _lang('LOC_94_ON_HAND')
                                , _lang('JWD')
                                , _lang('Diff')
                                
                            );
        $view['body'] = $tmp;
        // p($tmp);
        // exit;
        $this->load->view('report/export_R_To_R_Onhand_Excel',$view);
          
    }

    function load_template() {
        $view = array('file_name' => 'Standard_import_R_To_R_Stock_Onhand.xlsx', 'path_file' => './uploads/standard_import_R_To_R_Onhand.xlsx');
        $this->load->view("load_file.php", $view);
    }


}
