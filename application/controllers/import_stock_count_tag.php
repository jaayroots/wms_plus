<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// Create by Ton! 20130521
/* Location: ./application/controllers/import_pre_receive.php */
/* Support file .csv only. Coding program convert excel is csv later. */

class import_stock_count_tag extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->library('excel');
        $this->load->library('validate_data');

        $this->load->model("company_model", "company");
        $this->load->model("workflow_model", "flow");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "prod");
        $this->load->model('im_ex_model', 'imex');
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("system_management_model", "sys"); //ADD BY POR 2014-02-27

        $this->mnu_NavigationUri = "import_stock_count_tag";

        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->importForm();
    }

    function importForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $this->load->helper('form');
        $str_form = form_fieldset('Import Pre-Receive');
        $str_form.=$this->parser->parse('form/import_pre_receive', array(), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Import Pre-Receive'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
        ));
    }

    function upload() {
        // p('ssssssssssssss'); 
        $config['upload_path'] = $this->settings['uploads']['upload_path'];
        $config['allowed_types'] = '*';
        $config['file_name'] = 'Rec-' . date('Ymd') . '-' . generateRandomString(9);
        $this->load->library('upload', $config);
        $field_name = "xfile";
        // $this->upload->do_upload($field_name) = 1
        if ($this->upload->do_upload($field_name)) {
            $up = $this->upload->data();
            $res = $this->import($up["full_path"]);
         
           if($res != "Upload Unsuccessful !!"){
           $this->gen_pdf($res);
        }else {
            p("Upload Unsuccessful !!");
            log_message('error', 'IMPORT  Not Success');
            redirect('report/inventory_swa', 'refresh');
          
        }
        } else {
            p("Upload Unsuccessful !!");
            log_message('error', 'Not Success');
            redirect('report/inventory_swa', 'refresh');
        }
      
    }
  
    function gen_pdf($res){
        $data = $res;
        ini_set('max_execution_time', 0);
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('pdf/mpdf');
        $new_file_name ='Tag-Count-' . date('Ymd') . '-' . date('His') . '.pdf';
        $view['data'] = $data;
        $view['new_filename'] = $new_file_name;
        $this->load->view("pdf_report/v_stock_count_tag.php",$view);

    }
    function import($path) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);


        $excel = array('xls', 'xlsx'); // Add array for file Ms-Excel
        if (in_array($ext, $excel)): // function import by XLS

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $objWorksheet = $objPHPExcel->getSheetByName('รวม FG'); 
          if (empty($objWorksheet)):
                        $res = "Upload Unsuccessful !!";
                        // break;
                        return $res;
                    endif;
      
            $start_get_value = FALSE;
            foreach ($objWorksheet->getRowIterator() as $key_row => $row) :
           
                if ($start_get_value):
                    $set_data = array();
                     $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(0, $key_row)->getFormattedValue()));
                    // if (empty($value)):
                    // //    p("data empty !!");
                    // //    log_message('error', 'Not Success');
                    // //      redirect('report/inventory_swa', 'refresh');
                    // //      return;
                    // // // break;
                    // //   $set_data = "";
                    // endif;
                    $set_data['location'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    $set_data['sku'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(2, $key_row)->getFormattedValue()));
                    $set_data['b_description'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(3, $key_row)->getFormattedValue()));
                    $set_data['qty'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(5, $key_row)->getFormattedValue()));
                    $set_data['uom'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(6, $key_row)->getFormattedValue()));
                    $set_data['lot_number'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(8, $key_row)->getFormattedValue()));
                    $aData[] = $set_data;
                else:
                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getValue()));
                    if ($value != ''):
                        $start_get_value = TRUE;
                    endif;

                endif;

            endforeach;

        endif;

        #=============================================================================================================================================================================
        #=============================================================================================================================================================================

// p($aData); 
// exit;

return $aData;
    }


}
