<?php

class test_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function query($order_id) {

        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->join("STK_T_Order_Detail", "STK_T_Order_Detail.Order_Id = STK_T_Order.Order_Id");
        $this->db->where("STK_T_Order.Order_Id", $order_id);
        $this->db->where("STK_T_Order_Detail.Confirm_Qty <", "STK_T_Order_Detail.Reserv_Qty", FALSE);
        $this->db->where("STK_T_Order_Detail.Active", ACTIVE);
        $query = $this->db->get();
        $result = $query->result();
p($result);
        // Add By Akkarapol, 12/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง
        if (count($result) == 0):
            $this->db->select("Order_Detail_2.*");
            $this->db->from("STK_T_Order_Detail Order_Detail_1");
            $this->db->join("STK_T_Order_Detail Order_Detail_2", "Order_Detail_1.Split_From_Item_Id = Order_Detail_2.Item_Id");
            $this->db->where("Order_Detail_1.Order_Id", $order_id);
            $this->db->where("Order_Detail_1.Active", ACTIVE);
            $this->db->where("Order_Detail_1.Reserv_Qty > Order_Detail_1.Confirm_Qty "); // Add By Akkarapol, 20/09/2013, เพิ่มเช็คเอาเฉพาะอันที่ Reserv_Qty มากกว่า Confirm_Qty
            $this->db->order_by('Order_Detail_1.Item_Id', 'DESC');
            $query = $this->db->get();
            $result = $query->result();
p($result);
        endif;
        // END Add By Akkarapol, 12/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง
        // Add By Akkarapol, 23/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง
        if (count($result) == 0):
            $this->db->select("*");
            $this->db->from("STK_T_Order_Detail");
            $this->db->where("Item_Id = (SELECT TOP 1 Split_From_Item_Id FROM STK_T_Order_Detail WHERE Order_Id = " . $order_id . " AND STK_T_Order_Detail.Reserv_Qty > STK_T_Order_Detail.Confirm_Qty ORDER BY Item_Id DESC)");
//            $this->db->where("STK_T_Order_Detail.Reserv_Qty > STK_T_Order_Detail.Confirm_Qty ");
            $query = $this->db->get();
            $result = $query->result();
p($result);
        endif;
        // END Add By Akkarapol, 23/09/2013, เพิ่มสำหรับเคสที่ทำการ Split แล้วจะทำให้ ไม่สามารถทำให้เป็น Partial ได้ จึงต้อง Query ซ้อนออกมาอีกครั้ง โดยจะดึงจาก Split_From_Item_Id ไปหาค่ามาอีกครั้งหนึ่ง

//p($result);
//exit();

        if (is_array($result) && (count($result) > 0)) {
            $this->db->set("Is_Partial", ACTIVE);
            $this->db->where_in("Order_Id", $order_id);
            $this->db->update("STK_T_Order");
        }
        return "C001";
    }

}

?>
