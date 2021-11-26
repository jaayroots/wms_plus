<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of im_ex_model
 *
 * @author 
 */
class import_inventory_movement_model extends CI_Model {

    function getdata(){
        $this->db->select("CONVERT(Varchar(10), o.Estimate_Action_Date, 103) TRANSDATE");
        $this->db->select("o.Doc_Refer_AWB DOCNUM");
        $this->db->select("od.product_code ITEMNO ,");
        $this->db->select("(CASE WHEN (o.Process_Type = 'INBOUND') THEN 'JWD_IN' WHEN (o.Process_Type = 'OUTBOUND') THEN 'JWD_OUT'ELSE '' END) Process_Type ,");
        $this->db->select("SUM(od.Confirm_Qty) QTY");
        $this->db->from("STK_T_Order o");
        $this->db->join("STK_T_Order_Detail od ", "o.Order_Id = od.Order_Id", "LEFT");
        $this->db->join("STK_T_Workflow w ", "o.Flow_Id = w.Flow_Id", "LEFT");
        $this->db->join("SYS_M_State s", "w.Process_Id = s.Process_Id AND w.Present_State = s.State_No", "LEFT");
        $this->db->join("STK_M_Product p", "od.Product_Code = p.Product_Code", "LEFT");
        $this->db->where("w.Process_Id in ('1','2')
                            AND w.Present_State = '-2'
                            AND s.State_No IS NOT NULL
                            AND od.active = 'Y'
                            AND CONVERT(VARCHAR(10), o.Estimate_Action_Date, 103) = CONVERT(VARCHAR(10), DATEADD(DAY, -1, GETDATE()), 103)");
        $this->db->group_by("CONVERT(Varchar(10), o.Estimate_Action_Date, 103) ");
        $this->db->group_by("o.Doc_Refer_AWB");
        $this->db->group_by("od.product_code , (CASE
                            WHEN (o.Process_Type = 'INBOUND') THEN 'JWD_IN'
                            WHEN (o.Process_Type = 'OUTBOUND') THEN 'JWD_OUT'
                            ELSE ''
                            END)");
                        
        $query = $this->db->get();
        // p($this->db->last_query()); exit;
        $result = $query->result();
        return $result;

    }
    function getdate_data($getdate,$todate){
        $this->db->select("CONVERT(Varchar(10), o.Estimate_Action_Date, 103) TRANSDATE");
        $this->db->select("o.Doc_Refer_AWB DOCNUM");
        $this->db->select("od.product_code ITEMNO ,");
        $this->db->select("(CASE WHEN (o.Process_Type = 'INBOUND') THEN 'JWD_IN' WHEN (o.Process_Type = 'OUTBOUND') THEN 'JWD_OUT'ELSE '' END) Process_Type ,");
        $this->db->select("SUM(od.Confirm_Qty) QTY");
        $this->db->from("STK_T_Order o");
        $this->db->join("STK_T_Order_Detail od ", "o.Order_Id = od.Order_Id", "LEFT");
        $this->db->join("STK_T_Workflow w ", "o.Flow_Id = w.Flow_Id", "LEFT");
        $this->db->join("SYS_M_State s", "w.Process_Id = s.Process_Id AND w.Present_State = s.State_No", "LEFT");
        $this->db->join("STK_M_Product p", "od.Product_Code = p.Product_Code", "LEFT");
        $this->db->where("w.Process_Id in ('1','2')
                            AND w.Present_State = '-2'
                            AND s.State_No IS NOT NULL
                            AND od.active = 'Y'
                            AND (o.Estimate_Action_Date between  CONVERT(datetime,'$getdate',103) and CONVERT(datetime,'$todate',103) )
                            ");
        $this->db->group_by("CONVERT(Varchar(10), o.Estimate_Action_Date, 103) ");
        $this->db->group_by("o.Doc_Refer_AWB");
        $this->db->group_by("od.product_code , (CASE
                            WHEN (o.Process_Type = 'INBOUND') THEN 'JWD_IN'
                            WHEN (o.Process_Type = 'OUTBOUND') THEN 'JWD_OUT'
                            ELSE ''
                            END)");
                        
        $query = $this->db->get();
        $result = $query->result();
        return $result;

    }
   
   

}
