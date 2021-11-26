<?php

/**
 * Description of order_movement_model
 *
 * @author Thida
 */
class order_movement_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function get_submodule_activity($order_id = NULL, $module = NULL, $activity = NULL) {

        $this->db->select('MAX(Id) AS last_id, Order_Id, Edge_Id, Sub_Module, Module');
        $this->db->from('vw_activity_by');

        if ($order_id != NULL):
            $this->db->where('Order_Id', $order_id);
        endif;

        if ($module != NULL):
            $this->db->where('Module', $module);
        endif;

        #check for Picking By or Dispatch By 
        if ($activity == 'cf_pick_by' || 'cf_dispatch_by'):
            $this->db->where("Sub_Module <> 'approveAction'");
        endif;

        #check for Putaway By
        if ($activity == 'cf_putaway_by'):
            $this->db->where("Sub_Module <> 'approveAction'");
        endif;

        $this->db->group_by('Order_Id, Edge_Id, Sub_Module, Module');
        $this->db->order_by('last_id', 'DESC');

        $query = $this->db->get();
        $query = $query->row();
//        echo $this->db->last_query();exit();
        if (!empty($query)):
            return $query->Sub_Module;
        else:
            return NULL;
        endif;
    }

    // Create Joke 10/10/57  Edit 29/10/57
    public function get_Order_Tracking($order_id, $flow_id) {
        //p($order_id);
        //p($flow_id);

        $this->db->select('MAX(a.Id) AS last_id, a.Order_Id, a.Edge_Id, a.Sub_Module, a.Module, CTL_M_Contact.First_NameTH, CTL_M_Contact.Last_NameTH, SYS_M_Stateedge.[Description], SYS_M_Process.Process_NameEn');
        $this->db->from('STK_T_Logs_Action a');
        $this->db->join('CTL_M_Contact', 'a.Activity_By = CTL_M_Contact.Contact_Id', 'left');
        $this->db->join('SYS_M_Stateedge', 'SYS_M_Stateedge.Edge_Id = a.Edge_Id', 'left');
        $this->db->join('SYS_M_Process', 'SYS_M_Stateedge.Process_Id =  SYS_M_Process.Process_Id', 'left');
        $this->db->join('STK_T_Workflow', 'SYS_M_Stateedge.Process_Id = STK_T_Workflow.Process_Id and STK_T_Workflow.Flow_Id =' . $flow_id, 'left');
        $this->db->where("a.Order_Id", $order_id);
        $this->db->where("SYS_M_Stateedge.Edge_Id in (select Edge_Id from SYS_M_Stateedge where SYS_M_Stateedge.Process_Id = STK_T_Workflow.Process_Id)");
        $this->db->group_by('a.Order_Id, a.Edge_Id, a.Sub_Module, a.Module, CTL_M_Contact.First_NameTH, CTL_M_Contact.Last_NameTH, SYS_M_Stateedge.[Description], SYS_M_Process.Process_NameEn');
        $this->db->order_by('last_id');
//        $this->db->where("STK_T_Order.Flow_Id = '" . $data . "'");
        $query = $this->db->get();
//        p($this->db->last_query());
        return $query;
    }
    

    public function detail_order_tracking($data_flow, $data_type) {
//        $data_flow_id = $data_flow['flow_id'];
//        $type = $data_type['type'];
//        p($data_flow);
        //p($data_type);

        if ($data_type == "1") {
            $this->db->select("1 as type,order1.Order_Id, order1.Flow_Id, order1.Document_No,
            order1.Doc_Refer_Int, order1.Doc_Refer_Ext, order1.Doc_Refer_Inv, 
            order1.Doc_Refer_CE, order1.Doc_Refer_BL");

            $this->db->from('STK_T_Order order1');
            $this->db->where("order1.Flow_Id", $data_flow);

            $query = $this->db->get();
            //$this->db->last_guery();
            return $query;
        } else if ($data_type == "2") {
            $this->db->select("2 as type,relocate.Order_Id,relocate.Flow_Id, relocate.Doc_Relocate as Document_No , '' as Doc_Refer_Int , '' as Doc_Refer_Ext ,
                                    '' as Doc_Refer_Inv , '' as Doc_Refer_CE , '' as Doc_Refer_BL");

            $this->db->from('STK_T_Relocate relocate');
            $this->db->where("relocate.Flow_Id", $data_flow);
            $query_reject = $this->db->get();

//            p($query_reject);

            return $query_reject;
        }
    }

    // Create Joke 22/10/57 
    public function get_data_OT($data) {
        //p($data['doc_type']);
        $data_input = $data['doc_value'] . "%";

        $this->db->select("1 as type,order1.Order_Id, order1.Flow_Id, order1.Document_No, order1.Doc_Refer_Int, order1.Doc_Refer_Ext, order1.Doc_Refer_Inv, order1.Doc_Refer_CE, order1.Doc_Refer_BL, 
                                    workflow.Flow_Id, workflow.Process_Id, workflow.Present_State, state1.Process_Id, state1.State_No, state1.State_NameEn, process.Process_Id, process.Process_NameEn");

        $this->db->from('STK_T_Order order1');
        $this->db->join('STK_T_Workflow workflow', 'order1.Flow_Id = workflow.Flow_Id', 'left');
        $this->db->join('SYS_M_State state1', 'workflow.Process_Id = state1.Process_Id AND workflow.Present_State = state1.State_No', 'INNER');
        $this->db->join('SYS_M_Process process', 'workflow.Process_Id = process.Process_Id', 'left');

        if ($data['doc_type'] == 'Document_No') {
            $this->db->where("order1.Document_No LIKE ", $data_input);
        } else if ($data['doc_type'] == 'Doc_Refer_Ext') {
            $this->db->where("order1.Doc_Refer_Ext LIKE ", $data_input);
        } else if ($data['doc_type'] == 'Doc_Refer_Int') {
            $this->db->where("order1.Doc_Refer_Int LIKE ", $data_input);
        } else if ($data['doc_type'] == 'Doc_Refer_Inv') {
            $this->db->where("order1.Doc_Refer_Inv LIKE ", $data_input);
        } else if ($data['doc_type'] == 'Doc_Refer_CE') {
            $this->db->where("order1.Doc_Refer_CE LIKE ", $data_input);
        } else if ($data['doc_type'] == 'Doc_Refer_BL') {
            $this->db->where("order1.Doc_Refer_BL LIKE ", $data_input);
        }
        $query = $this->db->get();
        $result = $query->result();
//         p($this->db->last_query()); 
        $result_reject = array();
        if ($data['doc_type'] == 'Document_No') {
            $this->db->select("2 as type,relocate.Order_Id,relocate.Flow_Id, relocate.Doc_Relocate as Document_No , '' as Doc_Refer_Int , '' as Doc_Refer_Ext ,
                                    '' as Doc_Refer_Inv , '' as Doc_Refer_CE , '' as Doc_Refer_BL ,
                                    workflow.Flow_Id,workflow.Process_Id, workflow.Present_State,
                                    state1.Process_Id, state1.State_No, state1.State_NameEn,
                                    process.Process_Id, process.Process_NameEn");

            $this->db->from('STK_T_Relocate relocate');
            $this->db->join('STK_T_Workflow workflow', 'relocate.Flow_Id = workflow.Flow_Id', 'left');
            $this->db->join('SYS_M_State state1', 'workflow.Process_Id = state1.Process_Id AND workflow.Present_State = state1.State_No', 'INNER');
            $this->db->join('SYS_M_Process process', 'workflow.Process_Id = process.Process_Id', 'left');
            $this->db->where("relocate.Doc_Relocate LIKE ", $data_input);
            $query_reject = $this->db->get();
//              p($this->db->last_query()); exit;
            $result_reject = $query_reject->result();
        }

//        $query_reject = $this->db->get();
//        $result_reject = $query_reject->result();
        $result_tmp = array_merge($result, $result_reject);
        return $result_tmp;
    }

}

?>
