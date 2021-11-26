<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class address_type_model extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_AddressType_List() {
        $this->db->select("CTL_M_AddressType.AddressType_Id AS Id 
            , CTL_M_AddressType.AddressType_Code
            , CTL_M_AddressType.AddressType_NameEN
            , CTL_M_AddressType.AddressType_NameTH
            , CTL_M_AddressType.AddressType_Desc
            , CASE WHEN CTL_M_AddressType.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_AddressType");
        $this->db->order_by("CTL_M_AddressType.AddressType_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_AddressType($AddressType_Id = NULL, $AddressType_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_AddressType");
        if (!empty($AddressType_Id)):
            $this->db->where("CTL_M_AddressType.AddressType_Id", $AddressType_Id);
        endif;
        if (!empty($AddressType_Code)):
            $this->db->where("CTL_M_AddressType.AddressType_Code", $AddressType_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_AddressType.Active", YES);
        endif;
        $this->db->order_by("CTL_M_AddressType.AddressType_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_AddressType($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_AddressType', $data);
                $AddressType_Id = $this->db->insert_id();
                if ($AddressType_Id != 0):
                    return $AddressType_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_AddressType', $data);
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows > 0) {
                    return TRUE; // Save Success.
                } else {
                    return FALSE; // Save Unsuccess.
                }
            endif;
        else:
            return FALSE;
        endif;
    }

}
