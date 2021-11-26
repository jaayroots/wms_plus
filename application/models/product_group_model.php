<?php

/*
 * Create by Ton! 20131209
 */
?>
<?php

class product_group_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_ProductGroup_List() {
        $this->db->select("CTL_M_ProductGroup.ProductGroup_Id AS Id 
            , CTL_M_ProductGroup.ProductGroup_Code
            , CTL_M_ProductGroup.ProductGroup_NameEN
            , CTL_M_ProductGroup.ProductGroup_NameTH
            , CTL_M_ProductGroup.ProductGroup_Desc
            , CASE WHEN CTL_M_ProductGroup.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_ProductGroup");
        $this->db->order_by("CTL_M_ProductGroup.ProductGroup_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_ProductGroup($ProductGroup_Id = NULL, $ProductGroup_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_ProductGroup");
        if (!empty($ProductGroup_Id)):
            $this->db->where("CTL_M_ProductGroup.ProductGroup_Id", $ProductGroup_Id);
        endif;
        if (!empty($ProductGroup_Code)):
            $this->db->where("CTL_M_ProductGroup.ProductGroup_Code", $ProductGroup_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_ProductGroup.Active", YES);
        endif;
        $this->db->order_by("CTL_M_ProductGroup.ProductGroup_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_ProductGroup($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_ProductGroup', $data);
                $ProductGroup_Id = $this->db->insert_id();
                if ($ProductGroup_Id != 0):
                    return $ProductGroup_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_ProductGroup', $data);
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
