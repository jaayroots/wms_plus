<?php

/*
 * Create by Ton! 20131209
 */
?>
<?php

class product_brand_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_ProductBrand_List() {
        $this->db->select("CTL_M_ProductBrand.ProductBrand_Id AS Id 
            , CTL_M_ProductBrand.ProductBrand_Code
            , CTL_M_ProductBrand.ProductBrand_NameEN
            , CTL_M_ProductBrand.ProductBrand_NameTH
            , CTL_M_ProductBrand.ProductBrand_Desc
            , CASE WHEN CTL_M_ProductBrand.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_ProductBrand");
        $this->db->order_by("CTL_M_ProductBrand.ProductBrand_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_ProductBrand($ProductBrand_Id = NULL, $ProductBrand_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_ProductBrand");
        if (!empty($ProductBrand_Id)):
            $this->db->where("CTL_M_ProductBrand.ProductBrand_Id", $ProductBrand_Id);
        endif;
        if (!empty($ProductBrand_Code)):
            $this->db->where("CTL_M_ProductBrand.ProductBrand_Code", $ProductBrand_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_ProductBrand.Active", YES);
        endif;
        $this->db->order_by("CTL_M_ProductBrand.ProductBrand_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_ProductBrand($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_ProductBrand', $data);
                $ProductBrand_Id = $this->db->insert_id();
                if ($ProductBrand_Id != 0):
                    return $ProductBrand_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_ProductBrand', $data);
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
