<?php

/*
 * Create by Ton! 20131212
 */
?>
<?php

class image_category_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_ADM_M_ImageCategory_List() {
        $this->db->select("ADM_M_ImageCategory.ImageCategory_Id AS Id 
            , ADM_M_ImageCategory.ImageCategory_Code
            , ADM_M_ImageCategory.ImageCategory_NameEN
            , ADM_M_ImageCategory.ImageCategory_NameTH
            , ADM_M_ImageCategory.ImageCategory_Desc
            , CASE WHEN ADM_M_ImageCategory.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("ADM_M_ImageCategory");
        $this->db->order_by("ADM_M_ImageCategory.ImageCategory_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_ADM_M_ImageCategory($ImageCategory_Id = NULL, $ImageCategory_Code = NULL, $Parent_Id = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("ADM_M_ImageCategory");
        if (!empty($ImageCategory_Id)):
            $this->db->where("ADM_M_ImageCategory.ImageCategory_Id", $ImageCategory_Id);
        endif;
        if (!empty($ImageCategory_Code)):
            $this->db->where("ADM_M_ImageCategory.ImageCategory_Code", $ImageCategory_Code);
        endif;
        if (!empty($Parent_Id)):
            $this->db->where("ADM_M_ImageCategory.Parent_Id", $Parent_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("ADM_M_ImageCategory.Active", YES);
        endif;
        $this->db->order_by("ADM_M_ImageCategory.ImageCategory_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_ADM_M_ImageCategory($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_ImageCategory', $data);
                $ImageCategory_Id = $this->db->insert_id();
                if ($ImageCategory_Id != 0):
                    return $ImageCategory_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_M_ImageCategory', $data);
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
