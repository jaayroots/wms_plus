<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// Create by Ton! 20130521
/* Location: ./application/controllers/import_pre_receive.php */
/* Support file .csv only. Coding program convert excel is csv later. */

class import_pre_receive extends CI_Controller {

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

        $this->mnu_NavigationUri = "import_pre_receive";

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
            log_message('error', 'IMPORT Pre-Receive Not Success');
            redirect('import_pre_receive', 'refresh');
        }
    }

    function import($path) {

        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // Add By Akkarapol, 10/02/2014, เพิ่มการจัดการ import ด้วยไฟล์ .xls ตั้งแต่การ get ค่าออกมาจากไฟล์ ในแต่ละ cell การแปลงค่าที่ได้รับมาให้ตรงกับการใช้งานในแต่ละ field จนถึงการ set ค่าเข้าตัวแปร เพื่อเตรียมข้อมูลนำไป import ต่อไป

        $excel = array('xls', 'xlsx'); // Add array for file Ms-Excel
        if (in_array($ext, $excel)): // function import by XLS

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $start_get_value = FALSE;
            foreach ($objWorksheet->getRowIterator() as $key_row => $row) :
                if ($start_get_value):
                    $set_data = array();

                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    if (empty($value)):
                        break;
                    endif;

                    $set_data['Create_Date'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getFormattedValue()));
                    $spl = explode('-', $set_data['Create_Date']);

                    if (empty($spl[2])):
                        break;
                    endif;

                    $spl[2] = ('25' . $spl[2]) - 543;
                    $set_data['Create_Date'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];

                    $chk_est_act_date = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(3, $key_row)->getFormattedValue()));
                    if (empty($chk_est_act_date)):
                        $set_data['Estimate_Action_Date'] = date('d').'-'.date('m').'-'.(date('y') + 43); /// -_-"
                        $set_data['Estimate_Action_Date'] = date('d-m-y',strtotime($set_data['Estimate_Action_Date']));
                    else:
                        $set_data['Estimate_Action_Date'] = $chk_est_act_date;
                    endif;
//                  $set_data['Estimate_Action_Date'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(3, $key_row)->getFormattedValue()));
                    
                    $spl = explode('-', $set_data['Estimate_Action_Date']);
                    $spl[2] = ('25' . $spl[2]) - 543;
                    $set_data['Estimate_Action_Date'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];

//                    $set_data['Source_Id'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(4, $key_row)->getFormattedValue()));
//                    $set_data['Product_Code'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(5, $key_row)->getFormattedValue()));
                    $set_data['Product_Code'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(5, $key_row)->getValue()));
                    $set_data['Product_Name'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(6, $key_row)->getFormattedValue()));
                    $set_data['Doc_Refer_Ext'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(7, $key_row)->getFormattedValue()));
                    $set_data['Reserv_Qty'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(8, $key_row)->getFormattedValue()));
                    $set_data['Destination_Id'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(9, $key_row)->getFormattedValue()));
                    $set_data['Product_Lot'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(10, $key_row)->getFormattedValue()));
                    $set_data['Product_Serial'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(11, $key_row)->getFormattedValue()));

                    /**
                     * Check Config Price Per Unit Before set data
                     */
                    if ($conf_price_per_unit):
                        $set_data['Price_Per_Unit'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(12, $key_row)->getFormattedValue()));
                        if ($set_data['Price_Per_Unit'] != ""):
                            $set_data['Price_Per_Unit'] = str_replace(",", "", $set_data['Price_Per_Unit']);
                        else:
                            $set_data['Price_Per_Unit'] = 0;
                        endif;
                        $set_data['Unit_Price_Id'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(13, $key_row)->getFormattedValue()));
                    else:
                        $set_data['Price_Per_Unit'] = NULL;
                        $set_data['Unit_Price_Id'] = NULL;
                    endif;


                    $set_data['IMP_Check'] = 'N';
                    $check_result = $this->imex->checkDocNo(trim($set_data['Doc_Refer_Ext']), 'INBOUND');
                    if ($check_result == 1) :
                        $set_data['IMP_Check'] = "D"; // Doc_Refer_Ext Already.
                    else :
                        $chkProd = $this->prod->getProductIDByProdCode(trim($set_data['Product_Code']));

                        if ($chkProd == "") :
                            $set_data['IMP_Check'] = "P"; // Don't have product.
                        endif;
                    endif;

                    //ADD BY POR 2014-03-05 check data for insert database
                    $key_set = array('Reserv_Qty'); //Prepare data for save into database.
                    $error = ($this->validate_data->check_numeric($set_data, $key_set)); //check data

                    if (!empty($error['critical'])): //continue if $error not empty.
                        $set_data['IMP_Check'] = "F"; //data fail,type fail
                    endif;
                    //END ADD

