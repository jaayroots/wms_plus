<?php

/*
 * Create by Ton! 20131206
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class title_name_model extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_TitleName_List() {
        $this->db->select("CTL_M_TitleName.TitleName_Id AS Id 
            , CTL_M_TitleName.TitleName_Code
            , CTL_M_TitleName.TitleName_EN
            , CTL_M_TitleName.TitleName_TH
            , CTL_M_TitleName.TitleName_Desc
            , CASE WHEN CTL_M_TitleName.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_TitleName");
        $this->db->order_by("CTL_M_TitleName.TitleName_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_TitleName($TitleName_Id = NULL, $TitleName_Code = NULL, $TitleName_EN = NULL, $Active = TRUE) {// Add by Ton! 20130912
        $this->db->select("*");
        $this->db->from("CTL_M_TitleName");
        if (!empty($TitleName_Id)):
            $this->db->where("TitleName_Id", $TitleName_Id);
        endif;
        if (!empty($TitleName_Code)):
            $this->db->where("TitleName_Code", $TitleName_Code);
        endif;
        if (!empty($TitleName_EN)):
            $this->db->where("TitleName_EN", $TitleName_EN);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_TitleName.Active", YES);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }
    
       function save_CTL_M_TitleName($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_TitleName', $data);
                $TitleName_Id = $this->db->insert_id();
                if ($TitleName_Id != 0):
                    return $TitleName_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_TitleName', $data);
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
