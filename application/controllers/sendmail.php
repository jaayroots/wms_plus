<?php

//
//if (!defined('BASEPATH'))
//    exit('No direct script access allowed');

/**
 * Description of Admin_controller
 * -------------------------------------
 * Put this class on project at 22/04/2013 
 * @author Pakkaphon P.(PHP PG) 
 * Create by NetBean IDE 7.3
 * SWA WMS PLUS Project.
 * Use with dispatch module function
 * Use Codeinigter Framework with combination of css and js.
 * --------------------------------------
 */
class sendmail extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            $ip_allow_list = array("127.0.0.1", "172.16.14.31", "172.16.38.17", '0.0.0.0');
            $ip = $this->input->ip_address();
            if (!in_array($ip, $ip_allow_list)) {
                show_404();
            }
        }

        $this->load->model("email_model", "email_model");
        $this->load->model("location_model", "location");
    }

    public function index() {
        $this->Send_email();
    }

    public function Send_email() {
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
        // Connect mysql for get database all 
        $data_connect_mysql = array(
            "Hostname" => "172.16.14.31",
            "Username" => "root",
            "Password" => "bj4free"
        );

        // Connect mssql for get database all 
        $data_connect_mssql = array(// Connect MS SQL Server บนตัว Test
            "server_name" => "172.16.14.33",
            "username" => "sa",
            "password" => "azsxdcfv",
            "database" => "WMSP_TALESUN" // ยกชื่อดาต้าเบสที่มีอยู่ในระบบ
        );

        $db_mysql = $this->email_model->get_db_mysql($data_connect_mysql); // Get Name and DB Name From MySQL     
        $dbname = $this->location->get_db_mssql_all($data_connect_mssql); // Get Name All Database MS SQL     

        foreach ($dbname as $key => $value) {
            $this->email->clear(TRUE);
            $this->email->set_newline("\r\n");
            $this->email->from('jcs@gmail.co.th');
            $this->email->subject("System auto send file " . date("d/m/Y"));
            if (array_key_exists($value->name, $db_mysql)) {
                $db_array = array(// Connect MS SQL Server บนตัว Test
                    "Server_Name" => "172.16.14.33",
                    "User_Name" => "sa",
                    "Password" => "azsxdcfv",
                    "Database_Name" => $value->name
                );

                $data = $this->email_model->get_email_all($db_array); // Get email all from database all mssql

                $to = "";

                if ($data) { // Get mail sent to.
                    foreach ($data as $key1 => $value1) {
                        $to .= "," . $value1->Email_Name;
                    }
                    $to = substr($to, 1);
                }
                $this->email->to($to);
                $company = $this->email_model->get_company($db_array);

                // Function Get File
                $Receive_path = $this->Receive_csv_report($db_array, $company[0]->Company_NameEN);
                $Dispatch_path = $this->Dispatch_csv_report($db_array, $company[0]->Company_NameEN);
                $Inventory_today_path = $this->Inventory_today_csv_report($db_array, $company[0]->Company_NameEN);

                $message = "Dear Sir/Madam,\r\n";
                $message .= "\r\n";
                $message .= "This is an e-mail notification to inform you that your report \r\n";
                $message .= "for " . $company[0]->Company_NameEN . " already complete, Please see the detail below \r\n";
                $message .= "\r\n";

                if (!empty($Receive_path)) {
                    $this->email->attach($Receive_path, $company[0]->Company_NameEN);
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
                    echo 'success ';
                } else {
                    // Case IF Send Mail ERROR!!                        
                }
            }
        }
    }

    function Receive_csv_report($db_array, $company) {
        $date = date("Y-m-d");
        $result = $this->email_model->get_data_receive_today($date, $db_array);
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
                fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
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

    function Dispatch_csv_report($db_array, $company) {
        $date = date("Y-m-d");
        $result = $this->email_model->get_data_dispatch_today($date, $db_array);
        if ($result) {
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
                fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
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

    function Inventory_today_csv_report($db_array, $company) {
        $new = array(
            "renter_id" => "",
            "product_id" => "",
            "status_id" => ""
        );
        $result = $this->email_model->get_data_inventory_today($new, $db_array);

        if ($result) {
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
                fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
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

/* End of file */
    /* Location: ./application/controllers/authen.php */

    