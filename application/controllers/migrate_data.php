<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class migrate_data extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("migrate_model", "migrate");
        $this->settings = native_session::retrieve();
    }

    public function index() {
        
    }

    public function migrate_balance() {
        // FIX CONTENT;
        $directory = getcwd() . "/test.csv";
        $path = "";
        $ext = pathinfo($directory, PATHINFO_EXTENSION);

        $objCSV = fopen($directory, "r");

        // HEADER COLUMN

        while (($objArr = fgetcsv($objCSV, 1000, ",")) != FALSE) {

            $error = array();
            $is_error = FALSE;

            $document_no = $objArr[1];
            $receive_date = $objArr[2];
            $product_code = $objArr[3];
            $product_lot = $objArr[5];
            $product_serial = $objArr[6];
            $product_mfd = (empty($objArr[7]) ? NULL : $objArr[7] );
            $product_exp = (empty($objArr[8]) ? NULL : $objArr[8] );
            $product_status = $objArr[9];
            $location = $objArr[10];
            $balance = $objArr[11];
            $pallet_code = $objArr[13];
            $pallet_id = NULL;
            $remark = $objArr[14];
            $unit = $objArr[15];

            // QUERY FOR PRODUCT ID
            $product_id = $this->migrate->getProductIdByCode($product_code);
            if (!$product_id) {
                $error[] = "PRODUCT_CODE_NOT_FOUND";
                $is_error = TRUE;
            }

            $actual_location_id = $this->migrate->getLocationIdByCode($location);
            if (!$actual_location_id) {
                $error[] = "LOCATION_CODE_NOT_FOUND";
                $is_error = TRUE;
            }

            $unit_id = $this->migrate->getUnitIdByUnit($unit);
            if (!$unit_id) {
                $error[] = "UNIT_NOT_FOUND";
                $is_error = TRUE;
            }

            $receive_type = $this->migrate->getNormalReceiveType();
            if (!$receive_type) {
                $error[] = "RECEIVE_TYPE_NOT_FOUND";
                $is_error = TRUE;
            }

            $product_sub_status = $this->migrate->getNotSpecifiedStatus();
            if (!$product_sub_status) {
                $error[] = "SUB_STATUS_NOT_FOUND";
                $is_error = TRUE;
            }

//            $product_sub_status = $this->migrate->getNotSpecifiedStatus();
//            if (!$product_sub_status) {
//                $error[] = "SUB_STATUS_NOT_FOUND";
//                $is_error = TRUE;
//            }

            if ($pallet_code) {
                $pallet_id = $this->migrate->genPalletIdFromPalletCode($pallet_code);
                if (!$pallet_id) {
                    $error[] = "PALLET_NOT_FOUND_OR_INUSED";
                    //$is_error = TRUE;
                    $pallet_id = NULL;
                    // SUB CONDITION
                }
            }

            // Validate Receive Date Format
            // Accept Format dd/mm/yyyy in century only
            if ($receive_date) {
                $t = explode("/", $receive_date);
                $receive_date = $t[2] . "-" . $t[1] . "-" . $t[0] . " 00:00:00.000";
            } else {
                $receive_date = date("Y-m-d H:i:s.000");
            }
            
            if ($is_error == FALSE) {

                $data = array();
                $data['Document_No'] = 'MIGRATE';
                $data['Doc_Refer_Int'] = 'MIGRATE';
                $data['Doc_Refer_Ext'] = $document_no;
                $data['Doc_Refer_Inv'] = 'MIGRATE';
                $data['Doc_Refer_CE'] = 'MIGRATE';
                $data['Doc_Refer_BL'] = 'MIGRATE';
                $data['Doc_Refer_AWB'] = 'MIGRATE';
                $data['Product_Id'] = $product_id;
                $data['Product_Code'] = $product_code;
                $data['Product_Status'] = $product_status;
                $data['Product_Sub_Status'] = $product_sub_status;
                $data['Suggest_Location_Id'] = NULL;
                $data['Actual_Location_Id'] = $actual_location_id;
                $data['Old_Location_Id'] = NULL;
                $data['Pallet_Id'] = $pallet_id;
                $data['Receive_Type'] = $receive_type;
                $data['Receive_Date'] = $receive_date;
                $data['Putaway_Date'] = date('Y-m-d H:i:s.000');
                $data['Putaway_By'] = 1;
                $data['Product_License'] = NULL;
                $data['Product_Lot'] = $product_lot;
                $data['Product_Serial'] = $product_serial;
                $data['Product_Mfd'] = $product_mfd;
                $data['Product_Exp'] = $product_exp;
                $data['Receive_Qty'] = $balance;
                $data['PD_Reserv_Qty'] = 0;
                $data['PK_Reserv_Qty'] = 0;
                $data['Dispatch_Qty'] = 0;
                $data['Balance_Qty'] = $balance;
                $data['Adjust_Qty'] = 0;
                $data['Unit_Id'] = $unit_id;
                $data['Owner_Id'] = 1; // Fix Data
                $data['Renter_Id'] = 1; // Fix Data
                $data['History_Item_Id'] = NULL;
                $data['Is_Pending'] = 'N';
                $data['Is_Partial'] = 'N';
                $data['Is_Repackage'] = 'N';
                $data['Unlock_Pending_Date'] = NULL;
                $data['Lock_Id'] = NULL;
                $data['Active'] = 'Y';
                $data['Flow_Id'] = NULL;
                $data['Activity_Involve'] = NULL;
                $data['Price_Per_Unit'] = NULL;
                $data['Unit_Price_Id'] = NULL;
                $data['All_Price'] = NULL;
                $data['Is_Count'] = NULL;
                $data['Old_Location_Code'] = NULL;
                $data['Remark'] = NULL;
                $data['Item_Id'] = NULL;
                $data['Cont_Id'] = NULL;
                $data['Invoice_Id'] = NULL;
                $data['Vendor_Id'] = NULL;

                if ($this->migrate->insert_inbound($data)) {
                    echo "SUCCESS";
                } else {
                    echo "FAILED";
                }

                echo "<hr/>";
            } else {
                p($error, FALSE);
                p($objArr, FALSE);
                echo "\r\n";
            }
        }
    }

    public function migrate_user() {
        // FIX CONTENT;
        $directory = getcwd() . "/user.csv";
        $ext = pathinfo($directory, PATHINFO_EXTENSION);
        $objCSV = fopen($directory, "r");

        // HEADER COLUMN
        while (($objArr = fgetcsv($objCSV, 1000, ",")) != FALSE) {

            $error = array();
            $is_error = FALSE;

            $document_no = $objArr[1];
            $receive_date = $objArr[2];
            $product_code = $objArr[3];
            $product_lot = $objArr[5];
            $product_serial = $objArr[6];
            $product_mfd = (empty($objArr[7]) ? NULL : $objArr[7] );
            $product_exp = (empty($objArr[8]) ? NULL : $objArr[8] );
            $product_status = $objArr[9];
            $location = $objArr[10];
            $balance = $objArr[11];
            $pallet_code = $objArr[13];
            $pallet_id = NULL;
            $remark = $objArr[14];
            $unit = $objArr[15];

            // QUERY FOR PRODUCT ID
            $product_id = $this->migrate->getProductIdByCode($product_code);
            if (!$product_id) {
                $error[] = "PRODUCT_CODE_NOT_FOUND";
                $is_error = TRUE;
            }

            $actual_location_id = $this->migrate->getLocationIdByCode($location);
            if (!$actual_location_id) {
                $error[] = "LOCATION_CODE_NOT_FOUND";
                $is_error = TRUE;
            }

            $unit_id = $this->migrate->getUnitIdByUnit($unit);
            if (!$unit_id) {
                $error[] = "UNIT_NOT_FOUND";
                $is_error = TRUE;
            }

            $receive_type = $this->migrate->getNormalReceiveType();
            if (!$receive_type) {
                $error[] = "RECEIVE_TYPE_NOT_FOUND";
                $is_error = TRUE;
            }

            $product_sub_status = $this->migrate->getNotSpecifiedStatus();
            if (!$product_sub_status) {
                $error[] = "SUB_STATUS_NOT_FOUND";
                $is_error = TRUE;
            }

            $product_sub_status = $this->migrate->getNotSpecifiedStatus();
            if (!$product_sub_status) {
                $error[] = "SUB_STATUS_NOT_FOUND";
                $is_error = TRUE;
            }


            if ($pallet_code) {
                $pallet_id = $this->migrate->genPalletIdFromPalletCode($pallet_code);
                if (!$pallet_id) {
                    $error[] = "PALLET_NOT_FOUND_OR_INUSED";
                    $is_error = TRUE;
                }
            }

            if ($is_error == FALSE) {

                $data = array();
                $data['Document_No'] = 'MIGRATE';
                $data['Doc_Refer_Int'] = 'MIGRATE';
                $data['Doc_Refer_Ext'] = $document_no;
                $data['Doc_Refer_Inv'] = 'MIGRATE';
                $data['Doc_Refer_CE'] = 'MIGRATE';
                $data['Doc_Refer_BL'] = 'MIGRATE';
                $data['Doc_Refer_AWB'] = 'MIGRATE';
                $data['Product_Id'] = $product_id;
                $data['Product_Code'] = $product_code;
                $data['Product_Status'] = $product_status;
                $data['Product_Sub_Status'] = $product_sub_status;
                $data['Suggest_Location_Id'] = NULL;
                $data['Actual_Location_Id'] = $actual_location_id;
                $data['Old_Location_Id'] = NULL;
                $data['Pallet_Id'] = $pallet_id;
                $data['Receive_Type'] = $receive_type;
                $data['Receive_Date'] = date('Y-m-d H:i:s.000');
                $data['Putaway_Date'] = date('Y-m-d H:i:s.000');
                $data['Putaway_By'] = 1;
                $data['Product_License'] = NULL;
                $data['Product_Lot'] = $product_lot;
                $data['Product_Serial'] = $product_serial;
                $data['Product_Mfd'] = $product_mfd;
                $data['Product_Exp'] = $product_exp;
                $data['Receive_Qty'] = $balance;
                $data['PD_Reserv_Qty'] = 0;
                $data['PK_Reserv_Qty'] = 0;
                $data['Dispatch_Qty'] = 0;
                $data['Balance_Qty'] = $balance;
                $data['Adjust_Qty'] = 0;
                $data['Unit_Id'] = $unit_id;
                $data['Owner_Id'] = 1; // Fix Data
                $data['Renter_Id'] = 1; // Fix Data
                $data['History_Item_Id'] = NULL;
                $data['Is_Pending'] = 'N';
                $data['Is_Partial'] = 'N';
                $data['Is_Repackage'] = 'N';
                $data['Unlock_Pending_Date'] = NULL;
                $data['Lock_Id'] = NULL;
                $data['Active'] = 'Y';
                $data['Flow_Id'] = NULL;
                $data['Activity_Involve'] = NULL;
                $data['Price_Per_Unit'] = NULL;
                $data['Unit_Price_Id'] = NULL;
                $data['All_Price'] = NULL;
                $data['Is_Count'] = NULL;
                $data['Old_Location_Code'] = NULL;
                $data['Remark'] = NULL;
                $data['Item_Id'] = NULL;
                $data['Cont_Id'] = NULL;
                $data['Invoice_Id'] = NULL;
                $data['Vendor_Id'] = NULL;

                if ($this->migrate->insert_inbound($data)) {
                    echo "SUCCESS";
                } else {
                    echo "FAILED";
                }

                echo "<hr/>";
            } else {
                p($error, FALSE);
                p($objArr, FALSE);
                echo "\r\n";
            }
        }
    }

}
