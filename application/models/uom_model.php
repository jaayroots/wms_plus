<?php

class Uom_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_uom_list() {
        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.code = CTL_M_UOM_Language.CTL_M_UOM_code AND CTL_M_UOM_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_uom_all() {
        $this->db->select("CTL_M_UOM.id AS Id
            , CTL_M_UOM.code
            , CTL_M_UOM_Language.name
            , CASE WHEN CTL_M_UOM.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active");
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.id = CTL_M_UOM_Language.CTL_M_UOM_id AND CTL_M_UOM_Language.language = '" . $this->config->item('lang3digit') . "'");
//        $this->db->where("CTL_M_UOM.Active", ACTIVE); 
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function get_template_all() {
        $this->db->select("CTL_M_UOM_Template.id AS Id
            , CTL_M_UOM_Template_Language.public_name
            , CTL_M_UOM_Template_Language.description
            , CASE WHEN CTL_M_UOM_Template.Active IN ('Y') THEN 'YES' ELSE 'NO' END AS Active
            , CTL_M_UOM_Template.root_unit
        ");
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        //$this->db->where("CTL_M_UOM.Active", ACTIVE); 
	$this->db->where("CTL_M_UOM_Template.id > 4");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }

    function get_uom_master_list() {
        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.id = CTL_M_UOM_Language.CTL_M_UOM_id AND CTL_M_UOM_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where("CTL_M_UOM.parent_id", '0');
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);// Comment Out by Ton! 20140218 For Edit Active.
        $this->db->order_by("CTL_M_UOM.id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query;
    }

    function get_uom_slave_list() {
        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.id = CTL_M_UOM_Language.CTL_M_UOM_id AND CTL_M_UOM_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where("CTL_M_UOM.parent_id !=", '0');
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $this->db->order_by("CTL_M_UOM.id"); // Comment Out by Ton! 20140218 For Edit Active.
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_uom_detail($column = '*', $where = ' 1=1 ') {
//        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        $this->db->select($column);
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.id = CTL_M_UOM_Language.CTL_M_UOM_id AND CTL_M_UOM_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where($where);
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }

    function get_template_detail($column = '*', $where = ' 1=1 ') {
//        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        $this->db->select($column);
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("CTL_M_UOM_Select", "CTL_M_UOM_Select.id = CTL_M_UOM_Template.CTL_M_UOM_Select_id");
        $this->db->where($where);
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function get_uom_detail_all_lang($column = NULL, $where = NULL) {
//        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        if (!empty($column)):
            $this->db->select($column);
        else:
            $this->db->select("DISTINCT CTL_M_UOM.*");
        endif;
        $this->db->from("CTL_M_UOM");
        $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.id = CTL_M_UOM_Language.CTL_M_UOM_id ");
        if (!empty($where)):
            $this->db->where($where);
        endif;
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
//        echo $this->db->last_query();//exit();
        return $query;
    }

    function get_template_detail_all_lang($column = NULL, $where = NULL) {
//        $this->db->select('CTL_M_UOM.id AS Id, CTL_M_UOM.code, CTL_M_UOM_Language.name');
        if (!empty($column)):
            $this->db->select($column);
        else:
            $this->db->select("DISTINCT CTL_M_UOM_Template.*");
        endif;
        $this->db->from("CTL_M_UOM_Template");
        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id ");
        if (!empty($where)):
            $this->db->where($where);
        endif;
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        return $query;
    }

    function save_uom_master($type = NULL, $data = NULL, $where = NULL) {
        if ($type == 'ist') :// Insert.
            $this->db->insert('CTL_M_UOM', $data);
            $last_id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($last_id > 0) :
//                $this->db->select('CTL_M_UOM.id');
//                $this->db->from("CTL_M_UOM");
//                $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.code = CTL_M_UOM_Language.CTL_M_UOM_code ");
//                $this->db->where('CTL_M_UOM.id', $last_id);
//                return $this->db->get()->result()->id;
                return $last_id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_UOM', $data);
//            echo $this->db->last_query();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_uom_master_language_batch($type = NULL, $data = NULL, $where = NULL) {
        if ($type == 'ist') :// Insert.
            $this->db->insert_batch('CTL_M_UOM_Language', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_UOM_Language', $data);
//            echo $this->db->last_query();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    public function get_uom_select($column = NULL, $where = NULL) {
        if (!empty($column)):
            $this->db->select($column);
        else:
            $this->db->select("DISTINCT CTL_M_UOM_Select.*");
        endif;
        $this->db->from("CTL_M_UOM_Select");
//        $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id ");
        if (!empty($where)):
            $this->db->where($where);
        endif;
//        $this->db->where("CTL_M_UOM.Active", ACTIVE);
        $query = $this->db->get();
//        echo $this->db->last_query(); exit();
        return $query;
    }

    function save_uom_select($type, $data, $where) {
        if ($type == 'ist') :// Insert.
            $this->db->insert('CTL_M_UOM_Select', $data);
            $last_id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($last_id > 0) :
//                $this->db->select('CTL_M_UOM_Select.code');
//                $this->db->from("CTL_M_UOM_Select");
////                $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.code = CTL_M_UOM_Language.CTL_M_UOM_code ");
//                $this->db->where('CTL_M_UOM_Select.id',$last_id);
                return $last_id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_UOM_Select', $data);
//            echo $this->db->last_query();exit();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_template_master($type = NULL, $data = NULL, $where = NULL) {
        if ($type == 'ist') :// Insert.
            $this->db->insert('CTL_M_UOM_Template', $data);
            $last_id = $this->db->insert_id();
//            echo $this->db->last_query();
            if ($last_id > 0) :
//                $this->db->select('CTL_M_UOM_Template.code');
//                $this->db->from("CTL_M_UOM_Template");
////                $this->db->join("CTL_M_UOM_Language", "CTL_M_UOM.code = CTL_M_UOM_Language.CTL_M_UOM_code ");
//                $this->db->where('CTL_M_UOM_Template.id',$last_id);
                return $last_id;
            else:
                return FALSE;
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_UOM_Template', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    function save_template_master_language_batch($type, $data, $where) {
        if ($type == 'ist') :// Insert.
            $this->db->insert_batch('CTL_M_UOM_Template_Language', $data);
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        elseif ($type == 'upd') :// Update.
            $this->db->where($where);
            $this->db->update('CTL_M_UOM_Template_Language', $data);
//            echo $this->db->last_query();
            $afftectedRows = $this->db->affected_rows();
            if ($afftectedRows >= 0) :
                return TRUE; // Save Success.
            else :
                return FALSE; // Save UnSuccess.
            endif;
        endif;
    }

    // Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับ query เอาค่าใน Table 'CTL_M_UOM_Template_Of_Product'
    public function get_uom_of_product($column = '*', $where = ' 1=1 ', $order_by = null) {
        if ($column == '*'):
            $column = 'CTL_M_UOM_Template_Of_Product.*,TLIN.public_name AS Unit_Value_In,TLOUT.public_name AS Unit_Value_Out';
        endif;

        $this->db->select($column);
        $this->db->from("CTL_M_UOM_Template_Of_Product");
        $this->db->join("CTL_M_UOM_Template_Language TLIN", "CTL_M_UOM_Template_Of_Product.standard_unit_in_id = TLIN.CTL_M_UOM_Template_id AND TLIN.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->join("CTL_M_UOM_Template_Language TLOUT", "CTL_M_UOM_Template_Of_Product.standard_unit_out_id = TLOUT.CTL_M_UOM_Template_id AND TLOUT.language = '" . $this->config->item('lang3digit') . "'");
        $this->db->where($where);
        if ($order_by != null):
            $this->db->order_by($order_by);
        endif;
        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query;
    }

    // END Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับ query เอาค่าใน Table 'CTL_M_UOM_Template_Of_Product'
    // Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับ ลบ UOM Of Product ที่ไม่ต้องการใช้แล้ว
    public function del_uom_of_product($list_chk_uom_id = NULL, $where = NULL) {
        $this->db->where_not_in('id', $list_chk_uom_id);
        $this->db->where($where);
        $this->db->delete('CTL_M_UOM_Template_Of_Product');
//        $afftectedRows = $this->db->affected_rows();
//        if ($afftectedRows > 0):
//            return TRUE;
//        else:
//            return FALSE;
//        endif;
    }

    // END Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับ ลบ UOM Of Product ที่ไม่ต้องการใช้แล้ว
    // Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับบันทึกลง CTL_M_UOM_Template_Of_Product
    public function save_uom_of_product($type = NULL, $data = NULL, $where = NULL) {
        if ($type != NULL):
            if ($type == 'ist'):
                $this->db->insert_batch('CTL_M_UOM_Template_Of_Product', $data);
                $afftectedRows = $this->db->affected_rows();
                return $afftectedRows;
            elseif ($type == 'upd'):
                $this->db->where($where);
                $this->db->update('CTL_M_UOM_Template_Of_Product', $data);
//                echo $this->db->last_query();exit();
                $afftectedRows = $this->db->affected_rows();
                if ($afftectedRows >= 0) :
                    return TRUE; // Save Success.
                else :
                    return FALSE; // Save UnSuccess.
                endif;
            endif;
        endif;
    }

    // END Add By Akkarapol, 15+16/01/2014, เพิ่มฟังก์ชั่นสำหรับบันทึกลง CTL_M_UOM_Template_Of_Product
}
