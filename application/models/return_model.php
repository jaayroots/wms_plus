<?php

class return_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function search_dispatch_document($search) {

        // SELECT ==========
        $this->db->select("prd.Product_Code");
        $this->db->select("prd.Product_NameEN");
        $this->db->select("dt.Product_Lot");
        $this->db->select("dt.Product_Serial");
        $this->db->select("dt.Product_Mfd");
        $this->db->select("dt.Product_Exp");
        $this->db->select("dt.Reserv_Qty");
        $this->db->select("pallet_in.Pallet_Code AS Pallet_Id");
        $this->db->select("pallet_out.Pallet_Code AS Pallet_Id_Out");

        // JOIN =========
        $this->db->join("STK_T_Order_Detail dt", "od.Order_Id = dt.Order_Id");
        $this->db->join("STK_M_Product prd", "dt.Product_Id = prd.Product_Id");
        $this->db->join("STK_T_Workflow wf", "od.Flow_Id = wf.Flow_Id");
        $this->db->join("STK_T_Pallet pallet_in", "pallet_in.Pallet_Id = dt.Pallet_Id","LEFT");
        $this->db->join("STK_T_Pallet pallet_out", "pallet_out.Pallet_Id = dt.Pallet_Id_Out","LEFT");

        // WHERE =========
        $this->db->where("od.Document_No", $search['document']);
        $this->db->where("dt.Active", "Y");

        // GROUP BY =============
        //$this->db->group_by("o.Document_No");

        $query = $this->db->get("STK_T_Order od");

        $result = $query->result();

        return $result;
    }

    public function return_dispatch_document($params) {
        // Search for state
        $ignore = array(-1, -2);
        $this->db->select("wf.Present_State");
        $this->db->select("wf.Flow_Id");
        $this->db->join("STK_T_Order od", "od.Flow_Id = wf.Flow_Id");
        $this->db->where("od.Document_No", $params['document']);
        $this->db->where("wf.Process_Id", 2);
        $this->db->where_not_in("wf.Present_State", $ignore);
        $query = $this->db->get("STK_T_Workflow wf");
        $rowcount = $query->num_rows();
        if ($rowcount == 1) {
            $rs = $query->row();

            // PRE_DISPATCH
            if (in_array($rs->Present_State, array(0, 1, 2))) {
                
            } else if (in_array($rs->Present_State, array(3, 4, 5, 6))) {

                // START CLEAR PALLET AND PALLET_DETAIL
                $this->db->select("dt.Order_Id");

                $this->db->select("dt.Item_Id");

                $this->db->select("pd.Id AS pallet_detail_id");

                $this->db->select("pd.Pallet_Id AS pallet_id");

                $this->db->join("STK_T_Order_Detail dt", "dt.Order_Id = od.Order_Id");

                $this->db->join("STK_T_Pallet_Detail pd", "pd.Item_Id = dt.Item_Id", "LEFT");

                $this->db->where("od.Flow_Id", $rs->Flow_Id);

                $query_detail = $this->db->get("STK_T_Order od");

                $result_detail = $query_detail->result();

                // Clear Pallet
                foreach ($result_detail as $i => $v) {

                    if (!empty($v->pallet_detail_id)) {
                        $this->db->delete("STK_T_Pallet_Detail", array("Id" => $v->pallet_detail_id, "Pallet_Id" => $v->pallet_id, "Item_Id" => $v->Item_Id));
                    }

                    // กรณีที่ Pallet นี้มีหลายใบ Order อาจจะต้องเพิ่มการแก้ไขไป
                    // Clear STK_T_Order_Detail
                    $detail_updated = array(
                        "Actual_Location_Id" => NULL,
                        "Confirm_Qty" => NULL,
                        "DP_Confirm_Qty" => NULL,
                        "Activity_Code" => NULL,
                        "Activity_By" => NULL,
                        "Pallet_Id_Out" => NULL,
                        "Pallet_From_Item_Id" => NULL,
                        "Activity_Date" => NULL
                    );

                    $this->db->where("Active", "Y");
                    $this->db->where("Item_Id", $v->Item_Id);

                    $this->db->update("STK_T_Order_Detail", $detail_updated);

                    unset($detail_updated);
                }

                // Clear Dispatch               
                $this->db->delete("STK_T_Logs_Action", array("Module" => "pre_dispatch", "Sub_Module" => "confirmPreDispatch", "Order_Id" => $v->Order_Id));
                $this->db->delete("STK_T_Logs_Action", array("Module" => "pre_dispatch", "Sub_Module" => "approvePreDispatch", "Order_Id" => $v->Order_Id));
                $this->db->delete("STK_T_Logs_Action", array("Module" => "picking", "Order_Id" => $v->Order_Id));
                $this->db->delete("STK_T_Logs_Action", array("Module" => "dispatch", "Order_Id" => $v->Order_Id));

                // Back Step to Pre-Dispatch
                // CLOSE Trigger
                $this->db->query("ALTER TABLE STK_T_Workflow DISABLE TRIGGER workflow_logs_work_mod");

                $flow_updated = array("Present_State" => 1);
                $this->db->where("Flow_Id", $rs->Flow_Id);
                $this->db->update("STK_T_Workflow", $flow_updated);

                // Enabled Trigger
                $this->db->query("ALTER TABLE STK_T_Workflow ENABLE TRIGGER workflow_logs_work_mod");

                return "SUCCESS";
            }
        } else {
            return "ERROR_ROWS";
        }
    }

}
