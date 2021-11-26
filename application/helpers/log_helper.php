<?php
//Now function not use 2014-07-07
if (!function_exists('logOrderDetail')) {

    function logOrderDetail($order_id, $module, $action_id, $action_type) {
        $CI = & get_instance();
//        $CI->load->database();
        $sql = "SELECT * FROM STK_T_Order_Detail WHERE Order_Id = $order_id ";
        $query = $CI->db->query($sql);
        $result = $query->result_array();
        $data = array();
        foreach ($result as $rows) {
            $rows['Module'] = $module;
            $rows['Action_Id'] = $action_id;
            $rows['Action_Type'] = $action_type;
            $data[] = $rows;
        }
        $CI->db->insert_batch('STK_L_Order_Detail', $data);

        $sql = "update STK_T_Order_Detail set  Product_Mfd = null , Product_Exp  = null
                 where Product_Mfd = '1900-01-01 00:00:00.000'  or  Product_Exp = '1900-01-01 00:00:00.000'";
        $CI->db->query($sql);
    }

}
?>