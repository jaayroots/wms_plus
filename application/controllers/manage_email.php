<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class manage_email extends CI_Controller {

    public $mnu_NavigationUri; // NavigationUri @Table ADM_M_MenuBar.

    function __construct() {
        parent::__construct();
        $this->load->model("email_model", "email_model");
        $this->load->model("location_model", "location");
        $this->load->model("report_model", "report");
        $this->load->library("session");
        $this->mnu_NavigationUri = "c_email";
    }

    public function index() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $data = array();
        $str_form = $this->parser->parse('form/manage_email_send_data', $data, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => "Email Address"
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPicking"></i>'
            , 'button_back' => '<INPUT TYPE="button" class="' . $this->config->item('css_button') . '" VALUE="' . BACK . '" ONCLICK="history.back()">'
            , 'button_action' => ""
            , 'user_login' => $this->session->userdata('username')
            , 'button_cancel' => ''
        ));
    }

    public function get_data_table() {
        $data_list = array();
        $data = $this->email_model->get_data_email();
        $i = 1;
        foreach ($data as $key => $value) {
            $btn_edit = '<a data-id="' . $value->Email_Id . '" class="btn_edit" data-toggle="modal" data-target="#modal_edit")\"><img src="' . base_url() . '/css/images/icons/edit.png" alt=""></a>';
            $btn_delete = '<a data-id="' . $value->Email_Id . '" class="btn_delete" data-toggle="modal" data-target="#modal_delete")"><img src="' . base_url() . '/css/images/icons/del.png" alt=""></a>';
            $list["id"] = $i;
            $list["Email_Name"] = $value->Email_Name;
            $list["btn_edit"] = $btn_edit;
            $list["btn_delete"] = $btn_delete;
            $data_list["list"][] = $list;
            $i++;
        }
        echo json_encode($data_list);
    }

    function add_email() {
        $temp_array = $this->input->post("temp_array");
        if (empty($temp_array)) {
            die("Empty email address, Please check your data");
        } else {
            $total = array_unique($temp_array);
            $duplicate_string = "";
            foreach ($total as $key => $value) {
                $duplicate = $this->email_model->validate_duplicate($value);
                if (empty($duplicate)) {
                    $new_array = array(
                        "Email_Name" => $value,
                        "Created_Date" => date("Y-m-d H:i:s"),
                        "Created_By" => $this->session->userdata('user_id'),
                        "Modified_Date" => NULL,
                        "Modified_By" => NULL
                    );
                    $this->email_model->insert_email($new_array);
                } else {
                    $duplicate_string .= "Duplicate: " . $value . "\r\n";
                }
            }
            echo $duplicate_string;
        }
    }

    /**
     * Note
     * 1. Before processing input please validate. IE when input type is number you can verify data type of input first.
     * 2. Before return response please validate output. IE if response is empty may be you can return FALSE or something.
     */
    function get_modify_email() {
        $email_id = $this->input->post("email_id");
        $data = $this->email_model->modify_email($email_id);
        if (!empty($data)) {
            echo $data[0]->Email_Name;
        }
    }

    function modify_email() {
        $email_id = $this->input->post("email_id");
        $modify_email = $this->input->post("modify_email");
        $duplicate = $this->email_model->validate_duplicate($modify_email);

        if (empty($duplicate)) {
            $new_array = array(
                "Email_Name" => $modify_email,
                "Modified_Date" => date("Y-m-d H:i:s"),
                "Modified_By" => $this->session->userdata('user_id')
            );
            $result = $this->email_model->update_email($new_array, $email_id);
            if ($result == TRUE) {
                echo "success";
            } else {
                echo "fail";
            }
        } else {
            echo "Duplicate";
        }
    }

    function delete_email() {
        $id_image_trash = $this->input->post("id_image_trash");
        $result = $this->email_model->delete_email($id_image_trash);
        if ($result == TRUE) {
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function Send_email() {
        $data = $this->email_model->get_data_email();
        if (!empty($data)) {
            ini_set('display_errors', '0');
            error_reporting(0);
            $config = Array(
                'protocol' => 'smtp',
                'smtp_host' => 'localhost',
                'smtp_port' => 25,
                'smtp_user' => '',
                'smtp_pass' => '',
                'mailtype' => 'text',
                'charset' => 'iso-8859-1',
                'wordwrap' => TRUE,
                'crlf' => "\r\n"
            );

            $this->load->library('email', $config); // Use Library Email           
            $this->email->clear(TRUE);
            $this->email->set_newline("\r\n");
            $this->email->from('jcs@gmail.co.th');
            $this->email->subject("System auto send file " . date("d/m/Y"));
            $to = "";

            foreach ($data as $index => $email) {
                $to .= "," . $email->Email_Name;
            }
            $to = substr($to, 1);

            $this->email->to($to);
            $company = $this->email_model->get_company_name();

            // Function Get File
            $Receive_path = $this->Receive_csv_report($company[0]->Company_NameEN);
            $Dispatch_path = $this->Dispatch_csv_report($company[0]->Company_NameEN);
            $Inventory_today_path = $this->Inventory_today_csv_report($company[0]->Company_Id, $company[0]->Company_NameEN);

            $message = "Dear Sir/Madam,\r\n";
            $message .= "\r\n";
            $message .= "This is an e-mail notification to inform you that your report \r\n";
            $message .= "for " . $company[0]->Company_NameEN . " already complete, Please see the detail below \r\n";
            $message .= "\r\n";

            if (!empty($Receive_path)) {
                $this->email->attach($Receive_path);
                $message .= "Receive Report \r\n";
            }

            if (!empty($Dispatch_path)) {
                $this->email->attach($Dispatch_path);
                $message .= "Dispatch Report \r\n";
            }

            if (!empty($Inventory_today_path)) {
                $this->email->attach($Inventory_today_path);
                $message .= "Inventory Report \r\n";
            }
            $message .= "\r\n";

            $message .= "Best regards, \r\n";
            $message .= "WMSPlus Team ";
            $this->email->message($message);

            if ($this->email->send()) {
                echo json_encode('success');
            } else {
                // Case IF Send Mail ERROR!!               
            }
        } else {
            echo json_encode('no_data');
        }
    }

    function Receive_csv_report($company) {
        $result = $this->email_model->get_receive_today();
        if (!empty($result)) {
            $this->load->helper('download');
            $filename = "Receive_" . $company . "_" . date("Ymd") . date("His") . ".csv";
            $my_path = FCPATH . "uploads/";
            $csv = $my_path . $filename;
            if (($file = fopen($csv, "w")) !== false) {
                $csv_fields = array();
                $csv_fields[] = 'No.';
                $csv_fields[] = 'Document No.';
                $csv_fields[] = 'Doc_Refer_Ext.';
                $csv_fields[] = 'Product Code';
                $csv_fields[] = 'Product Name';
                $csv_fields[] = 'Product lot';
                $csv_fields[] = 'Product Serial';
                $csv_fields[] = 'Product Mfd';
                $csv_fields[] = 'Product Exp';
                $csv_fields[] = 'Receive QTY';

                //add BOM to fix UTF-8 in Excel
                fputs($file, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                fputcsv($file, $csv_fields);
                $i = 1;
                foreach ($result as $key => $value) {
                    //p($value);
                    $str = array();
                    $str["No."] = $i;
                    $str["Document_No"] = $value->Document_No != "" ? $value->Document_No : "";
                    $str["Doc_Refer_Ext"] = $value->Doc_Refer_Ext != "" ? tis620_to_utf8($value->Doc_Refer_Ext) : "";
                    $str["Product_Code"] = $value->Product_Code != "" ? $value->Product_Code : "";
                    $str["Product_NameEN"] = $value->Product_Name != "" ? tis620_to_utf8($value->Product_Name) : "";
                    $str["Product_Lot"] = $value->Product_Lot != "" ? tis620_to_utf8($value->Product_Lot) : "";
                    $str["Product_Serial"] = $value->Product_Serial != "" ? tis620_to_utf8($value->Product_Serial) : "";
                    $str["Product_Mfd"] = $value->Product_Mfd != "" ? $value->Product_Mfd : "";
                    $str["Product_Exp"] = $value->Product_Exp != "" ? $value->Product_Exp : "";
                    $str["Confirm"] = $value->Receive_Qty;

                    fputcsv($file, $str);
                    $i++;
                }
                fclose($file);
                return $csv;
            }
        } else {
            return FALSE;
        }
    }

    function Dispatch_csv_report($company) {
        $result = $this->email_model->get_dispatch_today($date, $db_array);
        if (!empty($result)) {
            $this->load->helper('file');
            $this->load->helper('download');
            $filename = "Dispatch_" . $company . "_" . date("Ymd") . date("His") . ".csv";
            $my_path = FCPATH . "uploads/";
            $csv = $my_path . $filename;
            if (($file = fopen($csv, "w")) !== false) {
                $csv_fields = array();
                $csv_fields[] = 'No.';
                $csv_fields[] = 'Document No.';
                $csv_fields[] = 'Product Code';
                $csv_fields[] = 'Product Name';
                $csv_fields[] = 'Product lot';
                $csv_fields[] = 'Product Serial';
                $csv_fields[] = 'Product Mfd';
                $csv_fields[] = 'Product Exp';
                $csv_fields[] = 'Dispatch QTY';

                //add BOM to fix UTF-8 in Excel
                fputs($file, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                fputcsv($file, $csv_fields);
                $i = 1;
                foreach ($result as $key => $value) {
                    $str = array();
                    $str["No."] = $i;
                    $str["Document_No"] = $value->Document_No != "" ? $value->Document_No : "";
                    $str["Product_Code"] = $value->Product_Code != "" ? $value->Product_Code : "";
                    $str["Product_NameEN"] = $value->Product_NameEN != "" ? tis620_to_utf8($value->Product_NameEN) : "";
                    $str["Product_Lot"] = $value->Product_Lot != "" ? tis620_to_utf8($value->Product_Lot) : "";
                    $str["Product_Serial"] = $value->Product_Serial != "" ? tis620_to_utf8($value->Product_Serial) : "";
                    $str["Product_Mfd"] = $value->Product_Mfd != "" ? $value->Product_Mfd : "";
                    $str["Product_Exp"] = $value->Product_Exp != "" ? $value->Product_Exp : "";
                    $str["Confirm"] = $value->Confirm_Qty;
                    fputcsv($file, $str);
                    $i++;
                }
                fclose($file);
                return $csv;
            }
        } else {
            return FALSE;
        }
    }

    function Inventory_today_csv_report($renter_id, $company) {
        $new = array(
            "renter_id" => $renter_id,
            "product_id" => "",
            "status_id" => ""
        );
        $result = $this->report->searchInventoryToday_swa($new);

        if (!empty($result)) {
            $this->load->helper('file');
            $this->load->helper('download');
            $filename = "Balance_" . $company . "_" . date("Ymd") . date("His") . ".csv";
            $my_path = FCPATH . "uploads/";
            $csv = $my_path . $filename;
            if (($file = fopen($csv, "w")) !== false) {

                $csv_fields = array();
                $csv_fields[] = 'No.';
                $csv_fields[] = 'Material No.';
                $csv_fields[] = 'Material Description';
                $csv_fields[] = 'Batch No.';
                $csv_fields[] = 'Invoice No';
                $csv_fields[] = 'Company Name';
                $csv_fields[] = 'Cont No./Size';
                $csv_fields[] = 'Pallet Code';
                $csv_fields[] = 'PENDING';
                $csv_fields[] = 'NORMAL';
                $csv_fields[] = 'DAMAGE';
                $csv_fields[] = 'REPACK';
                $csv_fields[] = 'NC';
                $csv_fields[] = 'SHORTAGE';
                $csv_fields[] = 'GRADE';
                $csv_fields[] = 'BLOCK';
                $csv_fields[] = 'DG';
                $csv_fields[] = 'BORROW';
                $csv_fields[] = 'Total';
                $csv_fields[] = 'Booked';
                $csv_fields[] = 'Dispatch Qty.';
                $csv_fields[] = 'Unit';
                $csv_fields[] = 'QTY/UOM';
                $csv_fields[] = 'Unit/Product';

                //add BOM to fix UTF-8 in Excel
                fputs($file, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                fputcsv($file, $csv_fields);
                $i = 1;
                foreach ($result as $key => $value) {
                    $str = array();
                    $str["No."] = $i;
                    $str["Product_Code"] = $value->Product_Code;
                    $str["Product_NameEN"] = tis620_to_utf8($value->Product_NameEN);
                    $str["Product_Lot"] = $value->Product_Lot != "" ? tis620_to_utf8($value->Product_Lot) : "";
                    $str["Invoice_No"] = $value->Invoice_No != "" ? tis620_to_utf8($value->Invoice_No) : "";
                    $str["Company_NameEN"] = $value->Company_NameEN != "" ? tis620_to_utf8($value->Company_NameEN) : "";
                    $str["Cont"] = tis620_to_utf8($value->Cont);
                    $str["Pallet_Code"] = tis620_to_utf8($value->Pallet_Code);
                    $str["counts_1"] = $value->counts_1;
                    $str["counts_2"] = $value->counts_2;
                    $str["counts_3"] = $value->counts_3;
                    $str["counts_4"] = $value->counts_4;
                    $str["counts_5"] = $value->counts_5;
                    $str["counts_6"] = $value->counts_6;
                    $str["counts_7"] = $value->counts_7;
                    $str["counts_8"] = $value->counts_8;
                    $str["counts_9"] = $value->counts_9;
                    $str["counts_10"] = $value->counts_10;
                    $str["totalbal"] = $value->totalbal;
                    $str["Booked"] = $value->Booked;
                    $str["Dispatch"] = $value->Dispatch;
                    $str["Unit_Value"] = tis620_to_utf8($value->Unit_Value);
                    $str["Uom_Qty"] = $value->Uom_Qty;
                    $str["Uom_Unit_Val"] = tis620_to_utf8($value->Uom_Unit_Val);
                    fputcsv($file, $str);
                    $i++;
                }
                fclose($file);
                return $csv;
            }
        } else {
            return FALSE;
        }
    }

}
