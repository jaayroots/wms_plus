<?php

class report_booking_model extends CI_Model {

    protected $db_connection = NULL;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function search_booking($search) {

	$this->db->select('dt.Product_Code');
	$this->db->select('pd.Product_NameEN');
	$this->db->select('st.Dom_TH_Desc AS Product_Status');
	$this->db->select('ss.Dom_TH_Desc AS Product_Sub_Status');
	$this->db->select('CONVERT(nvarchar(10) , ib.Receive_Date , 103) AS Receive_Date');
	$this->db->select('ib.Doc_Refer_CE');
	$this->db->select('dt.Product_Lot');
	$this->db->select('dt.Product_Serial');
	$this->db->select('inv.Invoice_No');
	$this->db->select('SUM(dt.Reserv_Qty) AS PD_Reserv_Qty');
	$this->db->select('pl.Pallet_Code');
	$this->db->select('dt.Remark');
	$this->db->select('od.Doc_Refer_Ext');
	$this->db->select('lng.name');
        $this->db->select('dt.Price_Per_Unit');
        $this->db->from("STK_T_Order od");
  
	$this->db->join("STK_T_Order_Detail dt", "od.Order_Id = dt.Order_Id", "LEFT");
	$this->db->join("STK_T_Workflow wf", "wf.Flow_Id = od.FLow_Id", "LEFT");
        $this->db->join("STK_M_Product pd", "dt.Product_Id = pd.Product_Id", "LEFT");
        $this->db->join("STK_T_Pallet pl", "pl.Pallet_Id = dt.Pallet_Id", "LEFT");
        $this->db->join("SYS_M_Domain st", "st.Dom_Code = dt.Product_Status AND st.Dom_Host_Code = 'PROD_STATUS'", "LEFT");
        $this->db->join("SYS_M_Domain ss", "ss.Dom_Code = dt.Product_Sub_Status AND ss.Dom_Host_Code = 'SUB_STATUS'", "LEFT");
        $this->db->join("STK_T_Invoice inv", "inv.Invoice_Id = dt.Invoice_Id", "LEFT");
	$this->db->join("STK_T_Inbound ib", "ib.Inbound_Id = dt.Inbound_Item_Id", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language lng", "lng.CTL_M_UOM_Template_id = ib.Unit_Id AND lng.language = 'eng'", "LEFT");

        $this->db->where('wf.Process_Id' , 2);
	$this->db->where('wf.Present_State Not In (-1,-2)');
	$this->db->where('dt.Active', 'Y');
	$this->db->where('ib.PD_Reserv_Qty > 0');

	if (isset($search['doc_type']) && $search['doc_type'] == "Doc_Refer_Ext" && $search['doc_value'] != "" ) {
		$this->db->where('od.Doc_Refer_Ext' , $search['doc_value']);
	}

        if (isset($search['doc_type']) && $search['doc_type'] == "Document_No" && $search['doc_value'] != "" ) {
                $this->db->where('od.Document_No' , $search['doc_value']);
        }

        if (isset($search['doc_type']) && $search['doc_type'] == "Doc_Refer_Inv" && $search['doc_value'] != "" ) {
                $this->db->where('inv.Invoice_No' , $search['doc_value']);
        }

        if (isset($search['doc_type']) && $search['doc_type'] == "Doc_Refer_CE" && $search['doc_value'] != "" ) {
                $this->db->where('ib.Doc_Refer_CE' , $search['doc_value']);
        }

	$this->db->group_by('dt.Product_Code');
	$this->db->group_by('pd.Product_NameEN');
	$this->db->group_by('st.Dom_TH_Desc');
	$this->db->group_by('ss.Dom_TH_Desc');
	$this->db->group_by('CONVERT(nvarchar(10) , ib.Receive_Date , 103)');
	$this->db->group_by('ib.Doc_Refer_CE');
	$this->db->group_by('dt.Product_Lot');
	$this->db->group_by('dt.Product_Serial');
	$this->db->group_by('inv.Invoice_No');
	$this->db->group_by('pl.Pallet_Code');
	$this->db->group_by('dt.Remark');
	$this->db->group_by('od.Doc_Refer_Ext');
	$this->db->group_by('lng.name');
	$this->db->group_by('dt.Price_Per_Unit');

        $query_normal = $this->db->get();

	//echo $this->db->last_query(); exit;
        
        return $query_normal->result();
    }

    /**
     *
     * @param type array(document_no)
     * @return array
     */
    function search_booking_pdf($search) {
        $result_query = $this->search_booking($search);
        $dataRows = array();
        $dataRow = array();

        foreach ($result_query as $value) {
            $head = md5($value->Flow_Id . $value->Doc_Refer_Ext . $value->Document_No . $value->Doc_Refer_BL);
            if (isset($head, $dataRows[$head])) {
                $keyTmp = sizeof($dataRows[$head]);
            } else {
                $keyTmp = 0;
            }
            $dataRow['Row'] = $value->Row;
            $dataRow['Product_Code'] = $value->Product_Code;
            $dataRow['Product_NameEN'] = $value->Product_NameEN;
            $dataRow['Product_Status'] = $value->Product_Status;
            $dataRow['Product_Sub_Status'] = $value->Product_Sub_Status;
            $dataRow['Receive_Date'] = $value->Receive_Date;
	    $dataRow['Customs_Entry'] = $value->Doc_Refer_CE;
            $dataRow['Customs_Sequence'] = $value->Customs_Sequence;
            $dataRow['HS_Code'] = $value->HS_Code;
            $dataRow['Product_Lot'] = $value->Product_Lot;
            $dataRow['Product_Serial'] = $value->Product_Serial;
            $dataRow['Invoice_No'] = $value->Invoice_No;
            $dataRow['PD_Reserv_Qty'] = $value->PD_Reserv_Qty;
            $dataRow['name'] = $value->name;
            $dataRow['Pallet_Code'] = $value->Pallet_Code;
            $dataRow['remark'] = $value->Remark;
	    $dataRow['Price_Per_Unit'] = $value->Price_Per_Unit;

            $dataRows[$head][$keyTmp] = $dataRow;

            unset($dataRow);
            unset($head);
        }

        $result = $dataRows;
        //    p($result);exit;
        return $result;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
}
