<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class criteria_model extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function getSearchResult($params = NULL) {

        $this->db->select("od.Doc_Refer_Ext");
        $this->db->select("od.Document_No");
        $this->db->select("od.Process_Type");
        $this->db->select("ISNULL(convert(varchar,dt.Activity_Date,103), '') As Actual_Action_Date");
        $this->db->select("dt.Product_Code");
        $this->db->select("ISNULL(ct.Cont_No,'') AS Cont_No");
        $this->db->select("CASE WHEN od.Process_Type IN ('INBOUND') THEN ISNULL(pl_in.Pallet_Code,'') ELSE ISNULL(pl_out.Pallet_Code,'') END AS Pallet_Code");
        $this->db->select("CASE WHEN od.Process_Type IN ('INBOUND') THEN ISNULL(pl_in.Pallet_Id,'') ELSE ISNULL(pl_out.Pallet_Id,'') END AS Pallet_Id");
        // ID
        $this->db->select("CASE WHEN dt.Pallet_From_Item_Id Is Null THEN dt.Item_Id ELSE dt.Pallet_From_Item_Id END AS Item_Id");
        $this->db->select("od.Order_Id");
        $this->db->select("ISNULL(ct.Cont_Id,'') AS Cont_Id");
        $this->db->from("STK_T_Order_Detail dt");
//        $this->db->order_by("od.Actual_Action_Date", "DESC");
        $this->db->order_by("dt.Activity_Date", "DESC"); // Edit by Joke 19/04/60
        $this->db->join("CTL_M_Container ct", "dt.Cont_Id = ct.Cont_Id");
        $this->db->join("STK_T_Order od", "dt.Order_Id = od.Order_Id");
        $this->db->join("STK_T_Pallet pl_in", "pl_in.Pallet_Id = dt.Pallet_Id", "LEFT");
        $this->db->join("STK_T_Pallet pl_out", "pl_out.Pallet_Id = dt.Pallet_Id_Out", "LEFT");

        // BEGIN WHERE

        if (isset($params['document_type']) && $params['document_type'] != "ALL") {
            $this->db->where("od.Process_Type", $params['document_type']);
        }

        if (isset($params['container_no']) && !empty($params['container_no'])) {
            $this->db->where("ct.Cont_No", $params['container_no']);
        }

        if (isset($params['document_no']) && !empty($params['document_no'])) {
            $this->db->where("od.Doc_Refer_Ext", $params['document_no']);
        }

        if (isset($params['product_code']) && !empty($params['product_code'])) {
            $this->db->where("dt.Product_Code", $params['product_code']);
        }

        if (isset($params['product_status']) && !empty($params['product_status'])) {
            $this->db->where("dt.Product_Status", $params['product_status']);
        }

        if (isset($params['operation_date']) && !empty($params['operation_date'])) {
            $this->db->where("dt.Activity_Date >=", $params['operation_date'] . " 00:00:00");
            $this->db->where("dt.Activity_Date <=", $params['operation_date'] . " 23:59:59");
        }

        // END WHERE
//        $query = $this->db->get("STK_T_Order_Detail dt");
        $query = $this->db->get();
        return $query;
    }

    public function searchKPI($params = NULL) {

        $from_date = trim($params['from_date']);
        $to_date = trim($params['to_date']);

        $this->db->select("OD.Document_No");
        $this->db->select("CT.Cont_No");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(CT.Start_Date), 121), '') AS Container_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(CT.End_Date), 121), '') AS Container_End");
        $this->db->select("DATEDIFF(second, MIN(CT.Start_Date), MAX(CT.End_Date)) AS KPI_Container");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(PL.Create_Date), 121), '') AS Pallet_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(PL.Create_Date), 121), '') AS Pallet_End");
        $this->db->select("DATEDIFF(second, MIN(PL.Create_Date), MAX(PL.Create_Date)) AS KPI_Receive");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(PL.Approve_Date), 121),'') AS Putaway_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(PL.Approve_Date), 121),'') AS Putaway_End");
        $this->db->select("DATEDIFF(second, MIN(PL.Approve_Date), MAX(PL.Approve_Date)) AS KPI_Putaway");

        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(PK.Activity_Date), 121), '') AS Picking_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(PK.Activity_Date), 121), '') AS Picking_End");
        $this->db->select("DATEDIFF(second, MIN(PK.Activity_Date), MAX(PK.Activity_Date)) AS KPI_Picking");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(DT.Activity_Date), 121), '') AS Dispatch_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(DT.Activity_Date), 121), '') AS Dispatch_End");
        $this->db->select("DATEDIFF(second, MIN(DT.Activity_Date), MAX(DT.Activity_Date)) AS KPI_Dispatch");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MIN(CT_OUT.Start_Date), 121), '') AS Container_Out_Start");
        $this->db->select("ISNULL(CONVERT(VARCHAR(19),MAX(CT_OUT.End_Date), 121), '') AS Container_Out_End");
        $this->db->select("DATEDIFF(second, MIN(CT_OUT.Start_Date), MAX(CT_OUT.End_Date)) AS KPI_Container_Out");

        // END SELECT
        // JOIN
        $this->db->join("STK_T_Workflow WF", "WF.Flow_Id = OD.Flow_Id");
        $this->db->join("CTL_M_Container CT", "OD.Order_Id = CT.Order_Id", "LEFT");
        $this->db->join("STK_T_Pallet PL", "PL.Order_Id = OD.Order_Id AND Build_Type = 'INBOUND' AND PL.Active='Y'", "LEFT");
        $this->db->join("STK_T_Logs_Action PK", "PK.Order_Id = OD.Order_Id AND PK.Module = 'PICKING' AND PK.Sub_Module = 'CONFIRM'", "LEFT");
        $this->db->join("STK_T_Order_Detail DT", "DT.Order_Id = OD.Order_Id AND DT.Activity_Code = 'DISPATCH'", "LEFT");
        $this->db->join("CTL_M_Container CT_OUT", "CT_OUT.Cont_Id = DT.Cont_Id", "LEFT");

        // GROUP
        $this->db->group_by("OD.Document_No");
        $this->db->group_by("CT.Cont_No");
        $this->db->group_by("OD.Create_Date");

        // WHERE
        $this->db->where_not_in("WF.Present_State", Array(-1));

        if (isset($from_date) && !empty($from_date)) {
            $this->db->where("CONVERT(VARCHAR(10), OD.Create_Date, 120) >= '" . $from_date . "'");
        }

        if (isset($to_date) && !empty($to_date)) {
            $this->db->where("CONVERT(VARCHAR(10), OD.Create_Date, 120) <= '" . $to_date . "'");
        }

        // ORDER
        $this->db->order_by("OD.Create_Date", "DESC");
        $result = $this->db->get("STK_T_Order OD");

        return $result;
    }

}
