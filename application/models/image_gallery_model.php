<?php

/*
 * Create by Ton! 20131212
 */
?>
<?php

class image_gallery_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_ADM_M_ImageCategory_Gallery_List() {
        $this->db->select("ADM_M_ImageCategory.ImageCategory_Id AS Id 
            , ADM_M_ImageCategory.ImageCategory_Code
            , ADM_M_ImageCategory.ImageCategory_NameEN
            , ADM_M_ImageCategory.ImageCategory_NameTH
            , ADM_M_ImageCategory.ImageCategory_Desc");
        $this->db->from("ADM_M_ImageCategory");
        $this->db->order_by("ADM_M_ImageCategory.ImageCategory_Id");
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_ADM_M_ImageItem_List($ImageCategory_Id = NULL) {
        $this->db->select("ADM_M_ImageItem.ImageItem_Id
            , ADM_M_ImageItem.ImageName + ADM_M_ImageItem.ImageExt AS ImageItemName
            , ADM_M_ImageItem.ImageDesc
            , CASE WHEN ADM_M_ImageItem.Active IN ('1') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("ADM_M_ImageItem");
        if (!empty($ImageCategory_Id)):
            $this->db->where("ADM_M_ImageItem.ImageCategory_Id", $ImageCategory_Id);
        endif;
        $this->db->order_by("ADM_M_ImageItem.ImageCategory_Id, ADM_M_ImageItem.ImageItem_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

//    function get_ADM_M_ImageItem($ImageCategory_Id = NULL, $ImageItem_Id = NULL, $ImageName = NULL, $Active = TRUE) {
//        $this->db->select("*");
//        $this->db->from("ADM_M_ImageItem");
//        if (!empty($ImageCategory_Id)):
//            $this->db->where("ADM_M_ImageItem.ImageCategory_Id", $ImageCategory_Id);
//        endif;
//        if (!empty($ImageItem_Id)):
//            $this->db->where("ADM_M_ImageItem.ImageItem_Id", $ImageItem_Id);
//        endif;
//        if (!empty($ImageName)):
//            $this->db->where("ADM_M_ImageItem.ImageName", $ImageName);
//        endif;
//        if ($Active === TRUE):
//            $this->db->where("ADM_M_ImageItem.Active", YES);
//        endif;
//        $this->db->order_by("ADM_M_ImageItem.ImageCategory_Id, ADM_M_ImageItem.ImageItem_Id");
//        $query = $this->db->get();
//        //        echo $this->db->last_query();exit();
//        return $query;
//    }

    function save_ADM_M_ImageItem($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert('ADM_M_ImageItem', $data);
                $ImageCategory_Id = $this->db->insert_id();
                if ($ImageCategory_Id != 0):
                    return $ImageCategory_Id; // Save Success.
                else:
                    return FALSE; // Save Unsuccess.
                endif;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('ADM_M_ImageItem', $data);
//                echo $this->db->last_query(); 
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