//                    $set_data['Source_Id'] = $this->company->getCompanyIdByNameEN($set_data['Source_Id']);
//                    if (empty($set_data['Source_Id'])) {
//                       $set_data['IMP_Check'] = "SP";
//                    }

                    /**
                     * Check Config Price Per Unit Before set data
                     * @author KIK 20141008
                     */
                    if ($conf_price_per_unit):
                        $key_price_per_unit = array('Price_Per_Unit'); //Prepare data for save into database.
                        $error_price_per_unit = ($this->validate_data->check_numeric($set_data, $key_price_per_unit)); //check data

                        if (!empty($error_price_per_unit['critical'])): //continue if $error not empty.
                            $set_data['IMP_Check'] = "PF"; //data price fail,type fail
                        endif;

                        $key_unit_price_id = 'Unit_Price_Id'; //Prepare data for save into database.
                        $error_unit_price_id = ($this->validate_data->chk_unit_price_id($set_data, $key_unit_price_id)); //check data
                        if (!empty($error_unit_price_id['critical'])): //continue if $error not empty.
                            $set_data['IMP_Check'] = "UF"; //unit price fail,type fail
                        endif;

                    endif; //end check data type for price per unit
//                   exit();


                    // START MFD
                    $set_data['Product_Mfd'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(14, $key_row)->getFormattedValue()));
		    if ($set_data['Product_Mfd'] != '') {
	                    $spl = explode('-', $set_data['Product_Mfd']);
	                    $spl[2] = ('25' . $spl[2]) - 543;
	                    $set_data['Product_Mfd'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
		    }  else {
			$set_data['Product_Mfd'] = NULL;
		    }
                    // END MFD


                    // START EXP
                    $set_data['Product_Exp'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(15, $key_row)->getFormattedValue()));
		    if ($set_data['Product_Exp'] != '') {
                    $spl = explode('-', $set_data['Product_Exp']);
                    $spl[2] = ('25' . $spl[2]) - 543;
                    $set_data['Product_Exp'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
		    } else {
			$set_data['Product_Exp'] = NULL;
		    }
                    // END EXP


                    $aData[$set_data['Doc_Refer_Ext']][] = $set_data;

                else:

                    $value = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(1, $key_row)->getValue()));

                    if ($value != ''):
                        $start_get_value = TRUE;
                    endif;

                endif;

            endforeach;


        else: // function import by CSV
            //
            // in case Hensley go to sub function
            // CSV

            $objCSV = fopen($path, "r");

            $i = 1;
            $aData = array();
            while (($objArr = fgetcsv($objCSV, 1000, ",")) != FALSE) {

                if ($i >= 2) {
                    if (trim($objArr[7]) == "") {
                        continue;
                    }
                    $createDate = explode("/", $objArr[1]);
                    if (intval($createDate[1]) > 12) {
                        P("Please check format date!!");
                        redirect('import_pre_receive', 'refresh');
                    } else if (intval($createDate[0]) > 31 || intval($createDate[0]) < 1) {
                        P("Please check format date!!");
                        redirect('import_pre_receive', 'refresh');
                    }
                    $create_Date = $createDate[0] . "/" . $createDate[1] . "/" . strval(intval($createDate[2]) - 543); // Format [dd/mm/yyyy]
                    $Create_Date = convertDate($create_Date, "eng", "iso", "-");

                    $est_date = explode("/", $objArr[3]);
                    if (intval($est_date[1]) > 12) {
                        P("Please check format date!!");
                        redirect('import_pre_receive', 'refresh');
                    } else if (intval($est_date[0]) > 31 || intval($est_date[0]) < 1) {
                        P("Please check format date!!");
                        redirect('import_pre_receive', 'refresh');
                    }
                    $EstRecDate = $est_date[0] . "/" . $est_date[1] . "/" . strval(intval($est_date[2]) - 543); // Format [dd/mm/yyyy]
                    $Estimate_Action_Date = convertDate($EstRecDate, "eng", "iso", "-");

                    # Check Len Product_Code
                    $prodCode = trim($objArr[5]);

                    $IMP_Check = "N"; // Doc_Refer_Ext New.
                    $check_result = $this->imex->checkDocNo(trim($objArr[7]), 'INBOUND');
                    if ($check_result == 1) {
                        $IMP_Check = "D"; // Doc_Refer_Ext Already.
                    } else {
                        $chkProd = $this->prod->getProductIDByProdCode(trim($prodCode));
                        if ($chkProd == "") {
                            $IMP_Check = "P"; // Don't have product.
                        }
                    }

                    //ADD BY POR 2014-03-05 check data for insert database
                    $set_data['Reserv_Qty'] = trim($objArr[8]); //edit parameter 'setdata' to 'set_data'

                    $key_set = array('Reserv_Qty'); //Prepare data for save into database.
                    $error = ($this->validate_data->check_numeric($set_data, $key_set)); //check data

                    if (!empty($error['critical'])): //continue if $error not empty.
                        $IMP_Check = "F"; //data fail,type fail
                    endif;
                    //END ADD

//                  $OrderDetail['Source_Id'] = $this->company->getCompanyIdByNameEN($OrderDetail['Source_Id']);
//                  if (empty($OrderDetail['Source_Id'])) {
//                     $OrderDetail['IMP_Check'] = "SP";
//                  }

                    /**
                     * Check Config Price Per Unit Before set data
                     * @author KIK 20141008
                     */
                    if ($conf_price_per_unit):

                        // start check price per unit
                        if (trim($objArr[12]) != ""):
                            $objArr[12] = str_replace(",", "", trim($objArr[12]));
                        else:
                            $$objArr[12] = NULL;
                        endif;

                        $set_data_price_per_unit['Price_Per_Unit'] = $objArr[12]; //edit parameter 'setdata' to 'set_data'
                        $key_price_per_unit = trim($objArr[12]); //Prepare data for save into database.
                        $error = ($this->validate_data->check_numeric($set_data_price_per_unit, $key_price_per_unit)); //check data

                        if (!empty($error['critical'])): //continue if $error not empty.
                            $set_data['IMP_Check'] = "PF"; //data price fail,type fail
                        endif;
                        // end of check price per unit
                        // start check unit price id
                        $set_data_unit_price_id['Unit_Price_Id'] = trim($objArr[13]); //edit parameter 'setdata' to 'set_data'
                        $key_unit_price_id = 'Unit_Price_Id'; //Prepare data for save into database.
                        $error_unit_price_id = ($this->validate_data->chk_unit_price_id($set_data_unit_price_id, $key_unit_price_id)); //check data
                        if (!empty($error_unit_price_id['critical'])): //continue if $error not empty.
                            $set_data['IMP_Check'] = "UF"; //unit price fail,type fail
                        endif;
                    // end of check unit price id
                    else:
                        $objArr[12] = NULL;
                        $objArr[13] = NULL;
                    endif; //end check data type for price per unit
//                $aData[$objArr[7]]['estimate_date']=$Estimate_Action_Date;
                    $aData[$objArr[7]][] = array('Create_Date' => $Create_Date, 'Estimate_Action_Date' => $Estimate_Action_Date, 'Source_Id' => $objArr[4], 'Product_Code' => $prodCode,
                        'Doc_Refer_Ext' => $objArr[7], 'Reserv_Qty' => $objArr[8], 'Destination_Id' => $objArr[9], 'Product_Lot' => $objArr[10], 'Product_Serial' => $objArr[11], 'IMP_Check' => $IMP_Check);
                    /**
                     * Check Config Price Per Unit Before set data
                     * @author KIK 20141008
                     */
                    if ($conf_price_per_unit):
                        $aData[$objArr[7]]['Price_Per_Unit'] = $objArr[12];
                        $aData[$objArr[7]]['Unit_Price_Id'] = $objArr[13];
                    endif;
                }

                $i++;
            }
            fclose($objCSV);

        endif;

        #=============================================================================================================================================================================
        #=============================================================================================================================================================================

