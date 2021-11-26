<?php

/**
 * Description of counting_model
 *
 * @author Sureerat
 */
class pallet_model_reprint extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getPaList($module = 'putaway', $DocReferInt = '') {// Edit by Ton! 20130513
        $this->db->select("DISTINCT STK_T_Workflow.Flow_Id as Id, STK_T_Workflow.Present_State,
            STK_T_Order.Document_No, STK_T_Order.Doc_Refer_Ext, STK_T_Order.Doc_Refer_Int, STK_T_Order.Order_Id, CTL_M_Contact.First_NameEN+' '+Last_NameEN AS Vendor_Name");
        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("CTL_M_Contact", "STK_T_Order.Vendor_Id = CTL_M_Contact.Contact_Id AND CTL_M_Contact.IsVendor = 0", "left");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        $this->db->where("STK_T_Order.Doc_Type!=", "RCV002"); // RCV002== return
        if ($DocReferInt != '') {
            $this->db->where("STK_T_Order.Doc_Refer_Int", $DocReferInt);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
    }

    function show_putaway() {
        $this->db->select("DISTINCT STK_T_Workflow.Document_No,STK_T_Order.Doc_Refer_Ext,STK_T_Order.Doc_Refer_Int,STK_T_Order.Order_Id");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->where("SYS_M_Stateedge.Module", "putaway");
        $query = $this->db->get();
        // echo $this->db->last_query();
        return $query;
    }

    function getProdbyCode($order_id, $prodCode) {
        $this->db->select("STK_T_Order_Detail.*");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->where("SYS_M_Stateedge.Module", "putaway");

//        $this->db->join("STK_M_Location", "STK_T_Order_Detail.Suggest_Location_Id = STK_M_Location.Location_Id");// Edit by Ton! 20130710
        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        if ($order_id != "") {
            $this->db->where_in("STK_T_Order.Order_Id", $order_id);
        }
        if ($prodCode != "") {
            $this->db->where_in("STK_T_Order_Detail.Product_Code", $prodCode);
        }
        $query = $this->db->get();
        return $query->result();
    }

    function _getProdbyCode($order_id, $prodCode, $pallet_id, $pallet_type) {
        // $this->db->select("STK_T_Order_Detail.*");
        $this->db->select("DISTINCT STK_T_Order_Detail.Item_Id,STK_T_Order_Detail.Order_Id,STK_T_Order_Detail.Product_Id
							,STK_T_Order_Detail.Product_Code,STK_T_Order_Detail.Product_Status,STK_T_Order_Detail.Product_Sub_Status
							,STK_T_Order_Detail.Pallet_Id,STK_T_Order_Detail.Reserv_Qty,STK_T_Order_Detail.Confirm_Qty");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id
											AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->where("SYS_M_Stateedge.Module", "putaway");

        //$this->db->join("STK_M_Location", "STK_T_Order_Detail.Suggest_Location_Id = STK_M_Location.Location_Id");// Edit by Ton! 20130710

        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        if ($order_id != "") {
            $this->db->where_in("STK_T_Order.Order_Id", $order_id);
        }
        if ($prodCode != "") {
            $this->db->where_in("STK_T_Order_Detail.Product_Code", $prodCode);
        }
        $query = $this->db->get();

        $result = $query->result();
        //return $result;
        //echo $this->d
        //echo $this->db->last_query();

        if (count($result) == 0) {
            return array('N', 'Not found');
        }
        //print_r($result);
        $p_status = $result[0]->Product_Status;
        //echo ' p status = '.$p_status;

        if ($this->check_product_in_pallet($prodCode, $p_status, $pallet_id, $pallet_type) == 0) {
            if ($pallet_type == "PT001") {
                $message = 'Product Status not match';
            } else {
                $message = 'SKU and Product Status not match';
            }
            return array('N', $message);
        } else {
            return $result;
        }
    }

    /**
     * BALL
     * @param unknown $document_reference
     * @param unknown $prodCode
     * @param unknown $pallet_id
     * @param unknown $pallet_type
     * @return multitype:string |unknown
     */
    function get_product($document_reference, $prodCode, $pallet_id, $pallet_type) {
        $this->db->select("DISTINCT STK_T_Order_Detail.Item_Id,STK_T_Order_Detail.Order_Id,STK_T_Order_Detail.Product_Id
    			,STK_T_Order_Detail.Product_Code,STK_T_Order_Detail.Product_Status,STK_T_Order_Detail.Product_Sub_Status
    			,STK_T_Order_Detail.Pallet_Id,STK_T_Order_Detail.Reserv_Qty,STK_T_Order_Detail.Confirm_Qty, STK_T_Pallet_Detail.Confirm_Qty as Pallet_Qty");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id", "LEFT");
        $this->db->where("SYS_M_Stateedge.Module", "putaway");
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        if ($document_reference != "") {
            $this->db->where_in("STK_T_Order.Document_No", $document_reference);
        }
        if ($prodCode != "") {
            $this->db->where_in("STK_T_Order_Detail.Product_Code", $prodCode);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        $result = $query->result_array();

        if (count($result) == 0) {
            return array('N', 'Not found');
        }
        $p_status = $result[0]['Product_Status'];

        if ($this->check_product_in_pallet($prodCode, $p_status, $pallet_id, $pallet_type) == 0) {
            if ($pallet_type == "PT001") {
                $message = 'Product Status not match';
            } else {
                $message = 'SKU and Product Status not match';
            }
            return array('N', $message);
        } else {
            $response = array();
            foreach ($result as $r => $value) :
                if (array_key_exists($value['Item_Id'], $response)) {
                    $response[$value['Item_Id']]['Pallet_Qty'] = $response[$value['Item_Id']]['Pallet_Qty'] + $value['Pallet_Qty'];
                } else {
                    $response[$value['Item_Id']] = $value;
                }
            endforeach;

            return $response;
        }
    }

    function check_product_in_pallet($prod_Code, $product_status, $pallet_id, $pallet_type) {
        //PT001 = Mix , PT002 = Full
        if ($pallet_type == "PT001") {
            $compare = $this->check_pallet_mix($pallet_id);
            $compare2 = '';
        } else {
            $tmp_compare = $this->check_pallet_full($pallet_id);
            $compare = $tmp_compare[1];
            $compare2 = $tmp_compare[0];
        }

        if ($compare2 != "" && $compare != "" && $prod_Code == $compare2 && $product_status == $compare) {
            return 1;
        } else if ($compare2 == "" && $compare != "" && $product_status == $compare) {
            return 1;
        } else if ($compare == "" && $compare2 == "") {
            return 1;
        } else {
            return 0;
        }
    }

    function check_pallet_mix($pallet_id) {
        $this->db->select("DISTINCT Product_Status");
        $this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("STK_T_Pallet_Detail.Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        $q = $this->db->get();
        $r = $q->result();
        if (count($r) == 0) {
            return '';
        }
        return $r[0]->Product_Status;
    }

    function check_pallet_full($pallet_id) {
        $this->db->select('DISTINCT Product_Code,Product_Status');
        $this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("STK_T_Pallet_Detail.Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        $q = $this->db->get();
        $r = $q->result();
        if (count($r) == 0) {
            return array('', '');
        }
        return array($r[0]->Product_Code, $r[0]->Product_Status);
    }

    /**
     *
     * @param unknown $order_id
     * @param unknown $prodCode
     * @param unknown $pallet_id
     * @param unknown $pallet_type
     * @return multitype:string |unknown
     */
    function get_product_dp($document_reference, $prodCode, $pallet_id, $pallet_type) {
        $this->db->select("DISTINCT STK_T_Order_Detail.Item_Id,STK_T_Order_Detail.Order_Id,STK_T_Order_Detail.Product_Id
    			,STK_T_Order_Detail.Product_Code,STK_T_Order_Detail.Product_Status,STK_T_Order_Detail.Product_Sub_Status
    			,STK_T_Order_Detail.Pallet_Id,STK_T_Order_Detail.Reserv_Qty,STK_T_Order_Detail.Confirm_Qty, STK_T_Pallet_Detail.Confirm_Qty as Pallet_Qty");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id", "LEFT");
        $this->db->where("SYS_M_Stateedge.Module", "dispatch");
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        if ($document_reference != "") {
            $this->db->where_in("STK_T_Order.Document_No", $document_reference);
        }
        if ($prodCode != "") {
            $this->db->where_in("STK_T_Order_Detail.Product_Code", $prodCode);
        }
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) == 0) {
            return array('N', 'Not found');
        }

        //echo $this->db->last_query();

        $check_pallet = $this->check_order_in_pallet($pallet_id, $document_reference);

        if ($check_pallet != "") {
            return array('N', $check_pallet);
        }
        return $result;
    }

    //////////////////////

    function _getProdbyCodeDispath($order_id, $prodCode, $pallet_id, $pallet_type) {
        // $this->db->select("STK_T_Order_Detail.*");
        $this->db->select("DISTINCT STK_T_Order_Detail.Item_Id,STK_T_Order_Detail.Order_Id,STK_T_Order_Detail.Product_Id
							,STK_T_Order_Detail.Product_Code,STK_T_Order_Detail.Product_Status,STK_T_Order_Detail.Product_Sub_Status
							,STK_T_Order_Detail.Pallet_Id,STK_T_Order_Detail.Reserv_Qty,STK_T_Order_Detail.Confirm_Qty");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id
											AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("SYS_M_State", "STK_T_Workflow.Present_State = SYS_M_State.State_No AND STK_T_Workflow.Process_Id = SYS_M_State.Process_Id");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->where("SYS_M_Stateedge.Module", "dispatch");

        //$this->db->join("STK_M_Location", "STK_T_Order_Detail.Suggest_Location_Id = STK_M_Location.Location_Id");// Edit by Ton! 20130710

        $this->db->where("STK_T_Order_Detail.Pallet_Id", NULL);
        if ($order_id != "") {
            $this->db->where_in("STK_T_Order.Order_Id", $order_id);
        }
        if ($prodCode != "") {
            $this->db->where_in("STK_T_Order_Detail.Product_Code", $prodCode);
        }
        $query = $this->db->get();
        $result = $query->result();
        if (count($result) == 0) {
            return array('N', 'Not found');
        }
        echo '<br> ' . $this->db->last_query();

        $check_pallet = $this->check_order_in_pallet($pallet_id, $order_id);
        //echo '<br> count = '.$check_pallet;
        if ($check_pallet != "") {
            return array('N', $check_pallet);
        }
        return $result;
    }

    function check_order_in_pallet($pallet_id, $order_id) {
        $this->db->select("DISTINCT STK_T_Order_Detail.Order_Id,STK_T_Order.Doc_Refer_Ext");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id=STK_T_Order_Detail.Order_Id", "left");
        $this->db->where("Pallet_Id", $pallet_id);
        $q = $this->db->get();
        $r = $q->result();
        if (count($r) == 0 || $r[0]->Order_Id == $order_id) {
            return '';
        } else {
            return 'This pallet for Document External:' . $r[0]->Doc_Refer_Ext;
        }
    }

    function get_order_info($doc_no) {
        $this->db->select("*");
        $this->db->where("Order_Id", $doc_no);
        $this->db->from("STK_T_Order");
        $query = $this->db->get();
        return $query->result();
    }

    function show_pallet($ref_no) {
        $build_type = $this->get_pc_type($ref_no);
        $this->db->select("*");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Is_Full", 'N');
        $this->db->where("Build_Type", $build_type);
        $query = $this->db->get();
        return $query->result();
    }

    function get_pc_type($ref_no) {
        $this->db->select("Process_Type");
        $this->db->from("STK_T_Order");
        $this->db->where("Document_No", $ref_no);
        $q = $this->db->get();
        $r = $q->result();
        return $r[0]->Process_Type;
    }

    function get_pl_id_by_code($code) {
        $this->db->select("Pallet_Id");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $code);
        $query = $this->db->get();
        $r = $query->result();
        return $r[0]->Pallet_Id;
    }

    function get_pl_type_by_code($code) {
        $this->db->select("Pallet_Type");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $code);
        $query = $this->db->get();
        $r = $query->result();
        return $r[0]->Pallet_Type;
    }

    function get_pl_status($code) {
        $this->db->select("Is_Full");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $code);
        $query = $this->db->get();
        $r = $query->result();
        return $r[0]->Is_Full;
    }

    function show_pd_in_pallet($pallet_id) {
        /*
          $this->db->select("*");
          $this->db->from("STK_T_Order_Detail");
          $this->db->where("Pallet_Id", $pallet_id);
          $this->db->where("Active", 'Y');
          $q = $this->db->get();
          return $q->result();
         */

        $this->db->select("STK_T_Order_Detail.Order_Id, STK_T_Order_Detail.Item_Id, STK_T_Order_Detail.Product_Code, STK_T_Order_Detail.Reserv_Qty, STK_T_Pallet_Detail.Confirm_Qty, STK_T_Pallet_Detail.Id");
        $this->db->from("STK_T_Pallet_Detail");
        $this->db->join("STK_T_Order_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id");
        $this->db->where("STK_T_Pallet_Detail.Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        $q = $this->db->get();
        return $q->result();
    }

    function show_pdCode_in_pallet($pallet_id) {
        $this->db->select("Product_Code");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where("Active", 'Y');
        $q = $this->db->get();
        $row = array();
        foreach ($q->result() as $r) {
            $row[] = "'" . $r->Product_Code . "'";
        }
        return $row;
    }

    function show_item_in_pallet($pallet_id) {
        $this->db->select("Item_Id");
        $this->db->from("STK_T_Order_Detail");
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where("Active", 'Y');
        $q = $this->db->get();
        $row = array();
        foreach ($q->result() as $r) {
            $row[] = $r->Item_Id;
        }
        return $row;
    }

    function save_pallet($input) {
        //if ($this->check_pallet_name($input['pallet_name'], '') == 0) {
        $pallet_code = $this->get_id($input['build_type']);
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        $data['Pallet_Master_Id'] = trim($input['pallet_master_template']);
        $data['Cont_Id'] = trim(@$input['container']);
        /* $data['Pallet_Width'] = trim($input['pallet_width']);
          $data['Pallet_Lenght'] = trim($input['pallet_lenght']);
          $data['Pallet_Height'] = trim($input['pallet_height']);
          $data['Min_Load'] = trim($input['min_load']);
          $data['Max_Load'] = trim($input['max_load']);
          $data['Capacity_Max'] = trim($input['capacity']);
          $data['Weight_Max'] = trim($input['weight']); */
        $data['Create_By'] = trim($input['user_id']);
        $data['Create_Date'] = date("Y-m-d H:i:s");
        $data['Build_Type'] = $input['build_type'];
        $data['Order_Id'] = $input['order_id'];
        $data['Active'] = 'Y';
        $data['Is_Full'] = 'N';
        $this->db->where('Pallet_Code', $pallet_code);
        $this->db->update('STK_T_Pallet', $data);
        $last_insert_id = $this->db->insert_id();
        if ($input['build_type'] == "INBOUND") {
            $response = "C001";
        } else if ($input['build_type'] == "RE-PALLET") {
            $response = "C005";
        } else {
            $response = "C003";
        }
        return array($pallet_code, $response, $last_insert_id, $data['Pallet_Name']);

        //}
        //return array('', "C000", '', '');
    }

    function save_pallet_ap_mixDoc($input) {
        $pallet_code = $this->get_id('OUTBOUND');
//            p($input);
//            echo $pallet_code;exit();
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        $data['Pallet_Master_Id'] = trim($input['pallet_master_template']);
        $data['Cont_Id'] = trim(@$input['container']);
        $data['Consignee_Id'] = trim(@$input['consignee']);
        $data['Product_Lot'] = trim(@$input['lot']);
        $data['Product_Serial'] = trim(@$input['serial']);
        $data['Create_By'] = trim($input['user_id']);
        $data['Create_Date'] = date("Y-m-d H:i:s");
        $data['Build_Type'] = $input['build_type'];
        $data['Active'] = 'Y';
        $data['Active_Confirm'] = 'N';  //กำหนดให้เป็น N จนกว่าจะมีการ confirm HH ในหน้า list ถึงจะเป็น Y
        $data['Is_Full'] = 'N';
        $this->db->where('Pallet_Code', $pallet_code);
        $this->db->update('STK_T_Pallet', $data);

//            echo $this->db->last_query();exit();
        $last_insert_id = $this->db->insert_id();
        $aff_row = $this->db->affected_rows();

        #insert data ใน temp


        if ($aff_row > 0):
            $response = "C001";
        else:
            $response = "E001";
        endif;

        return array($pallet_code, $response, $last_insert_id);
    }

    function check_pallet_name($name, $pallet_id = '') {
        $this->db->select("Pallet_Id");
        $this->db->where("Pallet_Name", $name);
        if ($pallet_id != "") {
            $this->db->where("Pallet_Id!=", $pallet_id);
        }
        $this->db->from("STK_T_Pallet");
        $q = $this->db->get();
        $r = $q->result();
        if (count($r) == 0) {
            return 0;
        }
        return 1;
    }

    function get_id($build_type) {

        $data['Min_Load'] = '';
        $data['Create_Date'] = date("Y-m-d H:i:s");
        $this->db->insert('STK_T_Pallet', $data);

        //Get max pallet code
        $this->db->select("CONVERT(INT,MAX(SUBSTRING(Pallet_Code,5,5))) as max_code");
        $this->db->from('STK_T_Pallet');
        $this->db->where('Build_Type', $build_type);
        $max_pallet_code = $this->db->get()->row();
        $tid_code = $max_pallet_code->max_code;

//        $this->db->from('STK_T_Pallet');
//        $this->db->where('Build_Type',$build_type);
//        $query = $this->db->get();
//        $tid_code = $query->num_rows();
        $tid_code++;

        $tid = $this->db->insert_id();
        $id = str_pad($tid_code, 5, "0", STR_PAD_LEFT);
        if ($build_type == "INBOUND") {
            $code = "I" . date("y") . "-" . $id;
        } elseif ($build_type == "OUTBOUND") {
            $code = "O" . date("y") . "-" . $id;
        } elseif ($build_type == "RE-PALLET") {
            $code = "R" . date("y") . "-" . $id;
        }

        $data['Pallet_Code'] = $code;
        $this->db->where("Pallet_Id", $tid);
        $this->db->update('STK_T_Pallet', $data);

        return $code;
    }

    function update_pallet($input) {
        //if ($this->check_pallet_name($input['pallet_name'], $input['pallet_id']) == 0) :
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        /*
          $data['Pallet_Width'] = trim($input['pallet_width']);
          $data['Pallet_Lenght'] = trim($input['pallet_lenght']);
          $data['Pallet_Height'] = trim($input['pallet_height']);
          $data['Min_Load'] = trim($input['min_load']);
          $data['Max_Load'] = trim($input['max_load']);
          $data['Capacity_Max'] = trim($input['capacity']);
          $data['Weight_Max'] = trim($input['weight']);
         */
        $data['Pallet_Master_Id'] = $input['pallet_master_template'];
        $data['Cont_Id'] = @$input['container'];
        $data['Modified_By'] = $input['user_id'];
        $data['Modified_Date'] = date("Y-m-d H:i:s");
        //$data['Active'] = 'Y';
        //$data['Is_Full'] = 'N';
        $this->db->where('Pallet_Id', $input['pallet_id']);
        $this->db->update('STK_T_Pallet', $data);
        //p($this->db->last_query());
        if ($input['build_type'] == "INBOUND") {
            $status = "C002";
        } else {
            $status = "C003";
        }
        // else:
        //     $status = "C000";
        // endif;

        return array($input['pallet_code'], "C002");
    }

    function update_pallet_dp_mixDoc($input) {
        $data['Pallet_Type'] = trim($input['pallet_type']);
        $data['Pallet_Name'] = trim($input['pallet_name']);
        $data['Pallet_Master_Id'] = $input['pallet_master_template'];
        $data['Cont_Id'] = @$input['container'];
        $data['Modified_By'] = $input['user_id'];
        $data['Modified_Date'] = date("Y-m-d H:i:s");
        $this->db->where('Pallet_Id', $input['pallet_id']);
        $this->db->update('STK_T_Pallet', $data);
        //p($this->db->last_query());

        return array($input['pallet_code'], "C002");
    }

    function get_pallet_detail($pallet_id) {
        if ($pallet_id == "") {
            return array();
        } else {
            $this->db->select("*");
            $this->db->where("Pallet_Id", $pallet_id);
            $this->db->from("STK_T_Pallet");
            $query = $this->db->get();
            return $query->result();
        }
    }

    function get_pallet_detail_code($pallet_code) {
        if ($pallet_code == "") {
            return array();
        } else {
            $this->db->select("*");
            $this->db->where("Pallet_Code", $pallet_code);
            $this->db->from("STK_T_Pallet");
            $query = $this->db->get();
            return $query->result_array();
        }
    }

    function pl_name_by_code($pallet_code) {
        $this->db->select("Pallet_Name");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $pallet_code);
        $q = $this->db->get();
        $r = $q->result();
        if (count($r) != 0) {
            return $r[0]->Pallet_Name;
        }
        return '';
    }

    //add parameter $state for check doc_ext in this state only (fix partail case) : by kik : 20140829
    function get_order_id_by_doc($ref_no, $state) {
        $this->db->select("Order_Id");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Workflow", "STK_T_Order.Flow_Id = STK_T_Workflow.Flow_Id ");
        $this->db->where("Doc_Refer_Ext", $ref_no);
        if ($state != null):
            $this->db->where("STK_T_Workflow.Present_State", $state);
        endif;
        $q = $this->db->get();
        $r = $q->result();



        if (!empty($r)) {
            return $r[0]->Order_Id;
        }
        return '';
    }

    function save_pallet_to_order($pallet_id, $order_id, $item_id) {
        if (count($item_id) == 0) {
            return '0';
        }

        foreach ($item_id as $id) {
            $data['Pallet_Id'] = $pallet_id;
            $this->db->where('Item_Id', $id);
            $this->db->update('STK_T_Order_Detail', $data);
        }

        return '1';
    }

    function confirm_pallet_to_order($params) {
        if (count($params['txtSKUItem']) == 0) {
            return '0';
        }

        $items = $params['txtSKUItem'];

        foreach ($items as $idx => $value) {

            if ((int) $value['Id'] == 0 && $value['Confirm_Qty'] > 0) {

                // Check Quantity Data Before Putaway
                // End Check
                $data = array(
                    "Pallet_Id" => $params['pallet_id'],
                    "Item_Id" => $value['Item_Id'],
                    "Confirm_Qty" => $value['Confirm_Qty'],
                );

                $this->db->set('Create_Date', 'GETDATE()', FALSE);
                $this->db->insert('STK_T_Pallet_Detail', $data);
            }
        }

        return '1';
    }

    /**
     *
     * @param unknown $pallet_id
     * @param string $params
     */
    function set_pallet_full($pallet_id, $params = NULL) {

        $items = $params['txtSKUItem'];

        $this->db->trans_begin();

        $this->confirm_pallet_to_order($params);

        foreach ($items as $idx => $value) :

            // Select Order Detail Data
            $this->db->where("Item_Id", $value['Item_Id']);
            $query = $this->db->get("STK_T_Order_Detail");

            // Loop for copy new inbound, new order detail
            foreach ($query->result_array() as $row) :
                // Reset identity column
                unset($row['Item_Id']);

                // GET Inbound Id from Item_Id
                $new_reserv_qty = floatval($row['Reserv_Qty'] - $value['Confirm_Qty']);

                // Check If qty not enought roll back transaction
                if ($new_reserv_qty < 0) {
                    $this->db->trans_rollback();
                    return FALSE;
                }

                $row['Reserv_Qty'] = $value['Confirm_Qty'];
                $row['Confirm_Qty'] = $value['Confirm_Qty'];
                $row['Inbound_Item_Id'] = $this->gen_inbound_data($row['Inbound_Item_Id'], $value['Confirm_Qty']); // Copy inbound
                $row['Pallet_Id'] = $pallet_id;
                $this->db->insert('STK_T_Order_Detail', $row); // Copy order detail

                $item_id = $this->db->insert_id();
                $this->db->where("Id", $value['Id']);
                $this->db->update("STK_T_Pallet_Detail", array("Item_Id" => $item_id, "Old_Item_Id" => $value['Item_Id']));

                // Re-Update Order Detail for Old Item Id
                $this->db->where("Item_Id", $value['Item_Id']);
                $this->db->update("STK_T_Order_Detail", array("Reserv_Qty" => $new_reserv_qty, "Confirm_Qty" => $new_reserv_qty));

            endforeach;

        endforeach;

        $data['Is_Full'] = 'Y';
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->update("STK_T_Pallet", $data);

        if ($this->db->trans_status() === FALSE) :
            $this->db->trans_rollback();
            return FALSE;
        else :
            $this->db->trans_commit();
            return TRUE;
        endif;
    }

    /**
     *
     * @param unknown $inbound_id
     * @param unknown $qty
     */
    public function gen_inbound_data($inbound_id, $qty) {

        // Get Orginal Inbound Data
        $this->db->where("Inbound_Id", $inbound_id);
        $row = $this->db->get("STK_T_Inbound")->result_array();
        $remain_qty = floatval($row['0']['Receive_Qty'] - $qty);

        // --
        // Prepare data before insert new inbound
        unset($row['0']['Inbound_Id']);
        $row['0']['Receive_Qty'] = $qty;
        $row['0']['Balance_Qty'] = $qty;

        // Insert new inbound
        $this->db->insert('STK_T_Inbound', $row['0']);

        // get inbound id
        $_inbound_id = $this->db->insert_id();

        // update old data of inbound
        $this->db->where("Inbound_Id", $inbound_id);
        $this->db->update("STK_T_Inbound", array("Receive_Qty" => $remain_qty, "Balance_Qty" => $remain_qty));

        return $_inbound_id;
    }

    function get_pallet_list($Build_Type = NULL, $pallet_code = NULL) { // ปรับการรับ parameter ใหม่เพื่อให้ส่วนอื่นสามารถเรียกใช้ฟังก์ชั่นนี้ได้ด้วย โดยการเซ็ตค่า $Build_Type เป็น NULL และค่อยเช็คค่าเพื่อกำหนดให้ในภายหลังแทน
        if (empty($Build_Type))
            $Build_Type = "INBOUND";

        $this->db->select("distinct (pallet.Pallet_Id)");
        $this->db->select("Pallet_Code,  Pallet_Name, SYS_M_Domain.Dom_EN_SDesc as pallet_type_name,(Select COUNT(*) FROM STK_T_Order_Detail Where STK_T_Order_Detail.Pallet_Id = pallet.Pallet_Id) as Total , (Select sum(Confirm_Qty) FROM STK_T_Order_Detail Where STK_T_Order_Detail.Pallet_Id = pallet.Pallet_Id) as total_qty");
        $this->db->select("t_order.Doc_Refer_Ext,pallet.Cont_Id");
        $this->db->from("STK_T_Pallet pallet");
        $this->db->join("STK_T_Order_Detail detail", "pallet.Pallet_Id = detail.Pallet_Id");
        $this->db->join("STK_T_Order t_order", "detail.Order_Id = t_order.Order_Id");
        $this->db->join("STK_T_Workflow wf", "t_order.Flow_Id = wf.Flow_Id");
        $this->db->join("SYS_M_Domain", "pallet.Pallet_Type = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code = 'PALLET_TYPE'", "LEFT");
        $this->db->where("pallet.Is_Full", "Y");
        $this->db->where("pallet.Actual_Location_Id", NULL);
        $this->db->where("pallet.Build_Type", $Build_Type);
        $this->db->where("pallet.Active", 'Y');

        if ($Build_Type == 'INBOUND')
            $this->db->where("wf.Process_Id", '1');
        $this->db->where("wf.Present_State", '5');

        if (!empty($pallet_code))
            $this->db->where("pallet.Pallet_Code", $pallet_code);

        $this->db->order_by("t_order.Doc_Refer_Ext");

        $query = $this->db->get();

        return $query->result();
    }

    /**
     *
     * @param type $pallet_code
     * @return type
     * @modified by kik : 20140721
     */
    public function get_product_pallet($pallet_code) {

        $this->db->select("STK_T_Order.Doc_Refer_Ext
            , STK_M_Product.Product_NameEN
            , STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Order_Id
            , STK_T_Order_Detail.Product_Code
            , STK_T_Order_Detail.Item_Id
            ,STK_T_Order_Detail.Confirm_Qty");

        $this->db->join("STK_T_Pallet", "STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code");
        $this->db->join("STK_T_Order", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
        $this->db->where("STK_T_Order_Detail.Active", 'Y');
        $this->db->where("STK_M_Product.Active", 'Y');
        $query = $this->db->get("STK_T_Order_Detail");
        return $query->result();
    }

    public function get_product_pallet_and_doc_ext($pallet_code, $doc_ext, $process_type = NULL) {

        $this->db->select("STK_T_Order.Doc_Refer_Ext
            , STK_M_Product.Product_NameEN
            , STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Order_Id
            , STK_T_Order_Detail.Product_Code
            , STK_T_Order_Detail.Item_Id
            , STK_T_Order_Detail.Reserv_Qty");

        $this->db->join("STK_T_Pallet", "STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code");
        $this->db->join("STK_T_Order", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->join("STK_T_Workflow", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
        $this->db->where("STK_T_Order.Doc_Refer_Ext", $doc_ext);
        $this->db->where("STK_T_Order_Detail.Active", 'Y');
        $this->db->where("STK_T_Workflow.Present_State", '3');
        $this->db->where("STK_M_Product.Active", 'Y');
        if (!is_null($process_type))
            $this->db->where("STK_T_Order.Process_Type", $process_type);
        $query = $this->db->get("STK_T_Order_Detail");
        return $query->result();
    }

    /**
     *
     * @param type $pallet_id
     * @return type
     * @modified by kik : 20140721
     */
    public function get_product_pallet_out($pallet_id) {

        $this->db->select("STK_T_Order.Doc_Refer_Ext
            , STK_M_Product.Product_NameEN
            , STK_T_Pallet.Pallet_Code
            , STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Order_Id
            , STK_T_Order_Detail.Product_Code
            , STK_T_Order_Detail.Item_Id
            ,STK_T_Order_Detail.Confirm_Qty");

        $this->db->join("STK_T_Pallet", "STK_T_Order_Detail.Pallet_Id_Out = STK_T_Pallet.Pallet_Id");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code");
        $this->db->join("STK_T_Order", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->where("STK_T_Pallet.Pallet_Id", $pallet_id);
        $this->db->where("STK_M_Product.Active", 'Y');
        $query = $this->db->get("STK_T_Order_Detail");

        return $query->result();
    }

    #comment by kik : 20140721 : for edit query code
//    public function get_product_pallet($pallet_code) {
//    	$this->db->select("STK_T_Order.Doc_Refer_Ext, STK_M_Product.Product_NameEN, STK_T_Order_Detail.Product_Status, STK_T_Order_Detail.Order_Id, STK_T_Order_Detail.Product_Code, STK_T_Pallet_Detail.Item_Id,STK_T_Pallet_Detail.Confirm_Qty");
//    	$this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id");
//    	$this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Item_Id = STK_T_Pallet_Detail.Item_Id");
//    	$this->db->join("STK_M_Product", "STK_M_Product.Product_Code = STK_T_Order_Detail.Product_Code");
//    	$this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
//    	$this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
//    	$query = $this->db->get("STK_T_Pallet");
//    	return $query->result();
//    }

    /**
     * Search Data for Make Put Away Rule
     * @param unknown $pallet_code
     */
    public function prepare_suggest_data($pallet_code) {
        $this->db->select("TOP 1
            STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Product_Sub_Status
            , STK_T_Order_Detail.Product_Status
            , STK_M_Product.ProductCategory_Id
            , STK_M_Product.PutAway_Rule
            , STK_T_Order_Detail.Product_Code
            , STK_T_Order_Detail.Item_Id
            , STK_T_Order_Detail.Confirm_Qty");
//    	$this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id"); //comment by kik : 20140721 not use table pallet detail after confirm pallet
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id");
        $this->db->join("STK_M_Product", "STK_M_Product.Product_Code = STK_T_Order_Detail.Product_Code");
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
        $query = $this->db->get("STK_T_Pallet");
        return $query->result_array();
    }

    /**
     *
     * @param unknown $params
     */
    public function get_suggest_pallet_location($params) {
        //$sql = "EXEC sp_PA_suggestLocation_Pallet ?,?,?,?,?,?,?"; //เปลี่ยนให้ส่งตัวแปรตัวเดียวคือ pallet_id :COMMENT BY POR 2014-09-16
        $sql = "EXEC sp_PA_suggestLocation_Pallet ?";
        $parameter = array(
//    			"@category_id" 			=> $params['category_id']
//    			, "@product_status"	=> $params['product_status']
//    			, "@product_sub"		=> $params['product_sub']
//    			, "@width"					=> $params['width']
//    			, "@length"				=> $params['length']
//    			, "@height"				=> $params['height']
//    			, "@rule"					=> $params['rule']
            "@pallet_id" => $params['pallet_id'] //change parameter from  many to one : ADD BY POR 2014-09-16 // add "" into name parameter by kik : 20141103
        );

        $query = $this->db->query($sql, $parameter);
        $result = $query->result();
        return $result;
    }

    public function generate_column() {
        // Generate Column Name in Order Detail
        $l = 0;
        $column = array();
        $query = $this->db->query("EXEC sp_columns STK_T_Order_Detail");
        $result = $query->result();
        foreach ($result as $i => $v) :
            if ($l > 0) :
                $column[] = $v->COLUMN_NAME;
            endif;
            $l++;
        endforeach;
    }

    public function update_location_pallet($params, $SuggestLocationID, $ActualLocationID, $user_id) {
        $reason = ($params["txtReasonPA"] == "") ? NULL : $params["txtReasonPA"];
        $remark = ($params["txtRemark"] == "") ? NULL : $params["txtRemark"];

        $data = array(
            "Actual_Location_Id" => $ActualLocationID,
            "Suggest_Location_Id" => $SuggestLocationID,
            "Approve_By" => $user_id,
            "Reason_Code" => $reason,
            "Reason_Remark" => $remark
        );
        $this->db->set('Approve_Date', 'GETDATE()', FALSE);
        $this->db->where("Pallet_Code", $params['pallet_code']);
        $this->db->update("STK_T_Pallet", $data);

        if ($this->db->affected_rows() == 0) :
            return FALSE;
        else :
            return TRUE;
        endif;
    }

    public function update_location_order_detail($params, $SuggestLocationID, $ActualLocationID, $user_id) {

        $reason = ($params["txtReasonPA"] == "") ? NULL : $params["txtReasonPA"];
        $remark = ($params["txtRemark"] == "") ? NULL : $params["txtRemark"];
        $pallet_id = $this->get_pl_id_by_code($params["pallet_code"]);

        $data = array(
            "Suggest_Location_Id" => $SuggestLocationID,
            "Actual_Location_Id" => $ActualLocationID,
            "Reason_Code" => $reason,
            "Reason_remark" => $remark,
            "Activity_Code" => "PUTAWAY",
            "Activity_By" => $user_id,
        );

        $this->db->set('Activity_Date', 'GETDATE()', FALSE);
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->update("STK_T_Order_Detail", $data);

        if ($this->db->affected_rows() == 0) :
            return FALSE;
        else :
            // BEGIN SEARCH
            $this->db->select('Inbound_Item_Id');
            $this->db->where('Pallet_Id', $pallet_id);
            $query = $this->db->get('STK_T_Order_Detail');
            $result = $query->row();
            // END SEARCH
            // BEGIN UPDATE INBOUND
            $update = array(
                "Suggest_Location_Id" => $SuggestLocationID,
                "Actual_Location_Id" => $ActualLocationID,
                "Active" => 'N'
            );
            $this->db->where("Inbound_Id", $result->Inbound_Item_Id);
            $this->db->update("STK_T_Inbound", $update);
            // END UPDATE INBOUND
            return TRUE;
        endif;

//		if ($this->db->trans_status() === FALSE) :
//			$this->db->trans_rollback();
//			return FALSE;
//		else :
//			$this->db->trans_commit();
//			return TRUE;
//		endif;
    }

    public function update_location_pallet_detail($params, $SuggestLocationID, $ActualLocationID, $user_id) {

        $reason = ($params["txtReasonPA"] == "") ? NULL : $params["txtReasonPA"];
        $remark = ($params["txtRemark"] == "") ? NULL : $params["txtRemark"];
        $pallet_id = $this->get_pl_id_by_code($params["pallet_code"]);

        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $query = $this->db->get("STK_T_Pallet_Detail");

        $this->db->trans_begin();

        foreach ($query->result_array() as $idx => $val) :

            $data = array(
                "Suggest_Location_Id" => $SuggestLocationID,
                "Actual_Location_Id" => $ActualLocationID,
                "Confirm_Qty" => $val['Confirm_Qty'],
                "Reason_Code" => $reason,
                "Reason_remark" => $remark,
                "Activity_Code" => "PUTAWAY",
                "Activity_By" => $user_id,
            );

            $this->db->set('Activity_Date', 'GETDATE()', FALSE);
            $this->db->where("Item_Id", $val['Item_Id']);
            $this->db->update("STK_T_Order_Detail", $data);

        endforeach;

        if ($this->db->trans_status() === FALSE) :
            $this->db->trans_rollback();
            return FALSE;
        else :
            $this->db->trans_commit();
            return TRUE;
        endif;
    }

    public function get_pallet_data($pallet_id) {
        $this->db->select("Pallet_Id");
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $query = $this->db->get("STK_T_Pallet_Detail");
        return count($query->result_array());
    }

    //add parameter $state for check doc_ext in this state only (fix partail case) : by kik : 20140829
    public function convert_ref_to_doc($ref_no, $state = null) {
        $this->db->select("STK_T_Order.Document_No");
        $this->db->join("STK_T_Workflow", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->where("Doc_Refer_Ext", $ref_no);
        if ($state != null):
            $this->db->where("STK_T_Workflow.Present_State", $state);
        endif;
        $query = $this->db->get("STK_T_Order");
        return $query->result();
    }

    public function get_pallet_id_by_code($pallet_code) {
        $this->db->select("Pallet_Id");
        $this->db->where("Pallet_Code", $pallet_code);
        $query = $this->db->get("STK_T_Pallet");
        return $query->result();
    }

    public function save_data_to_pallet($data, $pallet_id) {
        $response = 0;
        $count = 0;

        $affected_rows = $this->delete_item_in_pallet($pallet_id, $data['txtSKUItem']);
        foreach ($data['txtSKUItem'] as $idx => $val) :
            $count += 1;
            $response = $this->check_item_in_pallet($pallet_id, $data['txtSKUItem'][$idx]);
            if (!$response) :
                if ($data['txtQTY'][$idx] != 0):
                    $affected_rows = $this->insert_item_to_pallet($pallet_id, $data['txtSKUItem'][$idx], $data['txtQTY'][$idx]);
                    if ($affected_rows > 0) :
                        $response += 1;
                    endif;
                endif;
            else :
                if ($data['txtQTY'][$idx] == 0):
                    $affected_rows = $this->delete_item_in_pallet_per_item_id($pallet_id, $data['txtSKUItem'][$idx]);
                    if ($affected_rows > 0) :
                        $response += 1;
                    endif;
                else:
                    $affected_rows = $this->update_item_to_pallet($pallet_id, $data['txtSKUItem'][$idx], $data['txtQTY'][$idx]);
                    if ($affected_rows > 0) :
                        $response += 1;
                    endif;
                endif;
            endif;
        endforeach;

        return ($response == $count ? TRUE : FALSE);
    }

    /**
     *
     * @param unknown $pallet_id
     * @param unknown $item_id
     * @return boolean TRUE when success / FALSE when unsuccess.
     */
    private function check_item_in_pallet($pallet_id, $item_id) {
        $this->db->select("Item_Id");
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where("Item_Id", $item_id);
        $query = $this->db->get("STK_T_Pallet_Detail");
        return (count($query->result()) == 0 ? FALSE : TRUE);
    }

    /**
     *
     * @param int $pallet_id
     * @param int $item_id
     * @param int $item_qty
     */
    private function insert_item_to_pallet($pallet_id, $item_id, $item_qty) {
        $data = array(
            'Pallet_Id' => $pallet_id,
            'Item_Id' => $item_id,
            'Confirm_Qty' => $item_qty
        );
        $this->db->insert('STK_T_Pallet_Detail', $data);
        $affected_rows = $this->db->affected_rows();
    }

    private function update_item_to_pallet($pallet_id, $item_id, $item_qty) {
        $data = array(
            'Confirm_Qty' => $item_qty
        );
        $this->db->where("Item_Id", $item_id);
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->update('STK_T_Pallet_Detail', $data);
        $affected_rows = $this->db->affected_rows();
    }

    /**
     *
     * @param pallet $pallet_id
     * @param unknown $items
     */
    private function delete_item_in_pallet($pallet_id, $items) {
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where_not_in("Item_Id", $items);
        $this->db->delete('STK_T_Pallet_Detail');
        $affected_rows = $this->db->affected_rows();
    }

    /**
     *
     * @param pallet $pallet_id
     * @param unknown $item_id
     */
    private function delete_item_in_pallet_per_item_id($pallet_id, $item_id) {
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where("Item_Id", $item_id);
        $this->db->delete('STK_T_Pallet_Detail');
        $affected_rows = $this->db->affected_rows();
    }

    /**
     * Function show pallet that can build in this document.
     * @param unknown $ref_no
     */
    function get_pallet_for_receive($ref_no, $refNo, $orderId = "", $order_by = NULL) {

        $build_type = $this->get_pc_type($ref_no);
        $this->db->select("pl.Pallet_Code, pl.Pallet_Id, pl.Pallet_Type, pl.Pallet_Code, pl.Pallet_Name , COUNT(STK_T_Pallet_Detail.Item_Id) AS Total,SYS_M_Domain.Dom_EN_SDesc as pallet_type_name,SUM(STK_T_Pallet_Detail.Confirm_Qty) as all_qty,pl.Cont_Id");
        $this->db->from("STK_T_Pallet pl");
        $this->db->join("STK_T_Pallet_Detail", "pl.Pallet_Id = STK_T_Pallet_Detail.Pallet_Id", "LEFT");
        $this->db->join("SYS_M_Domain", "pl.Pallet_Type = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code = 'PALLET_TYPE'", "LEFT");
        $this->db->where("Is_Full", 'N');
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'

        if (!empty($orderId)):
            $this->db->where("pl.Order_Id", $orderId);
        endif;
        $this->db->where("Build_Type", $build_type);
        $this->db->group_by("pl.Pallet_Code, pl.Pallet_Id, pl.Pallet_Type, pl.Pallet_Code, pl.Pallet_Name,SYS_M_Domain.Dom_EN_SDesc,pl.Cont_Id");
        $this->db->having("(COUNT(STK_T_Pallet_Detail.Item_Id) = 0 Or (Select DISTINCT oo.Doc_Refer_Ext From STK_T_Pallet pll Inner Join STK_T_Pallet_Detail pd On pll.Pallet_Id = pd.Pallet_Id INNER JOIN STK_T_Order_Detail od On od.Item_Id = pd.Item_Id INNER JOIN STK_T_Order oo On oo.Order_Id = od.Order_Id Where Doc_Refer_Ext = '" . $refNo . "' AND pll.Pallet_Code = pl.Pallet_Code AND pd.Active=1) = '" . $refNo . "' )");

        if (!empty($order_by)):
            $this->db->order_by($order_by);
        endif;
        $query = $this->db->get();
//echo $this->db->last_query();exit;
        return $query->result();
    }

    public function get_dimension() {
        $this->db->select("*");
        $query = $this->db->get("STK_T_Dimension");
        return $query->result();
    }

    public function get_item_in_pallet($order_id, $type = "pallet_item", $item_id = "") {
        $this->db->select('pallet_detail.Confirm_Qty, pallet_detail.Pallet_Id, pallet_detail.Item_Id, order_detail.Product_Code, product.Product_NameEN, product.Product_NameTH,STK_T_Pallet.Cont_Id');
        $this->db->join("STK_T_Pallet", "pallet_detail.Pallet_Id = STK_T_Pallet.Pallet_Id", "left");
        $this->db->join("STK_T_Order_Detail order_detail", "order_detail.Item_Id = pallet_detail.Item_Id");
        $this->db->join("STK_M_Product product", "product.Product_Id = order_detail.Product_Id");
        $this->db->where("order_detail.Order_Id", $order_id);
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        //กรณีมี item_id ด้วย ให้ค้นหาด้วย item_id ที่ต้องการ : BY POR 2014-07-22
        if (!empty($item_id)):
            $this->db->where("pallet_detail.Item_Id", $item_id);
        endif;
        $query = $this->db->get("STK_T_Pallet_Detail pallet_detail");

        $result = $query->result();

        if ($type == "pallet_item"):  //กรณีต้องการแสดงในหน้า view จะแสดงทั้ง pallet และ item
            $results = array();
            foreach ($result as $idx => $value) {
                $results[$value->Pallet_Id][$value->Item_Id]['Product_Code'] = $value->Product_Code;
                $results[$value->Pallet_Id][$value->Item_Id]['Product_Name'] = $value->Product_NameEN;
                $results[$value->Pallet_Id][$value->Item_Id]['Confirm_Qty'] = $value->Confirm_Qty;
                $results[$value->Pallet_Id][$value->Item_Id]['Cont_Id'] = $value->Cont_Id;
                $results[$value->Pallet_Id][$value->Item_Id]['Pallet_Id'] = $value->Pallet_Id;
            }

            return $results;
        else:
            return $result;
        endif;
    }

    public function delete_pallet($pallet_id) {

        $this->db->trans_begin();

        $this->db->query("Delete From STK_T_Pallet_Detail Where Pallet_Id = '" . $pallet_id . " ' ");

        $this->db->query("Delete From STK_T_Pallet Where Pallet_Id = '" . $pallet_id . "' ");

        if ($this->db->trans_status() === FALSE) {
            $response = FALSE;
            $this->db->trans_rollback();
        } else {
            $response = TRUE;
            $this->db->trans_commit();
        }

        return $response;
    }

    #Add By POR 2015-10-05

    public function cancel_repallet($pallet_id) {

        $this->db->trans_begin();

        #คืนค่าให้กับ PD_Reserve
        $this->db->query("UPDATE STK_T_Inbound SET PD_Reserv_Qty = PD_Reserv_Qty - tableb.Reserve_Qty
        FROM(
        SELECT Inbound_Item_Id,SUM(Reserv_Qty) Reserve_Qty
        FROM STK_T_Relocate_Detail
        WHERE STK_T_Relocate_Detail.Pallet_Id =  '" . $pallet_id . "'
        GROUP BY Inbound_Item_Id) tableb
        where STK_T_Inbound.Inbound_Id = tableb.Inbound_Item_Id");

        #กำหนด Flow ให้เป็น incomplete
        $this->db->query("UPDATE STK_T_Workflow SET Present_State = -1
        FROM(
        SELECT distinct Flow_Id FROM STK_T_Relocate rel
        LEFT JOIN STK_T_Relocate_Detail reld on rel.Order_Id = reld.Order_Id
        WHERE reld.Pallet_Id = '" . $pallet_id . "') tableb
        WHERE STK_T_Workflow.Flow_Id = tableb.Flow_Id");

        #update repallet ที่เพิ่งสร้าง ให้เป็น N คือยกเลิกไม่ใช้แล้ว
        $this->db->query("UPDATE STK_T_Pallet SET Active = 'N' WHERE Pallet_Id = " . $pallet_id . "");

        if ($this->db->trans_status() === FALSE) {
            $response = FALSE;
            $this->db->trans_rollback();
        } else {
            $response = TRUE;
            $this->db->trans_commit();
        }

        return $response;
    }

    //
    public function get_pallet_template($pallet_id) {
        $this->db->select("*");
        $this->db->where("Pallet_Id", $pallet_id);
        $query = $this->db->get("STK_T_Pallet");
        return $query->result();
    }

    public function get_pallet_type_master() {
        $this->db->select("*");
        $this->db->where('Active', 'Y'); //add condition active = Y : by kik : 20140805
        $query = $this->db->get("CTL_M_Pallet_Master");
        return $query->result();
    }

    #add join table  ADM_M_UserLogin and CTL_M_Contact for ISSUE 3323 :by kik : 20140204

    function get_pallet_detail_packing($pallet_id = '', $convert_date_type = '103') {

        $conf = $this->config->item('_xml'); // By ball : 20140707

        if ($pallet_id == "") {

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
	    $this->db->join("STK_M_Location loc", "loc.Location_Id= Pallet.Actual_Location_Id", 'LEFT');

            /**
             * Query Join Container Info
             */
            if ($conf['container']):
                $this->db->join("CTL_M_Container Cont", "Pallet.Cont_Id = Cont.Cont_Id", "LEFT");
                $this->db->join("CTL_M_Container_Size Cont_Size", "Cont.Cont_Size_Id = Cont_Size.Cont_Size_Id", "LEFT");
            endif;

            $this->db->where("Pallet_Id", $pallet_id);

            $query = $this->db->get();
            $return_query = $query->row();
//            echo $this->db->last_query();exit();
            return $return_query;
        }
    }

    /**
     * @created by kik : 20140902 for reprint packing list
     */
    function get_pallet_detail_packingMulti($pallet_id = '', $loc_from = '', $loc_to = '', $convert_date_type = '103') {

        $conf = $this->config->item('_xml');

        if ($pallet_id == "" && $loc_from == "" && $loc_to == "") {
            return array();
        } else {

            $convert_length = 10;
            if ($convert_date_type == '13'):
                $convert_length = 50;
            endif;

            $this->db->select("*");
            $this->db->select("CONVERT(varchar({$convert_length}),Pallet.Create_Date,{$convert_date_type}) AS Create_Date");
            $this->db->select("(Co.First_NameTH +' '+ Co.Last_NameTH) as Create_By_Name");
            $this->db->select("location.Location_Code");
            /*
             * Query Select Container Info
             */
            if ($conf['container']):
                $this->db->select("Cont.Cont_No,Cont_Size.Cont_Size_No,Cont_Size.Cont_Size_Unit_Code");
            endif;


            $this->db->from("STK_T_Pallet Pallet");
            $this->db->join('STK_M_Location location', 'location.Location_Id = Pallet.Actual_Location_Id');
            $this->db->join("CTL_M_Pallet_Master Pallet_M", "Pallet.Pallet_Master_Id = Pallet_M.Pallet_Master_Id", "LEFT");
            $this->db->join("ADM_M_UserLogin UL", "Pallet.Create_By = UL.UserLogin_Id", 'LEFT');
            $this->db->join("CTL_M_Contact CO", "UL.Contact_Id = Co.Contact_Id", 'LEFT');


            /**
             * Query Join Container Info
             */
            if ($conf['container']):
                $this->db->join("CTL_M_Container Cont", "Pallet.Cont_Id = Cont.Cont_Id");
                $this->db->join("CTL_M_Container_Size Cont_Size", "Cont.Cont_Size_Id = Cont_Size.Cont_Size_Id");
            endif;



            if ($pallet_id != NULL and $pallet_id != "" and $pallet_id != 0):
                $this->db->where('Pallet.Pallet_Id', $pallet_id);
            endif;

            if ($loc_from != "" and $loc_to != ""):
                $this->db->where("location.Location_Code between '" . $loc_from . "' and '" . $loc_to . "'");
            endif;

            if ($loc_from != "" and $loc_to == ""):
                $this->db->where('location.Location_Code', $loc_from);
            endif;

            if ($loc_from == "" and $loc_to != ""):
                $this->db->where('location.Location_Code', $loc_to);
            endif;


            $query = $this->db->get();
            $return_query = $query->result();
//            echo $this->db->last_query();exit();
            return $return_query;
        }
    }

    /**
     * @function get_packingList_receive
     * @param int $pallet_id
     * @return array
     *
     *
     * @ISSUE2492 Build Pallet
     *
     * @author : Kik 20140707
     *
     * @modified :
     *
     */
    function get_packingList_receive($pallet_id) {

        $this->db->select('pallet_detail.*');
        $this->db->select('order_detail.Product_Code
            ,order_detail.Product_Status
            ,order_detail.Product_Sub_Status
            ,order_detail.Product_Lot
            ,order_detail.Product_Serial
            ,CONVERT(varchar(10),order_detail.Product_Mfd,103) as Product_Mfd
            ,CONVERT(varchar(10),order_detail.Product_Exp,103) as Product_Exp
            ,order_detail.Price_Per_Unit
            ,order_detail.Unit_Price_Id
            ,order_detail.All_Price
            ,order_detail.Unit_Id
            ,inv.Invoice_No
	    ,location.Location_Code
	    ,product.User_Defined_4 As product_cate
	    ,g.ProductGroup_NameEN As product_class
            ');
        $this->db->select('STK_T_Order.Document_No,STK_T_Order.Doc_Refer_Ext');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Order_Detail order_detail', 'pallet_detail.Item_Id = order_detail.Item_Id');
        $this->db->join('STK_T_Order', 'order_detail.Order_Id = STK_T_Order.Order_Id');
        $this->db->join('STK_M_Product product', 'order_detail.Product_Code = product.Product_Code', 'LEFT');
	$this->db->join('CTL_M_ProductGroup g', 'g.ProductGroup_Id = product.ProductGroup_Id', 'LEFT');
	$this->db->join('STK_M_Location location', 'order_detail.Actual_Location_Id = location.Location_Id', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', "order_detail.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = '" . $this->config->item('lang3digit') . "'", 'LEFT');
        $this->db->join('SYS_M_Domain S2', "order_detail.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code='PROD_STATUS' AND S2.Dom_Active = 'Y'", 'LEFT');
        $this->db->join('SYS_M_Domain S3', "order_detail.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code='SUB_STATUS' AND S3.Dom_Active = 'Y'", 'LEFT');
        $this->db->join('SYS_M_Domain S4', "order_detail.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code='PRICE_UNIT' AND S4.Dom_Active = 'Y'", 'LEFT');
        $this->db->join('STK_T_Invoice inv', "order_detail.Invoice_Id = inv.Invoice_Id", 'LEFT');

        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->where('order_detail.Active', 'Y');
        $this->db->where('product.Active', 'Y');
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        $result = $query->result_array();

        return $result;
    }

//end of get_packingList_receive

    function update_pallet_confirm($pallet_id) {
        $data = array(
            'Is_Full' => ACTIVE
        );
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->update('STK_T_Pallet', $data);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows <= 0) :
            return FALSE; //Update unsuccess.
        else:
            return TRUE; //Update success.
        endif;
    }

    /**
     * @author  kik : 20140728
     * @param int $pallet_id
     * @return array
     */
    function get_pallet_item_by_palletid($pallet_id) {
        $this->db->select("*");
        $this->db->from("STK_T_Pallet_Detail");
        $this->db->join("STK_T_Pallet", "pallet_detail.Pallet_Id = STK_T_Pallet.Pallet_Id", "left");
        $this->db->where("Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $query = $this->db->get();
        return $query;
    }

    /**
     * @author kik : 20140728
     * @param string $pallet_code
     * @return array
     */
    function get_orderId_by_palletCode($pallet_code) {
        $this->db->select('STK_T_Order_Detail.Order_Id');
        $this->db->where('STK_T_Pallet.Pallet_Code', $pallet_code);
        $this->db->join('STK_T_Pallet', "STK_T_Order_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id");
        $query_order = $this->db->get("STK_T_Order_Detail");
        return $query_order->row();
    }

    //add parameter $state for check doc_ext in this state only (fix partail case) : by kik : 20140829
    function get_item_with_pallet($module, $refNo = "", $pallet_code = "", $state = null) {

        $pallet_qty = "";
        if ($pallet_code != "") {
            $pallet_qty = ", (Select DISTINCT Confirm_Qty From STK_T_Pallet pl INNER JOIN STK_T_Pallet_Detail pd On pl.Pallet_Id = pd.Pallet_Id AND pl.Pallet_Code = '" . $pallet_code . "' AND pd.Item_Id = STK_T_Order_Detail.Item_Id AND pd.Active=1) as Pallet_Qty";
        }
        $this->db->select("DISTINCT STK_T_Order_Detail.*, STK_M_Product.Product_NameEN AS ProductName," . $pallet_qty . "
        , (Select SUM(Confirm_Qty) From STK_T_Pallet_Detail Where Item_Id = STK_T_Order_Detail.Item_Id AND STK_T_Pallet_Detail.Active=1) as Qty_In_Pallet");

        $this->db->from("STK_T_Workflow");
        $this->db->join("SYS_M_Stateedge", "STK_T_Workflow.Process_Id = SYS_M_Stateedge.Process_Id AND SYS_M_Stateedge.From_State = STK_T_Workflow.Present_State");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->join("CTL_M_Company", "CTL_M_Company.Company_Id = STK_T_Order.Source_Id", "left");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Id = STK_M_Product.Product_Id", "left");
        $this->db->where("SYS_M_Stateedge.Module", $module);
        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        if ($refNo != "") {
            $this->db->where_in("STK_T_Order.Doc_Refer_Ext", $refNo);
        }
        $this->db->where("STK_T_Order_Detail.Active", "Y");

        if ($state != null):
            $this->db->where("STK_T_Workflow.Present_State", $state);
        endif;

        $query = $this->db->get();
        //p($this->db->last_query());
        return $query->result();
    }

    function query_pallet($select = "*", $where = "1=1", $order_by = NULL) {

        $this->db->select($select);
        $this->db->from("STK_T_Pallet");
        $this->db->where($where);
        if (!empty($order_by)):
            $this->db->order_by($order_by);
        endif;

        $query = $this->db->get();
        return $query;
    }

    function get_pallet_for_dispatch() {

        $conf = $this->config->item('_xml');
        $conf_mix_object = empty($conf['pallet_out_mix_doc']['object']) ? array() : @$conf['pallet_out_mix_doc']['object'];
        $conf_container = empty($conf['container']) ? false : @$conf['container'];
        $conf_mix_cont = empty($conf_mix_object['container']) ? false : @$conf_mix_object['container'];
        $conf_mix_consignee = empty($conf_mix_object['consignee']) ? false : @$conf_mix_object['consignee'];
        $conf_mix_lot = empty($conf_mix_object['lot']) ? false : @$conf_mix_object['lot'];
        $conf_mix_serial = empty($conf_mix_object['serial']) ? false : @$conf_mix_object['serial'];

        $group_by = array("pl.Pallet_Code, pl.Pallet_Id, pl.Pallet_Type, pl.Pallet_Code, pl.Pallet_Name,SYS_M_Domain.Dom_EN_SDesc,pl.Cont_Id,pl.Product_Lot,pl.Product_Serial");

        $this->db->select("pl.Pallet_Code, pl.Pallet_Id, pl.Pallet_Type, pl.Pallet_Code, pl.Pallet_Name , COUNT(STK_T_Pallet_Detail.Item_Id) AS Total,SYS_M_Domain.Dom_EN_SDesc as pallet_type_name,SUM(STK_T_Pallet_Detail.Confirm_Qty) as all_qty,pl.Cont_Id,pl.Product_Lot,pl.Product_Serial");
        $this->db->from("STK_T_Pallet pl");
        $this->db->join("STK_T_Pallet_Detail", "pl.Pallet_Id = STK_T_Pallet_Detail.Pallet_Id", "LEFT");
        $this->db->join("SYS_M_Domain", "pl.Pallet_Type = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code = 'PALLET_TYPE'", "LEFT");

        #add by kik : 20140912 : for show Consignee Name if can build pallet in Container only
        if ($conf_mix_cont || $conf_container):
            array_push($group_by, 'cont_size.Cont_Size_No');
            array_push($group_by, 'cont_size.Cont_Size_Unit_Code');
            array_push($group_by, 'cont.Cont_No');

            $this->db->select("CAST(cont.Cont_No AS VARCHAR(50)) + ' '+ CAST(cont_size.Cont_Size_No AS VARCHAR(5))+ ' '+cont_size.Cont_Size_Unit_Code AS Cont_No");
            $this->db->join("CTL_M_Container cont", "cont.Cont_Id = pl.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size cont_size", "cont.Cont_Size_Id = cont_size.Cont_Size_Id", "LEFT");
        endif; // end of show Consignee Name if can build pallet in Container only
        #add by kik : 20140912 : for show Consignee Name if can build pallet in Consignee only
        if ($conf_mix_consignee):
            array_push($group_by, 'company.Company_NameEN');
            $this->db->select("company.Company_NameEN as Consignee");
            $this->db->join("CTL_M_Company company", "company.Company_Id = pl.Consignee_Id", "LEFT");
        endif; // end of by show Consignee Name if can build pallet in Consignee only

        $this->db->where("Is_Full", 'N');
        $this->db->where('STK_T_Pallet_Detail.Active', 1);
        $this->db->where("Build_Type", 'OUTBOUND');

        $this->db->group_by($group_by);

        $this->db->order_by("pl.Pallet_Code", "DESC");

        $query = $this->db->get();
        return $query->result();
    }

    function get_item_build_pallet_confirm_dispatch($container_id = NULL, $consignee_id = NULL, $lot = NULL, $serial = NULL, $pallet_id = NULL
    , $conf_mix_cont = false, $conf_mix_consignee = false, $conf_mix_lot = false, $conf_mix_serial = false) {

        $this->db->select(" DISTINCT
            STK_T_Order_Detail.Item_Id
            , STK_T_Order.Doc_Refer_Ext
            , STK_T_Order_Detail.Product_Code
            , STK_M_Product.Product_NameEN
            , STK_T_Order_Detail.Product_Status
            , STK_T_Order_Detail.Product_Lot
            , STK_T_Order_Detail.Product_Serial
            , STK_T_Order_Detail.Product_Mfd
            , STK_T_Order_Detail.Product_Exp
            , STK_T_Order_Detail.Confirm_Qty
            , STK_T_Order_Detail.DP_Confirm_Qty
            , SYS_M_Domain.Dom_EN_SDesc AS Product_Sub_Status_Label
            , (Select SUM(Confirm_Qty) From STK_T_Pallet pl INNER JOIN STK_T_Pallet_Detail pd On pl.Pallet_Id = pd.Pallet_Id AND pd.Item_Id = STK_T_Order_Detail.Item_Id AND pl.Is_Full = 'N' AND pd.Active=1) as Pallet_Qty
            , (Select SUM(Confirm_Qty) From STK_T_Pallet_Detail Where Item_Id = STK_T_Order_Detail.Item_Id and Active=1) as Qty_In_Pallet
            , (SELECT SUM(Confirm_Qty) FROM STK_T_Order_Detail OD2 WHERE OD2.Pallet_From_Item_Id = STK_T_Order_Detail.Item_Id) as Confirmed_Qty
        ");

//        $this->db->select("*");

        $this->db->from("STK_T_Workflow");

        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id", "LEFT");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id", "LEFT");
        $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Code = STK_M_Product.Product_Code", "INNER");
//        $this->db->join("CTL_M_Container", "STK_T_Order.Order_Id = CTL_M_Container.Order_Id", "LEFT");
        // $this->db->join("STK_T_Pallet_Detail", "STK_T_Order_Detail.Item_Id = STK_T_Pallet_Detail.Item_Id", "LEFT");
        // $this->db->join("STK_T_Pallet", "STK_T_Pallet_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id", "LEFT");
        $this->db->join("SYS_M_Domain", "STK_T_Order_Detail.Product_Sub_Status = SYS_M_Domain.Dom_Code", "left");

        $this->db->where("STK_T_Workflow.Present_State", '5');
        $this->db->where("STK_T_Workflow.Process_Id", '2');
        $this->db->where("STK_T_Order_Detail.Pallet_Id_Out IS NULL");
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->where("isnull(STK_T_Order_Detail.DP_Confirm_Qty,0) < STK_T_Order_Detail.Confirm_Qty");
        $this->db->where("STK_M_Product.Active", "Y");
        //  $this->db->where('STK_T_Pallet_Detail.Active',1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
//        if(!empty($container_id)):
//           $this->db->where("CTL_M_Container.Cont_Id", $container_id);
//        endif;
        $container_id = 564;
        if (!empty($container_id) && $conf_mix_cont):
            $this->db->where("STK_T_Order_Detail.Cont_Id", $container_id);
        endif;

        if (!empty($consignee_id) && $conf_mix_consignee):
            $this->db->where("STK_T_Order.Destination_Id", $consignee_id);
        endif;

        if (!empty($lot) && $conf_mix_lot):
            if ($lot != ' '):
                $this->db->where("STK_T_Order_Detail.Product_Lot", $lot);
            endif;
        endif;

        if (!empty($serial) && $conf_mix_serial):
            if ($serial != ' '):
                $this->db->where("STK_T_Order_Detail.Product_Serial", $serial);
            endif;
        endif;

        $query = $this->db->get();
//        echo $this->db->last_query();exit;
        return $query;
    }

    public function get_item_in_pallet_by_pallet_id($pallet_id, $type = "pallet_item", $item_id = "") {

        #Load config : add by kik : 20140912
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];

        $this->db->select('pallet_detail.Confirm_Qty, pallet_detail.Pallet_Id, pallet_detail.Item_Id, order_detail.Product_Code, product.Product_NameEN, product.Product_NameTH,STK_T_Pallet.Cont_Id');
        $this->db->join("STK_T_Pallet", "pallet_detail.Pallet_Id = STK_T_Pallet.Pallet_Id", "left");
        $this->db->join("STK_T_Order_Detail order_detail", "order_detail.Item_Id = pallet_detail.Item_Id");
        $this->db->join("STK_M_Product product", "product.Product_Id = order_detail.Product_Id");

        /*
         * add by kik for check config invoice before show Invoice No in pallet detail : 20140912
         */
        if ($conf_inv):
            $this->db->select('invoice.Invoice_No');
            $this->db->join("STK_T_Invoice invoice", "invoice.Invoice_Id = order_detail.Invoice_Id", 'LEFT');
        endif; //end of check config invoice before show Invoice No in pallet detail

        $this->db->where("pallet_detail.Pallet_Id", $pallet_id);

        //กรณีมี item_id ด้วย ให้ค้นหาด้วย item_id ที่ต้องการ : BY POR 2014-07-22
        if (!empty($item_id)):
            $this->db->where("pallet_detail.Item_Id", $item_id);
        endif;
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $query = $this->db->get("STK_T_Pallet_Detail pallet_detail");
//        p($this->db->last_query());exit;
        $result = $query->result();

        if ($type == "pallet_item"):  //กรณีต้องการแสดงในหน้า view จะแสดงทั้ง pallet และ item
            $results = array();
            foreach ($result as $idx => $value) {
                $results[$value->Item_Id]['Product_Code'] = $value->Product_Code;
                $results[$value->Item_Id]['Product_Name'] = $value->Product_NameEN;
                $results[$value->Item_Id]['Confirm_Qty'] = $value->Confirm_Qty;
                $results[$value->Item_Id]['Cont_Id'] = $value->Cont_Id;
                $results[$value->Item_Id]['Pallet_Id'] = $value->Pallet_Id;

                /*
                 * add by kik for check config invoice before show Invoice No in pallet detail : 20140912
                 */
                if ($conf_inv):
                    $results[$value->Item_Id]['Invoice_No'] = $value->Invoice_No;
                endif; //end of check config invoice before show Invoice No in pallet detail
            }
            return $results;
        else:
            return $result;
        endif;
    }

    function get_pl_cf_dispatch_mix() {

        #Load config
        $conf = $this->config->item('_xml');
        $conf_mix_object = empty($conf['pallet_out_mix_doc']['object']) ? array() : @$conf['pallet_out_mix_doc']['object']; //p($conf_mix_object);exit();
        $conf_mix_cont = empty($conf_mix_object['container']) ? false : @$conf_mix_object['container'];
        $conf_container = empty($conf['container']) ? false : @$conf['container'];
        $conf_mix_consignee = empty($conf_mix_object['consignee']) ? false : @$conf_mix_object['consignee'];
        $conf_mix_lot = empty($conf_mix_object['lot']) ? false : @$conf_mix_object['lot'];
        $conf_mix_serial = empty($conf_mix_object['serial']) ? false : @$conf_mix_object['serial'];

        #Start Query

        /** select Zone * */
        $this->db->select("Distinct Pallet_Id_Out as Pallet_Id");
        $this->db->select("STK_T_Pallet.Pallet_Code
            ,  STK_T_Pallet.Pallet_Name
            ,  SYS_M_Domain.Dom_EN_SDesc as pallet_type_name
            , (Select COUNT(*) FROM STK_T_Order_Detail Where STK_T_Order_Detail.Pallet_Id_Out = STK_T_Pallet.Pallet_Id) as Total
            , (Select sum(Confirm_Qty) FROM STK_T_Order_Detail Where STK_T_Order_Detail.Pallet_Id_Out = STK_T_Pallet.Pallet_Id) as total_qty
            , STK_T_Pallet.Cont_Id");

        if ($conf_mix_cont || $conf_container):
            $this->db->select("CAST(CTL_M_Container.Cont_No AS VARCHAR(50)) + ' '+ CAST(CTL_M_Container_Size.Cont_Size_No AS VARCHAR(5))+ ' '+CTL_M_Container_Size.Cont_Size_Unit_Code AS Cont");
        endif;

        if ($conf_mix_consignee):
            $this->db->select("CTL_M_Company.Company_NameEN");
        endif;

        if ($conf_mix_lot):
            $this->db->select("STK_T_Pallet.Product_Lot");
        endif;

        if ($conf_mix_serial):
            $this->db->select("STK_T_Pallet.Product_Serial");
        endif;

        /** from and join Zone * */
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Pallet", "STK_T_Pallet.Pallet_Id = STK_T_Order_Detail.Pallet_Id_Out");
        $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code AND SYS_M_Domain.Dom_Host_Code = 'PALLET_TYPE'", "LEFT");

        if ($conf_mix_cont || $conf_container):
            $this->db->join("CTL_M_Container", "CTL_M_Container.Cont_Id = STK_T_Pallet.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container_Size.Cont_Size_Id = CTL_M_Container.Cont_Size_Id", "LEFT");
        endif;

        if ($conf_mix_consignee):
            $this->db->join("CTL_M_Company", "CTL_M_Company.Company_Id = STK_T_Pallet.Consignee_Id", "LEFT");
        endif;

        // Condition JOIN for exclude reject document
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id");
        $this->db->join("STK_T_Workflow", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id");
        $this->db->where("STK_T_Workflow.Present_State NOT IN ('-1')");

        $this->db->where("Pallet_Id_Out IS NOT NULL");
        $this->db->where("STK_T_Order_Detail.Active", 'Y');
        $this->db->where("STK_T_Pallet.Is_Full", 'Y');
        $this->db->where("STK_T_Pallet.Active", 'Y');
        $this->db->where("(Confirm_Qty <> DP_Confirm_Qty OR DP_Confirm_Qty IS NULL)");
        $query = $this->db->get();
//        echo $this->db->last_query();exit;
        return $query;
    }

    function cf_dp_by_palletId($pallet_id, $container = NULL) {

        $this->db->set('DP_Confirm_Qty', 'Confirm_Qty', FALSE);
        $this->db->set('Activity_Code', "'DISPATCH'", FALSE);
        $this->db->set('Activity_By', $this->session->userdata['user_id'], FALSE);
        $this->db->set('Activity_Date', "'" . mdate("%Y-%m-%d %h:%i:%s", time()) . "'", FALSE);

        //update select container : ADD BY POR 2014-10-03
        if (!empty($container)):
            $this->db->set('Cont_Id', $container, FALSE);
        endif;
        //end update container

        $this->db->where('Pallet_Id_Out', $pallet_id);
        $this->db->update('STK_T_Order_Detail');

        $aff_row = $this->db->affected_rows();

        if ($aff_row > 0):
            return TRUE;
        else:
            return FALSE;
        endif;
    }

    /**
     * @this function find order id ที่ยัง confirm dispatch ไม่ครบ
     * @param type $order_id
     * @return query result หากส่งกลับเป็นค่าว่างแสดงผลว่า order นั้นๆ ถูก confrim ไปครบแล้ว
     */
    function order_cfDp_All($order_id) {

        $this->db->select('Distinct Order_Id');
        $this->db->from("STK_T_Order_Detail");
        $this->db->where('(Confirm_Qty <> DP_Confirm_Qty OR DP_Confirm_Qty IS NULL)');
        //$this->db->or_where('DP_Confirm_Qty IS NULL');
        $this->db->where('Order_Id', $order_id);
        $this->db->where('Active', 'Y');
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query;
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
            ,STK_T_Inbound.Unit_Id');
        $this->db->select('STK_T_Inbound.Document_No,STK_T_Inbound.Doc_Refer_Ext');
        $this->db->select('product.Product_NameEN');
        $this->db->select('S1.public_name AS Unit_Value');
        $this->db->select('S2.Dom_Code AS Status_Code,S2.Dom_EN_Desc AS Status_Value');
        $this->db->select('S3.Dom_Code AS Sub_Status_Code, S3.Dom_EN_Desc AS Sub_Status_Value');
        $this->db->select('S4.Dom_EN_Desc AS Unit_Price_value');
        $this->db->from('STK_T_Pallet_Detail pallet_detail');
        $this->db->join('STK_T_Inbound', 'pallet_detail.Inbound_Id = STK_T_Inbound.Inbound_Id');
        $this->db->join('STK_M_Product product', 'STK_T_Inbound.Product_Code = product.Product_Code', 'LEFT');
        $this->db->join('CTL_M_UOM_Template_Language S1', 'STK_T_Inbound.Unit_Id = S1.CTL_M_UOM_Template_id AND S1.language = "' . $this->config->item('lang3digit') . '"', 'LEFT');
        $this->db->join('SYS_M_Domain S2', 'STK_T_Inbound.Product_Status = S2.Dom_Code AND S2.Dom_Host_Code="PROD_STATUS" ', 'LEFT');
        $this->db->join('SYS_M_Domain S3', 'STK_T_Inbound.Product_Sub_Status = S3.Dom_Code AND S3.Dom_Host_Code="SUB_STATUS" ', 'LEFT');
        $this->db->join('SYS_M_Domain S4', 'STK_T_Inbound.Unit_Price_Id = S4.Dom_Code AND S4.Dom_Host_Code="PRICE_UNIT" ', 'LEFT');

        $this->db->where('pallet_detail.Pallet_Id', $pallet_id);
        $this->db->where('pallet_detail.Parent_Id IS NULL');
        $this->db->where('pallet_detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->where('product.Active', 'Y');
        $this->db->order_by('pallet_detail.Id asc');
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        $result = $query->result_array();

        return $result;
    }

//end of get_packingList_receive

    function group_product_pallet($pallet_id) {
        $this->db->select("Product_Code");
        $this->db->from("STK_T_Pallet_Detail");
        $this->db->join("STK_T_Order_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id", "LEFT");
        $this->db->where("STK_T_Pallet_Detail.Pallet_Id", $pallet_id);
        $this->db->where('STK_T_Pallet_Detail.Active', 1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
        $this->db->group_by("Product_Code");

        $query = $this->db->get();

        return $query;
    }

    function get_all_inbound_qty_by_pallet_id($pallet_id) {
        $this->db->select("SUM(STK_T_Inbound.Balance_Qty) AS All_Qty_Of_Pallet, Pallet_Id");
        $this->db->from("STK_T_Inbound");
        $this->db->where_in("STK_T_Inbound.Pallet_Id", $pallet_id);
        $this->db->group_by("Pallet_Id");

        return $this->db->get();
    }

    function get_job_picking_by_pallet_code_and_doc_ext($pallet_code, $doc_ext) {
        $this->db->select("Pallet_Id");
        $this->db->from("STK_T_Pallet");
        $this->db->where("Pallet_Code", $pallet_code);
        $pallet_id = $this->db->get()->row()->Pallet_Id;
        $this->db->flush_cache();

        $this->db->select("STK_T_Order.Flow_Id, STK_T_Order_Detail.*");
        $this->db->select("STK_T_Inbound.Inbound_Id, STK_T_Inbound.Receive_Qty AS inb_Receive_Qty, STK_T_Inbound.Adjust_Qty AS inb_Adjust_Qty, STK_T_Inbound.Balance_Qty AS inb_Balance_Qty, STK_T_Inbound.Dispatch_Qty AS inb_Dispatch_Qty, STK_T_Inbound.PD_Reserv_Qty AS inb_PD_Reserv_Qty, STK_T_Inbound.PK_Reserv_Qty AS inb_PK_Reserv_Qty ");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Order", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id", "LEFT");
        $this->db->join("STK_T_Inbound", "STK_T_Inbound.Inbound_Id = STK_T_Order_Detail.Inbound_Item_Id", "LEFT");
        $this->db->where("STK_T_Order_Detail.Pallet_Id", $pallet_id);
        $this->db->where("STK_T_Order.Doc_Refer_Ext", $doc_ext);
        $this->db->where("STK_T_Order_Detail.Actual_Location_Id", NULL);
        $this->db->where("STK_T_Order_Detail.Active", 'Y');

        $this->db->order_by("STK_T_Order_Detail.Order_Id", "ASC");
        $this->db->order_by("STK_T_Order_Detail.Item_Id", "ASC");

        $query = $this->db->get();

        return $query;
    }

    /**
     * function for get Item_Id and Qty from a Exploded Pallet
     * @param type $pallet_id
     * @param type $table_set ('order','relocate','both')
     */
    function get_item_explode_from_pallet_id($pallet_id, $table_set = "both") {

        $this->db->select("max(STK_T_Inbound.Item_Id) AS Item_Id_Receive
            , max(STK_T_Order_Detail.Confirm_Qty) AS Confirm_Qty
            , max(CONVERT(VARCHAR(17),STK_T_Logs_Action.Activity_Date,113)) AS Activity_Date
            , max(STK_T_Order.Doc_Refer_Ext) AS Doc_Refer_Ext
            ");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Order", "STK_T_Workflow.Flow_Id = STK_T_Order.Flow_Id", "INNER");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id AND STK_T_Order_Detail.Pallet_Id = '{$pallet_id}'", "INNER");
        $this->db->join("STK_T_Inbound", "STK_T_Order_Detail.Inbound_Item_Id = STK_T_Inbound.Inbound_Id AND STK_T_Inbound.Active='" . ACTIVE . "'", "INNER");
        $this->db->join("STK_T_Logs_Action", "
            STK_T_Order_Detail.Order_Id = dbo.STK_T_Logs_Action.Order_Id
            AND STK_T_Order_Detail.Item_Id = dbo.STK_T_Logs_Action.Item_Id
            AND (
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'confirmAction'
                OR
                STK_T_Logs_Action.Module = 'relocate' AND STK_T_Logs_Action.Sub_Module = 'hhOpenAction'
                OR
                STK_T_Logs_Action.Module = 'product_status' AND STK_T_Logs_Action.Sub_Module = 'relocateConfirm'
                OR
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'confirmAction'
                OR
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'quickApproveAction'
                OR
                STK_T_Logs_Action.Module = 'reLocationProduct' AND STK_T_Logs_Action.Sub_Module = 'confirmRLProduct'
                OR
                STK_T_Logs_Action.Module = 'reLocationProduct' AND STK_T_Logs_Action.Sub_Module = 'quickApproveAction'
            )", "LEFT"
        );
        $this->db->where("(STK_T_Workflow.Process_Id IN (2,9) AND STK_T_Workflow.Present_State IN (4,5,6,-2)) OR (STK_T_Workflow.Process_Id IN (11) AND STK_T_Workflow.Present_State IN (-2))");
        $this->db->group_by("STK_T_Inbound.Item_Id");
        $query_order = $this->db->return_query(FALSE);
        $this->db->flush_cache();

        $this->db->select("max(STK_T_Inbound.Item_Id) AS Item_Id_Receive
            , max(STK_T_Relocate_Detail.Confirm_Qty) AS Confirm_Qty
            , max(CONVERT(VARCHAR(17),STK_T_Logs_Action.Activity_Date,113)) AS Activity_Date
            , max(STK_T_Relocate.Doc_Relocate) AS Doc_Refer_Ext
        ");
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Flow_Id = STK_T_Relocate.Flow_Id", "INNER");
        $this->db->join("STK_T_Relocate_Detail", "STK_T_Relocate.Order_Id = STK_T_Relocate_Detail.Order_Id AND STK_T_Relocate_Detail.Pallet_Id = '{$pallet_id}'", "INNER");
        $this->db->join("STK_T_Inbound", "STK_T_Relocate_Detail.Inbound_Item_Id = STK_T_Inbound.Inbound_Id AND STK_T_Inbound.Active='" . ACTIVE . "'", "INNER");
        $this->db->join("STK_T_Logs_Action", "
            STK_T_Relocate_Detail.Order_Id = dbo.STK_T_Logs_Action.Order_Id
            AND STK_T_Relocate_Detail.Item_Id = dbo.STK_T_Logs_Action.Item_Id
            AND (
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'confirmAction'
                OR
                STK_T_Logs_Action.Module = 'relocate' AND STK_T_Logs_Action.Sub_Module = 'hhOpenAction'
                OR
                STK_T_Logs_Action.Module = 'product_status' AND STK_T_Logs_Action.Sub_Module = 'relocateConfirm'
                OR
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'confirmAction'
                OR
                STK_T_Logs_Action.Module = 'picking' AND STK_T_Logs_Action.Sub_Module = 'quickApproveAction'
                OR
                STK_T_Logs_Action.Module = 'reLocationProduct' AND STK_T_Logs_Action.Sub_Module = 'confirmRLProduct'
                OR
                STK_T_Logs_Action.Module = 'reLocationProduct' AND STK_T_Logs_Action.Sub_Module = 'quickApproveAction'
            )", "LEFT"
        );
        $this->db->where("(STK_T_Workflow.Process_Id IN (5,6,10,14) AND STK_T_Workflow.Present_State IN (-2)) OR (STK_T_Workflow.Process_Id IN (8) AND STK_T_Workflow.Present_State IN (3,4,-2))");
        $this->db->group_by("STK_T_Inbound.Item_Id");
        $query_relocate = $this->db->return_query(FALSE);
        $this->db->flush_cache();

        if ($table_set == "both"):
            $query = $this->db->query("SELECT * FROM ($query_order UNION $query_relocate) AS unionTable");
        elseif ($table_set == "order"):
            $query = $this->db->query($query_order);
        elseif ($table_set == "relocate"):
            $query = $this->db->query($query_relocate);
        endif;
//        echo $this->db->last_query();
//        p($query->result());
        return $query;
    }

    function get_location_from_pallet_id($pallet) {
        $this->db->select("STK_M_Location.Location_Code");
        $this->db->from("STK_T_Pallet");
        $this->db->join('STK_M_Location', 'STK_M_Location.Location_Id = STK_T_Pallet.Actual_Location_Id');
        $this->db->where("Pallet_Id", $pallet);
        $query = $this->db->get();
        return $query->row()->Location_Code;
    }

    function get_re_pallet_detail_with_pallet_code($pallet_code = NULL) {
        if (empty($pallet_code))
            return array();

        /* code เก่าอัค comment by por เนื่องจากเห็นว่าสามารถไปดึงจาก inbound ได้โดยตรง
          $this->db->select(array(
          'STK_T_Pallet.Pallet_Id'
          , 'STK_T_Pallet.Pallet_Code'
          , 'STK_M_Product.Product_Code'
          , 'STK_M_Product.Product_NameEN'
          , 'SUM(ISNULL(vw_inbound.Est_Balance_Qty,0)) AS Est_Balance_Qty'
          , 'SUM(ISNULL(vw_inbound.Balance_Qty,0)) AS Balance_Qty'));
          $this->db->from("STK_T_Pallet");
          $this->db->join("STK_T_Pallet_Detail", "STK_T_Pallet.Pallet_Id = STK_T_Pallet_Detail.Pallet_Id", "LEFT");
          $this->db->join("STK_T_Order_Detail", "STK_T_Pallet_Detail.Item_Id = STK_T_Order_Detail.Item_Id", "LEFT");
          $this->db->join("STK_M_Product", "STK_T_Order_Detail.Product_Id = STK_M_Product.Product_Id", "LEFT");
          $this->db->join("vw_inbound", "STK_T_Pallet.Pallet_Id = vw_inbound.Pallet_Id", "LEFT");
          $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
          $this->db->where('STK_T_Pallet_Detail.Active',1);  //เพิ่มให้แสดงเฉพาะ Active = 'Y'
          $this->db->group_by("STK_T_Pallet.Pallet_Id, STK_T_Pallet.Pallet_Code, STK_M_Product.Product_Code, STK_M_Product.Product_NameEN");
         */

        #new code by por :2015-10-04
        $this->db->select(array(
            'pl.Pallet_Id'
            , 'pl.Pallet_Code'
            , 'STK_M_Product.Product_Code'
            , 'inb.Product_Lot'
            , 'inb.Product_Serial'
            , 'STK_M_Product.Product_NameEN'
            , 'SUM(inb.Receive_Qty - (ISNULL(inb.PD_Reserv_Qty,0) + ISNULL(inb.Dispatch_Qty,0) + ISNULL(inb.Adjust_Qty,0))) AS Est_Balance_Qty'
            , 'SUM(ISNULL(inb.Balance_Qty,0))  AS Balance_Qty'));
        $this->db->from("STK_T_Inbound inb");
        $this->db->join("STK_T_Pallet pl", "inb.Pallet_Id = pl.Pallet_Id", "LEFT");
        $this->db->join("STK_M_Product", "inb.Product_Id = STK_M_Product.Product_Id", "LEFT");
        $this->db->where("pl.Pallet_Code", $pallet_code);
        $this->db->where('inb.Active', 'Y');
        $this->db->group_by("pl.Pallet_Id, pl.Pallet_Code, STK_M_Product.Product_Code, STK_M_Product.Product_NameEN , inb.Product_Lot , inb.Product_Serial");
        $query = $this->db->get();
        //p($this->db->last_query());
        return $query;
    }

    function getInboundByPalletId($pallet_id) {

        $this->db->select("STK_T_Inbound.*, vw_inbound.Est_Balance_Qty AS Bal_Qty");
        $this->db->from("STK_T_Inbound");
        $this->db->join("vw_inbound", "vw_inbound.Inbound_Id = STK_T_Inbound.Inbound_Id");

        if (is_array($pallet_id)):
            $this->db->where_in("STK_T_Inbound.Pallet_Id", $pallet_id);
        else:
            $this->db->where("STK_T_Inbound.Pallet_Id", $pallet_id);
        endif;

        $this->db->where("vw_inbound.Est_Balance_Qty > 0");
        $this->db->where("STK_T_Inbound.Active", "Y");
        $query = $this->db->get();
        return $query;
    }

    function getListPalletManagement() {
        $this->db->select(
                array(
                    "distinct STK_T_Pallet.Pallet_Id"
                    , "STK_T_Pallet.Pallet_Code"
                    , "STK_T_Pallet.Pallet_Name"
                    , "(CTL_M_Contact.First_NameEN + '  ' + CTL_M_Contact.Last_NameEN) AS WorkerName"
        ));
        $this->db->from("STK_T_Workflow");
        $this->db->join("STK_T_Relocate", "STK_T_Workflow.Flow_Id = STK_T_Relocate.Flow_Id", "LEFT");
        $this->db->join("STK_T_Relocate_Detail", "STK_T_Relocate.Order_Id = STK_T_Relocate_Detail.Order_Id", "LEFT");
        $this->db->join("STK_T_Pallet", "STK_T_Relocate_Detail.Pallet_Id = STK_T_Pallet.Pallet_Id", "LEFT");
        $this->db->join("ADM_M_UserLogin", "STK_T_Workflow.Create_By = ADM_M_UserLogin.UserLogin_Id", "LEFT");
        $this->db->join("CTL_M_Contact", "ADM_M_UserLogin.Contact_Id = CTL_M_Contact.Contact_Id", "LEFT");
        $this->db->where_in("STK_T_Workflow.Process_Id", array('15'));
        $this->db->where("STK_T_Workflow.Present_State", "1");

        $query = $this->db->get();
        return $query;
    }

    function get_re_pallet_data_with_pallet_id($pallet_id) {

        $pallet_data = $this->_get_re_pallet_data($pallet_id);
        $item_list = $this->_get_re_pallet_items($pallet_id);

//        p($pallet_data);
//        p($item_list);

        $return['pallet_data'] = $pallet_data;
        $return['item_list'] = $item_list;

        return $return;
    }

    protected function _get_re_pallet_data($pallet_id) {
        $this->db->select("*");
        $this->db->from("STK_T_Pallet");
        $this->db->where("STK_T_Pallet.Pallet_Id", $pallet_id);
        $pallet_data = $this->db->get()->row();
        return $pallet_data;
    }

    protected function _get_re_pallet_items($pallet_id) {
        $this->db->select("STK_T_Relocate_Detail.Product_Code, STK_M_Product.Product_NameEN AS ProductName, SUM(STK_T_Relocate_Detail.Reserv_Qty) as Reserv_Qty");
        $this->db->from("STK_T_Relocate_Detail");
        $this->db->join("STK_M_Product", "STK_T_Relocate_Detail.Product_Id = STK_M_Product.Product_Id");
        $this->db->where("STK_T_Relocate_Detail.Pallet_Id", $pallet_id);
        $this->db->group_by("STK_T_Relocate_Detail.Product_Code, STK_M_Product.Product_NameEN"); //group เพื่อไม่ให้ค่าซ้ำกัน :ADD BY POR 2015-09-17
        $item_list = $this->db->get()->result();
        return $item_list;
    }

    function pallet_save($data, $where) {
        $this->db->where($where);
        $this->db->update('STK_T_Pallet', $data);
        $aff_row = $this->db->affected_rows();
        return $aff_row;
    }

    function getPalletIdByCode($Pallet_Code, $check_est = false) {
        $this->db->select("STK_T_Pallet.Pallet_Id");
        $this->db->from("STK_T_Pallet");
        $this->db->where("STK_T_Pallet.Pallet_Code", $Pallet_Code);
        $this->db->where("STK_T_Pallet.Active", 'Y');

        $query = $this->db->get();
//        echo $this->db->last_query(); exit;
        $locate = $query->row();

        return ($locate) ? $locate->Pallet_Id : NULL;
    }

    function getPalletNote($params = NULL) {
        $this->db->select("Pallet_Note");
        $this->db->where("Pallet_Id", $params['pallet_id']);
        $this->db->where("Active", 'Y');
        $query = $this->db->get("STK_T_Pallet");
        $rs = $query->row();
        return ($rs) ? $rs->Pallet_Note : NULL;
    }

    function savePalletNote($pallet_id = NULL, $data = NULL) {
        $this->db->where("Pallet_Id", $pallet_id);
        $rs = $this->db->update("STK_T_Pallet", $data);
        return ($rs) ? 'true' : 'false';
    }

    public function get_pallet_data_2 ($pallet_id) {
	$this->db->select("Location_Code");
	$this->db->select("Doc_Refer_Ext");
	$this->db->from("STK_T_Pallet");
	$this->db->join("STK_T_Inbound","STK_T_Pallet.Pallet_ID = STK_T_Inbound.Pallet_Id");
	$this->db->join("STK_M_Location","STK_M_Location.Location_Id = STK_T_Inbound.Actual_Location_Id");
	$this->db->where("STK_T_Pallet.Pallet_Id", $pallet_id);
	$this->db->where("STK_T_Inbound.Active", "Y");
	$query = $this->db->get();
	$r = $query->row();
	return $r;

    }

    public function getPalletIdFromLoc($location) {
	$this->db->select("pallet_id");
	$this->db->from("STK_T_Inbound ib");
	$this->db->join("STK_M_Location loc" , "loc.Location_Id = ib.Actual_Location_Id" , "LEFT");
	$this->db->where("ib.Active =  'Y'");
	$this->db->where("loc.Location_Code like 'E12%'");
	$this->db->order_by("loc.Location_Code","ASC");
	$query = $this->db->get();
	$result = $query->result();
	return $result;
    }


}
