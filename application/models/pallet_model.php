<?php

/**
 * Description of counting_model
 *
 * @author Sureerat
 */
class pallet_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function show_putaway() {
        $this->db->select("DISTINCT STK_T_Workflow.Document_No,STK_T_Order.Doc_Refer_Ext,STK_T_Order.Doc_Refer_Int,STK_T_Order.Order_Id");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.module", "putaway");
        $query = $this->db->get();
        // echo $this->db->last_query();
        return $query;
    }

    #add join table  ADM_M_UserLogin and CTL_M_Contact for ISSUE 3323 :by kik : 20140204
    function get_pallet_detail($pallet_id = '') {

        $conf = $this->config->item('_xml'); // By ball : 20140707

        if ($pallet_id == "") {

            return array();

        } else {

            $this->db->select("*");
            $this->db->select("CONVERT(varchar(10),Pallet.Create_Date,103) AS Create_Date");
            $this->db->select("(Co.First_NameTH +' '+ Co.Last_NameTH) as Create_By_Name");

             /*
             * Query Select Container Info
             */
            if($conf['container']):
                $this->db->select("Cont.Cont_No,Cont_Size.Cont_Size_No,Cont_Size.Cont_Size_Unit_Code");
            endif;


            $this->db->from("STK_T_Pallet Pallet");
            $this->db->join("CTL_M_Pallet_Master Pallet_M","Pallet.Pallet_Master_Id = Pallet_M.Pallet_Master_Id","LEFT");
            $this->db->join("ADM_M_UserLogin UL","Pallet.Create_By = UL.UserLogin_Id" ,'LEFT');
            $this->db->join("CTL_M_Contact CO","UL.Contact_Id = Co.Contact_Id" ,'LEFT');


            /**
             * Query Join Container Info
             */
            if($conf['container']):
                $this->db->join("CTL_M_Container Cont","Pallet.Cont_Id = Cont.Cont_Id");
                $this->db->join("CTL_M_Container_Size Cont_Size","Cont.Cont_Size_Id = Cont_Size.Cont_Size_Id");
            endif;

            $this->db->where("Pallet_Id", $pallet_id);

            $query = $this->db->get();
            $return_query = $query->row();
//            echo $this->db->last_query();exit();
            return $return_query;
        }
    }

    function get_order_info($Order_Id) {
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->where("Order_Id", $Order_Id);
        $query = $this->db->get();
        return $query->result();
    }

    function show_pallet() {
        $this->db->select("*");
        $this->db->from("STK_T_Pallet");
        $query = $this->db->get();
        return $query->result();
    }

    function save_pallet($input) {
        //$data['']=$input[''];
        $data['Pallet_Code'] = trim($input['pallet_code']);
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        $data['Pallet_Width'] = trim($input['pallet_width']);
        $data['Pallet_Lenght'] = trim($input['pallet_lenght']);
        $data['Pallet_Height'] = trim($input['pallet_height']);
        $data['Min_Load'] = trim($input['min_load']);
        $data['Max_load'] = trim($input['max_load']);
        $data['Capacity_Max'] = trim($input['capacity']);
        $data['Weight_Max'] = trim($input['weight']);
        $data['Create_By'] = trim($input['user_id']);
        $data['Create_Date'] = date("Y-m-d H:i:s");
        //$data['Modified_By']=$input[''];
        //$data['Modified_Date']=$input[''];
        $data['Active'] = ACTIVE;
        $data['Is_Full'] = 'N';
        $this->db->insert('STK_T_Pallet', $data);
        $this->db->select("Pallet_Id");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $input['pallet_code']);
        $this->db->where("Pallet_Name", $input['pallet_name']);
        $this->db->where("Pallet_Type", $input['pallet_type']);
        $get_id = $this->db->get();
        $result_id = $get_id->result();
        return array($result_id[0]->Pallet_Id, "C001");
    }

    function update_pallet($input) {
        $data['Pallet_Code'] = trim($input['pallet_code']);
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        $data['Pallet_Width'] = trim($input['pallet_width']);
        $data['Pallet_Lenght'] = trim($input['pallet_lenght']);
        $data['Pallet_Height'] = trim($input['pallet_height']);
        $data['Min_Load'] = trim($input['min_load']);
        $data['Max_load'] = trim($input['max_load']);
        $data['Capacity_Max'] = trim($input['capacity']);
        $data['Weight_Max'] = trim($input['weight']);
        $data['Modified_By'] = $input['user_id'];
        $data['Modified_Date'] = date("Y-m-d H:i:s");
        $data['Active'] = ACTIVE;
        $data['Is_Full'] = 'N';
        $this->db->where('Pallet_Id', $input['pallet_id']);
        $this->db->update('STK_T_Pallet', $data);
        return array($input['pallet_id'], "C002");
    }

    function get_pallet_in_location ($location_id){

        $this->db->select('Pallet_Id');
        $this->db->from('STK_T_Pallet');
        $this->db->where('Actual_Location_Id',$location_id);
        $this->db->where('Build_Type="INBOUND"');
        $get_query = $this->db->get();
        $result = $get_query->result();
        return $result;

    }

    function get_pallet_in_orderDetail ($order_id,$use_table = "STK_T_Order_Detail"){

        $this->db->select('distinct(Pallet_Id)');
        $this->db->select('Suggest_Location_Id');
        $this->db->select('Actual_Location_Id');
        $this->db->select('Old_Location_Id');
        $this->db->from($use_table);
        $this->db->where('Order_Id',$order_id);
        $this->db->where('DP_Type_Pallet ="FULL"');
        $get_query = $this->db->get();
        $result = $get_query->result();
        return $result;

    }

    function  update_pallet_colunm($colunm,$where){

        $this->db->where($where);
        $this->db->update('STK_T_Pallet', $colunm);
        
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    function  insert_pallet_history_location($colunm){

        $this->db->insert('STK_T_Pallet_History_Location', $colunm);

        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

     function  insert_pallet_detail_order($order_id){

         // Fix Re-Pallet
         $this->db->where("Order_Id" , $order_id);
         $q = $this->db->get("STK_T_Pallet")->row();
         // CASE RE-PALLET
         if (!$q) {
            $this->db->query("INSERT INTO STK_T_Pallet_Detail(Pallet_Id,Item_Id,Confirm_Qty,Create_Date,Create_By,Inbound_Id,Parent_Id)
            SELECT Pallet_Id,Item_Id,Confirm_Qty,GETDATE(),Activity_By,Inbound_Item_Id,NULL
            FROM STK_T_Order_Detail
            WHERE Order_Id=".$order_id." AND Pallet_Id IS NOT NULL");
            //echo $this->db->last_query();
         } else {
            $this->db->query("
                INSERT INTO STK_T_Pallet_Detail
                (Pallet_Id,Item_Id,Confirm_Qty,Create_Date,Create_By,Inbound_Id,Parent_Id)
                SELECT Pallet_Id,Item_Id,Confirm_Qty,GETDATE(),Activity_By,Inbound_Item_Id
                ,(select TOP 1 ID from STK_T_Pallet_Detail where Inbound_Id = Inbound_Item_Id and Active=1 order by ID asc)
                FROM STK_T_Order_Detail
                WHERE Order_Id=".$order_id." AND Pallet_Id IS NOT NULL");
         }

//        p($this->db->last_query());exit();
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

     function  insert_pallet_detail_relocatPT($order_id){

        $this->db->query("
            INSERT INTO STK_T_Pallet_Detail
                (Pallet_Id,Item_Id,Confirm_Qty,Create_Date,Create_By,Inbound_Id,Parent_Id)
                SELECT Pallet_Id,Item_Id,Confirm_Qty,GETDATE(),".$this->session->userdata('user_id').",Inbound_Item_Id
                ,(select TOP 1 ID from STK_T_Pallet_Detail where Inbound_Id = Inbound_Item_Id and Active=1 order by ID asc)
            FROM STK_T_Relocate_Detail
            WHERE Order_Id=".$order_id."
                AND Pallet_Id IS NOT NULL
                --AND DP_Type_Pallet='PARTAIL'");  //comment out by por :เนื่องจากทำให้เคน FULL ไม่แสดง

        //p($this->db->last_query()); //exit();
        $afftectedRows = $this->db->affected_rows();

        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    public function get_item_update_inbound($order_id){

        $this->db->select("Inbound_Item_Id");
        $this->db->select("STK_T_Relocate_Detail.Pallet_Id");
        $this->db->select("Inbound_Id as New_Inbound_Id");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->join("STK_T_Inbound","STK_T_Inbound.History_Item_Id = STK_T_Relocate_Detail.Inbound_Item_Id");
        $this->db->where("STK_T_Relocate_Detail.Order_Id", $order_id);
        $this->db->where("STK_T_Relocate_Detail.DP_Type_Pallet = 'FULL'");
        $this->db->where("STK_T_Inbound.Active = 'Y'");
        $query = $this->db->get();
        $result = $query->result();
        return $result ;

    }

   function  update_pallet_detail_colunm($colunm,$where){

        $this->db->where($where);
        $this->db->update('STK_T_Pallet_Detail', $colunm);

        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }

    public function  get_palletId_by_code($pallet_code){
//        echo $pallet_code;exit();

        $this->db->select("Pallet_Id");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code",$pallet_code);
        $get_query = $this->db->get();
        $result = $get_query->row();
        return $result->Pallet_Id;
    }

   function  update_order_detail_colunm($colunm,$where){

        $this->db->where($where);
        $this->db->update('STK_T_Order_Detail', $colunm);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Update success.
        } else {
            return FALSE; //Update unsuccess.
        }
    }



    function get_pallet_detail_packing($document_no = '', $convert_date_type = '103') {

        $conf = $this->config->item('_xml'); // By ball : 20140707

        if ($document_no == "") {

            return array();
        } else {

            $convert_length = 10;
            if ($convert_date_type == '13'):
                $convert_length = 50;
            endif;

            $this->db->select("*");
            $this->db->select("CONVERT(varchar({$convert_length}),Pallet.Create_Date,{$convert_date_type}) AS Create_Date");
            //$this->db->select("CONVERT(varchar({$convert_length}),STK_T_Order.Actual_Action_Date,{$convert_date_type}) AS Create_Date_Reprint");
            $this->db->select("CONVERT(varchar({$convert_length}),Pallet.Create_Date,{$convert_date_type}) AS Create_Date_Reprint");
            $this->db->select("(Co.First_NameTH +' '+ Co.Last_NameTH) as Create_By_Name");

            /*
             * Query Select Container Info
             */
            if ($conf['container']):
                $this->db->select("Cont.Cont_No,Cont_Size.Cont_Size_No,Cont_Size.Cont_Size_Unit_Code");
            endif;


            $this->db->from("STK_T_Pallet Pallet");
            $this->db->join("CTL_M_Pallet_Master Pallet_M", "Pallet.Pallet_Master_Id = Pallet_M.Pallet_Master_Id", "LEFT");
            $this->db->join("ADM_M_UserLogin UL", "Pallet.Create_By = UL.UserLogin_Id", 'LEFT');
            $this->db->join("CTL_M_Contact CO", "UL.Contact_Id = Co.Contact_Id", 'LEFT');
            $this->db->join("STK_T_Order", "Pallet.Order_Id = STK_T_Order.Order_Id", 'LEFT');


            /**
             * Query Join Container Info
             */
            if ($conf['container']):
                $this->db->join("CTL_M_Container Cont", "Pallet.Cont_Id = Cont.Cont_Id", "LEFT");
                $this->db->join("CTL_M_Container_Size Cont_Size", "Cont.Cont_Size_Id = Cont_Size.Cont_Size_Id", "LEFT");
            endif;

            $this->db->where("STK_T_Order.Document_No", $document_no);
	    $this->db->where("Pallet.Active","Y");
            $query = $this->db->get();
            $return_query = $query->result();
//            echo $this->db->last_query();exit();
            return $return_query;
        }
    }

    function get_packingList_receive_reprint($pallet_id) {
//        echo $pallet_id;exit();
        $this->db->select('pallet_detail.*');
        $this->db->select('STK_T_Inbound.Product_Code
            ,STK_T_Inbound.Product_Status
            ,STK_T_Inbound.Product_Sub_Status
            ,STK_T_Inbound.Product_Lot
            ,STK_T_Inbound.Product_Serial
            ,CONVERT(varchar(10),STK_T_Inbound.Product_Mfd,103) as Product_Mfd
            ,CONVERT(varchar(10),STK_T_Inbound.Product_Exp,103) as Product_Exp
            ,STK_T_Inbound.Price_Per_Unit
            ,STK_T_Inbound.Unit_Price_Id
            ,STK_T_Inbound.All_Price
            ,STK_T_Inbound.Unit_Id
	    ,g.ProductGroup_NameEN As product_class');
        $this->db->select('STK_T_Inbound.Document_No,STK_T_Inbound.Doc_Refer_Ext');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');
	$this->db->select('loc.Location_Code');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Inbound', 'pallet_detail.Pallet_Id = STK_T_Inbound.Pallet_Id','LEFT');
        $this->db->join('STK_M_Product product', 'STK_T_Inbound.Product_Id = product.Product_Id', 'LEFT');
	$this->db->join('CTL_M_ProductGroup g', 'g.ProductGroup_Id = product.ProductGroup_Id', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', 'STK_T_Inbound.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = "' . $this->config->item('lang3digit') . '"', 'LEFT');
        $this->db->join('SYS_M_Domain S2', 'STK_T_Inbound.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code="PROD_STATUS" ', 'LEFT');
        $this->db->join('SYS_M_Domain S3', 'STK_T_Inbound.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code="SUB_STATUS" ', 'LEFT');
        $this->db->join('SYS_M_Domain S4', 'STK_T_Inbound.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code="PRICE_UNIT" ', 'LEFT');

	$this->db->join('STK_T_Pallet pl', 'pl.Pallet_Id = pallet_detail.Pallet_Id', 'LEFT');
	$this->db->join('STK_M_Location loc', 'loc.Location_Id = pl.Actual_Location_Id ', 'LEFT');

        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
//        $this->db->where('product.Active', 'Y');
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        $result = $query->result_array();

        return $result;
    }


}

?>
