<?php

// Akkarapol, 02/09/2013, เขียนไฟล์นี้ใหม่ เพื่อใช้กับ partial receive โดยเฉพาะ
class partial_receive_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getPartialReceiveOrder() {
        #Create where clause
        $this->db->select('STK_T_Relocate_Detail.Document_No');
        $this->db->from('STK_T_Relocate_Detail');
        $query = $this->db->get();
        $result = $query->result();
        $where_clause = array();
        if (count($result) > 0) {
            foreach ($result as $rows) {
                $where_clause[] = $rows->Document_No;
            }
        }
        unset($result);
        $this->db->select("O.Document_No, 
                            O.Doc_Refer_Ext, 
                            O.Doc_Refer_Int, 
                            CONVERT(VARCHAR(10), O.Create_Date, 103) AS Receive_Date
                    ");
        $this->db->from("STK_T_Workflow W");
        $this->db->join('STK_T_Order O', 'W.Flow_Id = O.Flow_Id ', 'INNER');
        $this->db->where("W.Process_Id = '1'");
        $this->db->where_in("W.Present_State", array('-2', '5', '6')); // Edit By Akkarapol, 03/09/2013, เปลี่ยนเป็น where_in เพื่อที่จะได้ใส่ State ได้หลายตัว ซึ่ง '-2','5','6' ก็คือ Complete และ Putaway อีกสองสถานะ
        $this->db->where("O.Is_Partial", ACTIVE);
        $this->db->group_by("O.Document_No, O.Doc_Refer_Ext, O.Doc_Refer_Int, O.Create_Date");

        $query = $this->db->get();
//        echo $this->db->last_query(); 
        return $query;
    }

    function getOrderByDocumentNo($document_list) {
        $this->db->select("*");
        $this->db->from("STK_T_Order");
        $this->db->where_in("Document_No", $document_list);
        $this->db->order_by('Order_Id', 'DESC'); // Add By Akkarapol, 16/09/2013, เพิ่ม order เข้าไปเพื่อให้เรียกค่าของตัวหลังสุดออกมา
        $query = $this->db->get();
//		echo $this->db->last_query(); 
        return $query;
    }

    function addPartialReceiveToOrder($order) {
        $this->db->insert("STK_T_Order", $order);
        return $this->db->insert_id();
    }

    //#2764 BY POR 2013-10-04 สำหรับเรียก detail ที่ต้องบันทึกใหม่กรณีทำ Partial
    function getOrderDetailPartial($orderId) {
        $query = $this->db->query("EXEC sp_getOrderDetailPartial '" . $orderId . "'");
        return $query;
    }

    function getOrderDetailByOrderId($orderId) {
        // Edit By Akkarapol, 20/09/2013, จัดการ query ใหม่ เพื่อให้ได้ตรงตามที่ต้องการ โดย join ตารางตัวเองไปด้วย เพื่อเช็คหาค่าที่ถูกต้องที่สุด
        $this->db->select("STK_T_Order_Detail_1.*");
        $this->db->from("STK_T_Order_Detail STK_T_Order_Detail_1");
        $this->db->join("STK_T_Order_Detail STK_T_Order_Detail_2", "STK_T_Order_Detail_2.Item_Id = STK_T_Order_Detail_1.Split_From_Item_Id", "INNER");
        $this->db->where_in("STK_T_Order_Detail_1.Order_Id", $orderId);
        $this->db->where("STK_T_Order_Detail_1.Reserv_Qty > STK_T_Order_Detail_1.Confirm_Qty"); // Add By Akkarapol, 09/09/2013, เพิ่ม where เพื่อเอาเฉพาะ Order Detail ที่ยัง นำเข้า ไม่ครบ
        // $this->db->where("STK_T_Order_Detail_2.Split_From_Item_Id IS NULL"); // Add By Akkarapol, 12/09/2013, เพิ่ม where เพื่อเอาเฉพาะ Order Detail ที่ไม่ได้เป็นตัวที่ ทำการ Split มาจาก ตัวอื่น

        $query = $this->db->get();
//        echo $this->db->last_query();
        // Add By Akkarapol, 23/09/2013, เพิ่มการเช็คว่า ถ้าการ query ด้านบน ไม่มีค่า ให้ดึงค่าจากตัวที่ถูกทำการ Split มาหลังสุด
        $result = $query->result();
        if (count($result) == 0):
            $this->db->select("*");
            $this->db->from("STK_T_Order_Detail");

//            $this->db->where("Item_Id = (SELECT TOP 1 Split_From_Item_Id FROM STK_T_Order_Detail WHERE Order_Id = " . $orderId . " ORDER BY Item_Id DESC)"); // Comment By Akkarapol, 25/09/2013, แก้ไขเวลาที่ Unlock Partial แล้ว ค่า Receive Qty ที่ได้ออกมามีค่าเป็น 0 เนื่องจาก การ filter Item_Id ยังไม่ละเอียดพอ
            // Add By Akkarapol, 25/09/2013, แก้ไขเวลาที่ Unlock Partial แล้ว ค่า Receive Qty ที่ได้ออกมามีค่าเป็น 0 เนื่องจาก การ filter Item_Id ยังไม่ละเอียดพอ
            $this->db->where('Item_Id = 
CASE
WHEN
(SELECT TOP 1 Split_From_Item_Id FROM STK_T_Order_Detail WHERE Order_Id = ' . $orderId . ' ORDER BY Item_Id DESC) IS NULL
THEN
(SELECT TOP 1 Item_Id FROM STK_T_Order_Detail WHERE Order_Id = ' . $orderId . ' ORDER BY Item_Id DESC)
ELSE
(SELECT TOP 1 Split_From_Item_Id FROM STK_T_Order_Detail WHERE Order_Id = ' . $orderId . ' ORDER BY Item_Id DESC)
END');
            // END Add By Akkarapol, 25/09/2013, แก้ไขเวลาที่ Unlock Partial แล้ว ค่า Receive Qty ที่ได้ออกมามีค่าเป็น 0 เนื่องจาก การ filter Item_Id ยังไม่ละเอียดพอ

            $query = $this->db->get();
        endif;
        // END Add By Akkarapol, 23/09/2013, เพิ่มการเช็คว่า ถ้าการ query ด้านบน ไม่มีค่า ให้ดึงค่าจากตัวที่ถูกทำการ Split มาหลังสุด
//		echo $this->db->last_query(); 
        return $query;
    }

    function addPartialReceiveToOrderDetail($orderDetail) {
        $this->db->insert_batch('STK_T_Order_Detail', $orderDetail);
        $afftectedRows = $this->db->affected_rows();
        return $afftectedRows;
    }

    function changeIsNotPartialByOrderId($orderId) {
        $this->db->where('Order_Id', $orderId);
        $update = array(
            'Is_Partial' => 'N'
        );
        $this->db->update('STK_T_Order', $update);
        return $this->db->affected_rows();
    }

}

?>