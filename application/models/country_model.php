<?php

/*
 * Create by Ton! 20131204
 */
?>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class country_model extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function get_CTL_M_Country_List() {
        $this->db->select("CTL_M_Country.Country_Id AS Id 
            , CTL_M_Country.Country_Code
            , CTL_M_Country.Country_NameEN
            , CTL_M_Country.Country_NameTH
            , CTL_M_Country.Country_Desc
            , CASE WHEN CTL_M_Country.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_Country");
        $this->db->order_by("CTL_M_Country.Country_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_CTL_M_Country($Country_Id = NULL, $Country_Code = NULL, $Active = TRUE) {
        $this->db->select("*");
        $this->db->from("CTL_M_Country");
        if (!empty($Country_Id)):
            $this->db->where("CTL_M_Country.Country_Id", $Country_Id);
        endif;
        if (!empty($Country_Code)):
            $this->db->where("CTL_M_Country.Country_Code", $Country_Code);
        endif;
        if ($Active === TRUE):
            $this->db->where("CTL_M_Country.Active", YES);
        endif;
        $this->db->order_by("CTL_M_Country.Country_Id");
        $query = $this->db->get();
        //        echo $this->db->last_query();exit();
        return $query;
    }

    function save_CTL_M_Country($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('CTL_M_Country', $data);
                $Country_Id = $this->db->insert_id();
                if ($Country_Id != 0):
                    return $Country_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_Country', $data);
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
