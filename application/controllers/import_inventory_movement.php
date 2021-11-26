<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class import_inventory_movement extends CI_Controller {

    public $mnu_NavigationUri;

    function __construct() {
        parent::__construct();
        $this->load->library('excel');
        $this->load->library('validate_data');
        $this->load->model("import_inventory_movement_model", "im");
        $this->load->model("menu_model", "mnu");
        $this->mnu_NavigationUri = "import_inventory_movement";
        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->importForm();
    }

    function searchdate() {
        $search = $this->input->post();
        $getdate = $search['fdate'];
        $todate = date("d/m/Y");
        if ($getdate === $todate) {
            $data = $this->im->getdata();
            $this->import($data);
        } else {

            $data = $this->im->getdate_data($getdate);
            $this->import($data);
        }

    }

    function importForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $action_parmission = $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $this->load->helper('form');
        $str_form = form_fieldset('import_inventory_movement');
        $str_form .= $this->parser->parse('form/import_inventory_movement', array(), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'import_inventory_movement'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'button_back' => ''
            , 'button_clear' => ''
            , 'button_save' => ''
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function upload() {
        $search = $this->input->post();
        $config['upload_path'] = $this->settings['uploads']['upload_path'];
        $config['allowed_types'] = '*';
        $config['file_name'] = 'Rec-' . date('Ymd') . '-' . generateRandomString(9);
        $this->load->library('upload', $config);
        $field_name = "xfile";
        if ($this->upload->do_upload($field_name)) {
            $up = $this->upload->data();
            $this->import($up["full_path"], $search);
        } else {
            p("Upload Unsuccessful !!");
            log_message('error', 'import_inventory_movement Not Success');
            redirect('import_inventory_movement', 'refresh');
        }
    }

    function load_template() {
        $view = array('file_name' => 'standard_import_inventory_movement.xlsx', 'path_file' => './uploads/standard_import_Inventory_movement.xlsx');
        $this->load->view("load_file.php", $view);
    }

    function import($path, $search) {

        $conf = $this->config->item('_xml');
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $excel = array('xls', 'xlsx');

        if (in_array($ext, $excel)):

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $check_column_idx = $objPHPExcel->getActiveSheet()->getCell('A' . 1)->getValue();
            if ($check_column_idx != "TRANSDATE") {
                p("Incorrect Template Format, Please recheck about master template from website or contact administrator.");
                redirect('import_inventory_movement', 'refresh');
                exit();
            }

            $idx = 0;
            foreach ($objWorksheet->getRowIterator() as $key_row => $row) :
                if ($idx > 0) :
                    $set_data = array();

                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    if (empty($value)):
                        break;
                    endif;
                    $set_data['Doc_Refer_AWB'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    $set_data['Actual_Action_Date'] = str_replace('-', '/', trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(0, $key_row)->getFormattedValue())));
                    $set_data['Product_Code'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(3, $key_row)->getFormattedValue()));
                    $set_data['ECO_IN'] = (float) trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(8, $key_row)->getFormattedValue()));
                    $set_data['ECO_OUT'] = (float) trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(9, $key_row)->getFormattedValue()));
                    $set_data['Diff_IN'] = 0;
                    $set_data['type'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(2, $key_row)->getFormattedValue()));
                    $set_data['JWD_IN'] = 0;
                    $set_data['JWD_OUT'] = 0;
                    $set_data['Diff_OUT'] = 0;
                    $set_data['SumDiff'] = 0;
                    $set_data['from'] = 'excel';
                    $set_data['DAYENDSEQ'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(10, $key_row)->getFormattedValue()));
                    $aData[$set_data['Doc_Refer_AWB'] . $set_data['Product_Code']][] = $set_data;
                    $aData_[$set_data['Doc_Refer_AWB'] . $set_data['Product_Code']][] = $set_data;
                endif;
                $idx++;
            endforeach;
        endif;
        $i = 0;
        $flag_doc = NULL;

        foreach ($aData as $key => $value) {
            $max = count($value);
            if (count($value) > 1) {
                unset($aData[$key]);
                $test['Doc_Refer_AWB'] = $value[0]['Doc_Refer_AWB'];
                $test['Actual_Action_Date'] = $value[0]['Actual_Action_Date'];
                $test['Product_Code'] = $value[0]['Product_Code'];
                $test['type'] = $value[0]['type'];
                $test['ECO_IN'] = 0;
                $test['ECO_OUT'] = 0;
                for ($j = 0; $j <= $max; $j++) {
                    $test['ECO_IN'] += $value[$j]['ECO_IN'];
                    $test['ECO_OUT'] += $value[$j]['ECO_OUT'];
                }
                $test['JWD_IN'] = 0;
                $test['JWD_OUT'] = 0;
                $test['from'] = 'excel';
                $test['DAYENDSEQ'] = $value[0]['DAYENDSEQ'];
                $i++;
                array_push($aData[$value[0]['Doc_Refer_AWB'] . $value[0]['Product_Code']][] = $test);
            }
        }

        $getdate = $search['frm_date'];
        $todate = $search['to_date'];//date("d/m/Y");
        if ($getdate === $todate) {
            $data = $this->im->getdata();
        } else {

            $data = $this->im->getdate_data($getdate,$todate);
        }

        foreach ($data as $key => $value) {
            $data_['Actual_Action_Date'] = $value->TRANSDATE;
            $data_['Doc_Refer_AWB'] = $value->DOCNUM;
            $data_['Product_Code'] = $value->ITEMNO;
            if ($value->Process_Type == "JWD_IN") {
                $data_['Process_Type'] = "INBOUND";
                $data_['JWD_IN'] = $value->QTY;
                $data_['JWD_OUT'] = 0;
            }
            if ($value->Process_Type == "JWD_OUT") {
                $data_['Process_Type'] = "OUTBOUND";
                $data_['JWD_IN'] = 0;
                $data_['JWD_OUT'] = $value->QTY;
            }
            $data_['ECO_IN'] = 0;
            $data_['ECO_OUT'] = 0;
            $data_['Diff_IN'] = 0;
            $data_['Diff_OUT'] = 0;
            $data_['SumDiff'] = 0;
            $data_['DAYENDSEQ'] = 0;
            $aData_2[$data_['Doc_Refer_AWB'] . $data_['Product_Code']][] = $data_;
        }

        foreach ($aData as $key => $value) {
            $data_ex = $key;
            $ex_key[] = $data_ex;
        }

        foreach ($aData_2 as $key => $value) {
            $data_dase = $key;
            $dase_key[] = $data_dase;
        }

        $duplicate = array_intersect($ex_key, $dase_key);
        $duplicatesum = array();
        foreach ($duplicate as $key => $value) {
            $duplicatesum[$key][0]['Doc_Refer_AWB'] = $e_in = $aData[$value][0]['Doc_Refer_AWB'];
            $duplicatesum[$key][0]['Actual_Action_Date'] = $e_out = $aData[$value][0]['Actual_Action_Date'];
            $duplicatesum[$key][0]['Product_Code'] = $b_in = $aData_2[$value][0]['Product_Code'];
            $duplicatesum[$key][0]['type'] = $b_in = $aData[$value][0]['type'];
            $duplicatesum[$key][0]['ECO_IN'] = $e_in = $aData[$value][0]['ECO_IN'];
            $duplicatesum[$key][0]['ECO_OUT'] = $e_out = $aData[$value][0]['ECO_OUT'];
            $duplicatesum[$key][0]['JWD_IN'] = $b_in = $aData_2[$value][0]['JWD_IN'];
            $duplicatesum[$key][0]['JWD_OUT'] = $b_out = $aData_2[$value][0]['JWD_OUT'];
            $duplicatesum[$key][0]['Diff_IN'] = $e_in - $b_in;
            $duplicatesum[$key][0]['Diff_OUT'] = $e_out - $b_out;
            $duplicatesum[$key][0]['SumDiff'] = $duplicatesum[0][0]['Diff_IN'] + $duplicatesum[0][0]['Diff_OUT'];
            $duplicatesum[$key][0]['DAYENDSEQ'] = $e_in = $aData[$value][0]['DAYENDSEQ'];
            unset($aData[$value]);
            unset($aData_2[$value]);
        }
        $this->exportToExcel($duplicatesum, $aData, $aData_2);
    }

    function exportToExcel($duplicatesum, $aData, $aData_2) {
        $sumdata = $duplicatesum;
        $excel_data = $aData;
        $base_data = $aData_2;
        $data = array();
        $i = 0;
        foreach ($sumdata as $key => $rows) {
            $data['FirstOfTRANSDATE'] = $rows[$i]['Actual_Action_Date'];
            $data['FirstOfECL-TYPE'] = $rows[$i]['type'];
            $data['DOC-MAIN'] = $rows[$i]['Doc_Refer_AWB'];
            $data['ITEMNO'] = $rows[$i]['Product_Code'];
            $data['ECO_IN'] = $rows[$i]['ECO_IN'];
            $data['JWD_IN'] = $rows[$i]['JWD_IN'];
            $data['Diff_IN'] = $rows[$i]['JWD_IN'] - $rows[$i]['ECO_IN'];
            $data['ECO_OUT'] = $rows[$i]['ECO_OUT'];
            $data['JWD_OUT'] = $rows[$i]['JWD_OUT'];
            $data['Diff_OUT'] = $data['JWD_OUT'] - $data['ECO_OUT'];
            $data['SumDiff'] = $data['Diff_IN'] + $data['Diff_OUT'];
            $data['DAYENDSEQ'] = $rows[$i]['DAYENDSEQ'];
            $tmp[] = $data;
        }
        foreach ($excel_data as $key => $rows) {
            $data['FirstOfTRANSDATE'] = $rows[$i]['Actual_Action_Date'];
            $data['FirstOfECL-TYPE'] = $rows[$i]['type'];
            $data['DOC-MAIN'] = $rows[$i]['Doc_Refer_AWB'];
            $data['ITEMNO'] = $rows[$i]['Product_Code'];
            $data['ECO_IN'] = $rows[$i]['ECO_IN'];
            $data['JWD_IN'] = $rows[$i]['JWD_IN'];
            $data['Diff_IN'] = $rows[$i]['JWD_IN'] - $rows[$i]['ECO_IN'];
            $data['ECO_OUT'] = $rows[$i]['ECO_OUT'];
            $data['JWD_OUT'] = $rows[$i]['JWD_OUT'];
            $data['Diff_OUT'] = $rows[$i]['JWD_OUT'] - $rows[$i]['ECO_OUT'];
            $data['SumDiff'] = $data['Diff_IN'] + $data['Diff_OUT'];
            $data['DAYENDSEQ'] = $rows[$i]['DAYENDSEQ'];
            $tmp[] = $data;
        }

        foreach ($base_data as $key => $rows) {
            $data['FirstOfTRANSDATE'] = $rows[$i]['Actual_Action_Date'];
            $data['FirstOfECL-TYPE'] = $rows[$i]['Process_Type'];
            $data['DOC-MAIN'] = $rows[$i]['Doc_Refer_AWB'];
            $data['ITEMNO'] = $rows[$i]['Product_Code'];
            $data['ECO_IN'] = $rows[$i]['ECO_IN'];
            $data['JWD_IN'] = $rows[$i]['JWD_IN'];
            $data['Diff_IN'] = $rows[$i]['JWD_IN'] - $rows[$i]['ECO_IN'];
            $data['ECO_OUT'] = $rows[$i]['ECO_OUT'];
            $data['JWD_OUT'] = $rows[$i]['JWD_OUT'];
            $data['Diff_OUT'] = $rows[$i]['JWD_OUT'] - $rows[$i]['ECO_OUT'];
            $data['SumDiff'] = $data['Diff_IN'] + $data['Diff_OUT'];
            $data['DAYENDSEQ'] = $rows[$i]['DAYENDSEQ'];
            $tmp[] = $data;
        }

        $i++;
        $view['file_name'] = 'R _To_R_Inventory_movement_' . date('Ymd-His');
        $view['header'] = array(
            _lang('FirstOfTRANSDATE')
            , _lang('FirstOfECL_TYPE')
            , _lang('DOC-MAIN')
            , _lang('ITEMNO')
            , _lang('ECL_IN')
            , _lang('JWD_IN')
            , _lang('Diff_IN ')
            , _lang('ECL_OUT')
            , _lang('JWD_OUT')
            , _lang('Diff_OUT ')
            , _lang('SumDiff')
            , _lang('DAYENDSEQ')
        );
        $view['body'] = $tmp;
        $this->load->view('report/r_to_r_inventory_movement', $view);
    }

}
