<?php

class container_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_container($order_id) {
        $response = "";
        $this->db->select("Cont_Id,Cont_No,Cont_Size_Id");
        $this->db->where("Order_Id", $order_id);
        $this->db->where("Active", 'Y');
        $query = $this->db->get("CTL_M_Container");
        return $query;
    }

    public function save_container($order_id, $data, $return_cont = FALSE) {

        $affected_rows = 0;
        $loop = 0;
        $raw_data = $data;
        $con_id = array();

        foreach ($raw_data as $idx => $val) {
            $data = array(
                'Order_Id' => $order_id,
                'Cont_No' => utf8_to_tis620($val->name),
                'Cont_Size_Id' => $val->size,
                'Created_Date' => date("Y-m-d H:i:s"),
                'Created_By' => $this->session->userdata('user_id'),
                'Active' => 'Y'
            );

            $this->db->insert('CTL_M_Container', $data);
            $con_id[] = $this->db->insert_id();
            $affected_rows += $this->db->affected_rows();
            $loop += 1;
        }

        if ($return_cont):
            return $con_id;
        else:
            return ($affected_rows == $loop ? 1 : 0);
        endif;
    }

    public function update_container($order_id, $data, $type = "INBOUND") {
        $affected_rows = 0;
        $loop = 0;
        //$raw_data = explode(",",$data);
        $raw_data = $data;
        $con_id = "";
        $cont_id = "";
        $i = 1;
        foreach ($raw_data as $idx => $val) {
            if (empty($val->id)):
                $val->id = "NEW";
            endif;

            $data_insert = array();
            if ($val->id == 'NEW'):    //case new data
                if ($type == 'INBOUND'):
                    $data_insert = array(
                        'Order_Id' => $order_id,
                        'Cont_No' => utf8_to_tis620($val->name),
                        'Cont_Size_Id' => $val->size,
                        'Created_Date' => date("Y-m-d H:i:s"),
                        'Created_By' => $this->session->userdata('user_id'),
                        'Active' => 'Y'
                    );
                else:  //case outbound insert only container detail,order_id insert in CTL_M_Container_Order:ADD BY POR 2014-10-05
                //search order_id on detail ->if have order_id not insert because dispatch not insert container duplicate:ADD BY POR 2014-10-05
                /* comment ไว้ จะใช้ก็ต่อเมื่อกรณีที่ต้องการ insert container ตอน dispatch PC (ตอนนี้ให้ update อย่างเดียวเลยปิดส่วนนี้ไว้)
                  $this->db->select("Cont_Id");
                  $this->db->from("CTL_M_Container_Order");
                  $this->db->where("Order_Id",$order_id);
                  $res_cont = $this->db->get()->row();
                  if(!empty($res_cont)):
                  $cont_id = $res_cont->Cont_Id;
                  endif;

                  $num_rows=$this->db->count_all_results();

                  if($num_rows<1):
                  $data_insert = array(
                  'Cont_No' => utf8_to_tis620($val->name) ,
                  'Cont_Size_Id' => $val->size,
                  'Created_Date' => date("Y-m-d H:i:s"),
                  'Created_By' => $this->session->userdata('user_id'),
                  'Active' => 'Y',
                  'Status_Module' => 'OUTBOUND'
                  );
                  endif;
                 */
                endif;
                if (!empty($data_insert)):
                    $this->db->insert('CTL_M_Container', $data_insert);
                    $cont_id = $this->db->insert_id();
                endif;

                /* comment by por 2014-10-06 จะเปิดใช้กรณีอนุญาตให้ add container ใน dispatch PC
                  if($type == 'OUTBOUND'): //Add on detail
                  if(!empty($cont_id)):
                  $data_order = array(
                  'Cont_Id' => $this->db->insert_id() ,
                  'Order_Id' => $order_id,
                  'Active' => 'Y'
                  );

                  $this->db->insert('CTL_M_Container_Order', $data_order);
                  endif;
                  endif;
                 */


                $con_id .= $cont_id . ",";
                $affected_rows += $this->db->affected_rows();
                $loop += 1;
            else:   //case update data

                $con_id .= $val->id . ",";

                $data_update = array(
                    'Cont_No' => utf8_to_tis620($val->name),
                    'Cont_Size_Id' => $val->size,
                    'Modified_Date' => date("Y-m-d H:i:s"),
                    'Modified_By' => $this->session->userdata('user_id')
                );

                $this->db->where("Cont_Id", $val->id);
                $query = $this->db->update('CTL_M_Container', $data_update);
                $affected_rows += $this->db->affected_rows();
                $loop += 1;
                $i++;
            endif;
        }

        $con_id = substr($con_id, 0, -1);

        //Active no for record not use
        if (!empty($con_id)):
            $data = array(
                'Modified_Date' => date("Y-m-d H:i:s"),
                'Modified_By' => $this->session->userdata('user_id'),
                'Active' => 'N'
            );
            $this->db->where("Cont_Id not in (" . $con_id . ") and Order_Id =" . $order_id);
            $query = $this->db->update('CTL_M_Container', $data);
            $affected_rows += $this->db->affected_rows();
            $loop += $this->db->affected_rows();
        endif;


        return ($affected_rows == $loop ? 1 : 0);
    }

    public function extract_data($data) {
        
    }

    public function getContainerByOrderId($order_id, $top = "") {
        if (!empty($top)):
            $top = "TOP " . $top;
        endif;

        $this->db->select($top . " Cont_No, CTL_M_Container.Cont_Size_Id, Cont_Size_No, Cont_Size_Unit_Code");
        $this->db->where("Order_Id", $order_id);
        $this->db->where("CTL_M_Container.Active", 'Y');
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
        $query = $this->db->get("CTL_M_Container");

        return $query;
    }

    public function getContainerByOrderIdOutbound($order_id) {
        $this->db->select("DISTINCT CTL_M_Container.Cont_Id,CTL_M_Container.Cont_No, CTL_M_Container.Cont_Size_Id, Cont_Size_No, Cont_Size_Unit_Code");
        $this->db->join("CTL_M_Container", "CTL_M_Container_Order.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
        $this->db->where("CTL_M_Container_Order.Order_Id", $order_id);
        $this->db->where("CTL_M_Container_Order.Active", 'Y');
        $query = $this->db->get("CTL_M_Container_Order");

        return $query;
    }

    /**
     *
     * @return type
     */
    public function getContainerSize() {
        $this->db->where("Active", ACTIVE);
        $query = $this->db->get("CTL_M_Container_Size");

        return $query;
    }

    /**
     *
     * @param type $order_id
     * @return type
     */
    public function get_container_dropdown_list($order_id) {
        $this->db->select("*");
        $this->db->where("Order_ID", $order_id);
        $this->db->where("CTL_M_Container.Active", ACTIVE);
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id");
        $query = $this->db->get("CTL_M_Container");

        return $query;
    }

    /**
     *
     * @param type $order_id
     * @return type
     */
    public function get_container_dropdown_list_outbound($order_id) {
        $this->db->select("CTL_M_Container.Cont_Id,CTL_M_Container.Cont_No,Cont_Size_No,Cont_Size_Unit_Code");
        $this->db->join("CTL_M_Container", "CTL_M_Container_Order.Cont_Id = CTL_M_Container.Cont_Id");
        $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id");
        $this->db->where("CTL_M_Container_Order.Order_ID", $order_id);
        $this->db->where("CTL_M_Container_Order.Active", ACTIVE);
        $query = $this->db->get("CTL_M_Container_Order");

        return $query;
    }

    public function update_time_container($container_id, $mode = "START") {
        if ($mode == "START") {

            $data_update = array('Start_Date' => date("Y-m-d H:i:s"));
            $this->db->where("Cont_Id", $container_id);
            $this->db->where("Start_Date IS NULL");
            $this->db->update('CTL_M_Container', $data_update);
            $response = $this->db->_error_number();

            return $response;
        } else if ($mode == "END") {

            $data_update = array('End_Date' => date("Y-m-d H:i:s"));
            $this->db->where("Cont_Id", $container_id);
            $query = $this->db->update('CTL_M_Container', $data_update);
            $response = $this->db->affected_rows();

            return $response;
        } else {
            return FALSE;
        }
    }
    
}
