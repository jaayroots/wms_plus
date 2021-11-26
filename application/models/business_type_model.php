<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

class business_type_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_BusinessType_List() {
        $this->db->select("CTL_M_BusinessType.BusinessType_Id AS Id
            , CTL_M_BusinessType.BusinessType_Code
            , CTL_M_BusinessType.BusinessType_NameEN
            , CTL_M_BusinessType.BusinessType_NameTH
            , CTL_M_BusinessType.BusinessType_Desc
            , CASE WHEN CTL_M_BusinessType.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_BusinessType");
        $this->db->order_by("CTL_M_BusinessType.BusinessType_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function save_CTL_M_BusinessType($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_BusinessType', $data);
                $BusinessType_Id = $this->db->insert_id();
                if ($BusinessType_Id != 0):
                    return $BusinessType_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_BusinessType', $data);
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

