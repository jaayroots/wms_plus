<?php

class invoice_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_invoice($order_id) {
        $response = "";
        $this->db->select("Invoice_No");
        $this->db->where("Order_Id", $order_id);
        $query = $this->db->get("STK_T_Invoice");
        $result = $query->result();
        foreach ($result as $idx => $val) :
            $response .= $val->Name . ",";
        endforeach;
        return substr($response, 0, -1);
    }

    public function save_invoice($order_id, $data) {
        $affected_rows = 0;
        $loop = 0;
        $raw_data = explode(",", $data);

        foreach ($raw_data as $idx => $val) {
            $data = array(
                'Order_Id' => $order_id,
                'Name' => $val,
                'Create_Date' => date("Y-m-d H:i:s")
            );

            $this->db->insert('STK_T_Invoice', $data);
            $affected_rows += $this->db->affected_rows();
            $loop += 1;
        }

        return ($affected_rows == $loop ? 1 : 0);
    }

    //Insert table Invoice by PoR 2014-07-10
    public function insertInvoiceByDetail($order_id, $invoice_no) {
        $data = array(
            'Order_Id' => $order_id,
            'Invoice_No' => utf8_to_tis620($invoice_no),
            'Create_Date' => date("Y-m-d H:i:s"),
            'Create_By' => $this->session->userdata('user_id')
        );
        $this->db->insert('STK_T_Invoice', $data);
        $Invoice_Id = $this->db->insert_id(); //retrun id
        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0): //can insert
            return $Invoice_Id;
        else: //can't insert
            return 0;
        endif;
    }

//END insertInvoiceByDetail
    //Select Invoice_Id by order_id and invoice_no
    public function getInvoiceIdByInvoiceNo($order_id, $invoice_no) {
        $this->db->select("Invoice_Id");
        $this->db->from("STK_T_Invoice");
        $this->db->where("Order_Id", $order_id);
        $this->db->where("Invoice_No", $invoice_no);

        return $this->db->get();
    }

    public function extract_data($data) {
        
    }

}
