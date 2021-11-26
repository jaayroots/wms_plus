<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Workflow {

    function __construct() {

    }

    function openWorkflowForm($process_id, $present_state, $UserLogin_Id = NULL, $mnu_NavigationUri = NULL) {
        $CI = & get_instance();
        $CI->load->model("workflow_model", "flow");

        $from_state_name = "";
        $form_name = "";
        $str_button = "";
        $process_name = "";
        $process_type = "";
        $from_State = "";

        $state_detail = $CI->flow->getStateedgeDetail($process_id, $present_state);
        if (!empty($state_detail)) :
            foreach ($state_detail as $rows) :
                $form_name = $rows->Form;
                $from_state_name = $rows->From_State_Name;
                $process_name = $rows->Process_Name;
                $process_type = $rows->Process_Type;
                $next_state_detail = $CI->flow->getNextState($process_id, $rows->To_State);
                $rows->Next_State[] = $next_state_detail;
                $from_State = $present_state;
            endforeach;
            $str_button = $this->genButton($state_detail, $UserLogin_Id, $mnu_NavigationUri);
        endif;

        $data['process_id'] = $process_id;
        $data['present_state'] = $present_state;
        $data['process_type'] = $process_type;
        $data['process_name'] = $process_name;
        $data['form_name'] = $form_name;
        $data['from_state_name'] = $from_state_name;
        $data['state_detail'] = $state_detail;
        $data['str_buttun'] = $str_button;
        $data['from_State'] = $from_State; //BY POR 2013-10-03 เพิ่มให้ส่งค่ากลับว่าถึง step ไหนแล้ว
        return (object) $data;
    }

    function genButton($state_detail, $UserLogin_Id = NULL, $mnu_NavigationUri = NULL) {
        $CI = & get_instance();
        $CI->load->library('menu_auth');
        $strButton = "";

        $configs = $CI->config->item("_xml");
        $quick_picking_approve = @$configs['quick_picking_approve'];
        $action_parmission = NULL;
        if (!empty($UserLogin_Id) && !empty($mnu_NavigationUri)):
            $action_parmission = $CI->menu_auth->get_action_parmission($UserLogin_Id, $mnu_NavigationUri);
        endif;

        foreach ($state_detail as $data) {
            if ($data->Process_Id == 1 and ( $data->From_State == 3 or $data->From_State == 5)):
                if ($data->From_State == 3 && $data->To_State == 2 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                if ($data->From_State == 3 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                if ($data->From_State == 5 && $data->To_State == 7 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                // Reject in Process Confirm Put Away
                if ($data->From_State == 5 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;


            elseif ((($data->Process_Id == 2 or $data->Process_Id == 9) and ( $data->From_State == 3 or $data->From_State == 5))):

                //start add code for show reject button : by kik : 2013-12-13
                if ($data->From_State == 3 && $data->To_State == 2 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                // Add for Quick Approve Picking
                if ($data->From_State == 3 && $data->To_State == 5 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE) && $quick_picking_approve == TRUE):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;
                // End for Quick Approve Picking

                if ($data->From_State == 3 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

            //    if ($data->From_State == 3 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
            //        $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
            //        $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
            //    endif;

                // Reject and Return from wait for approve dispatch to wait for confirm dispatch
                if ($data->From_State == 5 && $data->To_State == 4 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                if ($data->From_State == 5 && $data->To_State == 2 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                // Reject from step
                if ($data->From_State == 5 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

                if ($data->From_State == 5 && $data->To_State == -2 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;

            elseif (($data->Process_Id == 3 and $data->From_State == 1) or ( $data->Process_Id == 4 and $data->From_State == 1)):
                //start add code for show reject button : by kik : 2013-11-26
                if ($data->From_State == 1 && $data->To_State == -1 && ($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT title ="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;
            else:

                $set_btn_id = strtolower("btn_action_{$data->Action_Type}");
                if (($action_parmission != NULL ? in_array($data->Edge_Id, $action_parmission) : TRUE)):
                    $onclick = "onClick=\"postRequestAction('" . $data->Module . "','" . $data->Sub_Module . "',this.value,'" . $data->To_State . "',this)\"";
                    $strButton .= '<INPUT id="' . $set_btn_id . '" title="' . $data->To_State_Name . '" data-dialog="' . $data->Description . '" TYPE="button" class="' . $CI->config->item("css_button") . '"VALUE="' . $data->Action_Type . '" ' . $onclick . ' name="action_type">&emsp;&emsp;';
                endif;
            endif;
            // END Add By Akkarapol, 25/09/2013, เพิ่มการตรวจสอบว่า ถ้า เป็น step 'wait for Confirm Receive' แล้ว ไม่ต้องให้มีปุ่ม Next step
        }
        // p($strButton); exit;
        return $strButton;
    }

    function addNewWorkflow($process_id, $present_state, $action_type, $next_state, $data, $parent_id = NULL) {
        $CI = & get_instance();
        $CI->load->model("workflow_model", "flow");
        $flow_id = $CI->flow->addWorkFlowTrax($process_id, $next_state, $data, $parent_id);
        $action_id = $CI->flow->addWorkFlowAction($process_id, $flow_id, $action_type, $present_state);
        return array($flow_id, $action_id);
    }

    function updateWorkflow($process_id, $flow_id, $present_state, $action_type, $next_state, $data) {
        $CI = & get_instance();
        $CI->load->model("workflow_model", "flow");
        $action_id = $CI->flow->updateWorkFlowTrax($process_id, $flow_id, $action_type, $present_state, $next_state, $data);

        // Add Function When Reject And Return must clear previous logs_action
        if ( ($present_state > $next_state) && ($next_state > 0) ) {
            $CI->flow->clearActionLogs( $flow_id, $process_id, $present_state, $next_state );
        }

        return $action_id;
    }

    public function update_workflow_counting($flow_id, $data) {
        $CI = & get_instance();
        $CI->load->model("workflow_model", "flow");
        $action_id = $CI->flow->update_workflow_counting($flow_id, $data);
        return $action_id;
    }

}
