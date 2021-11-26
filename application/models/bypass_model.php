<?php

class bypass_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function find_inbound($Product_Code) {
        $this->db->select("top 1 vw_inbound.* , inb.Document_No , inb.Doc_Refer_Int , inb.Doc_Refer_Inv , inb.Doc_Refer_CE , inb.Doc_Refer_BL , inb.Doc_Refer_AWB ");
        $this->db->from("vw_inbound");
         $this->db->join("STK_T_Inbound inb","inb.Inbound_Id = vw_inbound.Inbound_Id");
        $this->db->where("vw_inbound.Product_Code",$Product_Code);
	$this->db->where("vw_inbound.location_code not in (select location_code from master_tmp_ where product_code = '".$Product_Code."')");
//        $this->db->where("vw_inbound.Product_Lot",$Product_Lot);
//        $this->db->where("vw_inbound.Renter_Id",$renter);
//        $this->db->where("vw_inbound.Owner_Id",$owner);
  
        $this->db->where("vw_inbound.Product_Status","NORMAL");
        $this->db->where("vw_inbound.Est_Balance_Qty > 0");
//        $this->db->order_by("vw_inbound.Inbound_Id", "asc");
        $this->db->order_by("CASE PutAway_Rule
                                WHEN 'FIFO'
                                    THEN CASE vw_inbound.Receive_Date WHEN NULL THEN 0 ELSE vw_inbound.Receive_Date END
                                WHEN 'FEFO'
                                    THEN CASE vw_inbound.Product_Mfd_sort WHEN NULL THEN 0 ELSE vw_inbound.Product_Mfd_sort END
                                ELSE vw_inbound.Actual_Location_Id
                               END");
        $query = $this->db->get();
//        p($this->db->last_query());
        return $query;
    }
    
    function get_inbound_for_import_tf($Product_Code,$Product_Lot,$renter,$owner) {
        $this->db->select("*");
        $this->db->from("vw_inbound_transfer");
        $this->db->where("vw_inbound_transfer.Product_Code",$Product_Code);
        $this->db->where("vw_inbound_transfer.Product_Lot",$Product_Lot);
        $this->db->where("vw_inbound_transfer.Renter_Id",$renter);
        $this->db->where("vw_inbound_transfer.Owner_Id",$owner);
        $this->db->where("vw_inbound_transfer.Est_Balance_Qty > 0");
        $this->db->order_by("vw_inbound_transfer.Inbound_Id", "asc");
        $query = $this->db->get();
        
        return $query;
    }
    
    function reservPDReservQtyArray($order_detail, $operand = "+") {
        $afftectedRows = TRUE;
        if (!empty($order_detail)) {
            foreach ($order_detail as $rows) {
                $rows = (object) $rows;
                if (!empty($rows->Inbound_Item_Id)):
                    $afftectedRows = $this->reservPDReservQty($rows->Inbound_Item_Id, $rows->Reserv_Qty, $operand);
                    if (!$afftectedRows):
                        return $afftectedRows;
                    endif;
                endif;
            }
        }
        return $afftectedRows;
    }
    
    function reservPDReservQty($inbound_id, $pd_reserv_qty, $operand) {

        $set_value = "	PD_Reserv_Qty = CASE
        WHEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) <= 0  THEN 0
        WHEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " ) > 0  THEN (PD_Reserv_Qty " . $operand . " " . $pd_reserv_qty . " )
        ELSE PD_Reserv_Qty
        END
        ";
        $sql = " UPDATE STK_T_Inbound SET " . $set_value . " WHERE Inbound_Id = " . $inbound_id;

        $this->db->query($sql);

        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return true;
        } else {
            return false;
        }
    }
    
     function addOrder($order) {// Duplicate Function !!
        $this->db->set('Create_Date', 'GETDATE()', FALSE);
        $this->db->insert("STK_T_Relocate", $order);
        return $this->db->insert_id();
    }
    
    function addOrderDetailByOneRecord($order_detail) {// Add by Ton! 20130531
        $this->db->insert("STK_T_Relocate_Detail", $order_detail);
        return $this->db->insert_id();
    }
    
    function get_master_tmp_($id_master = null){
        $this->db->select("*");
        $this->db->from("master_tmp_");
         $this->db->where("Active","Y");
         if(!empty($id_master)){
            $this->db->where("id",$id_master);
         }
        $query = $this->db->get();
        
        return $query;
    }
    
     function get_stock($product_code,$location_code){
//            select sum(isnull(Balance_Qty,0))
//            from STK_T_Inbound inb
//            join STK_M_Location lo on lo.Location_Id = inb.Actual_Location_Id
//            where 1=1
//            and inb.Active = 'Y'
//            and inb.Product_Code = '102-101000'
//            and lo.Location_Code = 'A01-01-D'
        $this->db->select("sum(isnull(Balance_Qty,0)) as instock");
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_M_Location lo","lo.Location_Id = inb.Actual_Location_Id");
        $this->db->where("inb.Active","Y");
        $this->db->where("inb.Product_Code",$product_code);
        $this->db->where("lo.Location_Code",$location_code);
        $query = $this->db->get();
//        p($this->db->last_query()); exit;
        return $query;
    }
    
    function reject_state_zero() {
        $update = array(
            'Present_State' => -1
            , 'Modified_By' => $this->_usr["user_id"]
            , 'Modified_Date' => date("Y-m-d H:i:s")
        );
        $where = array(
            'Process_Id' => 20
            , 'Present_State' => 0
        );
        $this->db->where($where);
        $this->db->update('STK_T_Workflow', $update);
        $afftectedRows = $this->db->affected_rows();
        $result = ($afftectedRows == 0 ? FALSE : TRUE);
        return $result;
    }
    
    function get_lo_id_by_code($location_code){
        $this->db->select("Location_Id");
        $this->db->from("STK_M_Location");
         $this->db->where("Location_Code",$location_code);
        $query = $this->db->get();
        
        return $query;
    }
    
     function get_flow($order_id){
        $this->db->select("Flow_Id");
        $this->db->from("STK_T_Relocate");
         $this->db->where("Order_Id",$order_id);
        $query = $this->db->get();
        
        return $query;
    }
    
     function get_master_by_product($product_code){
        $this->db->select("id");
        $this->db->from("master_tmp_");
         $this->db->where("product_code",$product_code);
         $this->db->where("active",'Y');
        $query = $this->db->get();
        
        return $query;
//        
//        select id
//        from master_tmp_
//        where product_code = ''
//        and active = 'Y'
    }
    function get_doc_no_by_id($order_id){
        $this->db->select("Location_Id");
        $this->db->from("STK_M_Location");
         $this->db->where("Location_Code",$location_code);
        $query = $this->db->get();
        
        return $query;
    }
    


    
}
