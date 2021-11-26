<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

class province_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_Province_List() {
        $this->db->select("CTL_M_Province.Province_Id AS Id 
            , CTL_M_Province.Province_Code
            , CTL_M_Province.Province_NameEN
            , CTL_M_Province.Province_NameTH
            , CTL_M_Province.Province_Desc
            , CTL_M_Country.Country_NameEN
            , CASE WHEN CTL_M_Province.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Province");
        $this->db->join("CTL_M_Country", "CTL_M_Country.Country_Id = CTL_M_Province.Country_Id", "LEFT");
        $this->db->order_by("CTL_M_Province.Province_Id, CTL_M_Province.Country_Id");
//        $this->db->order_by("CTL_M_Province.Country_Id, CTL_M_Province.Province_Id");
        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_Province($Province_Id = NULL, $Province_Code = NULL, $Country_Id = NULL, $Active = TRUE) {
        $this->db->select("CTL_M_Province.*, CTL_M_Country.Country_NameEN");
        $this->db->from("CTL_M_Province");
        $this->db->join("CTL_M_Country", "CTL_M_Province.Country_Id = CTL_M_Country.Country_Id", "LEFT");
        if (!empty($Province_Id)) {
            $this->db->where("CTL_M_Province.Province_Id", $Province_Id);
        }
        if (!empty($Province_Code)):
            $this->db->where("CTL_M_Province.Province_Code", $Province_Code);
        endif;
        if (!empty($Country_Id)):
            $this->db->where("CTL_M_Province.Country_Id", $Country_Id);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_Province.Active", YES);
        endif;
        $this->db->order_by("CTL_M_Province.Province_Id");
        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query;
    }

    function save_CTL_M_Province($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_Province', $data);
                $Province_Id = $this->db->insert_id();
                if ($Province_Id != 0):
                    return $Province_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_Province', $data);
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

