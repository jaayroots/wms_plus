<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of receive
 *
 * @author P'Zex
 */
class master_tmp extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
    function insert_tmp($by_list){     
        
        $date = date_create();

        $this->db->select("Product_Code");
        $this->db->from("STK_M_Product");
        $this->db->where('Product_Id',$by_list['product_code']);
        $query = $this->db->get();
        $query = $query->row();
        
        $data = array(
            'location_code' => $by_list['location_code'],
            'product_code' => $query->Product_Code,
            're_order_point' => $by_list['re_order_point'],
            'max' => $by_list['max'],
            'create_by' =>  $parameter['user_id'] = $this->session->userdata('user_id'),
            'create_date' => date_format($date, 'Y-m-d H:i:s'),
            'modify_by' => NULL,
            'modify_date' => NULL,
            'active' => 'Y',
    );
            $this->db->insert('master_tmp_', $data);
    }
    
    function data_list(){     
        
        $this->db->select("*");
        $this->db->select("CASE WHEN mst.active = 'Y' THEN 'YES' ELSE 'NO' END AS status");
        $this->db->select("us.UserAccount");
        $this->db->from("master_tmp_ mst");
        $this->db->join("ADM_M_UserLogin us","mst.create_by = us.UserLogin_Id");
        $this->db->where('mst.active','Y');
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_location($location){

        $this->db->select("TOP 50 loc.Location_Id,wh.Warehouse_NameEN,loc.Location_Code");
        $this->db->from("STK_M_Warehouse wh");
        $this->db->join("STK_M_Location loc","wh.Warehouse_Id = loc.Warehouse_Id");
        $this->db->where('loc.Active','Y');
        if(empty($location_like)){
            $this->db->like('loc.Location_Code', $location); 
        }
        $this->db->order_by('loc.Location_Code','ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    function check_product($product_id){

        $this->db->select("*");
        $this->db->from("STK_M_Product");
        $this->db->where('product_id',$product_id);
        $query = $this->db->get();
        return $query->result();
    }
    function check_location($location_code){
        $this->db->select("*");
        $this->db->from("STK_M_Location");
        $this->db->where('location_code',$location_code);
        $query = $this->db->get();
        // echo $this->db->last_query(); 
        return $query->result();
    }
    function delete_list($id_master){
        $this->db->set('active', 'N');
        $this->db->where('id', $id_master);
        $this->db->update('master_tmp_');
        $afftectedRows = $this->db->affected_rows();
    }
    
    function chk_master($m_data){
        $this->db->select("*");
        $this->db->from("master_tmp_");
        $this->db->where('product_code',$m_data['Product_Code']);
        $this->db->where('location_code',$m_data['Location_Code']);
        $query = $this->db->get();
        return $query->result();
    }
        
}
?>
