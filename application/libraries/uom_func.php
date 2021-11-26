<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class uom_func {

    function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        $controller = $CI->uri->segment(1);
        $user_id = $CI->session->userdata("user_id");
        $router = $CI->router->fetch_class();
        if (!$user_id && (!in_array($router, array("authen", "sendmail")))) :
            if (!$_COOKIE['connection']) : // check $_COOKIE if have connection from HH allow connection if not reject : by ball : 2013-01-10
                redirect('authen/login');
            endif;
        endif;
    }

    public function set_active_uom($id = NULL, $active = TRUE) {

        $CI = & get_instance();
        $CI->load->model("uom_model", "uom");

        /**
         * set Variable
         */
        $return = array();
        $check_not_err = TRUE;
        $active = ($active) ? 'Y' : 'N';

        if ($active == 'Y'):
            $sql_for_get_id = "
                WITH allParent AS 
                (
                    SELECT *
                    FROM CTL_M_UOM
                    WHERE id = {$id}
                    UNION ALL
                    SELECT uom.*
                    FROM CTL_M_UOM uom
                    JOIN allParent ON uom.id = allParent.parent_id
                 )
                SELECT id
                FROM allParent
            ";
        else:
            $sql_for_get_id = "
                SELECT allChild.id
                FROM CTL_M_UOM uom
                JOIN CTL_M_UOM allChild ON uom.id = allChild.parent_id
                WHERE uom.id = {$id}
            ";
        endif;


        /**
         * get CTL_M_UOM
         */
        if ($check_not_err):
            $result = $CI->db->query($sql_for_get_id)->result();
            $result[]->id = $id;
            if (empty($result)):
                $check_not_err = FALSE;

                /**
                 * Set Alert Zone (set Error Code, Message, etc.)
                 */
                $return['critical'][]['message'] = "Can not get Data CTL_M_UOM by ID = '{$id}'.";
            endif;
        endif;

        /**
         * update Active UOM
         */
        if ($check_not_err):
            $set_id = '';
            foreach ($result as $key_rs => $rs):
                $save_data['active'] = $active;
                $set_id[] = $save_where['id'] = $rs->id;
                $return_save = $CI->uom->save_uom_master('upd', $save_data, $save_where);
                if (!$return_save):
                    $check_not_err = FALSE;

                    /**
                     * Set Alert Zone (set Error Code, Message, etc.)
                     */
                    $return['critical'][]['message'] = "Can not update Active UOM by ID = '" . $rs->id . "'.";
                endif;
            endforeach;
        endif;


        /**
         * update Active UOM_Template
         */
        if ($check_not_err):
            foreach ($set_id as $key_s_id => $s_id):
                $sql_for_get_select = "
                    SELECT CTL_M_UOM_Template.*
                    FROM CTL_M_UOM_Select
                    JOIN CTL_M_UOM_Template ON CTL_M_UOM_Select.id = CTL_M_UOM_Template.CTL_M_UOM_Select_id
                    WHERE type_id = {$s_id}
                    OR unit_id = {$s_id}
                ";
                $result = $CI->db->query($sql_for_get_select)->result();
                if (empty($result)):
//                    $check_not_err = FALSE;
//
//                    /**
//                     * Set Alert Zone (set Error Code, Message, etc.)
//                     */
//                    $return['critical'][]['message'] = "Can not get Data UOM_Template of UOM_ID = '{$s_id}'.";
                else:
                    foreach ($result as $key_rs => $rs):
                        $save_data['active'] = ($active == 'Y' ? (!empty($rs->tmp_active) ? $rs->tmp_active : $active) : $active);
                        $save_where['id'] = $rs->id;
                        $result = $CI->uom->save_template_master("upd", $save_data, $save_where);
                        if (empty($result)):
                            $check_not_err = FALSE;

                            /**
                             * Set Alert Zone (set Error Code, Message, etc.)
                             */
                            $return['critical'][]['message'] = "Can not Update Active UOM_Template_ID = '" . $rs->id . "'.";
                        endif;
                    endforeach;
                endif;
            endforeach;

        endif;

        return $return;
        exit();


//        $CI = & get_instance();
//        $CI->load->model("uom_model", "uom");
//
//        $result = TRUE;
//
//        $isParent = FALSE;
//        $uom_id = array(); // For set active CTL_M_UOM.
//        $parent_id = ""; // For set active CTL_M_UOM_Select
//        $id_for_set_uom_select = ""; // For set active CTL_M_UOM_Select
//        if (!empty($id)):
//            # Get parent_id.
//            $find_column = "DISTINCT CTL_M_UOM.parent_id, CTL_M_UOM.id";
//            $find_where = "CTL_M_UOM.id = " . $id;
//            $r_parent_id = $CI->uom->get_uom_detail_all_lang($find_column, $find_where)->result();
//            if (count($r_parent_id) > 0):
//                foreach ($r_parent_id as $value) :
//                    if ($value->parent_id === "0"):// is Parent.
//                        $isParent = TRUE;
//                    endif;
//                    $parent_id = $value->parent_id;
//                    $id_for_set_uom_select = $value->id;
//                endforeach;
//            endif;
//            
//            if ($parent_id !== "" && $id_for_set_uom_select != ""):
//                $uom_id[] = $id;
//
//                if ($isParent === TRUE):
//                    # Get Child Id for set Active.
//                    $find_column = "DISTINCT CTL_M_UOM.id";
//                    $find_where = " CTL_M_UOM.parent_id = '" . $id_for_set_uom_select . "'";
//                    $r_uom = $CI->uom->get_uom_detail_all_lang($find_column, $find_where)->result();
//                    if (count($r_uom) > 0):
//                        foreach ($r_uom as $value) :
//                            $uom_id[] = (in_array($value->id, $uom_id) ? 0 : $value->id);
//                        endforeach;
//                    endif;
//                else:
//                    # Get Parent Id for set Active.
//                    if ($active === TRUE):
//                        $find_column = "DISTINCT CTL_M_UOM.id";
//                        $find_where = " CTL_M_UOM.parent_id = 0 ";
//                        $find_where = $find_where . " AND CTL_M_UOM.id = '" . $parent_id . "'";
//                        $r_uom = $CI->uom->get_uom_detail_all_lang($find_column, $find_where)->result();
//                        if (count($r_uom) > 0):
//                            foreach ($r_uom as $value) :
//                                $uom_id[] = (in_array($value->id, $uom_id) ? 0 : $value->id);
//                            endforeach;
//                        endif;
//                    endif;
//                endif;
//                
//                if (!empty($uom_id)):
//                    foreach (array_keys($uom_id, 0, true) as $key) :// Remove Value = 0
//                        unset($uom_id[$key]);
//                    endforeach;
//
//                    # Inactive CTL_M_UOM
//                    foreach ($uom_id as $value) :
//                        if ($active === TRUE):
//                            $uom_data['active'] = ACTIVE;
//                        else:
//                            $uom_data['active'] = INACTIVE;
//                        endif;
//                        $uom_where['id'] = $value;
//                        $CI->uom->save_uom_master("upd", $uom_data, $uom_where);
//                    endforeach;
//
//                    $this->set_active_uom_template_of_product($uom_id, $active); // call func.
//                else:
//                    $result = FALSE;
//                endif;
//
//                # Inactive CTL_M_UOM_Select.
//                if ($active === TRUE):
//                    $uom_select_data['active'] = ACTIVE;
//                else:
//                    $uom_select_data['active'] = INACTIVE;
//                endif;
//                if ($isParent === TRUE):
//                    $uom_select_where['type_id'] = $id_for_set_uom_select;
//                else:
//                    $uom_select_where['type_id'] = $parent_id;
//                endif;
//                $CI->uom->save_uom_select("upd", $uom_select_data, $uom_select_where);
//
//                $uom_template_id = array(); // For set active CTL_M_UOM_Template.                
//                # Get id for Inactive CTL_M_UOM_Template.
//                $find_column = "DISTINCT CTL_M_UOM_Select.id";
//                if ($isParent === TRUE):
//                    $find_where = " CTL_M_UOM_Select.type_id = '" . $id_for_set_uom_select . "'";
//                else:
//                    $find_where = " CTL_M_UOM_Select.type_id = '" . $parent_id . "'";
//                endif;
//                $r_uom_select = $CI->uom->get_uom_select($find_column, $find_where)->result();
//                if (count($r_uom_select) > 0):
//                    foreach ($r_uom_select as $value) :
//                        $uom_template_id[] = (in_array($value->id, $uom_template_id) ? 0 : $value->id);
//                    endforeach;
//
//                    if (!empty($uom_template_id)):
//                        foreach (array_keys($uom_template_id, 0, true) as $key) :// Remove Value = 0
//                            unset($uom_template_id[$key]);
//                        endforeach;
//
//                        $this->set_active_uom_template($uom_template_id, $active); // call func.
//                    endif;
//                endif;
//            else:
//                $result = FALSE;
//            endif;
//        else:
//            $result = FALSE;
//        endif;
//
//        return $result;
    }

    public function set_active_uom_template($id = array(), $active = FALSE) {
        $CI = & get_instance();
        $CI->load->model("uom_model", "uom");

        $result = TRUE;
        if (!empty($id)):
            # Inactive CTL_M_UOM_Template.
            foreach ($id as $value) :
                if ($active === TRUE):
                    $uom_data['active'] = ACTIVE;
                else:
                    $uom_data['active'] = INACTIVE;
                endif;
                $uom_where['id'] = $value;
                $CI->uom->save_template_master("upd", $uom_data, $uom_where);
            endforeach;
        else:
            $result = FALSE;
        endif;

        return $result;
    }

    public function set_active_uom_template_of_product($id = array(), $active = FALSE) {
        $CI = & get_instance();
        $CI->load->model("uom_model", "uom");
        $result = TRUE;
        if (!empty($id)):
            # Inactive CTL_M_UOM_Template_Of_Product by standard_unit_in_id.
            foreach ($id as $value) :
                if ($active === TRUE):
                    $uom_template_data_in['active'] = ACTIVE;
                else:
                    $uom_template_data_in['active'] = INACTIVE;
                endif;
                $uom_template_where_in['standard_unit_in_id'] = $value;
                $CI->uom->save_uom_of_product("upd", $uom_template_data_in, $uom_template_where_in);
            endforeach;

            # Inactive CTL_M_UOM_Template_Of_Product by standard_unit_out_id.
            foreach ($id as $value) :
                if ($active === TRUE):
                    $uom_template_data_out['active'] = ACTIVE;
                else:
                    $uom_template_data_out['active'] = INACTIVE;
                endif;
                $uom_template_where_out['standard_unit_out_id'] = $value;
                $CI->uom->save_uom_of_product("upd", $uom_template_data_out, $uom_template_where_out);
            endforeach;
        else:
            $result = FALSE;
        endif;

        return $result;
    }

}