// p($aData); exit;

        if (!empty($aData) && $aData != "") {
//            P($aData);return;
            $datestring = "%Y-%m-%d %h:%i:%s";
            $time = time();
            $this->load->helper("date");
            $human = mdate($datestring, $time);

            $impID = array();

            foreach ($aData as $OrderData) {
                if (!empty($OrderData)) { //EDIT BY POR 2014-03-04 แก้ไขให้ใช้ not empty แทน count($OrderData) > 0 
                    # insert IMP_H_Pre_Receive
                    $impHData = array("Created_By" => $this->session->userdata('user_id'), "Created_Date" => $human);
                    $IMP_ID = $this->imex->saveIMP_H_Pre_Receive($impHData);

                    array_push($impID, $IMP_ID);

                    if ($IMP_ID > 0): //ADD LINE BY POR 2014-03-06 if $IMP_ID is not  zero can do next step
                        $orderID = "";
                        $IMP_DSeq = 1;
                        $chkOrder = FALSE;
                        foreach ($OrderData as $OrderDetail) :
                            $this->transaction_db->transaction_start(); //ADD BY POR 2014-03-04
                            # insert IMP_D_Pre_Receive
                            $impDData = array();
                            $impDData['IMP_ID'] = $IMP_ID;
                            $impDData['IMP_DSeq'] = $IMP_DSeq;
                            $impDData['Report_Sent_Date'] = $OrderDetail['Create_Date'];
                            $impDData['Estimate_Action_Date'] = $OrderDetail['Estimate_Action_Date'];
                            $impDData['Source_Id'] = $OrderDetail['Source_Id'];
                            $impDData['Destination_Id'] = $OrderDetail['Destination_Id'];
                            $impDData['Product_Code'] = $OrderDetail['Product_Code'];
                            $impDData['Product_Name'] = $OrderDetail['Product_Name'];
                            $impDData['Doc_Refer_Ext'] = $OrderDetail['Doc_Refer_Ext'];
                            $impDData['Reserv_Qty'] = $OrderDetail['Reserv_Qty'];
                            $impDData['Product_Lot'] = $OrderDetail['Product_Lot'];
                            $impDData['Product_Serial'] = $OrderDetail['Product_Serial'];
                            /**
                             * Check Config Price Per Unit Before set data
                             * @author KIK 20141008
                             */
                            if ($conf_price_per_unit):
                                $impDData['Price_Per_Unit'] = $OrderDetail['Price_Per_Unit'];
                                $impDData['Unit_Price_Id'] = $OrderDetail['Unit_Price_Id'];
                            else:
                                $impDData['Price_Per_Unit'] = NULL;
                                $impDData['Unit_Price_Id'] = NULL;
                            endif;

                            $impDData['IMP_Check'] = $OrderDetail['IMP_Check'];

                            $this->imex->saveIMP_D_Pre_Receive($impDData);

                            if ($OrderDetail['IMP_Check'] == "D") {
                                log_message('error', $OrderDetail['Doc_Refer_Ext'] . ' Doc_Refer_Ext Already');  //ADD BY POR 2014-03-04 เก็บ log ใน file text ด้วย
                                $this->imex->logIMP_D_Pre_Receive("Doc_Refer_Ext Already. [" . $OrderDetail['Doc_Refer_Ext'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "P") {
                                log_message('error', 'Not found ' . _lang('product_code') . ' ' . $OrderDetail['Product_Code']);
                                $this->imex->logIMP_D_Pre_Receive("Not found " . _lang('product_code') . " [" . $OrderDetail['Product_Code'] . "]", $IMP_ID, $IMP_DSeq);
//                            } else if ($OrderDetail['IMP_Check'] == "SP") {
//                                log_message('error', 'Suplier Not found ' . $OrderDetail['Source_Id']);
//                                $this->imex->logIMP_D_Pre_Receive("Suplier Not found", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "N") {
                                # insert STK_T_Order
                                if ($chkOrder == FALSE) {
                                    $orderID = $this->insertOrder($OrderDetail['Doc_Refer_Ext'], $OrderDetail['Estimate_Action_Date'], $OrderDetail['Source_Id']);
                                    if ($orderID != "") {
                                        $chkOrder = TRUE;
                                    } else {
                                        log_message('error', 'Insert Order unsuccessful'); //ADD BY POR 2014-03-04 เก็บ log ใน file text ด้วย
                                        $this->imex->logIMP_D_Pre_Receive("Insert Order unsuccessful.", $IMP_ID, $IMP_DSeq);
                                    }
                                }
                                # insert STK_T_Order_Detail
                                if ($orderID != "") {
                                    $item_id = $this->insertOrderDetail($orderID, $OrderDetail['Product_Code'], $OrderDetail['Reserv_Qty'], $OrderDetail['Product_Lot'], $OrderDetail['Product_Serial'], $OrderDetail['Price_Per_Unit'], $OrderDetail['Unit_Price_Id'] , $OrderDetail['Product_Mfd'] , $OrderDetail['Product_Exp']);
                                    if ($item_id == "") {
                                        log_message('error', $OrderDetail['Product_Code'] . ' Insert Order Detail unsuccessful'); //ADD BY POR 2014-03-04 เก็บ log ใน file text ด้วย
                                        $this->imex->logIMP_D_Pre_Receive("Insert Order Detail unsuccessful.", $IMP_ID, $IMP_DSeq);
                                    }
                                }
                            } else if ($OrderDetail['IMP_Check'] == "F") { //ADD BY POR 2014-03-06
                                log_message('error', $OrderDetail['Reserv_Qty'] . ' Reserv_Qty incorrect');
                                $this->imex->logIMP_D_Pre_Receive("Reserv_Qty incorrect. [" . $OrderDetail['Reserv_Qty'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "PF") { //check price per unit fail format : add by kik : 20141008
                                log_message('error', $OrderDetail['Price_Per_Unit'] . ' Price Per Unit incorrect');
                                $this->imex->logIMP_D_Pre_Receive("Price Per Unit incorrect. [" . $OrderDetail['Price_Per_Unit'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "UF") { //check price per unit fail format : add by kik : 20141008
                                log_message('error', $OrderDetail['Unit_Price_Id'] . ' Unit Price Id incorrect');
                                $this->imex->logIMP_D_Pre_Receive("Unit Price Id incorrect. [" . $OrderDetail['Unit_Price_Id'] . "]", $IMP_ID, $IMP_DSeq);
                            }


                            $IMP_DSeq++;

                            $this->transaction_db->transaction_end(); //ADD BY POR 2014-03-06 transaction commit when query pass
                        endforeach;
                    else:
                        log_message('error', 'IMP_H_Picking can not insert'); //ADD BY POR 2014-03-04 เก็บ log ใน file text ด้วย
                    endif;
                }
            } //end foreach
//            $this->resultUploadList($impID); //Comment Out by Ton! 20130829
            // ------ START Add by Ton! 20130829 ------
            if (!isset($_SESSION)) {
                session_start();
            }
            unset($_SESSION["impID"]);
            $_SESSION["impID"] = $impID;
            session_write_close();

            redirect('resultUploadReceive/resultUploadList?r=' . serialize($impID), 'refresh');
        } else {
            P("No Data !!");
            redirect('import_pre_receive', 'refresh');
        }
    }

    function resultUploadList($impID) {
        $action = array(VIEW);
        $action_module = "import_pre_receive/resultProcessec";
        $column = array("No.", "Document No.", "All Order", "Success", "Unsuccess");

        if (Count($impID) > 0) {
            $query = $this->imex->getResultIMP_Pre_Receive($impID);
            $result_list = $query->result();
        } else {
            $result_list = array();
        }

        $data = array();
        $data_list = array();
        if (is_array($result_list) && count($result_list)) {
            $count = 1;
            foreach ($result_list as $rows) {
                $data['Id'] = $count;
                $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $data['sum_order'] = $rows->sum_order;
                $data['sum_success'] = $rows->sum_success;
                $data['sum_unsuccess'] = $rows->sum_unsuccess;

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenu()
            'menu' => $this->menu->loadMenuAuth()// Edit by Ton! 20131111
//            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'Result Upload'
            , 'datatable' => $datatable
            , 'button_add' => ""
        ));
    }

    function resultProcessec() {
        $data = $this->input->post();
        $mode = $data['mode'];
        $id = $data['id'];
        if ($mode == "V") {
            $this->resultUnsuccessList($id);
        }
    }

    function resultUnsuccessList($IMP_ID) {
        $action = array();
        $column = array(
            _lang('no')
            , _lang('document_no')
            , _lang('product_code')
            , _lang('qty')
            , _lang('remark')
        );

        $query = $this->imex->getIMP_Pre_Receive_Unsuccess($IMP_ID);
        $unsuccess_list = $query->result();

        $data = array();
        $data_list = array();
        if (is_array($unsuccess_list) && count($unsuccess_list)) {
            $count = 1;
            foreach ($unsuccess_list as $rows) {
                $data['Id'] = $count;
                $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                $data['Product_Code'] = $rows->Product_Code;
                $data['Qty'] = $rows->Reserv_Qty;
                $data['Remark'] = $rows->IMP_Remark;

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, 'import_pre_receive/addNewPorduct', $action);

        $this->parser->parse('list_template', array(
//            'menu' => $this->menu->loadMenuAuth()
            'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'menu_title' => 'Result Unsuccess'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . BACK . "'
             ONCLICK=\"history.back()\">"
        ));
    }

    function insertOrder($DocReferExt, $estimateDate, $suplier_id) {
        $datestring = "%Y-%m-%d %h:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

        # generate GRN Number
//        $document_no = createGRNNo();
        $document_no = create_document_no_by_type("GRN"); // Add by Ton! 20140428
        # create Workflow
        $process_id = 1;
        $present_state = 0;
        $action_type = "Open Pre-Receive";
        $next_state = 1;
        $data['Document_No'] = $document_no;
        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data); //Edit by Ton! 20131021

        $queryProcess = $this->flow->getProcessDetailByProcessId($process_id); // get Process_Type
        $process_list = $queryProcess->result();
        if ($process_list != "") {
            foreach ($process_list as $processlist) {
                $ProcessType = $processlist->Process_Type;
            }
        }

        $order = array(
            'Flow_Id' => $flow_id
            , 'Document_No' => $document_no
            , 'Doc_Refer_Ext' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
            , 'Doc_Refer_AWB' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
            , 'Process_Type' => $ProcessType
            , 'Doc_Type' => 'RCV001'
            , 'Owner_Id' => $suplier_id
            , 'Renter_Id' => $this->session->userdata('renter_id')
            , 'Estimate_Action_Date' => $estimateDate
            , 'Destination_Id' => $this->session->userdata('owner_id')
            , 'Source_Type' => 'Supplier'
            , 'Destination_Type' => 'Owner'
            , 'Create_By' => $this->session->userdata('user_id')
            , 'Create_Date' => $human
            , 'Remark' => NULL
        );

        $orderID = $this->stock->addOrder($order);

        return $orderID;
    }

    function insertOrderDetail($orderID, $prodCode, $reservQty, $prodLot, $prodSerial, $prodPrice, $prodUnitPrice , $mfd , $exp) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $detail = array();
        $detail['Order_Id'] = $orderID;
        $prodId = $this->prod->getProductIDByProdCode($prodCode);
        $detail['Product_Id'] = $prodId;
        $detail['Unit_Id'] = $this->prod->getUnitIdByProdID($prodId); //Add by Ton! 20130710
        $detail['Product_Code'] = $prodCode;
        $detail['Reserv_Qty'] = $reservQty;
        $detail['Product_Lot'] = $prodLot;
        $detail['Product_Serial'] = $prodSerial;
        /**
         * check confir price_per_unit before check data
         */
        if ($conf_price_per_unit):
            $detail['Price_Per_Unit'] = $prodPrice;
            $detail['Unit_Price_Id'] = $prodUnitPrice;
            $detail['All_Price'] = $reservQty * $prodPrice;
        endif;

	$detail['Product_Mfd'] = $mfd;
    $detail['Product_Exp'] = $exp;
    // $date_exp= $exp;

    // if($date_exp =="") {
    //             $result = $this->prod->get_product_rule($prodId);
    //             $rule = $result[0]->PutAway_Rule; 
    //             $min_aging = $result[0]->Min_Aging;
    //             $mfd =  $detail['Product_Mfd'];
    //         if($rule == "FEFO" && $min_aging !="" && $mfd != "" ){
    //             $datemin_aging = round($min_aging);
    //             $datedate = date("Y-m-d", strtotime("+$datemin_aging day", strtotime($mfd))); 
    //             $diff = round(abs(strtotime("$mfd") - strtotime("$datedate"))/60/60/24);
    //             //  p($diff); exit;
    //             $detail['Product_Exp'] = $datedate; 
    //         }else{
    //             $detail['Product_Exp'] = null;
    //         }
    // }else{
    //          $detail['Product_Exp'] = $date_exp;
    // }



        $detail['Product_Status'] = "NORMAL";
        $detail['Product_Sub_Status'] = "SS000"; // Add by Ton! 20130826
        $detail['Remark'] = NULL;

        //ADD BY POR 2014-06-03 กำหนด unit of price โดยค้นหาค่า default ของ unit of price จากตาราง SYS_DOMAIN
        $detail['Unit_Price_Id'] = $this->prod->getUnitPriceCodeDefault();

        $item_id = $this->stock->addOrderDetailByOneRecord($detail);
        if ($item_id != "") {
            return $item_id;
        } else {
            return NULL;
        }
    }

    function load_template() {
        $view = array(
            'file_name' => 'standard_import_pre-receive.xlsx'
            , 'path_file' => './uploads/standard_import_pre-receive.xlsx'
        );
        $this->load->view("load_file.php", $view);
    }

}
