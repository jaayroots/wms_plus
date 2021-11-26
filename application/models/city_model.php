<?php // Create by Ton! 20130422                  ?>
<?php

class city_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_City_List() {
        $this->db->select("CTL_M_City.City_Id AS Id 
            , CTL_M_City.City_Code
            , CTL_M_City.City_NameEN
            , CTL_M_City.City_NameTH
            , CTL_M_City.City_Desc
            , CTL_M_Province.Province_NameEN
            , CASE WHEN CTL_M_City.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_City");
        $this->db->join("CTL_M_Province", "CTL_M_City.Province_Id = CTL_M_Province.Province_Id", "LEFT");
        $this->db->order_by("CTL_M_City.City_Id, CTL_M_City.Province_Id");
//        $this->db->order_by("CTL_M_City.Province_Id, CTL_M_City.City_Id");
        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_City($City_Id = NULL, $City_Code = NULL, $Province_Id = NULL, $Active = TRUE) {
        $this->db->select("CTL_M_City.*, CTL_M_Province.Province_NameEN");
        $this->db->from("CTL_M_City");
        $this->db->join("CTL_M_Province", "CTL_M_City.Province_Id = CTL_M_Province.Province_Id", "LEFT");
        if (!empty($City_Id)) {
            $this->db->where("CTL_M_City.City_Id", $City_Id);
        }
        if (!empty($City_Code)):
            $this->db->where("CTL_M_City.City_Code", $City_Code);
        endif;
        if (!empty($Province_Id)):
            $this->db->where("CTL_M_City.Province_Id", $Province_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_City.Active", 1);
        endif;
        $this->db->order_by("CTL_M_City.City_Id");
        $query = $this->db->get();
        return $query;
    }

    function save_CTL_M_City($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_City', $data);
                $City_Id = $this->db->insert_id();
                if ($City_Id != 0):
                    return $City_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_City', $data);
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

?>
