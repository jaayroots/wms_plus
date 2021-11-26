<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* Location: ./application/controllers/import_pre_dispatch.php */
/* Support file .csv only. Coding program convert excel is csv later. */

class import_pre_dispatch extends CI_Controller {

    public $mnu_NavigationUri;

    function __construct() {
        parent::__construct();
        $this->load->library('excel');
        $this->load->library('validate_data');

        $this->load->model("workflow_model", "flow");
        $this->load->model("stock_model", "stock");
        $this->load->model("product_model", "prod");
        $this->load->model("company_model", "com");
        $this->load->model('im_ex_model', 'imex');
        $this->load->model('authen_model', 'atn');
        $this->load->model("menu_model", "mnu");
        $this->load->model("inbound_model", "inbound");
        $this->load->model("system_management_model", "sys");

        $this->mnu_NavigationUri = "import_pre_dispatch";

        $this->settings = native_session::retrieve();
    }

    public function index() {
        $this->importForm();
    }

    function importForm() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $this->menu_auth->get_action_parmission($this->session->userdata('user_id'), $this->mnu_NavigationUri);
        $this->load->helper('form');
        $str_form = form_fieldset('Import Pre-Dispatch');
        $str_form.=$this->parser->parse('form/import_pre_dispatch', array(), TRUE);
        $this->parser->parse('form_template', array(
            'user_login' => $this->session->userdata('username')
            , 'copyright' => COPYRIGHT
            , 'menu_title' => 'Import Pre-Dispatch'
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
        $config['file_name'] = 'Dis-' . date('Ymd') . '-' . generateRandomString(9);
        $this->load->library('upload', $config);
        $field_name = "xfile";
        if ($this->upload->do_upload($field_name)) {
            $up = $this->upload->data();
            $this->import($up["full_path"]);
        } else {
            p("Upload Unsuccessful !!");
            redirect('import_pre_dispatch', 'refresh');
        }
    }

    function import($path) {
        #Load config
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $conf_import_excel = empty($conf['import_excel']['pre_dispatch']) ? false : @$conf['import_excel']['pre_dispatch'];
        $conf_import_map_doc_type = @array_flip($conf_import_excel['doc_types']);
        $conf_first_row = $conf_import_excel['first_row'];

        //select all destination
        $comp_result = $this->com->getDestinationAll()->result_array();
        $arr_destination = array();
        $destination_default = NULL;
        foreach ($comp_result as $key => $value):
            $arr_destination[$value['Company_Code']] = $value['Company_Id'];
        endforeach;
        //end select
        //select all dispatch type
        $dpType_result = $this->sys->getDispatchType();
        $arr_dpType = array();
        $dpType_default = NULL;
        foreach ($dpType_result as $key => $value):
            $arr_dpType[$value->Dom_Code] = $value->Dom_ID;
        endforeach;
        //end select

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $excel = array('xls', 'xlsx'); // Add array for file Ms-Excel
        
        if (in_array($ext, $excel)): // function import by XLS

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $getCell = $conf_first_row - 1;
            $check_column_idx = $objPHPExcel->getActiveSheet()->getCell('B' . $getCell)->getValue();

            if ($check_column_idx != "Delivery Date") {
                p("Incorrect Template Format, Please recheck about master template from website or contact administrator.");
                redirect('import_pre_dispatch', 'refresh');
                exit();
            }

            $start_get_value = FALSE;
            foreach ($objWorksheet->getRowIterator() as $key_row => $row) :

                # Check a first row of Data import
                if ($key_row >= $conf_import_excel['first_row']):

                    $set_data = array();
                    $set_data['IMP_Check'] = 'N';

                    # Change Create_Date from EXCEL to datetime now
                    $set_data['Create_Date'] = date("Y-m-d");

                    $set_data['Estimate_Action_Date'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(change_letter_number($conf_import_excel['columns']['Estimate_Action_Date']), $key_row)->getFormattedValue()));

                    if (empty($set_data['Estimate_Action_Date'])):
                        break;
                    endif;

                    if (is_valid_date($set_data['Estimate_Action_Date'], 'dd/mm/yyyy')) :
                        $spl = explode('/', $set_data['Estimate_Action_Date']);
                        $set_data['Estimate_Action_Date'] = $spl[2] . '-' . $spl[1] . '-' . $spl[0];
                    elseif (is_valid_date($set_data['Estimate_Action_Date'], 'mm-dd-yy')):
                        $spl = explode('-', $set_data['Estimate_Action_Date']);
                        $spl[2] = ($spl[2] > date("y") + 20 ? ("25" . $spl[2]) - 543 : ("20" . $spl[2]));
                        $set_data['Estimate_Action_Date'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                    elseif (is_valid_date($set_data['Estimate_Action_Date'], 'mm/dd/yyyy')):
                        $spl = explode('/', $set_data['Estimate_Action_Date']);
                        $set_data['Estimate_Action_Date'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                    else:
                        $set_data['IMP_Check'] = 'DF_EST';
                        $set_data['Estimate_Action_Date'] = NULL;
                    endif;
          
                    # Each $conf_import_excel['columns'] for Define variable $set_data by data in EXCEL with Key and Value of $conf_import_excel['columns']
                    $ignore_key = array('Estimate_Action_Date');

                    foreach ($conf_import_excel['columns'] as $key_columns => $columns):
                        // p($columns);
                        // exit;
                        if (!in_array($key_columns, $ignore_key)):
                            $set_data[$key_columns] = trim(iconv("UTF-8", "TIS-620//IGNORE", (($columns == "-") ? NULL : $objWorksheet->getCellByColumnAndRow(change_letter_number($columns), $key_row)->getFormattedValue())));
                        endif;
                    endforeach;

                    # Add Product_Mfd and Product_Exp in import
                    if (!empty($set_data['Product_Mfd'])):

                        if (is_valid_date($set_data['Product_Mfd'], 'dd/mm/yyyy')) :
                            $spl = explode('/', $set_data['Product_Mfd']);
                            $set_data['Product_Mfd'] = $spl[2] . '-' . $spl[1] . '-' . $spl[0];
                        elseif (is_valid_date($set_data['Product_Mfd'], 'mm-dd-yy')):
                            $spl = explode('-', $set_data['Product_Mfd']);
                            $spl[2] = ($spl[2] > date("y") + 20 ? ("25" . $spl[2]) - 543 : ("20" . $spl[2]));
                            $set_data['Product_Mfd'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                        elseif (is_valid_date($set_data['Product_Mfd'], 'mm/dd/yyyy')):
                            $spl = explode('/', $set_data['Product_Mfd']);
                            $set_data['Product_Mfd'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                        else:
                            $set_data['IMP_Check'] = 'DF_MFD';
                            $set_data['Product_Mfd'] = NULL;
                        endif;
                    else:
                        $set_data['Product_Mfd'] = NULL;
                    endif;

                    if (!empty($set_data['Product_Exp'])):
                        if (is_valid_date($set_data['Product_Exp'], 'dd/mm/yyyy')) :
                            $spl = explode('/', $set_data['Product_Exp']);
                            $set_data['Product_Exp'] = $spl[2] . '-' . $spl[1] . '-' . $spl[0];
                        elseif (is_valid_date($set_data['Product_Exp'], 'mm-dd-yy')):
                            $spl = explode('-', $set_data['Product_Exp']);
                            $spl[2] = ($spl[2] > date("y") + 20 ? ("25" . $spl[2]) - 543 : ("20" . $spl[2]));
                            $set_data['Product_Exp'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                        elseif (is_valid_date($set_data['Product_Exp'], 'mm/dd/yyyy')):
                            $spl = explode('/', $set_data['Product_Exp']);
                            $set_data['Product_Exp'] = $spl[2] . '-' . $spl[0] . '-' . $spl[1];
                        else:
                            $set_data['IMP_Check'] = 'DF_EXP';
                            $set_data['Product_Exp'] = NULL;
                        endif;
                    else:
                        $set_data['Product_Exp'] = NULL;
                    endif;

                    /**
                     * Check Config Price Per Unit Before set data
                     */
                    if ($conf_price_per_unit):
                        if ($set_data['Price_Per_Unit'] != ""):
                            $set_data['Price_Per_Unit'] = str_replace(",", "", $set_data['Price_Per_Unit']);
                        else:
                            $set_data['Price_Per_Unit'] = 0;
                        endif;
                    else:
                        $set_data['Price_Per_Unit'] = NULL;
                        $set_data['Unit_Price_Id'] = NULL;
                    endif;


                    // $destination = $set_data['Destination_Code'] = $set_data['Destination_Id'];
                    // if (empty($destination)):
                    //     $set_data['Destination_Id'] = $destination_default;
                    // else:
                    //     if (empty($arr_destination[$destination])):
                    //         $set_data['IMP_Check'] = "C";  // C is Consignee == Destination
                    //         $set_data['Destination_Id'] = 0;
                    //         $set_data['Destination_Notfound'] = $destination;
                    //     else:
                    //         $set_data['Destination_Id'] = $arr_destination[$destination];
                    //     endif;
                    // endif;

                    // Condition Tag Destination_Code is empty
                    if(empty($set_data['Destination_Code'])){
                        $set_data['Destination_Code'] = 'ECOLAB';
                    }
                    //End Condition Tag Destination_Code is empty
                    
                    /**
                     * Check Doc_Type between Excel and System
                     */
                    $doc_type = $set_data['Doc_Type'];
                    if (empty($doc_type)):
                        $set_data['Doc_Type'] = $dpType_default;
                    else:
                        $doc_type = @$conf_import_map_doc_type[$set_data['Doc_Type']];
                        if (empty($arr_dpType[$doc_type])):
                            $set_data['IMP_Check'] = "DOC_TYPE";
                            $set_data['Doc_Type_Notfound'] = $set_data['Doc_Type'];
                            $set_data['Doc_Type'] = 0;
                        else:
                            $set_data['Doc_Type'] = $doc_type;
                        endif;
                    endif;


                    if (!empty($aData[$set_data['Doc_Refer_Ext']])):
                        /**
                         * check Destination in Document must be equal
                         */
                        // if ($aData[$set_data['Doc_Refer_Ext']][0]['IMP_Check'] == "C!EQ"):
                        //     $set_data['IMP_Check'] = "C!EQ"; // C is Consignee == Destination | C!EQ == Consignee is not equal
                        // elseif ($aData[$set_data['Doc_Refer_Ext']][0]['Destination_Id'] != $set_data['Destination_Id']):
                        //     $set_data['IMP_Check'] = "C!EQ"; // C is Consignee == Destination | C!EQ == Consignee is not equal
                        //     foreach ($aData[$set_data['Doc_Refer_Ext']] as $key_this_doc => $this_doc):
                        //         $aData[$set_data['Doc_Refer_Ext']][$key_this_doc]['IMP_Check'] = "C!EQ"; // C is Consignee == Destination | C!EQ == Consignee is not equal
                        //     endforeach;
                        // endif;

                        /**
                         * check Doc_Type in Document must be equal
                         */
                        if ($aData[$set_data['Doc_Refer_Ext']][0]['IMP_Check'] == "DOC_TYPE!EQ"):
                            $set_data['IMP_Check'] = "DOC_TYPE!EQ";
                        elseif ($aData[$set_data['Doc_Refer_Ext']][0]['Doc_Type'] != $set_data['Doc_Type']):
                            $set_data['IMP_Check'] = "DOC_TYPE!EQ";
                            foreach ($aData[$set_data['Doc_Refer_Ext']] as $key_this_doc => $this_doc):
                                $aData[$set_data['Doc_Refer_Ext']][$key_this_doc]['IMP_Check'] = "DOC_TYPE!EQ";
                            endforeach;
                        endif;
                    endif;

                    $check_result = $this->imex->checkDocNo(trim($set_data['Doc_Refer_Ext']), 'OUTBOUND');
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

                    $set_data['Doc_Refer_Int'] = trim(iconv("UTF-8", "TIS-620//IGNORE", $objWorksheet->getCellByColumnAndRow(change_letter_number($conf_import_excel['columns']['Doc_Refer_Int']), $key_row)->getFormattedValue()));


                   
                    $aData[$set_data['Doc_Refer_Ext']][] = $set_data;

                endif;
            endforeach;

        endif;

        if (!empty($aData) && $aData != "") {
            $datestring = "%Y-%m-%d %H:%i:%s";
            $time = time();
            $this->load->helper("date");
            $human = mdate($datestring, $time);

            $impID = array();
            foreach ($aData as $OrderData) {
                if (!empty($OrderData)) {
                    # insert IMP_H_Pre_Receive
                    $impHData = array("Created_By" => $this->session->userdata('user_id'), "Created_Date" => $human);
                    $IMP_ID = $this->imex->saveIMP_H_Pre_Dispatch($impHData);

                    array_push($impID, $IMP_ID);

                    if ($IMP_ID > 0):
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
//			                $impDData['Doc_Refer_Int'] = $OrderDetail['Doc_Refer_Int'];
                            $impDData['Reserv_Qty'] = $OrderDetail['Reserv_Qty'];
                            $impDData['Product_Lot'] = $OrderDetail['Product_Lot'];
                            $impDData['Product_Serial'] = $OrderDetail['Product_Serial'];
                            $impDData['Shipper_Id'] = $OrderDetail['Shipper_Id'];
                            $impDData['Product_Mfd'] = $OrderDetail['Product_Mfd'];
                            $impDData['Product_Exp'] = $OrderDetail['Product_Exp'];

                            // Add Free Text Destination_Code,Destination_Name
                            $impDData['Destination_Code'] = $OrderDetail['Destination_Code'];
                            $impDData['Destination_Name'] = $OrderDetail['Destination_Name'];

                            if ($conf_price_per_unit):
                                $impDData['Price_Per_Unit'] = $OrderDetail['Price_Per_Unit'];
                                $impDData['Unit_Price_Id'] = $OrderDetail['Unit_Price_Id'];
                            else:
                                $impDData['Price_Per_Unit'] = NULL;
                                $impDData['Unit_Price_Id'] = NULL;
                            endif;

                            $impDData['IMP_Check'] = $OrderDetail['IMP_Check'];
                            
                            $this->imex->saveIMP_D_Pre_Dispatch($impDData);

                            if ($OrderDetail['IMP_Check'] == "D") {
                                log_message('error', "Doc_Refer_Ext [" . $OrderDetail['Doc_Refer_Ext'] . "] Already exists.");
                                $this->imex->logIMP_D_Pre_Dispatch("Doc_Refer_Ext [" . $OrderDetail['Doc_Refer_Ext'] . "] Already exists.", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "P") {
                                log_message('error', 'Not found ' . _lang('product_code') . ' ' . $OrderDetail['Product_Code']);
                                $this->imex->logIMP_D_Pre_Dispatch("Not found " . _lang('product_code') . " [" . $OrderDetail['Product_Code'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "N") {
                                # insert STK_T_Order
                                if ($chkOrder == FALSE) {
                                    $orderID = $this->insertOrder($OrderDetail['Doc_Refer_Ext'], $OrderDetail['Estimate_Action_Date'], $OrderDetail['Source_Id'], $OrderDetail['Destination_Id'], $OrderDetail['Doc_Type'] , $OrderDetail['Doc_Refer_Int'],$OrderDetail['Destination_Code'],$OrderDetail['Destination_Name']);
                                    if ($orderID != "") {
                                        $chkOrder = TRUE;
                                    } else {
                                        log_message('error', 'Insert Order unsuccessful');
                                        $this->imex->logIMP_D_Pre_Dispatch("Insert Order unsuccessful.", $IMP_ID, $IMP_DSeq);
                                    }
                                }
                                # insert STK_T_Order_Detail
                                if ($orderID != "") {
                                    $item_id = $this->insertOrderDetail($orderID, $OrderDetail['Product_Code'], $OrderDetail['Reserv_Qty'], $OrderDetail['Product_Lot'], $OrderDetail['Product_Serial'], $OrderDetail['Price_Per_Unit'], $OrderDetail['Unit_Price_Id'], $OrderDetail['Shipper_Id'], $OrderDetail['Product_Mfd'], $OrderDetail['Product_Exp']);
                                    if ($item_id == "") {
                                        log_message('error', $OrderDetail['Product_Code'] . ' Insert Order Detail unsuccessful');
                                        $this->imex->logIMP_D_Pre_Dispatch("Insert Order Detail unsuccessful.", $IMP_ID, $IMP_DSeq);
                                    }
                                }
                            } else if ($OrderDetail['IMP_Check'] == "F") {

                                log_message('error', $OrderDetail['Reserv_Qty'] . ' Reserv_Qty incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch("Reserv_Qty incorrect. [" . $OrderDetail['Reserv_Qty'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "PF") { //check price per unit fail format : add by kik : 20141008
                                log_message('error', $OrderDetail['Price_Per_Unit'] . ' Price Per Unit incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch("Price Per Unit incorrect. [" . $OrderDetail['Price_Per_Unit'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "UF") { //check price per unit fail format : add by kik : 20141008
                                log_message('error', $OrderDetail['Unit_Price_Id'] . ' Unit Price Id incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch("Unit Price Id incorrect. [" . $OrderDetail['Unit_Price_Id'] . "]", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "C") { //consignee not found
                                log_message('error', 'Consignee => [' . $OrderDetail['Destination_Notfound'] . '] Not Found');
                                $this->imex->logIMP_D_Pre_Dispatch('Destination => [' . $OrderDetail['Destination_Notfound'] . '] Not Found', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DF") { //date format fail
                                log_message('error', 'Date => [' . $OrderDetail['Estimate_Action_Date'] . '] or [' . $OrderDetail['Product_Mfd'] . '] or [' . $OrderDetail['Product_Mfd'] . '] Date Formate incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch('Date => [' . $OrderDetail['Estimate_Action_Date'] . '] or [' . $OrderDetail['Product_Mfd'] . '] or [' . $OrderDetail['Product_Mfd'] . '] Date Formate incorrect', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DF_EST") { //date format fail
                                log_message('error', 'ASN Date Formate incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch('ASN Date Formate incorrect', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DF_MFD") { //date format fail
                                log_message('error', 'MFD Date Formate incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch('MFD Date Formate incorrect', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DF_EXP") { //date format fail
                                log_message('error', 'EXP Date Formate incorrect');
                                $this->imex->logIMP_D_Pre_Dispatch('EXP Date Formate incorrect', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DOC_TYPE") { //Doc_Type_Notfound
                                log_message('error', "Document Type => [{$OrderDetail['Doc_Type']}] Not Found");
                                $this->imex->logIMP_D_Pre_Dispatch('Document Type => [' . $OrderDetail['Doc_Type'] . '] Not Found', $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "C!EQ") { //Consignee is not equal
                                log_message('error', "Consignee of Doc.Ext [{$OrderDetail['Doc_Refer_Ext']}] is not equal");
                                $this->imex->logIMP_D_Pre_Dispatch("Consignee of Doc.Ext {$OrderDetail['Doc_Refer_Ext']}] is not equal", $IMP_ID, $IMP_DSeq);
                            } else if ($OrderDetail['IMP_Check'] == "DOC_TYPE!EQ") { //Doc_Type is not equal
                                log_message('error', "Document Type of Doc.Ext [{$OrderDetail['Doc_Refer_Ext']}] is not equal");
                                $this->imex->logIMP_D_Pre_Dispatch("Document Type of Doc.Ext [{$OrderDetail['Doc_Refer_Ext']}] is not equal", $IMP_ID, $IMP_DSeq);
                            }

                            $IMP_DSeq++;

                            $this->transaction_db->transaction_end();

                        endforeach;
                    else:
                        log_message('error', 'IMP_H_Pre_Dispatch can not insert');
                    endif;
                }
            } //end foreach

            if (!isset($_SESSION)) {
                session_start();
            }
            unset($_SESSION["impID"]);
            $_SESSION["impID"] = $impID;
            session_write_close();
            redirect('resultUploadDispatch/resultUploadList?r=' . serialize($impID), 'refresh');
        } else {
            P("No Data !!");
            redirect('import_pre_dispatch', 'refresh');
        }
    }

    function insertOrder($DocReferExt, $estimateDate, $remark, $Destination_Id = NULL, $Doc_Type = NULL , $DocReferInt, $Destination_Code, $Destination_Name) {

        $datestring = "%Y-%m-%d %H:%i:%s";
        $time = time();
        $this->load->helper("date");
        $human = mdate($datestring, $time);

        # generate DDR Number
        $document_no = create_document_no_by_type("DDR");
        # create Workflow
        $process_id = 2;
        $present_state = 0;
        $action_type = "Open Pre-Dispatch";
        $next_state = 1;
        $data['Document_No'] = $document_no;
        list($flow_id, $action_id) = $this->workflow->addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data);

        $queryProcess = $this->flow->getProcessDetailByProcessId($process_id);
        $process_list = $queryProcess->result();
        if ($process_list != "") {
            foreach ($process_list as $processlist) {
                $ProcessType = $processlist->Process_Type;
            }
        }
            $Destination_Code = $Destination_Id;
            //FIND Destination_Id
            $Destination_Id = $this->stock->fine_destination_id($Destination_Id);

        $order = array(
            'Flow_Id' => $flow_id
            , 'Document_No' => $document_no
            , 'Doc_Refer_Ext' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
            , 'Doc_Refer_AWB' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferExt)
	        , 'Doc_Refer_Int' => iconv("UTF-8", "TIS-620//IGNORE", $DocReferInt)
            , 'Process_Type' => $ProcessType
            , 'Doc_Type' => $Doc_Type
            , 'Owner_Id' => $this->session->userdata('owner_id')
            , 'Renter_Id' => $this->session->userdata('renter_id')
            , 'Estimate_Action_Date' => $estimateDate
            , 'Destination_Id' => $Destination_Id
            , 'Source_Type' => 'Supplier'
            , 'Destination_Type' => 'Owner'
            , 'Create_By' => $this->session->userdata('user_id')
            , 'Create_Date' => $human
            , 'Remark' => $remark
            , 'Destination_Code' => $Destination_Code
            , 'Destination_Name' => $Destination_Name

        );

        $orderID = $this->stock->addOrder($order);
        return $orderID;
    }

    function insertOrderDetail($orderID, $prodCode, $reservQty, $prodLot, $prodSerial, $prodPrice, $prodUnitPrice, $shipper_id, $product_mfd, $product_exp) {
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
        $detail['Unit_Id'] = $this->prod->getUnitIdByProdID($prodId);
        $detail['Product_Code'] = $prodCode;
        $detail['Reserv_Qty'] = $reservQty;
        $detail['Product_Lot'] = (empty($prodLot) ? NULL : $prodLot);
        $detail['Product_Serial'] = (empty($prodSerial) ? NULL : $prodSerial);
        $detail['Product_Mfd'] = $product_mfd;
        $detail['Product_Exp'] = $product_exp;
        /**
         * check confir price_per_unit before check data
         */
        if ($conf_price_per_unit):
            $detail['Price_Per_Unit'] = $prodPrice;
            $detail['Unit_Price_Id'] = $prodUnitPrice;
            $detail['All_Price'] = $reservQty * $prodPrice;
        endif;

        $detail['Product_Status'] = "NORMAL";
        $detail['Product_Sub_Status'] = "SS000";
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
    
    function resultUploadList($impID) {
        $action = array(VIEW);
        $action_module = "import_pre_dispatch/resultProcessec";
        $column = array("No.", "Document No.", "All Order", "Success", "Unsuccess");

        if (Count($impID) > 0) {
            $query = $this->imex->getResultIMP($impID);
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
                $data['IMP_DocNo'] = $rows->IMP_DocNo;
                $data['sum_order'] = $rows->sum_order;
                $data['sum_success'] = $rows->sum_success;
                $data['sum_unsuccess'] = $rows->sum_unsuccess;

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
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
        $action_module = "";
        $column = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('from_sub_inv')
            , _lang('to_sub_inv')
            , _lang('qty')
            , _lang('remark')
        );

        $query = $this->imex->getIMPUnsuccess($IMP_ID);
        $unsuccess_list = $query->result();

        $data = array();
        $data_list = array();
        if (is_array($unsuccess_list) && count($unsuccess_list)) {
            $count = 1;
            foreach ($unsuccess_list as $rows) {
                $data['Id'] = $count;
                $data['Product_Code'] = $rows->IMP_PRDBarcode;
                $data['Product_Desc'] = $rows->IMP_PRDDesc;
                $data['From_Sub_Inv'] = $rows->IMP_FromSubInv;
                $data['To_Sub_Inv'] = $rows->IMP_ToSubInv;
                $data['Qty'] = $rows->IMP_PRDQTY;
                $data['Remark'] = $rows->IMP_Remark;

                $count++;
                $data_list[] = (object) $data;
            }
        }

        $datatable = $this->datatable->genCustomiztable($data_list, $column, $action_module, $action);

        $this->parser->parse('list_template', array(
            'menu' => $this->menu_auth->loadMenuAuth()
            , 'menu_title' => 'Result Unsuccess'
            , 'datatable' => $datatable
            , 'button_add' => "<INPUT TYPE='button' class='button dark_blue' VALUE='" . BACK . "'
             ONCLICK=\"history.back()\">"
        ));
    }

    function load_template() {
        $view = array('file_name' => 'standard_import_pre-dispatch.xlsx', 'path_file' => './uploads/standard_import_pre-dispatch.xlsx');
        $this->load->view("load_file.php", $view);
    }

}
