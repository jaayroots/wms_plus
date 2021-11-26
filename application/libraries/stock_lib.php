<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Stock_lib {

    function __construct() {
        $CI = & get_instance();
        $CI->load->helper('util_helper');
    }

    function getPreReceiveArea() {
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("Location_Id");
        $CI->db->from("STK_M_Location");
        $CI->db->join("STK_M_Storage", "STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id");
        $CI->db->join("STK_M_Storage_Type", "STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
        $CI->db->where("StorageType_Code", "ST05");
        $query = $CI->db->get();
        $result = $query->result();
        $location_id = "";
        if (count($result) > 0) :
            foreach ($result as $rows) :
                $location_id = $rows->Location_Id;
            endforeach;
        endif;
        return $location_id;
    }

    #ISSUE 3034 Reject Document
    #DATE:2013-12-16
    #BY:KIK
    #เพิ่มเพื่อใช้ในส่วนของ reject and (reject and return)
    #หากมีการ return กลับมา หลักจาก picking ไปแล้ว ในส่วนของ suggest location ที่สร้าง relocation ขึ้นใหม่ จะต้องเป็น pre-dispatch zone

    function getPreDispatchArea() {
        $CI = & get_instance();
        $CI->db->select("Location_Id");
        $CI->db->from("STK_M_Location");
        $CI->db->join("STK_M_Storage", "STK_M_Storage.Storage_Id = STK_M_Location.Storage_Id");
        $CI->db->join("STK_M_Storage_Type", "STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
        $CI->db->where("StorageType_Code", "ST08");
        $query = $CI->db->get();
        $result = $query->result();
        $location_id = "";
        if (!empty($result)) :
            foreach ($result as $rows) :
                $location_id = $rows->Location_Id;
            endforeach;
        endif;
        return $location_id;
    }

    // Add By Akkarapol, 26/09/2013, เพิ่มฟังก์ชั่นสำหรับ get location ที่เหมาะสม ในกรณีที่เป็น Product ที่ Repackage จะให้วางของไว้ที่ ตำแหน่งที่ต้องการ
    function getRepackageArea() {
        $CI = & get_instance();
//        $CI->load->database();
//        $CI->db->select("ISNULL(STK_M_Location.Location_Id,''),* ");
        $CI->db->select("ISNULL(STK_M_Location.Location_Id, '') AS Location_Id"); // Edit by Ton! 20131018
        $CI->db->from("STK_M_Storage_Detail");
        $CI->db->join("STK_M_Location", "STK_M_Location.Storage_Detail_Id = STK_M_Storage_Detail.Storage_Detail_Id");
        $CI->db->join("STK_M_Storage", "STK_M_Storage_Detail.Storage_Id = STK_M_Storage.Storage_Id");
        $CI->db->join("STK_M_Storage_Type", "STK_M_Storage.StorageType_Id = STK_M_Storage_Type.StorageType_Id");
        $CI->db->join("SYS_M_Domain", "Dom_Host_Code ='VAS_REPACK' and Dom_Code = dbo.STK_M_Storage_Type.StorageType_Code AND SYS_M_Domain.Dom_Active = 'Y'");
        $query = $CI->db->get();
        $result = $query->result();
        $location_id = "";
        if (count($result) > 0) :
            foreach ($result as $rows) :
                $location_id = $rows->Location_Id;
            endforeach;
        endif;
        return $location_id;
    }

    // END Add By Akkarapol, 26/09/2013, เพิ่มฟังก์ชั่นสำหรับ get location ที่เหมาะสม ในกรณีที่เป็น Product ที่ Repackage จะให้วางของไว้ที่ ตำแหน่งที่ต้องการ

    function addStockReceiveOrder($order_id = NULL) {  #Default Location to Activiity Area First
        return "C001";
    }

    function updatePartialReceive($order_id) {
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $CI->db->where("STK_T_Order_Detail.Confirm_Qty <", "STK_T_Order_Detail.Reserv_Qty", FALSE);
        $CI->db->where("STK_T_Order_Detail.Active", ACTIVE);
        $query = $CI->db->get();
        $result = $query->result();

        // Add By Akkarapol, 12/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง
        if (empty($result)):
            $CI->db->select("Order_Detail_2.*");
            $CI->db->from("STK_T_Order_Detail Order_Detail_1");
            $CI->db->join("STK_T_Order_Detail Order_Detail_2", "Order_Detail_1.Split_From_Item_Id = Order_Detail_2.Item_Id");
            $CI->db->where("Order_Detail_1.Order_Id", $order_id);
            $CI->db->where("Order_Detail_1.Active", ACTIVE);
            $CI->db->where("Order_Detail_1.Reserv_Qty > Order_Detail_1.Confirm_Qty "); // Add By Akkarapol, 20/09/2013, เพิ่มเช็คเอาเฉพาะอันที่ Reserv_Qty มากกว่า Confirm_Qty
            $CI->db->order_by('Order_Detail_1.Item_Id', 'DESC');
            $query = $CI->db->get();
            $result = $query->result();
        endif;
        // END Add By Akkarapol, 12/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง

         #comment by kik : 20140722 ตอนนี้ไม่ได้ใช้ case นี้แล้ว จึงไม่ต้องมีการเช็คค่าจาก split_form_item อีก ไม่งั้นจะตกเงื่อนไขนี้ตลอดที่มีการทำ split มา
        // Add By Akkarapol, 23/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง
//        if (empty($result)):
//            $CI->db->select("*");
//            $CI->db->from("STK_T_Order_Detail");
//            $CI->db->where("Item_Id = (SELECT TOP 1 Split_From_Item_Id FROM STK_T_Order_Detail WHERE Order_Id = " . $order_id . " ORDER BY Item_Id DESC)");
//            $query = $CI->db->get();
//            $result = $query->result();
//        endif;
        // END Add By Akkarapol, 23/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง


        if (is_array($result) && (!empty($result))) :
            $CI->db->set("Is_Partial", ACTIVE);
            $CI->db->where_in("Order_Id", $order_id);
            $CI->db->update("STK_T_Order");
        endif;
        return TRUE; //EDIT BY POR 2014-03-10 แก้ไขจาก C001 เป็น true เพื่อให้รองรับ transaction
    }


    /**
     * addStockDispatchOrder
     * @param int $order_id
     * @return array['type_of_alert'][]['message']
     *
     * @modified : เพิ่มการเช็คในแต่ละขั้นตอนให้ละเอียดมากขึ้น ได้แก่ $result ต้องไม่เป็นค่าว่าง , Inbound_Item_Id ต้องไม่เป็นค่าว่าง เพื่อที่จะเอาไป update qty in inbound table , $item_list จะต้องไม่เป็นค่าว่าง เพื่อส่งไป update balance ,$data_list จะต้องไม่เป็นช่องว่าง เพื่อเอาไป insert into outbound table และจะต้องตรวจสอบว่า record ที่ insert จะต้องเท่ากับ record ใน order detail เท่านั้น จึงจะให้ผ่าน ฯลฯ : by kik : 20140530
     */
    function addStockDispatchOrder($order_id) {

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();

        $CI = & get_instance();

        $CI->db->select("O.* , OD.* ,OD.Item_Id,I.Activity_Involve,I.Unit_Id");
        $CI->db->from("STK_T_Order O");
        $CI->db->join("STK_T_Order_Detail OD", "OD.Order_Id = O.Order_Id");
        $CI->db->join("STK_T_Inbound I", "OD.Inbound_Item_Id = I.Inbound_Id");
        $CI->db->where("O.Order_Id", $order_id);
        $CI->db->where("OD.Active", 'Y');
        $query = $CI->db->get();
        $result = $query->result();


        /**
         * update Process
         */
        // p($check_not_err);
        if($check_not_err):
            $data = array();
            $data_list = array();
            $CI->load->model("stock_model", "stock");

            if (is_array($result) && !empty($result)) :

                /**
                 * update Dispatch_Qty, PD_Reserv_Qty in Inbound.
                 */
                foreach ($result as $rows) :
                    unset($data);
                    $data['Document_No'] = $rows->Document_No;
                    $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                    $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                    $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                    $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                    $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                    $data['Product_Id'] = $rows->Product_Id;
                    $data['Product_Code'] = $rows->Product_Code;
                    $data['Product_Status'] = $rows->Product_Status;
                    $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                    $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                    $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                    $data['Pallet_Id'] = $rows->Pallet_Id;
                    $data['Dispatch_Date'] = $rows->Actual_Action_Date;
                    $data['Dispatch_Type'] = $rows->Doc_Type;
                    $data['Picking_Date'] = $rows->Activity_Date;
                    $data['Picking_By'] = $rows->Activity_By;
                    $data['Product_License'] = $rows->Product_License;
                    $data['Product_Lot'] = $rows->Product_Lot;
                    $data['Product_Serial'] = $rows->Product_Serial;
                    $data['Product_Mfd'] = $rows->Product_Mfd;
                    $data['Product_Exp'] = $rows->Product_Exp;
                    $data['Dispatch_Qty'] = $rows->Confirm_Qty;
                    $data['Inbound_Item_Id'] = $rows->Inbound_Item_Id;
                    $data['Owner_Id'] = $rows->Owner_Id;
                    $data['Renter_Id'] = $rows->Renter_Id;
                    $data['Unit_Id'] = $rows->Unit_Id;
                    $data['Activity_Involve'] = $rows->Activity_Involve;
                    $data['Flow_Id'] = $rows->Flow_Id;
                    $data['Item_Id'] = $rows->Item_Id;
                    $data['Pallet_Id_Out'] = $rows->Pallet_Id_Out;  // add by kik : 20140825
                    $data['Cont_Id'] = $rows->Cont_Id;              // add by kik : 20140825
                    $data['Invoice_Id'] = $rows->Invoice_Id;        // add by kik : 20140825
                    $data['Real_Action_Date'] = $rows->Real_Action_Date;        // add by kik : 20141209
                    $data_list[] = $data;
                    $item_list[] = $rows->Inbound_Item_Id;

                    /**
                     * update Dispatch_Qty, PD_Reserv_Qty in Inbound.
                     */
                    if($rows->Inbound_Item_Id != "" && $rows->Inbound_Item_Id != 0)://check not empty inbound id by kik : 20140530
                        $result_reservDispatch = $CI->stock->reservDispatch($rows->Inbound_Item_Id, $rows->Confirm_Qty, "+");  //edit parameter from $data[]  to $rows->xxx  by kik : 20140530
                            if (!$result_reservDispatch) :
                                $check_not_err = FALSE;

                                /**
                                 * Set Alert Zone (set Error Code, Message, etc.)
                                 */
                            log_message("ERROR", "Can not update Dispatch_Qty, PD_Reserv_Qty, PK_Reserv_Qty in Inbound.");
                                $return['critical'][]['message'] = "Can not update Dispatch_Qty, PD_Reserv_Qty, PK_Reserv_Qty in Inbound.";
                            break;
                            endif;
                    else:
                                $check_not_err = FALSE;

                                /**
                                 * Set Alert Zone (set Error Code, Message, etc.)
                                 */
                    log_message("ERROR", "Can not update Dispatch_Qty, PD_Reserv_Qty, PK_Reserv_Qty in Inbound.");
                                $return['critical'][]['message'] = "Can not update Dispatch_Qty, PD_Reserv_Qty, PK_Reserv_Qty in Inbound.";
                    endif;


                endforeach;

               /**
                * update Balance Qty
                */
                // p($check_not_err);
              
               if($check_not_err ):
           
                   if(!empty($item_list))://check not empty $item_list  by kik : 20140530
                        $result_updateBalanceQty = $this->updateBalanceQty($item_list);
                        // p($result_updateBalanceQty);exit;
                        if ($result_updateBalanceQty <= 0) :
                             $check_not_err = FALSE;
                              /**
                               * Set Alert Zone (set Error Code, Message, etc.)
                               */
                        log_message("ERROR", "Can not update Balance Qty.");
                              $return['critical'][]['message'] = "Can not update Balance Qty.";
                        endif;
                    else:
                            $check_not_err = FALSE;
                              /**
                               * Set Alert Zone (set Error Code, Message, etc.)
                               */
                    log_message("ERROR", "Can not update Balance Qty.");
                              $return['critical'][]['message'] = "Can not update Balance Qty.";
                   endif;

               endif;


               /**
                * Create Outbound
                */
               $afftectedRows_check = 0;
            //    p($check_not_err);
               if($check_not_err):
                   if(!empty($data_list))://check $data_list not empty  by kik : 20140530
                       $CI->db->insert_batch('STK_T_Outbound', $data_list, $afftectedRows_check);
                        $afftectedRows = $CI->db->affected_rows();
						//echo sizeof($result);exit();return false;
                        //log_message("ERROR", "Demo " . $demo);
                        //log_message("ERROR", "affected " . $afftectedRows);
                        //log_message("ERROR", "result " . count($result));
                        if ($afftectedRows_check != sizeof($result)) :
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                        log_message("ERROR", "Can not Create Outbound.");
                            $return['critical'][]['message'] = "Can not Create Outbound.";
                        endif;

                   else:
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                   			log_message("ERROR", "Can not Create Outbound.");
                            $return['critical'][]['message'] = "Can not Create Outbound.";
                   endif;

               endif;


            else://check have order detail
                // p($check_not_err);
                $check_not_err = FALSE;
                  /**
                  * Set Alert Zone (set Error Code, Message, etc.)
                  */
            	log_message("ERROR", "No have order detail");
                 $return['critical'][]['message'] = "No have order detail";
            endif;

        endif;
    
//        return "C001";
        return $return;
    }

    function updateBalanceQty($item_list) {
      
        $CI = & get_instance();
        $afftectedRows=0;
        foreach ($item_list as $value):
            $new_balance = 0;
            $info_sql = $CI->db->query("SELECT * FROM STK_T_Inbound WHERE Inbound_Id=" . $value);
            $info = $info_sql->result();

            //ADD BY POR 2013-12-12 เพิ่ม number_format เข้าไปเพื่อแก้ไขปัญหา ลบกันเหลือ 0 ค่าที่ได้จะไม่เท่ากับ 0 จริง แต่จะเป็นค่าที่ไม่สามารถหาค่าได้เช่น 3.99680288865E-15
            $new_balance = set_number_format(($info[0]->Receive_Qty - $info[0]->Dispatch_Qty) - $info[0]->Adjust_Qty);
            $new_balance = str_replace(",", "", $new_balance); //+++++ADD BY POR 2013-12-12 ตัด comma ออกเพื่อให้สามารถบันทึกในรูปแบบ float ได้
        
            // p($new_balance);
            // exit;
            #add for set active = 'N' when balance <= 0  : by kik  : 2014-02-20
            $set_active = "";
            if($new_balance <= 0){
                $new_balance = 0;
                $set_active = ",Active = 'N'";
            }
            #end add by kik : 20140220

            $CI->db->query("UPDATE STK_T_Inbound
                                    SET Balance_Qty=" . $new_balance.$set_active . "
                                    WHERE Inbound_Id=" . $value);

            $afftectedRow = $CI->db->affected_rows();
            $afftectedRows += $afftectedRow;
            
            unset($new_balance);
            unset($set_active);

        endforeach;

        return $afftectedRows;

    }

    function updateStockPutawayOrder($order_id) {  # Location Follow By Handheld Scan
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();
        $location_prcv = $this->getPreReceiveArea();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $rows) {
                unset($data);
                if ($rows->Confirm_Qty > 0) {
                    $data['Document_No'] = $rows->Document_No;
                    $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                    $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                    $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                    $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                    $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                    $data['Product_Id'] = $rows->Product_Id;
                    $data['Product_Code'] = $rows->Product_Code;
                    $data['Product_Status'] = $rows->Product_Status;
                    $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                    if (("" == $rows->Suggest_Location_Id) || (0 == $rows->Suggest_Location_Id)) {
                        $rows->Suggest_Location_Id = $location_prcv; # Default Activiity Area
                    }
                    if (("" == $rows->Actual_Location_Id) || (0 == $rows->Actual_Location_Id)) {
                        $rows->Actual_Location_Id = $location_prcv;  # Default Activiity Area
                    }
                    $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                    $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                    $data['Pallet_Id'] = $rows->Pallet_Id;
                    $data['Receive_Date'] = $rows->Actual_Action_Date;
                    $data['Receive_Type'] = $rows->Doc_Type;
                    $data['Product_License'] = $rows->Product_License;
                    $data['Product_Lot'] = $rows->Product_Lot;
                    $data['Product_Serial'] = $rows->Product_Serial;
                    $data['Product_Mfd'] = $rows->Product_Mfd;
                    $data['Product_Exp'] = $rows->Product_Exp;
                    $data['Receive_Qty'] = $rows->Confirm_Qty;
                    $data['Balance_Qty'] = $rows->Confirm_Qty;
                    $data['PD_Reserv_Qty'] = 0;
                    $data['PK_Reserv_Qty'] = 0;
                    $data['Dispatch_Qty'] = 0;
                    $data['Adjust_Qty'] = 0;
                    $data['Owner_Id'] = $rows->Owner_Id;
                    $data['Renter_Id'] = $rows->Renter_Id;
                    $data['Unit_Id'] = $rows->Unit_Id;
                    $data['Putaway_Date'] = $rows->Activity_Date;
                    $data['Putaway_By'] = $rows->Activity_By;
                    $data['Is_Pending'] = $rows->Is_Pending;
                    $data['Is_Partial'] = $rows->Is_Partial;
                    $data['Is_Repackage'] = $rows->Is_Repackage;
                    $data['Flow_Id'] = $rows->Flow_Id;
                    $data_list[] = $data;
                }
            }
            $CI->db->trans_start();
            $CI->db->insert_batch('STK_T_Inbound', $data_list);
            $CI->db->trans_complete();
            if ($CI->db->trans_status() === FALSE) {
                $CI->db->trans_rollback();
                return "E001";
            } else {
                $CI->db->trans_commit();
            }
        }
        return "C001";
    }

//    Add By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive แต่ไม่อยากไปยุ่งกับฟังก์ชั่น updateStockPutawayOrder ที่ถูกเขียนไว้เพราะกลัวจะกระทบ เลยสร้างฟังก์ชั่นใหม่นี้ขึ้นมาแทน
    function updateStockReceiveOrder($order_id) {
        $check_not_err=TRUE;

        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*, CONVERT(VARCHAR(20), STK_T_Order.Actual_Action_Date, 103) AS From_Receive_Date");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id AND STK_T_Order_Detail.Active ='Y' "); // add by kik : 09-09-2013
//        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id"); (comment by kik :  09-09-2013)
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();
        $location_prcv = $this->getPreReceiveArea();
        $From_Receive_Date = '';
        if (is_array($result) && (!empty($result))):
            $From_Receive_Date = $result[0]->From_Receive_Date;
//            $tmp =  split('/',$From_Receive_Date);
            $tmp =  preg_split('/\//', $From_Receive_Date);
            $From_Receive_Date = $tmp[2]."-".$tmp[1]."-".$tmp[0];
            foreach ($result as $keyRows => $rows) {// p($rows);exit();
                unset($data);
//                if ($rows->Confirm_Qty >= 0) { comment by kik : 20140805 cf ต้องมากกว่า 0 ถึงจะเอาเข้า inbound
                  if ($rows->Confirm_Qty > 0) { //add new again by kik : cf ต้องมากกว่า 0 ถึงจะเอาเข้า inbound
                    $data['Item_id'] = $rows->Item_Id; //add by kik for add item_id into inbound : 20140516
                    $data['Document_No'] = $rows->Document_No;
                    $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                    $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                    $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                    $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                    $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                    $data['Product_Id'] = $rows->Product_Id;
                    $data['Product_Code'] = $rows->Product_Code;
                    $data['Product_Status'] = $rows->Product_Status;
                    $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                    if (("" == $rows->Suggest_Location_Id) || (0 == $rows->Suggest_Location_Id)) {
                        $rows->Suggest_Location_Id = $location_prcv; # Default Activiity Area
                    }
                    if (("" == $rows->Actual_Location_Id) || (0 == $rows->Actual_Location_Id)) {

                        // Add By Akkarapol, 26/09/2013, เพิ่มการตรวจสอบ ในกรณีที่ Product เป็น Repackage จะให้วางของไว้ที่ ตำแหน่งที่ต้องการ
                        if ($rows->Product_Sub_Status == 'SS002'):
                            $location_prcv = $this->getRepackageArea();
                        endif;
                        // END Add By Akkarapol, 26/09/2013, เพิ่มการตรวจสอบ ในกรณีที่ Product เป็น Repackage จะให้วางของไว้ที่ ตำแหน่งที่ต้องการ

                        $rows->Actual_Location_Id = $location_prcv;  # Default Activiity Area
                    }
//                    $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id; // Comment By Akkarapol, 16/09/2013, ตอน Approve Receive ตัว Suggest Location ต้องไม่มีค่า
                    $data['Suggest_Location_Id'] = NULL; // Add By Akkarapol, 16/09/2013, ตอน Approve Receive ตัว Suggest Location ต้องไม่มีค่า
                    $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                    $data['Pallet_Id'] = $rows->Pallet_Id;
                    $data['Receive_Date'] = $rows->Actual_Action_Date;
                    $data['Receive_Type'] = $rows->Doc_Type;
                    $data['Product_License'] = $rows->Product_License;
                    $data['Product_Lot'] = $rows->Product_Lot;
                    $data['Product_Serial'] = $rows->Product_Serial;
                    $data['Product_Mfd'] = $rows->Product_Mfd;
                    $data['Product_Exp'] = $rows->Product_Exp;
                    $data['Receive_Qty'] = $rows->Confirm_Qty;
                    $data['Balance_Qty'] = $rows->Confirm_Qty;
                    $data['PD_Reserv_Qty'] = 0;
                    $data['PK_Reserv_Qty'] = 0;
                    $data['Dispatch_Qty'] = 0;
                    $data['Adjust_Qty'] = 0;
                    $data['Owner_Id'] = $rows->Owner_Id;
                    $data['Renter_Id'] = $rows->Renter_Id;
                    $data['Unit_Id'] = $rows->Unit_Id;
                    //$data['Putaway_Date'] = $rows->Activity_Date;  COMMENT BY POR 2013-11-08 เนื่องจากจะไม่ update หรือ insert ส่วนนี้นอกจากในขั้นตอน putaway
                    //$data['Putaway_By'] = $rows->Activity_By; COMMENT BY POR 2013-11-08 เนื่องจากจะไม่ update หรือ insert ส่วนนี้นอกจากในขั้นตอน putaway
                    $data['Is_Pending'] = $rows->Is_Pending;
                    $data['Is_Partial'] = $rows->Is_Partial;
                    $data['Is_Repackage'] = $rows->Is_Repackage;
                    $data['Flow_Id'] = $rows->Flow_Id;

                    //ADD BY POR 2014-01-13 เพิ่มให้บันทึกราคาด้วย
                    $data['Price_Per_Unit'] = $rows->Price_Per_Unit;
                    $data['Unit_Price_Id'] = $rows->Unit_Price_Id;
                    $data['All_Price'] = $rows->All_Price;
                    //END ADD

                    $data['Cont_Id'] = $rows->Cont_Id;
                    $data['Invoice_Id'] = $rows->Invoice_Id;
                    $data['From_Receive_Date'] = $rows->From_Receive_Date;
                    $data['Vendor_Id'] = $rows->Vendor_Id; //บันทึก vender (IOR) ด้วยเพื่อให้รองรับ custom report :ADD BY POR 2015-09-11
                    
                    $data_list[$keyRows] = $data; // Edit By Akkarapol, 11/09/2013, เพิ่ม $keyRows เข้าไป เพราะจำเป็นต้องใช้ Index ที่แน่นอนเพื่อดึงข้อมูล
                    $itemIdList[$keyRows] = $rows->Item_Id; // Add By Akkarapol, 11/09/2013, เพิ่ม itemIdList เข้าไปเก็บ Item_Id เพื่อใช้ในการ Update ข้อมูลในขั้นตอนถัดไป
                }
            }

            // Comment By Akkarapol, 11/09/2013, คอมเม้นต์ทิ้งเพราะต้องใช้ foreach ในการ วนลูปแทน เพื่อให้เข้าไป Update ข้อมูลของ Order_Detail ได้
//            $CI->db->insert_batch('STK_T_Inbound', $data_list);
//            $CI->db->trans_complete();
            // END Comment By Akkarapol, 11/09/2013, คอมเม้นต์ทิ้งเพราะต้องใช้ foreach ในการ วนลูปแทน เพื่อให้เข้าไป Update ข้อมูลของ Order_Detail ได้
            // Add By Akkarapol, 11/09/2013, เปลี่ยนจากการใช้ insert_batch เป็นการใช้ foreach วนลูปเอา เนื่องจากต้องการนำค่า Inbound_Id ไปใส่ในตาราง Order_Detail ด้วย เพราะหากใช้ batch จะไม่สามารถนำ ID ที่ Insert ไปทั้งหมดออกมาได้นั่นเอง
            foreach ($data_list as $keyData => $Data):
                $lastInsertId = '';
                unset($Data['From_Receive_Date']);
                $CI->db->insert('STK_T_Inbound', $Data);
                $lastInsertId = $CI->db->insert_id();
                if(empty($lastInsertId)): //ADD BY POR 2014-03-10 ถ้าไม่มีค่าส่งกลับมาแสดงว่าไม่สามารถ insert ได้
                    $check_not_err=FALSE;
                    break;
                endif;

                if($check_not_err):
                    $setDataForUpdate['Inbound_Item_Id'] = $lastInsertId;
                    $setDataForWhere['Item_Id'] = $itemIdList[$keyData];

                    $CI->db->where($setDataForWhere);
                    $CI->db->update('STK_T_Order_Detail', $setDataForUpdate);
                    unset($setDataForUpdate);


                    # Input Data into 'Onhand' from receive_date to yesterday
                    if(strtotime($From_Receive_Date) < strtotime(date('Y-m-d')) ):

                        $backword_date = $From_Receive_Date;
                        $day_back = TRUE;
                        while ($day_back):
                            $str = "
                            INSERT INTO STK_T_Onhand_History (Product_Id,Product_Code,Product_Status,Product_Sub_Status,Actual_Location_Id,Balance_Qty,Available_Date,Product_Lot,Product_Serial,Product_Mfd,Product_Exp,Estimate_Qty,Renter_Id,Pallet_Id,Inbound_Id)
                                SELECT Product_Id,Product_Code,Product_Status,Product_Sub_Status,Actual_Location_Id,isnull(Balance_Qty,0),'{$backword_date}',Product_Lot,Product_Serial,Product_Mfd,Product_Exp ,isnull((Receive_Qty-PD_Reserv_Qty-Dispatch_Qty-Adjust_Qty),0),Renter_Id,Pallet_Id,Inbound_Id
                                FROM STK_T_Inbound
                                WHERE Inbound_Id = '{$lastInsertId}'
                            ";
                            $CI->db->query($str);

                            $next_date = date('Y-m-d H:i:s', strtotime($backword_date .' +1 day'));
                            if(strtotime($next_date) >= strtotime(date('Y-m-d'))):
                                $day_back = FALSE;
                            endif;
                            $backword_date = $next_date;
                        endwhile;

                    endif;

                endif;

            endforeach;

            // END Add By Akkarapol, 11/09/2013, เปลี่ยนจากการใช้ insert_batch เป็นการใช้ foreach วนลูปเอา เนื่องจากต้องการนำค่า Inbound_Id ไปใส่ในตาราง Order_Detail ด้วย เพราะหากใช้ batch จะไม่สามารถนำ ID ที่ Insert ไปทั้งหมดออกมาได้นั่นเอง
        else:
            $check_not_err=FALSE;
        endif;

        return $check_not_err;
    }

//    END Add By Akkarapol, 28/08/2013, เพิ่มไปเพราะการอัพเดทสต๊อกเข้า Inbound นั้นไม่ได้ทำที่ PutAway แต่ต้องมาทำตั้งแต่ขั้นของการ Receive แต่ไม่อยากไปยุ่งกับฟังก์ชั่น updateStockPutawayOrder ที่ถูกเขียนไว้เพราะกลัวจะกระทบ เลยสร้างฟังก์ชั่นใหม่นี้ขึ้นมาแทน



    function updateinboundReceiveOrder($order_id) {
        $check_not_err=TRUE;

        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id AND STK_T_Order_Detail.Active ='Y' ");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        if(empty($result)):
            $check_not_err = FALSE;
        endif;

        if($check_not_err):
            $data = array();
            $data_list = array();
            if (is_array($result) && (!empty($result))) {
                foreach ($result as $keyRows => $rows) {
    //                p($rows);exit();
                    unset($data);
                    if ($rows->Confirm_Qty > 0) {
                        $data['Document_No'] = $rows->Document_No;
                        $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                        $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                        $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                        $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                        $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                        $data['Product_Id'] = $rows->Product_Id;
                        $data['Product_Code'] = $rows->Product_Code;
                        $data['Product_Status'] = $rows->Product_Status;
                        $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                        $data['Pallet_Id'] = $rows->Pallet_Id;
                        $data['Receive_Date'] = $rows->Actual_Action_Date;
                        $data['Receive_Type'] = $rows->Doc_Type;
                        $data['Product_License'] = $rows->Product_License;
                        $data['Product_Lot'] = $rows->Product_Lot;
                        $data['Product_Serial'] = $rows->Product_Serial;
                        $data['Product_Mfd'] = $rows->Product_Mfd;
                        $data['Product_Exp'] = $rows->Product_Exp;
                        $data['Receive_Qty'] = $rows->Confirm_Qty;
                        $data['Balance_Qty'] = $rows->Confirm_Qty;
                        $data['PD_Reserv_Qty'] = 0;
                        $data['PK_Reserv_Qty'] = 0;
                        $data['Dispatch_Qty'] = 0;
                        $data['Adjust_Qty'] = 0;
                        $data['Owner_Id'] = $rows->Owner_Id;
                        $data['Renter_Id'] = $rows->Renter_Id;
                        $data['Unit_Id'] = $rows->Unit_Id;
                        $data['Is_Pending'] = $rows->Is_Pending;
                        $data['Is_Partial'] = $rows->Is_Partial;
                        $data['Is_Repackage'] = $rows->Is_Repackage;
                        $data['Flow_Id'] = $rows->Flow_Id;
                        $data['Cont_Id'] = $rows->Cont_Id;
                        $data['Invoice_Id'] = $rows->Invoice_Id;
                        $data['Vendor_Id'] = $rows->Vendor_Id;  //ให้บันทึก vender_id(IOR) ด้วย เพื่อรองรับ custom report  :ADD BY POR 2015-09-11
                        $CI->db->where('Inbound_Id', $rows->Inbound_Item_Id);
                        $CI->db->update("STK_T_Inbound", $data);

                        if($CI->db->affected_rows() <= 0): //ADD BY POR 2014-03-10 กรณีไม่สามารถ insert ได้
                            $check_not_err = FALSE;
                            break;
                        endif;
                    }
                }

        //            $CI->db->trans_start();
        //            foreach ($data_list as $keyData => $Data):
        //                $lastInsertId = '';
        //                $CI->db->insert('STK_T_Inbound', $Data);
        //                $lastInsertId = $CI->db->insert_id();
        //
        //                $setDataForUpdate['Inbound_Item_Id'] = $lastInsertId;
        //                $setDataForWhere['Item_Id'] = $itemIdList[$keyData];
        //
        //                $CI->db->where($setDataForWhere);
        //                $CI->db->update('STK_T_Order_Detail', $setDataForUpdate);
        //                unset($setDataForUpdate);
        //            endforeach;
        //            if ($CI->db->trans_status() === FALSE) {
        //                $CI->db->trans_rollback();
        //                return "E001";
        //            } else {
        //                $CI->db->trans_commit();
        //            }
                }
        endif;

        return $check_not_err;
    }

    function updateStockPicking($order_id) {  #Case Calculate When Picking Approve
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        if (is_array($result) && (count($result) > 0)) {
            foreach ($result as $rows) {
                unset($data);
                $data['Product_Id'] = $rows->Product_Id;
                $data['Product_Code'] = $rows->Product_Code;
                $data['Product_Status'] = $rows->Product_Status;
                $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                ;
                $data['Pallet_Id'] = $rows->Pallet_Id;
                $data['Product_Lot'] = $rows->Product_Lot;
                $data['Product_Serial'] = $rows->Product_Serial;
                $data['Product_Mfd'] = $rows->Product_Mfd;
                $data['Product_Exp'] = $rows->Product_Exp;
                $data['Owner_Id'] = $rows->Owner_Id;
                $data['Renter_Id'] = $rows->Renter_Id;
                $picking_qty = $rows->Confirm_Qty;  /* Add Qty in [STK_T_Inbound]Reserv_Qty */
                //echo ">>".$picking_qty."<<";
                if (strlen(trim($picking_qty)) <= 0) {
                    $picking_qty = 0;
                }

#Question Check Validate Product Picking from Inbound Id Too!!
                $sql = " UPDATE STK_T_Inbound SET PD_Reserv_Qty=(PD_Reserv_Qty + $picking_qty )
						,PK_Reserv_Qty = (PK_Reserv_Qty + $picking_qty )  WHERE ";
                $sql .= " Product_Id		 = '" . $data['Product_Id'] . "' ";
                $sql .= " AND Product_Code		 = '" . $data['Product_Code'] . "' ";
                $sql .= " AND Actual_Location_Id = '" . $data['Actual_Location_Id'] . "' ";
                $sql .= " AND Product_Lot		 = '" . $data['Product_Lot'] . "' ";
                $sql .= " AND Product_Serial	 = '" . $data['Product_Serial'] . "' ";
                $sql .= " AND Product_Mfd		 = '" . $data['Product_Mfd'] . "' ";
                $sql .= " AND Product_Exp		 = '" . $data['Product_Exp'] . "' ";
                $CI->db->query($sql);
            }
            return "C001";
        }
    }

    function updatereservPDReservQty($order_id, $Item_Id, $Inbound_Id) {
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $CI->db->where("STK_T_Order_Detail.Item_Id", $Item_Id);
        $query = $CI->db->get();
        $result = $query->result();
        if (is_array($result) && (count($result) > 0)) {
            foreach ($result as $rows) {
                $picking_qty = $rows->Reserv_Qty;
                if (strlen(trim($picking_qty)) <= 0) {
                    $picking_qty = 0;
                }
                $sql = " UPDATE STK_T_Inbound SET PD_Reserv_Qty = (PD_Reserv_Qty + $picking_qty ) WHERE ";
                $sql.=" Inbound_Id = " . $Inbound_Id;
                $CI->db->query($sql);
            }
            return "C001";
        }
    }

    function moveLocation($order_id) {
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Relocate_Detail RD");
        $CI->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id");
        $CI->db->where("RD.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();
        if (is_array($result) && (count($result) > 0)) {
            foreach ($result as $rows) {
                $info_sql = $CI->db->query("SELECT *,CONVERT(VARCHAR(20), Unlock_Pending_Date, 20) AS Unlock_Pending_Date FROM STK_T_Inbound WHERE Inbound_Id=" . $rows->Inbound_Item_Id);
                $info = $info_sql->result();
                $data['Document_No'] = $info[0]->Document_No;
                $data['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
                $data['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
                $data['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
                $data['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
                $data['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
                $data['Product_Id'] = $info[0]->Product_Id;
                $data['Product_Code'] = $info[0]->Product_Code;

                // Edit By Akkarapol, 22/01/2014, เปลี่ยนการเซ็ตค่าของ Product_Status และ Product_Sub_Status จากที่เคยนำมาจาก Inbound อันเก่า ให้เป็นค่าตามที่ได้เลือกมาที่หน้า Pending Flow แทน
//                $data['Product_Status'] = $info[0]->Product_Status;
//                $data['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
                $data['Product_Status'] = $rows->Product_Status;
                $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                // END Edit By Akkarapol, 22/01/2014, เปลี่ยนการเซ็ตค่าของ Product_Status และ Product_Sub_Status จากที่เคยนำมาจาก Inbound อันเก่า ให้เป็นค่าตามที่ได้เลือกมาที่หน้า Pending Flow แทน

                $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                $data['Old_Location_Id'] = $rows->Old_Location_Id;
                $data['Pallet_Id'] = $rows->Pallet_Id;
                $data['Receive_Date'] = $info[0]->Receive_Date;
                $data['Receive_Type'] = $info[0]->Receive_Type;
                $data['Product_License'] = $info[0]->Product_License;
                $data['Product_Lot'] = $info[0]->Product_Lot;
                $data['Product_Serial'] = $info[0]->Product_Serial;
                $data['Product_Mfd'] = $info[0]->Product_Mfd;
                $data['Product_Exp'] = $info[0]->Product_Exp;
                $data['Receive_Qty'] = $rows->Confirm_Qty;
                $data['PD_Reserv_Qty'] = 0;
                $data['PK_Reserv_Qty'] = 0;
                $data['Dispatch_Qty'] = 0;
                $data['Balance_Qty'] = $rows->Confirm_Qty;
                $data['Adjust_Qty'] = 0;
                $data['Putaway_Date'] = $rows->Putaway_Date;
                $data['Putaway_By'] = $rows->Putaway_By;
                $data['Owner_Id'] = $info[0]->Owner_Id;
                $data['Renter_Id'] = $info[0]->Renter_Id;
                $data['Unit_Id'] = $info[0]->Unit_Id;
                $data['Active'] = ACTIVE;
                $data['History_Item_Id'] = $rows->Inbound_Item_Id;
                $data['Activity_Involve'] = $info[0]->Activity_Involve;
                $data['Flow_Id'] = $rows->Flow_Id;
                $data['Unlock_Pending_Date'] = $info[0]->Unlock_Pending_Date; // Add By Akkarapol, 04/09/2013, เพิ่มเพราะ ต้องการคัดลอกข้อมูล Unlock_Pending_Date จาก Inbound เก่า มาไว้ที่ Inbound ที่ทำการ Approve Putaway เรียบร้อยแล้ว
                //ADD BY POR 2014-01-16 เพิ่มให้บันทึกเกี่ยวกับราคา
                $data['Price_Per_Unit'] = $rows->Price_Per_Unit;
                $data['Unit_Price_Id'] = $rows->Unit_Price_Id;
                $data['All_Price'] = $rows->All_Price;
                //END ADD
                $data_list[] = $data;
                $update_item[] = $rows->Inbound_Item_Id;
            } // close loop result
            $CI->db->insert_batch('STK_T_Inbound', $data_list);
            $CI->db->trans_complete();
            if ($CI->db->trans_status() === FALSE) {
                $CI->db->trans_rollback();
                //$response = "E001";
            } else {
                $CI->db->trans_commit();
                //$response = "C001";
            }
            //$afftectedRows=$this->db->affected_rows();
            //p($update_item);
            $update = $CI->db->query("UPDATE STK_T_Inbound SET Active='" . INACTIVE . "' WHERE Inbound_Id IN (" . implode(",", $update_item) . ")");
            return "C001";
        } // close have result
    }

    public function move_location_approve($order_id) {
        $check_not_err = TRUE;
        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("*");
        $CI->db->from("STK_T_Relocate_Detail RD");
        $CI->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id");
        $CI->db->where("RD.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();
        if (is_array($result) && (!empty($result))) {
            foreach ($result as $rows) {
                $info_sql = $CI->db->query("SELECT *,CONVERT(VARCHAR(20), Unlock_Pending_Date, 20) AS Unlock_Pending_Date FROM STK_T_Inbound WHERE Inbound_Id=" . $rows->Inbound_Item_Id);
                $info = $info_sql->result();
                //print_r($rows);
                //print_r($info);
//                $data['Document_No'] = $info[0]->Document_No;
                $data['Document_No'] = $rows->Document_No;
                $data['Doc_Refer_Int'] = $info[0]->Doc_Refer_Int;
                $data['Doc_Refer_Ext'] = $info[0]->Doc_Refer_Ext;
                $data['Doc_Refer_Inv'] = $info[0]->Doc_Refer_Inv;
                $data['Doc_Refer_CE'] = $info[0]->Doc_Refer_CE;
                $data['Doc_Refer_BL'] = $info[0]->Doc_Refer_BL;
                $data['Product_Id'] = $info[0]->Product_Id;
                $data['Product_Code'] = $info[0]->Product_Code;
                $data['Product_Status'] = $info[0]->Product_Status;
                $data['Product_Sub_Status'] = $info[0]->Product_Sub_Status;
                $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                $data['Old_Location_Id'] = $rows->Old_Location_Id;
                $data['Pallet_Id'] = $rows->Pallet_Id;
                $data['Receive_Date'] = $info[0]->Receive_Date;
                $data['Receive_Type'] = $info[0]->Receive_Type;
                $data['Product_License'] = $info[0]->Product_License;
                $data['Product_Lot'] = $info[0]->Product_Lot;
                $data['Product_Serial'] = $info[0]->Product_Serial;
                $data['Product_Mfd'] = $info[0]->Product_Mfd;
                $data['Product_Exp'] = $info[0]->Product_Exp;
                $data['Receive_Qty'] = $rows->Confirm_Qty;
                $data['PD_Reserv_Qty'] = 0;
                $data['PK_Reserv_Qty'] = 0;
                $data['Dispatch_Qty'] = 0;
                $data['Balance_Qty'] = $rows->Confirm_Qty;
                $data['Adjust_Qty'] = 0;
                $data['Putaway_Date'] = $rows->Putaway_Date;
                $data['Putaway_By'] = $rows->Putaway_By;
                $data['Owner_Id'] = $info[0]->Owner_Id;
                $data['Renter_Id'] = $info[0]->Renter_Id;
                $data['Unit_Id'] = $info[0]->Unit_Id;
                $data['Active'] = ACTIVE;
                $data['History_Item_Id'] = $rows->Inbound_Item_Id;
                $data['Activity_Involve'] = $info[0]->Activity_Involve;
                $data['Flow_Id'] = $rows->Flow_Id;
                $data['Unlock_Pending_Date'] = $info[0]->Unlock_Pending_Date; // Add By Akkarapol, 04/09/2013, เพิ่มเพราะ ต้องการคัดลอกข้อมูล Unlock_Pending_Date จาก Inbound เก่า มาไว้ที่ Inbound ที่ทำการ Approve Putaway เรียบร้อยแล้ว

                //ADD BY POR 2014-06-16 เพิ่มให้บันทึกหรืออัพเดท price per unit ด้วย
                $data['Price_Per_Unit'] = $rows->Price_Per_Unit;
                $data['Unit_Price_Id'] = $rows->Unit_Price_Id;
                $data['All_Price'] = $rows->All_Price;
                //END ADD

                $data['Cont_Id'] = $rows->Cont_Id;
                $data['Invoice_Id'] = $rows->Invoice_Id;

                $data_list[] = $data;
                $update_item[] = $rows->Inbound_Item_Id;
            } // close loop result
            $CI->db->insert_batch('STK_T_Inbound', $data_list);
            $afftectedRows1 = $CI->db->affected_rows();
             if ($afftectedRows1 < 1) {
                $check_not_err = FALSE;
            }
            //$afftectedRows=$this->db->affected_rows();
            //p($update_item);
//            if($check_not_err):
//                $update = $CI->db->query("UPDATE STK_T_Inbound SET Active='" . INACTIVE . "' WHERE Inbound_Id IN (" . implode(",", $update_item) . ")");
//                $afftectedRows2 = $CI->db->affected_rows();
//                if ($afftectedRows2 < 1) {
//                    $check_not_err = FALSE;
//                }
//            endif;

        } // close have result
        else{
            $check_not_err = FALSE;
        }

        if($check_not_err):
            return "C001";
        else:
            return "E001";
        endif;
    }

    function moveProductLocation($order_id) {

        $CI = get_instance();

        #Load config
        $conf = $CI->config->item('_xml');
        $conf_inv = empty($conf['invoice'])?false:@$conf['invoice'];
        $conf_cont = empty($conf['container'])?false:@$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit'])?false:@$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet'])?false:@$conf['build_pallet'];

        $CI->db->select("*");
        $CI->db->from("STK_T_Relocate_Detail RD");
        $CI->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id");
        $CI->db->where("R.Order_Id", $order_id);
        // p($CI->db->last_query()); exit;
        $query = $CI->db->get();
        $result = $query->result_object();
        $data = array();
        $data_list = array();
// 
        if(!empty($result) && is_array($result)):

               foreach ($result as $rows) { 
                // p($rows);exit;
                    $info_sql = $CI->db->query("SELECT * FROM STK_T_Inbound WHERE Inbound_Id=" . $rows->Inbound_Item_Id);
                    
                    $info = $info_sql->row();
                    $data['Item_id'] = $rows->Item_Id;
                    $data['Document_No'] = $rows->Doc_Relocate;   //EDIT BY POR 2014-11-18 change Doc_No to Doc_relo because new doc_no come from STK_T_Relocate
                    $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                    $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                    $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                    $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                    $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                    $data['Product_Id'] = $rows->Product_Id;
                    $data['Product_Code'] = $rows->Product_Code;
                    $data['Product_Status'] = $rows->Product_Status;
                    $data['Product_Sub_Status'] = $rows->Product_Sub_Status;

                    #set data for change status only
                    if($rows->Process_Type =='CH-STATUS'):
                        $data['Suggest_Location_Id'] = $info->Suggest_Location_Id;
                        $data['Actual_Location_Id'] = $info->Actual_Location_Id;

                        $info_wf_sql = $CI->db->query("SELECT Present_State FROM STK_T_Relocate reloc JOIN STK_T_Workflow wf on reloc.Flow_Id = wf.Flow_Id WHERE Order_Id=" . $order_id);
                        $info_wf = $info_wf_sql->row();
                      
                        if($info_wf->Present_State == -2):
                            $data['PD_Reserv_Qty'] = 0;
                        else:
                            $data['PD_Reserv_Qty'] = $rows->Reserv_Qty;
                        endif;

                    else:
                        $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                        $data['Actual_Location_Id'] = $rows->Actual_Location_Id;

                    endif;

                   //ADD
                    $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                    //END
                    
                    $data['Old_Location_Id'] = $rows->Old_Location_Id;
                    // p($data['Actual_Location_Id']);exit();
                    #add for ISSUE 3334 : by kik : 20140221
                    if($rows->DP_Type_Pallet == "FULL"){
                        $data['Pallet_Id'] = $rows->Pallet_Id;
                    }else{
                        $data['Pallet_Id'] = NULL;
                    }

                    #end add for ISSUE 3334 : by kik : 20140221

                    $data['Receive_Date'] = $info->Receive_Date;
                    $data['Receive_Type'] = $info->Receive_Type;
                    $data['Product_License'] = $rows->Product_License;
                    $data['Product_Lot'] = $rows->Product_Lot;
                    $data['Product_Serial'] = $rows->Product_Serial;
                    $data['Product_Mfd'] = $rows->Product_Mfd;
                    $data['Product_Exp'] = $rows->Product_Exp;
                    //$data['Receive_Qty'] = $rows->Reserv_Qty;  //COMMENT BY POR 2014-06-16
                    $data['Receive_Qty'] = $rows->Confirm_Qty; //ADD BY POR 2014-06-16 แก้ไขจาก reserv_Qty เป็น Confirm เนื่องจากเราต้องเอาจำนวนที่เค้ายืนยันจริงๆ
                    $data['PK_Reserv_Qty'] = 0;
                    $data['Dispatch_Qty'] = 0;
                    //$data['Balance_Qty'] = $rows->Reserv_Qty; //COMMENT BY POR 2014-06-16  //ADD BY POR 2014-06-16 แก้ไขจาก reserv_Qty เป็น Confirm เนื่องจากเราต้องเอาจำนวนที่เค้ายืนยันจริงๆ
                    $data['Balance_Qty'] = $rows->Confirm_Qty;
                    $data['Adjust_Qty'] = 0;
                    $data['Putaway_Date'] = $rows->Putaway_Date;
                    $data['Putaway_By'] = $rows->Putaway_By;
                    $data['Owner_Id'] = $info->Owner_Id;
                    $data['Renter_Id'] = $info->Renter_Id;
                    $data['Unit_Id'] = $rows->Unit_Id;
                    $data['Active'] = ACTIVE;
                    $data['History_Item_Id'] = $rows->Inbound_Item_Id;
                    $data['Activity_Involve'] = $info->Activity_Involve;
                    $data['Flow_Id'] = $rows->Flow_Id;

                    //ADD BY POR 2014-06-16 เพิ่มให้ insert price per unit ด้วย
                    $data['Price_Per_Unit'] = $rows->Price_Per_Unit;
                    $data['Unit_Price_Id'] = $rows->Unit_Price_Id;
                    $data['All_Price'] = $rows->All_Price;

                    #add value of invoice_no if config open invoice by item
                    if($conf_inv):
                        $data['Invoice_Id'] = $info->Invoice_Id;
                    endif;

                    #add value of container_no if config open invoice by item
                    if($conf_cont):
                        $data['Cont_Id'] = $info->Cont_Id;
                    endif;

                    #add value of container_no if config open invoice by item
                    if($conf_pallet):
                        // $data['Pallet_Id'] = $info->Pallet_Id;
                        $data['Pallet_Id'] = $rows->Pallet_Id; //ดึงจาก relocation_detail โดยตรง :ADD BY POR 2015-09-10
                    endif;
                    // p($data);exit;

                    //END ADD

                    $data_list[] = $data;
                    // p($data_list);exit;

            } // close have result

                if(!empty($data_list)):
                    // p($data_list);exit;

                    $CI->db->insert_batch('STK_T_Inbound', $data_list);
                    // p($CI->db->last_query()); exit;
                    $afftectedRows1 = $CI->db->affected_rows();

                    if($afftectedRows1 > 0):
                        return TRUE;
                    else:
                        return FALSE;
                    endif;

                else:

                    return FALSE;

                endif;

        else:

            return FALSE;

        endif;


    }

    /**
     * @function update_inbound_ch_prod_status use for update data inbound table in change product status approve
     * @author Kik
     * @param type $order_id
     * @return boolean
     * @created  : 20140516
     *
     */
    function update_inbound_ch_prod_status($order_id) {

        $CI = & get_instance();
        $CI->db->select("*");
        $CI->db->from("STK_T_Relocate_Detail RD");
        $CI->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id");
        $CI->db->where("RD.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $afftectedRows = 0;
        if(!empty($result) && is_array($result)):

            foreach ($result as $rows) {
//                    p($rows);exit();
                $item_id = $rows->Item_Id;
                $inbound_old = $rows->Inbound_Item_Id; //ADD BY POR 2014-06-19 เพิ่มให้ where เงื่อนไขนี้เพิ่มด้วย เนื่องจากถ้า where ด้วย item นะไป update inbound อื่นที่มี item_id ที่เหมือนกัน

                $CI->db->set("Suggest_Location_Id", $rows->Suggest_Location_Id, FALSE);
                $CI->db->set("Actual_Location_Id", $rows->Actual_Location_Id, FALSE);
                $CI->db->set("PD_Reserv_Qty", 0, FALSE);
                $CI->db->set("Putaway_Date",  "CONVERT(datetime, '" .$rows->Putaway_Date . "', 103)", FALSE);
                $CI->db->set("Putaway_By" ,$rows->Putaway_By,FALSE);

                $CI->db->where('Item_Id',$item_id);
                $CI->db->where('History_Item_Id',$inbound_old);  //ADD BY POR 2014-06-19 เพิ่มให้ where เงื่อนไขนี้เพิ่มด้วย เนื่องจากถ้า where ด้วย item นะไป update inbound อื่นที่มี item_id ที่เหมือนกัน
                $CI->db->update("STK_T_Inbound");

                $afftectedRow=$CI->db->affected_rows();
//                echo $afftectedRow;
                if ($afftectedRow < 0) :
                    return FALSE; //Update success.
                endif;

                $afftectedRows+=$afftectedRow;
                unset($afftectedRow);
                unset($item_id);

            }

            if($afftectedRows > 0):
                return TRUE;
            else:
                return FALSE;
            endif;

        else:

            return FALSE;

        endif;

    }

    function updateProductLocation($order_id) {
        $CI = & get_instance();
        $CI->db->select("*");
        $CI->db->from("STK_T_Relocate_Detail RD");
        $CI->db->join("STK_T_Relocate R", "RD.Order_Id = R.Order_Id");
        $CI->db->where("RD.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();

        if(!empty($result) && is_array($result)):

               foreach ($result as $rows) { //p($rows);exit();

                    $data['Item_id'] = $rows->Item_Id;
                    $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                    $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                    $data['PD_Reserv_Qty'] = 0;
                    $data['Putaway_Date'] = $rows->Putaway_Date;
                    $data['Putaway_By'] = $rows->Putaway_By;

                    $data_list[] = $data;

            } // close have result

                if(!empty($data_list)):

                    $CI->db->insert_batch('STK_T_Inbound', $data_list);

                    $afftectedRows1 = $CI->db->affected_rows();

                    if($afftectedRows1 > 0):
                        return TRUE;
                    else:
                        return FALSE;
                    endif;

                else:

                    return FALSE;

                endif;

        else:

            return FALSE;

        endif;


    }

    #add function updateInboundForPK for defect 518 : by kik : 20131204
    function updateInboundForPK($old_Inbound_Id = NULL, $new_Inbound_Id = NULL, $picking_qty, $case = 1) {// Add by Ton 20130610 Edit by Ton! 20131018
        $CI = & get_instance();
//        $CI->load->database();
        #case 1 : Suggest_Location = Actual_Location
        #       - update inbound PK_Reserv_Qty - $picking_qty
        #case 2 : Suggest_Location != Actual_Location
        #       - update old inbound PD_Reserv_Qty - $picking_qty
        #       - update new inbound PD_Reserv_Qty + $picking_qty AND PK_Reserv_Qty + $picking_qty
//        echo "<br>".$old_Inbound_Id;
//        echo "<br>".$new_Inbound_Id;
//        echo "<br>".$picking_qty;
//        echo "<br>".$case;
//        exit();
        $sql2 = "";
        switch ($case) {
            case 1:
                $sql = "UPDATE STK_T_Inbound SET PK_Reserv_Qty = (PK_Reserv_Qty + $picking_qty) WHERE Inbound_Id = $old_Inbound_Id";
                break;
            case 2:
                if ($old_Inbound_Id != NULL && $new_Inbound_Id != NULL) {
                    $sql = "UPDATE STK_T_Inbound SET PD_Reserv_Qty = (PD_Reserv_Qty - $picking_qty)  WHERE Inbound_Id = $old_Inbound_Id";
                    $sql2 = "UPDATE STK_T_Inbound SET PD_Reserv_Qty = (PD_Reserv_Qty + $picking_qty),PK_Reserv_Qty = (PK_Reserv_Qty + $picking_qty) WHERE Inbound_Id = $new_Inbound_Id";
                }

                break;
        }
//        echo $sql;
//        echo $sql2;
        #start trans
//        $CI->db->trans_start();

        $CI->db->query($sql);
        $afftectedRows1 = $CI->db->affected_rows();

        if ($afftectedRows1 > 0) {
//            echo "1";

            if ($sql2 != "") {
//                echo "2";
                $CI->db->query($sql2);
//                p($CI->db->last_quety());
//                exit();
                $afftectedRows2 = $CI->db->affected_rows();
                if ($afftectedRows2 > 0) {
//                    echo "3";
//                    $CI->db->trans_commit();
                    return TRUE;
                } else {
//                    echo "4";
//                    $CI->db->trans_rollback();
                    return FALSE;
                }
            } else {
//                echo "5";
//                $CI->db->trans_commit();
                return TRUE;
            }
        } else {
//            echo "6";
//            $CI->db->trans_rollback();
            return FALSE;
        }
    }


    /**
     *
     * @function adjust_update_order work for update qty in INBOUND table
     * @param int $order_id
     * @return boolean (true=update all , false = some item not update)
     *
     * @last_modified : kik : 20140304
     *
     */
    function adjust_update_order($order_id) {
        $CI = & get_instance();
        $CI->db->select("*");
        $CI->db->from("STK_T_Order_Detail");
        $CI->db->where("Order_Id", $order_id);
        $CI->db->where("Active", "Y");  #add by kik (10-10-2013)
        $query = $CI->db->get();
        $afftectedRows=0;

        foreach ($query->result() as $row) {
            $info_sql = $CI->db->query("SELECT * FROM STK_T_Inbound WHERE Inbound_Id=" . $row->Inbound_Item_Id);
            $info = $info_sql->result();

            // Add By Akkarapol, 18/09/2013, เพิ่ม $oldAdjust ไปสำหรับว่า ทำ Adjust มามากกว่า 1 รอบ ก็จะเอาค่าเก่ามาคำนวนด้วย
            $oldAdjust = (!empty($info[0]->Adjust_Qty) ? $info[0]->Adjust_Qty : 0);


            //ADD BY POR 2013-12-12 เพิ่ม number_format เข้าไปเพื่อแก้ไขปัญหา ลบกันเหลือ 0 ค่าที่ได้จะไม่เท่ากับ 0 จริง แต่จะเป็นค่าที่ไม่สามารถหาค่าได้เช่น 3.99680288865E-15
            $new_balance = set_number_format(($info[0]->Receive_Qty - $info[0]->Dispatch_Qty - $oldAdjust) - $row->Confirm_Qty);
            $new_balance = str_replace(",", "", $new_balance); //+++++ADD BY POR 2013-12-12 ตัด comma ออกเพื่อให้สามารถบันทึกในรูปแบบ float ได้

            $new_adjust = $row->Confirm_Qty + $info[0]->Adjust_Qty;

            $new_PDreserv = (($info[0]->PD_Reserv_Qty - $row->Confirm_Qty) <= 0) ? 0 : $info[0]->PD_Reserv_Qty - $row->Confirm_Qty; #add by kik (10-10-2013)

            #add for set active = 'N' when balance <= 0  : by kik  : 2014-02-20
            $set_active = "";
            if($new_balance <= 0){
                $set_active = ",Active = 'N'";
            }
            #end add by kik : 20140220

            $update = $CI->db->query("UPDATE STK_T_Inbound SET
                                        Adjust_Qty=" . $new_adjust . "
                                        ,Balance_Qty=" . $new_balance . "
                                        ,PD_Reserv_Qty=" . $new_PDreserv.$set_active . "
                                     WHERE Inbound_Id=" . $row->Inbound_Item_Id);
            $afftectedRows += $CI->db->affected_rows();

        }

        if ($afftectedRows >= 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }

    }

    function insert_outbound($order_id) {
        $CI = & get_instance();
//        $CI->load->database();

        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->where("Order_Id", $order_id);
        $info_query = $CI->db->get();
        $order_info = $info_query->result();

        $CI->db->select("*");
        $CI->db->from("STK_T_Order_Detail");
        $CI->db->where("Order_Id", $order_id);
        $CI->db->order_by("Item_Id", "ASC");
        $query = $CI->db->get();
        $result = $query->result();
        $data = array();
        $data_list = array();
        if (is_array($result) && !empty($result)) {
            $insert = 'INSERT INTO STK_T_Outbound
							(Document_No
							, Doc_Refer_Int
							, Doc_Refer_Ext
							, Doc_Refer_Inv
							, Doc_Refer_CE
							, Doc_Refer_BL
							, Doc_Refer_AWB
							, Product_Id
							, Product_Code
							, Product_Status
							, Product_Sub_Status
							, Suggest_location_Id
							, Actual_location_Id
							, Pallet_Id
							, Dispatch_Type
							, Picking_Date
							, Picking_By
							, Dispatch_Date
							, Product_License
							, Product_Lot
							, Product_Serial
							, Dispatch_Qty
							, Inbound_Item_Id
							, Unit_Id
							, Owner_Id
							, Renter_Id
							, Activity_Involve
							, Flow_Id
                                                        , Item_Id
							) ';

            $unioun = "";
            foreach ($result as $rows) {
                $insert .= $unioun . " SELECT '" . $order_info[0]->Document_No . "'
									, '" . $order_info[0]->Doc_Refer_Int . "'
									, '" . $order_info[0]->Doc_Refer_Ext . "'
									, '" . $order_info[0]->Doc_Refer_Inv . "'
									, '" . $order_info[0]->Doc_Refer_CE . "'
									, '" . $order_info[0]->Doc_Refer_BL . "'
									, '" . $order_info[0]->Doc_Refer_AWB . "'
									, '" . $rows->Product_Id . "'
									, '" . $rows->Product_Code . "'
									, '" . $rows->Product_Status . "'
									, '" . $rows->Product_Sub_Status . "'
									, '" . $rows->Suggest_Location_Id . "'
									, '" . $rows->Actual_Location_Id . "'
									, '" . $rows->Pallet_Id . "'
									, '" . $order_info[0]->Doc_Type . "'
									, '" . $rows->Activity_Date . "'
									, '" . $rows->Activity_By . "'
									, '" . $order_info[0]->Actual_Action_Date . "'
									, '" . $rows->Product_License . "'
									, '" . $rows->Product_Lot . "'
									, '" . $rows->Product_Serial . "'
									, '" . $rows->Confirm_Qty . "'
									, '" . $rows->Inbound_Item_Id . "'
									, '" . $rows->Unit_Id . "'
									, '" . $order_info[0]->Owner_Id . "'
									, '" . $order_info[0]->Renter_Id . "'
									, '" . $rows->Activity_Involve . "'
									, '" . $rows->Flow_Id . "'
                                                                        , '" . $rows->Item_Id . "'
									";
                $unioun = " \n UNION ALL ";

                $upd = "UPDATE STK_T_Inbound SET Dispatch_Qty=Dispatch_Qty+" . $rows->Confirm_Qty . "
						, Balance_Qty=Balance_Qty-" . $rows->Confirm_Qty . "
						, PD_Reserv_Qty=PD_Reserv_Qty+" . $rows->Confirm_Qty . "
						, PK_Reserv_Qty=PK_Reserv_Qty+" . $rows->Confirm_Qty . "
						WHERE Inbound_Id=" . $rows->Inbound_Item_Id;
                $CI->db->query($upd);
            }
            $CI->db->query($insert);
        }
    }

    /* function pDispatchReservQty($order_detail){
      $CI =& get_instance();
      $CI->load->database();
      if(count($order_detail)>0){
      foreach($order_detail as $rows){
      $rows = (object) $rows;
      $sql = "UPDATE STK_T_Inbound SET PD_Reserv_Qty = (PD_Reserv_Qty + ".$rows->Reserv_Qty.") WHERE Inbound_Id = ".$rows->Inbound_Item_Id;
      $CI->db->query($sql);
      }
      }
      }
     */


    /**
     *
     * @param int $order_id
     * @return array['type_of_alert'][]['message']
     */
    public function addTransferStock($order_id) {

        $CI = & get_instance();

        /**
         * set Variable
         */
        $check_not_err = TRUE;
        $return = array();


        $CI->db->select("*");
        $CI->db->from("STK_T_Order");
        $CI->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $CI->db->where("STK_T_Order.Order_Id", $order_id);
        $query = $CI->db->get();
        $result = $query->result();


        /**
         * update Process
         */
        if($check_not_err):
            $data = array();
            $data_list = array();
            $location_prcv = $this->getPreReceiveArea();
            $CI->load->model("system_management_model", "sys"); // Debug Defect #364 Add by Ton! 20130829 ไม่ได้ load แล้วจะเรียกใช้ได้ไง?
            $receive_type_list = $CI->sys->getNormalReceiveType();
            if (!empty($receive_type_list)) :
                foreach ($receive_type_list as $rows) :
                    $receive_type = $rows->Dom_Code;
                endforeach;
            endif;
            if ($receive_type == "") : // ไม่รู้ว่าเอาไว้ทำอะไรเหมือนกัน แต่ไม่กล้าย้าย เพราะอาจจะต้องการเซ็ทเพื่อแปลงค่า แต่จริงๆแล้วน่าจะประกาศไว้ด้านบนได้เพื่อเป็นการประกาศค่า default ของตัวแปร
                $receive_type = "";
            endif;
            if (is_array($result) && !empty($result)) :
                foreach ($result as $rows) :
                    unset($data);
                    if ($rows->Confirm_Qty > 0) :
                        $data['Document_No'] = $rows->Document_No;
                        $data['Doc_Refer_Int'] = $rows->Doc_Refer_Int;
                        $data['Doc_Refer_Ext'] = $rows->Doc_Refer_Ext;
                        $data['Doc_Refer_Inv'] = $rows->Doc_Refer_Inv;
                        $data['Doc_Refer_CE'] = $rows->Doc_Refer_CE;
                        $data['Doc_Refer_BL'] = $rows->Doc_Refer_BL;
                        $data['Product_Id'] = $rows->Product_Id;
                        $data['Product_Code'] = $rows->Product_Code;
                        $data['Product_Status'] = $rows->Product_Status;
                        $data['Product_Sub_Status'] = $rows->Product_Sub_Status;
                        if (("" == $rows->Suggest_Location_Id) || (0 == $rows->Suggest_Location_Id)) :
                            $rows->Suggest_Location_Id = $location_prcv; # Default Activiity Area
                        endif;
                        if (("" == $rows->Actual_Location_Id) || (0 == $rows->Actual_Location_Id)) :
                            $rows->Actual_Location_Id = $location_prcv;  # Default Activiity Area
                        endif;
                        $data['Suggest_Location_Id'] = $rows->Suggest_Location_Id;
                        $data['Actual_Location_Id'] = $rows->Actual_Location_Id;
                        $data['Pallet_Id'] = $rows->Pallet_Id;
                        $data['Receive_Date'] = $rows->Actual_Action_Date;
                        $data['Receive_Type'] = $receive_type; //$rows->Doc_Type;
                        $data['Product_License'] = $rows->Product_License;
                        $data['Product_Lot'] = $rows->Product_Lot;
                        $data['Product_Serial'] = $rows->Product_Serial;
                        $data['Product_Mfd'] = $rows->Product_Mfd;
                        $data['Product_Exp'] = $rows->Product_Exp;
                        $data['Receive_Qty'] = $rows->Confirm_Qty;
                        $data['Balance_Qty'] = $rows->Confirm_Qty;
                        $data['PD_Reserv_Qty'] = 0;
                        $data['PK_Reserv_Qty'] = 0;
                        $data['Dispatch_Qty'] = 0;
                        $data['Adjust_Qty'] = 0;
                        $data['Owner_Id'] = $rows->Owner_Id;
                        $data['Renter_Id'] = $rows->Renter_Id;
                        $data['Unit_Id'] = $rows->Unit_Id;
                        $data['Putaway_Date'] = date('Y-m-d');
                        $data['Putaway_By'] = $rows->Activity_By;
                        $data['Is_Pending'] = 'N';
                        $data['Is_Partial'] = 'N';
                        $data['Is_Repackage'] = 'N';
                        $data['Price_Per_Unit'] = $rows->Price_Per_Unit;
                        $data['Unit_Price_Id'] = $rows->Unit_Price_Id;
                        $data['All_Price'] = $rows->All_Price;
                        $data['History_Item_id'] = $rows->Inbound_Item_Id;
                        $data['Flow_Id'] = $rows->Flow_Id;
                        $data_list[] = $data;
                    endif;
                endforeach;

                /**
                 * create Inbound
                 */
                $CI->db->insert_batch('STK_T_Inbound', $data_list);
                $afftectedRows = $CI->db->affected_rows();
                if ($afftectedRows <= 0) :
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not create Inbound.";
                    // break;
                endif;
            endif;
        endif;

        return $return;
    }

}
