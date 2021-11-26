<?php

class report_jcs_model extends CI_Model {

    protected $db_connection = NULL;

    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
    }

    function search_receive($search) {
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];

        /**         * *******************************************************************************
         * Document ปกติ
         * ********************************************************************************* */
        /**
         * ====================================================================================================
         * Select Zone
         */
        $this->db->select("inb.Document_No
            ,inb.Doc_Refer_Ext
            ,prod.Product_NameEN AS Product_Name
            ,prod.TypeLicense
            ,inb.Product_Lot
            ,inb.Product_Serial
            ,inb.Unit_Id
            ,CTL_M_UOM_Template.Cubic_Meters AS cbm
            ,CTL_M_UOM_Template.Width AS w
            ,CTL_M_UOM_Template.Length AS l
            ,CTL_M_UOM_Template.Height AS h
            ,sum(inb.Receive_Qty) Receive_Qty
            ,CONVERT(VARCHAR(20), inb.Receive_Date, 103) as Receive_Date
            ,CONVERT(VARCHAR(20), inb.Receive_Date, 120) as Receive_Date_sort
            ,inb.Doc_Refer_Int
            ,inb.Doc_Refer_Inv
            ,S1.public_name AS Unit_Value
            ,'Is_reject' = CASE
               WHEN w.Present_State <> -1 THEN 'N'
               WHEN  w.Present_State = -1 THEN 'Y'
            END
            ");

        /**
         * ====================================================================================================
         * Group Zone
         */
        $groupby = array("inb.Document_No"
            , "inb.Doc_Refer_Ext"
            , "prod.Product_NameEN"
            , "prod.TypeLicense"
            , "inb.Product_Lot"
            , "inb.Product_Serial"
            , "inb.Unit_Id"
            , "CTL_M_UOM_Template.Cubic_Meters"
            , "CTL_M_UOM_Template.Width"
            , "CTL_M_UOM_Template.Length"
            , "CTL_M_UOM_Template.Height"
            , "CONVERT(VARCHAR(20), inb.Receive_Date, 103)"
            , "CONVERT(VARCHAR(20), inb.Receive_Date, 120)"
            , "inb.Owner_Id", "inb.Renter_Id"
            , "r.Order_Id", "inb.Inbound_Id"
            , "inb.Doc_Refer_Int", "inb.Doc_Refer_Inv"
            , "S1.public_name"
            , "w.Present_State"
        );

        if ($conf_inv):
            $this->db->select("inb.Invoice_Id");
            $this->db->select("STK_T_Invoice.Invoice_No");
            $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = inb.Invoice_Id", "LEFT");

            array_push($groupby, "inb.Invoice_Id");
            array_push($groupby, "STK_T_Invoice.Invoice_No");
        endif;


        if ($conf_cont):
            $this->db->select("inb.Cont_Id");
            $this->db->select("CTL_M_Container.Cont_No");
            $this->db->select("CTL_M_Container_Size.Cont_Size_No");
            $this->db->select("CTL_M_Container_Size.Cont_Size_Unit_Code");

            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = inb.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");

            array_push($groupby, "inb.Cont_Id");
            array_push($groupby, "CTL_M_Container.Cont_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_No");
            array_push($groupby, "CTL_M_Container_Size.Cont_Size_Unit_Code");

        endif;

        /**
         * ====================================================================================================
         * From/Join Zone
         */
        $this->db->join("STK_T_Order r", "inb.Document_No=r.Document_No", "LEFT");
        $this->db->join("STK_T_Workflow w", "inb.Flow_Id = w.Flow_Id", "LEFT");
        $this->db->join("CTL_M_UOM_Template", "CTL_M_UOM_Template.id = inb.Unit_Id", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language S1", "inb.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("STK_M_Product prod", "inb.Product_Id = prod.Product_Id");
        $this->db->from("STK_T_Inbound inb");

        $this->db->group_by($groupby);
        unset($groupby);

        /**
         * ====================================================================================================
         * Where Zone
         */
        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='" . $from_date . "'");
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)<='" . $to_date . "'");
            } else {
                $this->db->where("CONVERT(varchar(10),inb.Receive_Date,120)>='" . $from_date . "'");
            }
        }

        //$this->db->where("inb.Active", 'Y');
        $this->db->where_in('w.Process_Id', array(1));
        $this->db->where_in('w.Present_State', array(5, 6, -2));
        $this->db->order_by("CONVERT(VARCHAR(20), inb.Receive_Date, 103)", "asc");
        $this->db->order_by("prod.Product_NameEN", "ASC");
        $this->db->order_by("S1.public_name", "ASC");

        $query_normal = $this->db->get();
        $result = $query_normal->result();

        return $result;
    }

    function search_receive_pdf($search) {


        #Load config (add by kik : 20140723)
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];

        $result_query = $this->search_receive($search);
        $dataRows = array();
        $dataRow = array();
        $dataRow_reject = array();
        $idx = 0;
        foreach ($result_query as $value) {

            $dataRow['Receive_Date'] = $value->Receive_Date;
            $dataRow['Doc_Refer_Ext'] = $value->Doc_Refer_Ext;
            $dataRow['Product_Name'] = $value->Product_Name;
            $dataRow['Product_Lot'] = $value->Product_Lot;
            $dataRow['Product_Serial'] = $value->Product_Serial;
            $dataRow['Product_SerialLot'] = $value->Product_Serial;
            $dataRow['w'] = $value->w;
            $dataRow['h'] = $value->h;
            $dataRow['l'] = $value->l;
            $dataRow['TypeLicense'] = $value->TypeLicense;
            $dataRow['cbm'] = $value->cbm;
            $dataRow['Receive_Qty'] = $value->Receive_Qty;
            $dataRow['Unit_Value'] = $value->Unit_Value;

            if ($conf_inv):
                $dataRow['Invoice_No'] = $value->Invoice_No;
            endif;

            $dataRow['Is_reject'] = NULL;

            $dataRows[$idx][] = $dataRow;

            unset($dataRow);
        }

        $result = $dataRows;

        return $result;
    }

    function search_dispatch($search, $type = "") {

        $type_of_date = 'Dispatch_Date';

        if ($search['type_dp_date_val'] == 'real_dp_date'):
            $type_of_date = 'Real_Action_Date';
        endif;

        $this->db->select("o.Document_No");
        $this->db->select("o.Doc_Refer_Ext");
        $this->db->select("o.Product_Code");
        $this->db->select("(prod.Product_NameEN) AS Product_Name");
        $this->db->select("prod.TypeLicense");
        $this->db->select("o.Product_Lot");
        $this->db->select("o.Product_Serial");
        $this->db->select("inv.Invoice_No");
        $this->db->select("o.Unit_Id");
        $this->db->select("CTL_M_UOM_Template.Cubic_Meters AS cbm");
        $this->db->select("CTL_M_UOM_Template.Width AS w");
        $this->db->select("CTL_M_UOM_Template.Length AS l");
        $this->db->select("CTL_M_UOM_Template.Height AS h");
        $this->db->select("sum(o.Dispatch_Qty) AS Dispatch_Qty");
        $this->db->select("(CONVERT(VARCHAR(20), inb.Receive_Date, 103)) AS Receive_Date");
        $this->db->select("(CONVERT(VARCHAR(20), o." . $type_of_date . ", 103)) AS Dispatch_Date");
        $this->db->select("S1.public_name AS Unit_Value");

        // JOIN =========
        $this->db->join("STK_T_Order r", "o.Document_No=r.Document_No", "LEFT");
        $this->db->join("CTL_M_UOM_Template_Language S1", "o.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'", "LEFT");
        $this->db->join("CTL_M_UOM_Template", "CTL_M_UOM_Template.id = o.Unit_Id", "LEFT");
        $this->db->join("STK_M_Product prod", "o.Product_Id = prod.Product_Id", "LEFT");
        $this->db->join("STK_T_Inbound inb", "inb.Inbound_Id = o.Inbound_Item_Id", "LEFT");
        $this->db->join("STK_T_Invoice inv", "inv.Invoice_Id = o.Invoice_Id", "LEFT");

        /**
         * ====================================================================================================
         * Where Zone
         */
        if ($search['fdate'] != "") {
            $from_date = convertDate($search['fdate'], "eng", "iso", "-");
            if ($search['tdate'] != "") {
                $to_date = convertDate($search['tdate'], "eng", "iso", "-");
                $this->db->where("CONVERT(varchar(20),o." . $type_of_date . ",120)>='" . $from_date . " 00:00:00'");
                $this->db->where("CONVERT(varchar(20),o." . $type_of_date . ",120)<='" . $to_date . " 23:59:59'");
            } else {
                $this->db->where("CONVERT(varchar(20),o." . $type_of_date . ",120)>='" . $from_date . " 23:59:59'");
            }
        }

        $this->db->group_by("o.Document_No");
        $this->db->group_by("o.Doc_Refer_Ext");
        $this->db->group_by("o.Product_Code");
        $this->db->group_by("prod.TypeLicense");
        $this->db->group_by("prod.Product_NameEN");
        $this->db->group_by("o.Product_Lot");
        $this->db->group_by("o.Product_Serial");
        $this->db->group_by("inv.Invoice_No");
        $this->db->group_by("o.Unit_Id");
        $this->db->group_by("CONVERT(VARCHAR(20), inb.Receive_Date, 103)");
        $this->db->group_by("CONVERT(VARCHAR(20), o." . $type_of_date . ", 103)");
        $this->db->group_by("S1.public_name");
        $this->db->group_by("o.Product_Id");
        $this->db->group_by("CTL_M_UOM_Template.Cubic_Meters");
        $this->db->group_by("CTL_M_UOM_Template.Width");
        $this->db->group_by("CTL_M_UOM_Template.Height");
        $this->db->group_by("CTL_M_UOM_Template.Length");

        // ORDER BY
        $this->db->order_by("CONVERT(VARCHAR(20), o." . $type_of_date . ", 103)", "ASC");
        $this->db->order_by("o.Product_Code", "ASC");
        $this->db->order_by("S1.public_name", "ASC");

        $query = $this->db->get("STK_T_Outbound o");

        $result = $query->result();

        return $result;
    }

    function invertory_stock_balance_today($search) {
        $this->load->model("report_model", "r");
        //แสดง header ในส่วน Product Status
        $status_range = $this->r->getStatusRange($search['status_id']);

        //แสดง จำนวน balance
        $sum_balance = $this->r->getSqlSearchBalance($status_range, 'STK_T_Inbound');

        $this->db->select('STK_T_Inbound.Product_Code');
        $this->db->select('Product_NameEN');
        $this->db->select('STK_T_Inbound.Doc_Refer_Ext');
        $this->db->select('STK_T_Inbound.Product_Lot');
        $this->db->select('STK_T_Inbound.Product_Serial');
        $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Mfd,103) Product_Mfd');
        $this->db->select('convert(varchar(10),STK_T_Inbound.Product_Exp,103) Product_Exp');
        $this->db->select("STK_T_Invoice.Invoice_No, STK_M_Product.TypeLicense as DIW_Class");
        $this->db->select('STK_T_Pallet.Pallet_Code');
        $this->db->select("UOM_Temp.public_name AS Unit_Value, 0 as Uom_Qty,isnull(CTL_M_UOM_Template.Cubic_Meters,0) as CBM");
        $this->db->select("CONVERT(VARCHAR(20), STK_T_Inbound.Receive_Date, 103) as Receive_Date, isnull(CTL_M_UOM_Template.Width, 0) as Width");
        $this->db->select("isnull(CTL_M_UOM_Template.Length, 0) as Length , isnull(CTL_M_UOM_Template.Height, 0) as Height");
        $this->db->select('sum(Balance_Qty) as totalbal');
        $this->db->from('STK_T_Inbound');
        $this->db->join('STK_M_Location', 'STK_T_Inbound.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Inbound.Product_Id=STK_M_Product.Product_Id', 'LEFT');
        $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'");
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp", "STK_T_Inbound.Unit_Id = UOM_Temp.CTL_M_UOM_Template_id AND UOM_Temp.language = 'eng' ");
        $this->db->join("CTL_M_UOM_Template", "CTL_M_UOM_Template.id = STK_T_Inbound.Unit_Id");
        $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = STK_T_Inbound.Invoice_Id", "LEFT");

        $this->db->where('STK_T_Inbound.Active', ACTIVE);
        $this->db->where($this->r->searchConditionInventory($search, $status_range, 'STK_T_Inbound'));

        $this->db->group_by(array('STK_T_Inbound.Product_Code', 'Product_NameEN', 'STK_T_Inbound.Product_Lot', 'STK_T_Inbound.Product_Serial', 'STK_T_Inbound.Doc_Refer_Ext',
            'STK_T_Inbound.Product_Mfd', 'STK_T_Inbound.Product_Exp', 'STK_T_Pallet.Pallet_Code', 'STK_T_Invoice.Invoice_No', ' STK_M_Product.TypeLicense',
            'UOM_Temp.public_name', 'CTL_M_UOM_Template.Cubic_Meters', 'STK_T_Inbound.Receive_Date', 'CTL_M_UOM_Template.Width', 'CTL_M_UOM_Template.Length', 'CTL_M_UOM_Template.Height'));
        $this->db->having("sum(Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0
        $this->db->order_by("CONVERT(VARCHAR(20), STK_T_Inbound.Receive_Date, 103)", "ASC");
        $this->db->order_by("STK_T_Inbound.Product_Code", "ASC");
        $this->db->order_by("UOM_Temp.public_name", "ASC");
        $query = $this->db->get();
        return $query->result();
    }

    function invertory_stock_balance($search) {
        $this->load->model("report_model", "r");
        $date = strtr($search["search_date"], '/', '-');

        //แสดง header ในส่วน Product Status
        $status_range = $this->r->getStatusRange($search['status_id']);

        //แสดง จำนวน balance
        $sum_balance = $this->r->getSqlSearchBalance($status_range, 'STK_T_Inbound');

        $this->db->select('STK_T_Onhand_History.Product_Code');
        $this->db->select('Product_NameEN');
        $this->db->select('STK_T_Inbound.Doc_Refer_Ext');
        $this->db->select('STK_T_Onhand_History.Product_Lot');
        $this->db->select('STK_T_Onhand_History.Product_Serial');
        $this->db->select('convert(varchar(10),STK_T_Onhand_History.Product_Mfd,103) Product_Mfd');
        $this->db->select('convert(varchar(10),STK_T_Onhand_History.Product_Exp,103) Product_Exp');
        $this->db->select("STK_T_Invoice.Invoice_No, STK_M_Product.TypeLicense as DIW_Class");
        $this->db->select('STK_T_Pallet.Pallet_Code');
        $this->db->select("UOM_Temp.public_name AS Unit_Value, 0 as Uom_Qty,isnull(CTL_M_UOM_Template.Cubic_Meters,0) as CBM");
        $this->db->select("CONVERT(VARCHAR(20), STK_T_Inbound.Receive_Date, 103) as Receive_Date, isnull(CTL_M_UOM_Template.Width, 0) as Width");
        $this->db->select("isnull(CTL_M_UOM_Template.Length, 0) as Length , isnull(CTL_M_UOM_Template.Height, 0) as Height");
        $this->db->select('sum(STK_T_Onhand_History.Balance_Qty) as totalbal');
        $this->db->from('STK_T_Onhand_History');
        $this->db->join('STK_T_Inbound', 'STK_T_Onhand_History.Inbound_Id=STK_T_Inbound.Inbound_Id');
        $this->db->join('STK_M_Location', 'STK_T_Onhand_History.Actual_Location_Id=STK_M_Location.Location_Id', 'LEFT');
        $this->db->join('STK_M_Product', 'STK_T_Onhand_History.Product_Id=STK_M_Product.Product_Id', 'LEFT');
        $this->db->join("STK_T_Pallet", "STK_T_Onhand_History.Pallet_Id = STK_T_Pallet.Pallet_Id", 'LEFT');
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp_Prod", "STK_M_Product.Standard_Unit_In_Id = UOM_Temp_Prod.CTL_M_UOM_Template_id AND UOM_Temp_Prod.language = 'eng'");
        $this->db->join("CTL_M_UOM_Template_Language UOM_Temp", "STK_T_Inbound.Unit_Id = UOM_Temp.CTL_M_UOM_Template_id AND UOM_Temp.language = 'eng' ");
        $this->db->join("CTL_M_UOM_Template", "CTL_M_UOM_Template.id = STK_T_Inbound.Unit_Id");
        $this->db->join("STK_T_Invoice", "STK_T_Invoice.Invoice_Id = STK_T_Inbound.Invoice_Id", "LEFT");

//        $this->db->where('STK_T_Inbound.Active', ACTIVE);
        $this->db->where("datediff(day, Available_Date, '" . date("Y-m-d", strtotime($date)) . "') = 0");
        $this->db->where($this->r->searchConditionInventory($search, $status_range, 'STK_T_Onhand_History'));

        $this->db->group_by(array('STK_T_Onhand_History.Product_Code', 'Product_NameEN', 'STK_T_Onhand_History.Product_Lot', 'STK_T_Onhand_History.Product_Serial', 'STK_T_Inbound.Doc_Refer_Ext',
            'STK_T_Onhand_History.Product_Mfd', 'STK_T_Onhand_History.Product_Exp', 'STK_T_Pallet.Pallet_Code', 'STK_T_Invoice.Invoice_No', ' STK_M_Product.TypeLicense',
            'UOM_Temp.public_name', 'CTL_M_UOM_Template.Cubic_Meters', 'STK_T_Inbound.Receive_Date', 'CTL_M_UOM_Template.Width', 'CTL_M_UOM_Template.Length', 'CTL_M_UOM_Template.Height'));
        $this->db->having("sum(STK_T_Onhand_History.Balance_Qty)<>0"); //ADD BY POR 2014-03-12 ให้แสดงเฉพาะ balance มากกว่า 0
        $this->db->order_by("CONVERT(VARCHAR(20), STK_T_Inbound.Receive_Date, 103)", "ASC");
        $this->db->order_by("STK_T_Onhand_History.Product_Code", "ASC");
        $this->db->order_by("UOM_Temp.public_name", "ASC");
        $query = $this->db->get();
        return $query->result();
    }

    function search_stock($input) {
        $this->db->select("STK_T_Order.Doc_Refer_Ext, STK_T_Order.Document_No,isnull(CONVERT(varchar(11), STK_T_Order.Real_Action_Date, 103),'') as Real_Action_Date ,CTL_M_Container.Cont_No, CTL_M_Container_Size.Cont_Size_No");
        $this->db->select("STK_T_Inbound.Product_Code , STK_M_Product.Product_NameEN , STK_T_Inbound.Product_Lot ,CONVERT(varchar(11),STK_T_Inbound.Receive_Date,103) as Receive_Date");
        $this->db->select("(DATEDIFF(DD,CONVERT(VARCHAR(10),STK_T_Inbound.Receive_Date, 101),CONVERT(VARCHAR(10),STK_T_Order.Real_Action_Date, 101)) - 2) as Storage_Day ");
        $this->db->select("sum(STK_T_Order_Detail.DP_Confirm_Qty) as DP_Qty,CTL_M_Container_Size.Cont_Size_Unit_Code");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Order", "STK_T_Order_Detail.order_id = STK_T_Order.Order_Id");
        $this->db->join("STK_T_Inbound", "STK_T_Order_Detail.Inbound_Item_Id = STK_T_Inbound.Inbound_Id");
        $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Id = STK_M_Product.Product_Id");
        $this->db->join("CTL_M_Container", "STK_T_Order_Detail.Cont_Id = CTL_M_Container.Cont_Id");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id");
        $this->db->join("STK_T_Workflow", "STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id");
        $this->db->where("STK_T_Order.Process_Type", "OUTBOUND");
        $this->db->where("STK_T_Workflow.Process_Id", 2);
        $this->db->where("STK_T_Workflow.Present_State", -2);

        if (!empty($input["document_external"])) {
            $this->db->where("STK_T_Order.Doc_Refer_Ext", $input["document_external"]);
        }
        if (!empty($input["product_code"])) {
            $this->db->where("STK_T_Order_Detail.Product_Code", $input["product_code"]);
        }
        if (!empty($input["product_lot"])) {
            $this->db->where("STK_T_Order_Detail.Product_Lot", $input["product_lot"]);
        }

        if ($input['from_date'] != "") {
            $from_date = convertDate($input['from_date'], "eng", "iso", "-");
            $this->db->where("STK_T_Order.Real_Action_Date >= '" . $from_date . "'");
            if ($input['to_date'] != "") {
                $to_date = convertDate($input['to_date'], "eng", "iso", "-");
                $this->db->where("STK_T_Order.Real_Action_Date <= '" . $to_date . "'");
            }
        }
        $this->db->group_by("STK_T_Order.Doc_Refer_Ext, STK_T_Order.Document_No,  STK_T_Order.Real_Action_Date ,CTL_M_Container.Cont_No, CTL_M_Container_Size.Cont_Size_No,"
                . "STK_T_Inbound.Product_Code , STK_M_Product.Product_NameEN , STK_T_Inbound.Product_Lot ,STK_T_Inbound.Receive_Date,"
                . "CTL_M_Container_Size.Cont_Size_Unit_Code");
        $this->db->order_by("STK_T_Order.Real_Action_Date , STK_T_Order.Doc_Refer_Ext , STK_T_Inbound.Product_Code", "ASC");
        $query = $this->db->get();
//        echo $this->db->last_query();exit
        $result = $query->result();

        $data = array();
        foreach ($result as $key => $value) {
            $group_head = md5($value->Doc_Refer_Ext);
            $data[$group_head]["group_head"] = $value->Doc_Refer_Ext;
            if (array_key_exists($group_head, $data)) {
                $list = array();
                $list["Doc_Refer_Ext"] = $value->Doc_Refer_Ext;
                $list["Document_No"] = $value->Document_No;
                $list["Real_Action_Date"] = $value->Real_Action_Date;
                $list["Cont_No"] = $value->Cont_No;
                $list["Cont_Size"] = $value->Cont_Size_No . $value->Cont_Size_Unit_Code;
                $list["Product_Code"] = $value->Product_Code;
                $list["Product_NameEN"] = $value->Product_NameEN;
                $list["Product_Lot"] = $value->Product_Lot;
                $list["Receive_Date"] = $value->Receive_Date;
                $list["Storage_Day"] = $value->Storage_Day;
                $list["DP_Qty"] = $value->DP_Qty;
                $data[$group_head][] = $list;
            } else {
                $list = array();
                $list["Doc_Refer_Ext"] = $value->Doc_Refer_Ext;
                $list["Document_No"] = $value->Document_No;
                $list["Real_Action_Date"] = $value->Real_Action_Date;
                $list["Cont_No"] = $value->Cont_No;
                $list["Cont_Size"] = $value->Cont_Size_No . $value->Cont_Size_Unit_Code;
                $list["Product_Code"] = $value->Product_Code;
                $list["Product_NameEN"] = $value->Product_NameEN;
                $list["Product_Lot"] = $value->Product_Lot;
                $list["Receive_Date"] = $value->Receive_Date;
                $list["Storage_Day"] = $value->Storage_Day;
                $list["DP_Qty"] = $value->DP_Qty;
                $data[$group_head][] = $list;
            }
        }
        return $data;
    }

}
