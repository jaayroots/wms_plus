<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

class department_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_Department_List() {
        $this->db->select("CTL_M_Department.Department_Id AS Id 
            , CTL_M_Department.Department_Code
            , CTL_M_Department.Department_NameEN
            , CTL_M_Department.Department_NameTH
            , CTL_M_Department.Department_Desc
            , CASE WHEN CTL_M_Department.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Department");
        $this->db->order_by("CTL_M_Department.Department_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_Department($Department_Id = NULL, $Department_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_Department");
        if (!empty($Department_Id)):
            $this->db->where("CTL_M_Department.Department_Id", $Department_Id);
        endif;
        if (!empty($Department_Code)):
            $this->db->where("CTL_M_Department.Department_Code", $Department_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_Department.Active", YES);
        endif;
        $this->db->order_by("CTL_M_Department.Department_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_Department($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_Department', $data);
                $Department_Id = $this->db->insert_id();
                if ($Department_Id != 0):
                    return $Department_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_Department', $data);
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
