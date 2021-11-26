<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

class position_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_Position_List() {
        $this->db->select("CTL_M_Position.Position_Id AS Id 
            , CTL_M_Position.Position_Code
            , CTL_M_Position.Position_NameEN
            , CTL_M_Position.Position_NameTH
            , CTL_M_Position.Position_Desc
            , CASE WHEN CTL_M_Position.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Position");
        $this->db->order_by("CTL_M_Position.Position_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_Position($Position_Id = NULL, $Position_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_Position");
        if (!empty($Position_Id)):
            $this->db->where("CTL_M_Position.Position_Id", $Position_Id);
        endif;
        if (!empty($Position_Code)):
            $this->db->where("CTL_M_Position.Position_Code", $Position_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_Position.Active", YES);
        endif;
        $this->db->order_by("CTL_M_Position.Position_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_Position($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_Position', $data);
                $Position_Id = $this->db->insert_id();
                if ($Position_Id != 0):
                    return $Position_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_Position', $data);
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
