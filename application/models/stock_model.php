<?php

class Stock_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function checkDocNo($DocNo) {// Add by Ton! 20130531
        $this->db->select("Order_Id");
        $this->db->where("Document_No", $DocNo);
        $this->db->from("STK_T_Order");
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function addOrder($order) {// Duplicate Function !!
        $this->db->set('Create_Date', 'GETDATE()', FALSE);
        $this->db->insert("STK_T_Order", $order);      
        return $this->db->insert_id();
    }

    function addOrderDetailByOneRecord($order_detail) {// Add by Ton! 20130531
        $this->db->insert("STK_T_Order_Detail", $order_detail);
        return $this->db->insert_id();
    }

    function addOrderDetail($order_detail) {// Duplicate Function !!
        $this->db->insert_batch('STK_T_Order_Detail', $order_detail);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    /**
     * function updateOrder for work update order table
     * @param array $order
     * @param array $where
     * @return boolean (true= update complete, false = update unsuccess)
     *
     * @last_modified : kik : 20140304 (add return value)
     *
     */
    function updateOrder($order, $where) {
        if (array_key_exists("Estimate_Action_Date", $order) && $order["Estimate_Action_Date"] != "") {
            $this->db->set("Estimate_Action_Date", "CONVERT(datetime, '" . $order["Estimate_Action_Date"] . "', 103)", FALSE);
            $this->db->where($where);
            $this->db->update("STK_T_Order");
        }
        if (array_key_exists("Actual_Action_Date", $order) && $order["Actual_Action_Date"] != "") {
            $this->db->set("Actual_Action_Date", "'" . $order["Actual_Action_Date"] . "'", FALSE); // Add By Akkarapol, 18/09/2013, เน€เธโ€ขเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธเธเน€เธย ' ' เน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธโ€เน€เธยเน€เธเธเน€เธเธ เน€เธยเน€เธเธเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธย SQL เน€เธยเน€เธเธเน€เธโ€”เน€เธเธ“เน€เธยเน€เธเธ’เน€เธยเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธโ€เน€เธย
            $this->db->where($where);
            $this->db->update("STK_T_Order");
        }

        //add real dispatch date for ISSUE 5265 : kik : 20141020
        if (array_key_exists("Real_Action_Date", $order) && $order["Real_Action_Date"] != "") {
            $this->db->set("Real_Action_Date", "CONVERT(datetime, '" . $order["Real_Action_Date"] . "', 103)", FALSE);
            $this->db->where($where);
            $this->db->update("STK_T_Order");
        }

        // Add By Ball
        if (array_key_exists("Modified_By", $order) && $order["Modified_By"] != "") {
            $this->db->set("Modified_By", $order["Modified_By"], FALSE);
            $this->db->where($where);
            $this->db->update("STK_T_Order");
        }

        $this->db->set('Modified_Date', 'GETDATE()', FALSE);
        unset($order["Estimate_Action_Date"]);
        unset($order["Actual_Action_Date"]);
        unset($order["Real_Action_Date"]); //add real dispatch date for ISSUE 5265 : kik : 20141020
        unset($order["Modified_Date"]);

        $this->db->where($where);
        $this->db->update("STK_T_Order", $order);
        $afftectedRows = $this->db->affected_rows();
//        p($this->db->last_query());
        if ($afftectedRows > 0):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    function _updateOrder($order, $where, $table = '') {// Add by Ton! 20130820
        $this->db->where($where);
        if ($table == '') {
            $this->db->update('STK_T_Order', $order);
        } else {
            $this->db->update($table, $order);
        }
//        echo $this->db->last_query();
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    function updateOrderDetail($order_detail, $where) {
        $this->db->where($where);
        $this->db->update("STK_T_Order_Detail", $order_detail);
//        echo $this->db->last_query();
//		log_message("ERROR", "Update Order Detail Query = " . $this->db->last_query());
        $afftectedRows = $this->db->affected_rows(); // Add by Ton! 20130521
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    /**
     * @funtion updateInboundDetail for work update inbound item , set updateDetail and where
     * @author Akkarapol 29/08/2013
     * @param array $setDataForUpdateInboundDetail
     * @param array $setDataWhere
     * @return boolean
     *
     * @last_modified kik : 20140307
     */
    function updateInboundDetail($setDataForUpdateInboundDetail, $setDataWhere) {

        $this->db->where($setDataWhere);
        $this->db->update("STK_T_Inbound", $setDataForUpdateInboundDetail);
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    function removeOrderDetail($item_list) {
        $this->db->where_in('Item_Id', $item_list);
        $this->db->delete('STK_T_Order_Detail');
        //echo $this->db->last_query();

        if ($this->db->affected_rows() >= 0) :
            return TRUE; //Update success.
        else :
            return FALSE; //Update unsuccess.
        endif;
    }

    /**
     *
     * @function reservPDReservQtyArray for work update PD_Reserv_Qty in inbound Table
     * @author THIDA : 20140304
     * @param array $order_detail
     * @param string(+,-) $operand
     * @return boolean (true=update all , false = some item not update)
     *
     * @last_modified xxxxxxxx (date)
     */
    function reservPDReservQtyArray($order_detail, $operand = "+") {
        $afftectedRows = TRUE;
        if (!empty($order_detail)) {
            foreach ($order_detail as $rows) {
                $rows = (object) $rows;
                // p($rows);
                if (!empty($rows->Inbound_Item_Id)):

                    // Condition find new Inbound Id from history Inbound Id
                    // กรณี inbound ที่เคย MM แล้ว ต้องหา inbound ล่าสุด
                    // 1. loop check หา inbound ที่ใช้ล่าสุด
                    $this->db->select("*");
                    $this->db->from("STK_T_inbound");
                    $this->db->where("History_Item_Id", $rows->Inbound_Item_Id);
                    $this->db->where("PD_Reserv_Qty", $rows->Confirm_Qty);
                    $this->db->order_by("Inbound_Id", 'DESC');
                    $query = $this->db->get();
                    // p($this->db->last_query());
                    // exit;
                    $query->result();
                    $new_inbound = $query->result_object[0]->Inbound_Id;
                    if(empty($new_inbound)){
                        $rows->Inbound_Item_Id;
                    }else{
                        $rows->Inbound_Item_Id = $new_inbound;
                    }
                    // p($rows->Inbound_Item_Id);
                    // exit;
                    // Condition find new Inbound Id from history Inbound Id
                    
                    $afftectedRows = $this->reservPDReservQty($rows->Inbound_Item_Id, $rows->Reserv_Qty, $operand);
                    if (!$afftectedRows):
                        return $afftectedRows;
                    endif;
                endif;
            }
        }
        return $afftectedRows;
    }

    /**
     * @function reservPDReservQty for work update PD_Reserv_Qty in inbound Table (only one inbound)
     * @author  THIDA : 20140304
     * @param int $inbound_id
     * @param float $pd_reserv_qty
     * @param string(+,-) $operand
     * @return boolean (update complete = ture , incomplete = false)
     *
     * @last_modified xxxxxxxx (date)
     */
    function reservPDReservQty($inbound_id, $pd_reserv_qty, $operand) {

        $set_value = "	PD_Reserv_Qty = CASE
            WHEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) <= 0  THEN 0
            WHEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) > 0  THEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " )
            ELSE PD_Reserv_Qty
            END";
        
        $sql = " UPDATE STK_T_Inbound SET " . $set_value . " WHERE Inbound_Id = " . $inbound_id;
        $this->db->query($sql);
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @function decreasePickingReserv for work update PK_Reserv_Qty type array in inbound table
     * @param array $order_detail
     * @return boolean (true=update all , false = some item not update)
     *
     */
    function decreasePickingReserv($order_detail) {

        $afftectedRows = TRUE;

        if (!empty($order_detail)) {

            foreach ($order_detail as $rows) {
                $update = (object) $rows;
                $this->db->set('PK_Reserv_Qty', 'PK_Reserv_Qty - ' . (int) $update->Confirm_Qty, FALSE);
                $this->db->where('Inbound_Id', $update->Inbound_Item_Id);
                $this->db->update('STK_T_Inbound');
                $afftectedRows = $this->db->affected_rows();

                if (!$afftectedRows) {
                    return $afftectedRows;
                }
            }
        }

        return $afftectedRows;
    }

    function clearPallet($order_id) {

        $data = new stdClass();
        $data->Active = 'N';
        $this->db->where('Order_Id', $order_id);
        $this->db->update('STK_T_Pallet', $data);
        $afftectedRows = $this->db->affected_rows();

        return TRUE;
    }

    /**
     * @function decreasePDReserv for work update PK_Reserv_Qty type array in inbound table
     * @param array $order_detail
     * @return boolean (true=update all , false = some item not update)
     *
     */
    function decreasePDReserv($order_detail, $isPickingStep = FALSE) {

        $afftectedRows = TRUE;

        if (!empty($order_detail)) {

            foreach ($order_detail as $rows) {
                $update = (object) $rows;
                $this->db->set('PD_Reserv_Qty', 'PD_Reserv_Qty - ' . (int) $update->Confirm_Qty, FALSE);
                $this->db->where('Inbound_Id', $update->Inbound_Item_Id);

                // Condition if didn't pick item clear PD
                if ($isPickingStep && (!is_null($update->Actual_Location_Id) && $update->Confirm_Qty )) {
                    $this->db->where('Inbound_Id', $update->Inbound_Item_Id);
                }
                $this->db->update('STK_T_Inbound');
                $afftectedRows = $this->db->affected_rows();

                if (!$afftectedRows) {
                    return $afftectedRows;
                }
            }
        }

        return $afftectedRows;
    }

    function decreasePDReservPicking($order_detail) {

        $afftectedRows = TRUE;

        if (!empty($order_detail)) {

            foreach ($order_detail as $rows) {
                // Condition if didn't pick item clear PD
                $update = (object) $rows;
                if (is_null($update->Actual_Location_Id) && (int) $update->Confirm_Qty == 0) {
                    $this->db->set('PD_Reserv_Qty', 'PD_Reserv_Qty - ' . (int) $update->Reserv_Qty, FALSE);
                    $this->db->where('Inbound_Id', $update->Inbound_Item_Id);
                    $this->db->update('STK_T_Inbound');
                    $afftectedRows = $this->db->affected_rows();

                    if (!$afftectedRows) {
                        return $afftectedRows;
                    }
                }
            }
        }

        return $afftectedRows;
    }

    /**
     * @function reservPickingArray for work update PK_Reserv_Qty type array in inbound table
     * @author  THIDA : 20140304
     * @param array $order_detail
     * @param string(+,-) $operand
     * @return boolean (true=update all , false = some item not update)
     *
     * @last_modified xxxxxxxx (date)
     *
     */
    function reservPickingArray($order_detail, $operand = "+") {
        $afftectedRows = TRUE;
        if (!empty($order_detail)) {
            foreach ($order_detail as $rows) {
                $rows = (object) $rows;
                if (!empty($rows->Confirm_Qty)) {
                    $afftectedRows = $this->reservPicking($rows->Inbound_Item_Id, $rows->Confirm_Qty, $operand);
                }
                if (!$afftectedRows) {
                    return $afftectedRows;
                }
            }
        }
        return $afftectedRows;
    }

    /**
     * @function reservPicking for work update PK_Reserv_Qty in inbound Table (only one inbound)
     * @param int $inbound_id
     * @param float $pd_reserv_qty
     * @param string(+,-) $operand
     * @return boolean (update complete = ture , incomplete = false)
     *
     * @last_modified xxxxxxxx (date)
     *
     */
    function reservPicking($inbound_id, $pd_reserv_qty, $operand) {

        $set_value = "PK_Reserv_Qty = CASE
            WHEN (PK_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) <= 0  THEN 0
            WHEN (PK_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) > 0  THEN (PK_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " )
            ELSE PK_Reserv_Qty
        END";

        $sql = " UPDATE STK_T_Inbound SET " . $set_value . " WHERE Inbound_Id = " . $inbound_id;

        $this->db->query($sql);

        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @function reservDispatch for work update Dispatch_Qty and PD_Reserv_Qty in inbound Table (only one inbound)
     * @param int $inbound_id
     * @param float $pd_reserv_qty
     * @param string(+,-) $operand
     * @return boolean (update complete = ture , incomplete = false)
     */
    function reservDispatch($inbound_id, $pd_reserv_qty, $operand) {

        $set_value = "	Dispatch_Qty = CASE
            WHEN (Dispatch_Qty " . $operand . " " . $pd_reserv_qty . " ) <= 0  THEN 0
            WHEN (Dispatch_Qty " . $operand . " " . $pd_reserv_qty . " ) > 0  THEN (Dispatch_Qty " . $operand . " " . $pd_reserv_qty . " )
            ELSE Dispatch_Qty
            END
            ";

        $set_value .= ",PD_Reserv_Qty = CASE
            WHEN (PD_Reserv_Qty - " . $pd_reserv_qty . " ) <= 0  THEN 0
            WHEN (PD_Reserv_Qty - " . $pd_reserv_qty . " ) > 0  THEN (PD_Reserv_Qty - " . $pd_reserv_qty . " )
            ELSE PD_Reserv_Qty
            END
            ";
        $set_value .= ",PK_Reserv_Qty = CASE
            WHEN (PK_Reserv_Qty - " . $pd_reserv_qty . " ) <= 0  THEN 0
            WHEN (PK_Reserv_Qty - " . $pd_reserv_qty . " ) > 0  THEN (PK_Reserv_Qty - " . $pd_reserv_qty . " )
            ELSE PK_Reserv_Qty
            END
            ";

        $sql = " UPDATE STK_T_Inbound SET " . $set_value . " WHERE Inbound_Id = " . $inbound_id;

        $this->db->query($sql);

        $afftectedRows = $this->db->affected_rows();

        unset($inbound_id);
        unset($pd_reserv_qty);
        unset($operand);

        if ($afftectedRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * function openActionForm use for open putaway
     *
     * modified : kik  20140708 : for add column invoice and contianer and Tuning code
     * modified : kik : 20140814 เพิ่ม parameter ตัวที่ 3 $confirm_more_zero เพื่อตรวจสอบให้ confirm_qty มากกว่า 0 มาแสดงเท่านั้น
     */
    function getOrderDetail($order_id, $confirm_more_zero = false, $order_by = "STK_T_Order_Detail.Item_Id ASC", $sub_module = false, $module = false) {

        #Load config (add by kik : 20140708)
        $conf = $this->config->item('_xml'); // By ball : 20140707
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        $state = "";
        if ($sub_module != "" and $module != ""):
            $state = "AND STK_T_Logs_Action.Sub_Module='$sub_module' AND STK_T_Logs_Action.Module = '$module'";
        elseif ($module != ""):
            $state = "AND STK_T_Logs_Action.Module = '$module'";
        endif;

        /**
         * ====================================================================================================
         * Select Zone
         */
        $this->db->select("DISTINCT
        	CONVERT(VARCHAR(10),STK_T_Order_Detail.Product_Mfd,103) as Product_Mfd
                ,CONVERT(VARCHAR(10),STK_T_Order_Detail.Product_Exp,103) as Product_Exp
        	,STK_T_Order_Detail.Product_Code
        	,STK_T_Order_Detail.Product_Lot
        	,STK_T_Order_Detail.Product_Serial
        	,STK_T_Order_Detail.Reserv_Qty
        	,STK_T_Order_Detail.Confirm_Qty
        	,STK_T_Order_Detail.Activity_Code
        	,STK_T_Order_Detail.Product_Id
        	,STK_T_Order_Detail.Unit_Id
        	,STK_T_Order_Detail.Suggest_Location_Id
        	,STK_T_Order_Detail.Actual_Location_Id
        	,STK_T_Order_Detail.Activity_Date
        	,STK_T_Order_Detail.Actual_Location_Id
        	,STK_T_Order_Detail.Reason_Remark
        	,STK_T_Order_Detail.Remark
        	,STK_M_Product.Supplier_Id
                ,STK_M_Product.Product_NameEN AS Full_Product_Name
                ,SUBSTRING(STK_M_Product.Product_NameEN,0,30)  AS Product_Name
                ,S1.public_name AS Unit_Value
                ,S2.Dom_Code   AS Status_Code
                ,S2.Dom_EN_Desc AS Status_Value
                ,S3.Dom_Code   AS Sub_Status_Code
                ,S3.Dom_EN_Desc AS Sub_Status_Value
                ,L1.Location_Code AS Suggest_Location
                ,L2.Location_Code AS Actual_Location
                ,STK_T_Order_Detail.Item_Id
                ,STK_T_Order_Detail.Product_Code
                ,STK_T_Order_Detail.Product_Lot
                ,STK_T_Order_Detail.Product_Serial
                ,STK_T_Order_Detail.Reserv_Qty
                ,STK_T_Order_Detail.Confirm_Qty
                ,STK_T_Order_Detail.Activity_Code
                ,STK_T_Order_Detail.Product_Id
                ,STK_T_Order_Detail.Unit_Id
                ,STK_T_Order_Detail.Suggest_Location_Id
                ,STK_T_Order_Detail.Actual_Location_Id
                ,STK_T_Order_Detail.Activity_Date
                ,STK_T_Order_Detail.Actual_Location_Id
                ,STK_T_Order_Detail.Remark
                ,Supplier_Id
                ,PutAway_Rule
                ,Split_From_Item_Id
                ,STK_T_Order_Detail.Inbound_Item_Id
        ");

        if (($sub_module != "" and $module != "") || ($sub_module != NULL and $module != NULL)):
            $this->db->select("(activity_by.First_NameTH+' '+activity_by.Last_NameTH)  as Activity_By_Name,STK_T_Logs_Action.Activity_By,CONVERT(VARCHAR(20),STK_T_Order_Detail.Activity_Date,120) as Activity_Dat");
        endif;


        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->select("STK_T_Order_Detail.Invoice_Id
                ,STK_T_Invoice.Invoice_No");
        endif; // end of query of Invoice
        #check query of Container (by kik : 20140708)
        if ($conf_cont):
            $this->db->select("STK_T_Order_Detail.Cont_Id
                ,CTL_M_Container.Cont_No
                ,CTL_M_Container_Size.Cont_Size_No
                ,CTL_M_Container_Size.Cont_Size_Unit_Code");
        endif; // end of query of Container
        #check query of Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            $this->db->select("Price_Per_Unit
                ,Unit_Price_Id
                ,All_Price
                ,unitprice.Dom_EN_Desc unitprice_name");
        endif; // end of query of Price per unit
        #check query of Pallet (by kik : 20140708)
        if ($conf_pallet):
            $this->db->select("STK_T_Order_Detail.Pallet_Id
                ,STK_T_Pallet.Pallet_Code");
        endif; // end of query of Pallet



        /**
         * ====================================================================================================
         * From/Join Zone
         */
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Id	= STK_M_Product.Product_Id");


        $this->db->join("CTL_M_UOM_Template_Language S1", "STK_T_Order_Detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("SYS_M_Domain S2", "STK_T_Order_Detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S3", "STK_T_Order_Detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");
        $this->db->join("STK_M_Location L1", "STK_T_Order_Detail.Suggest_Location_Id = L1.Location_Id", "LEFT");
        $this->db->join("STK_M_Location L2", "STK_T_Order_Detail.Actual_Location_Id = L2.Location_Id", "LEFT");


        if (($sub_module != "" and $module != "") || ($sub_module != NULL and $module != NULL)):
            $this->db->join("STK_T_Logs_Action", "STK_T_Logs_Action.Order_Id = STK_T_Order.Order_Id AND STK_T_Logs_Action.Item_Id=STK_T_Order_Detail.Item_Id $state", "LEFT");
            $this->db->join("ADM_M_UserLogin", "ADM_M_UserLogin.UserLogin_Id = STK_T_Logs_Action.Activity_By", "LEFT");
            $this->db->join("CTL_M_Contact activity_by", "ADM_M_UserLogin.Contact_Id = activity_by.Contact_Id", "LEFT");
        endif;

        if ($conf_pallet):
            $this->db->join("STK_T_Pallet", "STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        endif;


        #check query of Price per unit (by kik : 20140708)
        if ($conf_price_per_unit):
            $this->db->join("SYS_M_Domain unitprice", "STK_T_Order_Detail.Unit_Price_Id = unitprice.Dom_Code AND unitprice.Dom_Active = 'Y'", "LEFT");
        endif; // end of query of Price per unit
        #check query of Invoice (by kik : 20140708)
        if ($conf_inv):
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = STK_T_Order_Detail.Invoice_Id", "LEFT");
        endif; // end of query of Invoice
        #check query of Container  (by kik : 20140708)
        if ($conf_cont):
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = STK_T_Order_Detail.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");
        endif; // end of query of Container


        /**
         * ====================================================================================================
         * Where Zone
         */
        $this->db->where("STK_T_Order.Order_Id", $order_id);

        $this->db->where("STK_T_Order_Detail.Active", "Y");

        if ($confirm_more_zero):
            $this->db->where("STK_T_Order_Detail.Confirm_Qty > 0");
        endif;

        $this->db->order_by($order_by);
        $query = $this->db->get();
        return $query->result();
    }

    function getProductOrder($order_id, $query_column = "") {
        if ("" == $query_column) {
            $this->db->select(" Product_Id as Id,Product_Code,Product_NameEN,Product_NameTH,Standard_Unit_Id,FG_LICSE,IsMachine");
        } else {
            $this->db->select(" Product_Id as Id ," . $query_column);
        }
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Order_Id", $order_id);
        $query = $this->db->get();
        return $query;
    }

    function getCountingStock($stock) {
        $this->db->insert("STK_T_Counting", $stock);
        return $this->db->insert_id();
    }

    function getOrderByDocNo($document_no) {// Duplicate Function !!
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id  = STK_T_Order.Order_Id");
        $this->db->where("Document_No", $document_no);
        $query = $this->db->get();
        return $query->result();
    }

    #add select est. balance qty : by kik 11-10-2013

    function getProductInStockArray($item_list) {
        $this->db->select("I.*,I.Actual_Location_Id AS Location_Id,l.Location_Code,P.Product_NameEN
			,S1.public_name AS Unit_Value
			,S2.Dom_Code    AS Status_Code
			,S2.Dom_EN_Desc AS Status_Value
			,S3.Dom_Code    AS Sub_Status_Code
			,S3.Dom_EN_Desc AS Sub_Status_Value
                        ,I.Balance_Qty  AS Current_Balance_Qty
			,CONVERT(VARCHAR(10),I.Product_Mfd,103) as Product_Mfd
			,CONVERT(VARCHAR(10),I.Product_Exp,103) as Product_Exp
                        ,(I.Receive_Qty - I.PD_Reserv_Qty - I.Dispatch_Qty - I.Adjust_Qty) as Est_Balance_Qty
                        ,Price_Per_Unit
                        ,unitprice.Dom_EN_Desc as unit_price
                        ,All_Price
                        ,I.Unit_Price_Id
                        ,pallet.Pallet_Code
		");
        $this->db->from("STK_T_Inbound I");
        $this->db->join("STK_M_Location L", "I.Actual_Location_Id=L.Location_Id", "left");
        $this->db->join("STK_M_Product P", "I.Product_Id=P.Product_Id", "LEFT");
        $this->db->join("STK_M_Storage D", "l.Storage_Id = D.Storage_Id");
        $this->db->join("STK_M_Storage_Type E", "d.StorageType_Id = E.StorageType_Id");


        $this->db->join("CTL_M_UOM_Template_Language S1", "I.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");

        $this->db->join("SYS_M_Domain S2", "I.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'");
        $this->db->join("SYS_M_Domain S3", "I.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", "LEFT");
        //ADD BY POR 2014-01-16 เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธเธเน€เธโ€เน€เธยเน€เธยเน€เธเธ—เน€เธยเน€เธเธ unit price เน€เธยเน€เธเธเน€เธยเน€เธโ€“เน€เธเธเน€เธยเน€เธโ€ขเน€เธยเน€เธเธเน€เธย
        $this->db->join("SYS_M_Domain unitprice", "I.Unit_Price_Id = unitprice.Dom_Code AND unitprice.Dom_Host_Code='PRICE_UNIT' AND unitprice.Dom_Active = 'Y'", "LEFT");

        $this->db->join("STK_T_Pallet pallet", "pallet.Pallet_Id=I.Pallet_Id", "left"); //add for ISSUE 3334 : by kik : 20140220

        $this->db->where("E.StorageType_Code != ", "ST05");
        $this->db->where("I.Active", ACTIVE);
        $this->db->where_in("I.Inbound_Id", $item_list);
//		$this->db->where("(I.Receive_Qty - (I.Dispatch_Qty + I.Adjust_Qty))>0"); (comment by kik : 06-09-2013)
        $this->db->where("I.Balance_Qty > 0"); // add by kik :06-09-2013
        $this->db->order_by("I.Inbound_Id", "ASC");
        $query = $this->db->get();
        return $query;
    }

    function getStockDetailId($column, $where) {
        $this->db->select($column);
        $this->db->where($where);
        $this->db->from("STK_T_Inbound");
        $query = $this->db->get();
        return $query;
    }

    //END ADD
    // Add By Akkarapol, 16/12/2013, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธย get_duplicate_ext_doc เน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธโฌเน€เธยเน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธ’ ext doc เน€เธโ€”เน€เธเธ•เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ‘เน€เธยเน€เธย เน€เธเธเน€เธเธ•เน€เธยเน€เธยเน€เธเธ’เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธยเน€เธเธ…เน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธ—เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธย เน€เธยเน€เธโ€เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธย WITH เน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธเธเน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธโ€ขเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธเธ’ เน€เธโ€“เน€เธยเน€เธเธ’ Order เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธย เน€เธโฌเน€เธยเน€เธยเน€เธย Partial เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธ…เน€เธยเน€เธเธ เน€เธยเน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธเธ’เน€เธเธเน€เธเธ’เน€เธเธเน€เธโ€“เน€เธเธเน€เธเธ• ext doc เน€เธยเน€เธยเน€เธเธ“เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธโ€เน€เธย
    function get_duplicate_ext_doc($column, $where, $flow_id = NULL) {
        $this->db->start_cache();
        if (empty($flow_id)):
            $flow_id = 0;
        endif;
        $sql = "
                ;WITH TmpTable
                AS
                (
                   SELECT a.Order_Id, a.Parent_Order_Id, 0 AS Level
                   FROM STK_T_Order a
                   WHERE a.Parent_Order_Id = (SELECT Parent_Order_Id FROM STK_T_Order WHERE Flow_Id = {$flow_id})
                   UNION ALL
                   SELECT p.Order_Id, p.Parent_Order_Id, c.Level + 1
                   FROM TmpTable c
                   INNER JOIN STK_T_Order p ON c.Parent_Order_Id = p.Order_Id
                )
                SELECT Order_Id FROM TmpTable
                WHERE Level != 0
              ";

        $query = $this->db->query($sql);
        $list_id = $query->result_array();
        $id_list = '';
        if (!empty($list_id)):
            foreach ($list_id as $key_list => $list):
                if ($id_list == ''):
                    $id_list = $list['Order_Id'];
                else:
                    $id_list = $id_list . ',' . $list['Order_Id'];
                endif;
                $arr_list[] = $list['Order_Id'];
            endforeach;
        endif;
        $this->db->stop_cache();
        $this->db->flush_cache();
        $this->db->select($column);
        $this->db->from("STK_T_Order");
        if (!empty($arr_list)):
            $this->db->where_not_in('Order_Id', $arr_list);
        endif;
        $this->db->where($where);
        $this->db->join('STK_T_Workflow', 'STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id AND STK_T_Workflow.Present_State != "-1"', 'INNER');

        $query = $this->db->get();
//        p($this->db->last_query());
        return $query;
    }

    // END Add By Akkarapol, 16/12/2013, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธย get_duplicate_ext_doc เน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธโฌเน€เธยเน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธ’ ext doc เน€เธโ€”เน€เธเธ•เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ‘เน€เธยเน€เธย เน€เธเธเน€เธเธ•เน€เธยเน€เธยเน€เธเธ’เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธยเน€เธเธ…เน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธ—เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธย เน€เธยเน€เธโ€เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธย WITH เน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธเธเน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธโ€ขเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธเธ’ เน€เธโ€“เน€เธยเน€เธเธ’ Order เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธย เน€เธโฌเน€เธยเน€เธยเน€เธย Partial เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธ…เน€เธยเน€เธเธ เน€เธยเน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธเธ’เน€เธเธเน€เธเธ’เน€เธเธเน€เธโ€“เน€เธเธเน€เธเธ• ext doc เน€เธยเน€เธยเน€เธเธ“เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธโ€เน€เธย
//    ADD BY POR 2013-11-13 เน€เธเธเน€เธเธเน€เธยเน€เธเธ’เน€เธย function เน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธยเน€เธโ€เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธโ€เน€เธเธ’เน€เธโ€ขเน€เธยเน€เธเธ’เน€เธโ€”เน€เธเธ•เน€เธยเน€เธโ€ขเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธเธ’เน€เธเธ select เน€เธยเน€เธเธ…เน€เธเธ เน€เธโฌเน€เธยเน€เธเธ—เน€เธยเน€เธเธเน€เธยเน€เธยเน€เธย where เน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธ’
    function getOrderTable($column, $where) {
        $this->db->select($column);
        $this->db->where($where);
        $this->db->from("STK_T_Order");
        $query = $this->db->get();
        return $query->result();
    }

    //END ADD
    //ADD BY KIK 2013-12-26 เน€เธเธเน€เธเธเน€เธยเน€เธเธ’เน€เธย function เน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธยเน€เธโ€เน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธโ€เน€เธเธ’เน€เธโ€ขเน€เธยเน€เธเธ’เน€เธโ€”เน€เธเธ•เน€เธยเน€เธโ€ขเน€เธยเน€เธเธเน€เธยเน€เธยเน€เธเธ’เน€เธเธ select เน€เธยเน€เธเธ…เน€เธเธ เน€เธโฌเน€เธยเน€เธเธ—เน€เธยเน€เธเธเน€เธยเน€เธยเน€เธย where เน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธ’
    function getRelocateTable($column, $where) {
        $this->db->select($column);
        $this->db->where($where);
        $this->db->from("STK_T_Relocate");
        $query = $this->db->get();
        return $query->result();
    }

    // Add By Akkarapol, 06/09/2013,  เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธย get เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธ…เน€เธยเน€เธเธเน€เธยเน€เธโ€ขเน€เธเธ’เน€เธเธเน€เธเธ’เน€เธย Inbound เน€เธโ€เน€เธยเน€เธเธเน€เธเธ Document_No เน€เธยเน€เธเธ…เน€เธเธ Product_Id
    function getInboundDetailByDocNoAndProductId($docNo, $productId) {// Duplicate Function !!
        $this->db->select("*");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Document_No", $docNo);
        $this->db->where("Product_Id", $productId);
        $query = $this->db->get();
        return $query->result();
    }

    function getOrderDetailByItemId($itemId) {
        $this->db->select("*");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Item_Id", $itemId);
        $query = $this->db->get();
        return $query->row_array();
    }

    // END Add By Akkarapol, 11/09/2013, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธย เน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธย get เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธ…เน€เธยเน€เธเธเน€เธยเน€เธโ€ขเน€เธเธ’เน€เธเธเน€เธเธ’เน€เธย Order_Detail เน€เธโ€เน€เธยเน€เธเธเน€เธเธ Item_Id
    #add by kik , 27-09-2013 เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธย get order detail เน€เธโ€เน€เธยเน€เธเธเน€เธเธ order id
    #add parameter colunm , $where_detail by kik : 20140213
    function getOrderDetailByOrderId($orderId, $colunm = "*", $where_detail = "") {// Duplicate Function !!
        $this->db->select($colunm);
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Order_Id", $orderId);
        $this->db->where("Active", 'Y');

        if ($where_detail != "") {
            $this->db->where($where_detail);
        }

        $query = $this->db->get();
        return $query->result();
    }


    function getRelocateDetailByOrderId($orderId) {
        $this->db->select("*");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("Order_Id", $orderId);
        $query = $this->db->get();
        return $query->result();
    }

    function getBalanceByInboundId($inboundId) {
        $this->db->select("PD_Reserv_Qty");
        $this->db->select("Dispatch_Qty");
        $this->db->select("Balance_Qty");
        $this->db->select("(Balance_Qty - PD_Reserv_Qty) as remain_qty");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Inbound_Id", $inboundId);
        $this->db->where("Active", 'Y');
        $query = $this->db->get();
        return $query->result();
    }

    function getSumReservQtyOrderDetailByOrderId($orderId, $inboundId) {
        $this->db->select("sum(Reserv_Qty) as sumReserv");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Order_Id", $orderId);
        $this->db->where("Inbound_Item_Id", $inboundId);
        $this->db->where("Active", 'Y');
        $this->db->group_by("Order_Id", "Inbound_Item_Id");
        $query = $this->db->get();
//                p($this->db->last_query());
        return $query->row_array();
//		return $query->result();
    }

    function getSumReservQtyRelocateDetailByOrderId($orderId, $inboundId) {
        $this->db->select("sum(Reserv_Qty) as sumReserv");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("Order_Id", $orderId);
        $this->db->where("Inbound_Item_Id", $inboundId);
        //$this->db->where("Active",'Y');
        $this->db->group_by("Order_Id", "Inbound_Item_Id");
        $query = $this->db->get();
        //p($this->db->last_query());
        return $query->row_array();
        //return $query->result();
    }

    function validateReservQtyRelocateDetailByOrderId($orderId, $inboundId, $pre_dispatch_area_id) {
        $this->db->select("ISNULL(Confirm_Qty,0) As sumReserv");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("Inbound_Item_Id", $inboundId);
        $this->db->where("Actual_Location_Id Is Null");
        $this->db->where("Old_Location_Id", $pre_dispatch_area_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    function getOldReservQtyOrderDetailByItemId($itemId) {
        $this->db->select("Reserv_Qty");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Item_Id", $itemId);
        $this->db->where("Active", 'Y');
        $query = $this->db->get();
        return $query->row_array();
    }

    function getOldReservQtyRelocateDetailByItemId($itemId) {
        $this->db->select("Reserv_Qty");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("Item_Id", $itemId);
        $query = $this->db->get();
        return $query->row_array();
    }

    // END Add By Akkarapol, 08/10/2013, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธยเน€เธเธ’ reservQty เน€เธโฌเน€เธยเน€เธเธ—เน€เธยเน€เธเธเน€เธยเน€เธเธ“เน€เธยเน€เธยเน€เธยเน€เธยเน€เธยเน€เธโฌเน€เธยเน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธ’เน€เธยเน€เธยเน€เธเธ’เน€เธโ€”เน€เธเธ•เน€เธยเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธย เน€เธโฌเน€เธโ€”เน€เธยเน€เธเธ’เน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธโ€”เน€เธเธ•เน€เธยเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธเธเน€เธเธ—เน€เธเธเน€เธยเน€เธเธเน€เธย เน€เธโฌเน€เธยเน€เธเธ—เน€เธยเน€เธเธเน€เธโฌเน€เธเธเน€เธเธ’เน€เธยเน€เธยเน€เธยเน€เธยเน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ’เน€เธเธ update PD_reserv เน€เธยเน€เธย inbound เน€เธยเน€เธเธเน€เธยเน€เธโ€“เน€เธเธเน€เธยเน€เธโ€ขเน€เธยเน€เธเธเน€เธย
    // Add By Akkarapol, 30/01/2014, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธย Reserv_Qty เน€เธโ€เน€เธยเน€เธเธเน€เธเธ Order_Id เน€เธยเน€เธเธ…เน€เธเธ Product_Code
    function getSumReservQtyOrderDetailByProductCode($orderId, $product_code) {
        $this->db->select("sum(Reserv_Qty) as sumReserv");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Order_Id", $orderId);
        $this->db->where("Product_Code", $product_code);
        $this->db->where("Active", 'Y');
        $this->db->group_by("Order_Id", "Product_Code");
        $query = $this->db->get();
//                p($this->db->last_query());
        return $query;
    }

    // END Add By Akkarapol, 30/01/2014, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธย Reserv_Qty เน€เธโ€เน€เธยเน€เธเธเน€เธเธ Order_Id เน€เธยเน€เธเธ…เน€เธเธ Product_Code
    // Add By Akkarapol, 30/01/2014, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธย PD_Reserv_Qty, Balance_Qty, เน€เธยเน€เธเธ…เน€เธเธเน€เธยเน€เธเธ…เน€เธโ€ขเน€เธยเน€เธเธ’เน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธ’เน€เธย เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธโ€”เน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธย
    function getBalanceByProductCode($product_code) {
        $this->db->select("SUM(PD_Reserv_Qty) as PD_Reserv_Qty");
        $this->db->select("SUM(Balance_Qty) as Balance_Qty");
        $this->db->select("(SUM(Balance_Qty)-SUM(PD_Reserv_Qty)) as remain_qty");
        $this->db->from("STK_T_Inbound");
        $this->db->where("Product_Code", $product_code);
        $this->db->where("Active", 'Y');
        $query = $this->db->get();
//                p($this->db->last_query());
        return $query;
    }

    // END Add By Akkarapol, 30/01/2014, เน€เธโฌเน€เธยเน€เธเธ”เน€เธยเน€เธเธเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธยเน€เธยเน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธ“เน€เธเธเน€เธเธเน€เธเธ‘เน€เธยเน€เธเธเน€เธเธ’เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธย PD_Reserv_Qty, Balance_Qty, เน€เธยเน€เธเธ…เน€เธเธเน€เธยเน€เธเธ…เน€เธโ€ขเน€เธยเน€เธเธ’เน€เธยเน€เธเธเน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธ’เน€เธย เน€เธยเน€เธเธ…เน€เธเธเน€เธเธเน€เธเธเน€เธยเน€เธเธเน€เธยเน€เธโ€”เน€เธเธ‘เน€เธยเน€เธยเน€เธเธเน€เธเธเน€เธย
    //ADD BY POR 2014-06-16 เพิ่ม function sum Confrim ใน relocation_detail
    function getSumConfirmQtyRelocateDetailByOrderId($orderId, $inboundId) {
        $this->db->select("sum(Confirm_Qty) as sumReserv");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->where("Order_Id", $orderId);
        $this->db->where("Inbound_Item_Id", $inboundId);
        $this->db->group_by("Order_Id", "Inbound_Item_Id");
        $query = $this->db->get();
        return $query->row_array();
    }

    //END ADD
    //END ADD
    //ADD BY POR 2014-07-10 function get order_detail and invoice
    function getOrderDetailInvoice($item_id) {
        $this->db->select("Invoice_No");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Invoice", "STK_T_Order_Detail.Invoice_Id = STK_T_Invoice.Invoice_Id", "LEFT");
        $this->db->where("Item_Id", $item_id);

        $query = $this->db->get();

        return $query;
    }

    function removeOnhandHistory($inbound_list) {
        $this->db->where_in('Inbound_Id', $inbound_list);
        $this->db->delete('STK_T_Onhand_History');
        //echo $this->db->last_query();

        if ($this->db->affected_rows() >= 0) :
            return TRUE; //Update success.
        else :
            return FALSE; //Update unsuccess.
        endif;
    }

    function removeInbound($inbound_list) {
        $this->db->where_in('Inbound_Id', $inbound_list);
        $this->db->delete('STK_T_Inbound');
        if ($this->db->affected_rows() >= 0) :
            return TRUE; //Update success.
        else :
            return FALSE; //Update unsuccess.
        endif;
    }

    function getOrderDetailById($order_id = NULL , $item_id = NULL) {

        $this->db->select("od.Doc_Refer_Ext");
        $this->db->select("prd.Product_NameEn");
        $this->db->select("od.Actual_Action_Date");
        $this->db->select("od.Doc_Refer_Ext");
        $this->db->select("od.Doc_Refer_Inv");
        $this->db->select("dt.Product_Lot");
        $this->db->select("dt.Product_Serial");
        $this->db->select("dt.Invoice_Id");
        $this->db->select("dt.Confirm_Qty");
        $this->db->select("dt.Reserv_Qty");
        $this->db->select("dt.Product_Status");
        $this->db->select("(ctn.Cont_No + ' ' + ctns.Cont_Size_Unit) AS ContainerName");
        $this->db->select("cpn.Company_NameEN");
        $this->db->select("(SELECT Reserv_Qty FROM STK_T_Order_Detail WHERE Item_Id = dt.Pallet_From_Item_Id) As Parent_Qty");

        // JOIN 
        $this->db->join("STK_T_Order_Detail dt" , "dt.Order_Id = od.Order_Id");
        $this->db->join("STK_M_Product prd" , "prd.Product_Id = dt.Product_Id");
        $this->db->join("CTL_M_Container ctn", "ctn.Cont_Id = dt.Cont_Id", "LEFT");
        $this->db->join("CTL_M_Container_Size ctns", "ctns.Cont_Size_Id = ctn.Cont_Size_Id", "LEFT");
        $this->db->join("CTL_M_Company cpn", "cpn.Company_Id = od.Renter_Id AND cpn.Active = '1' ");

        // WHERE
        $this->db->where("od.Order_Id", $order_id);
        $this->db->where("dt.Item_Id", $item_id);

        $query = $this->db->get("STK_T_Order od");

        //echo $this->db->last_query(); exit;

        return $query->row();
    }

    function getProductList($order_id = NULL) {

        $this->db->select("prd.Product_NameEn");

        // JOIN
        $this->db->join("STK_T_Order_Detail dt" , "dt.Order_Id = od.Order_Id");
        $this->db->join("STK_M_Product prd" , "prd.Product_Id = dt.Product_Id");

        // WHERE
        $this->db->where("od.Order_Id", $order_id);
        $this->db->group_by("prd.Product_NameEn");

        $query = $this->db->get("STK_T_Order od");

        return $query->result();
    }

    function fine_destination_id($Destination_Id) {

        $this->db->select("*");
        $this->db->from("CTL_M_Company");
        $this->db->where("Company_Code", $Destination_Id);
        $query = $this->db->get();
        $res = $query->result();

        if(empty($res)){
            return $query = 1;
        }else{
            return $res[0]->Company_Id;
        }        
    }


}
