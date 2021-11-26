<?php

class system_management_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    # select distinct main Dom code

    function getSystemAll() {
        $this->db->select("Dom_ID AS Id,Dom_Code AS Name,Dom_EN_Desc AS Description,Dom_TH_Desc AS Description2");
        $this->db->where("Dom_Host_Code", "*");
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    function getDomCode($id) {
        $this->db->select("Dom_Code");
        $this->db->where("Dom_ID", $id);
        $this->db->from("SYS_M_Domain");
        $get_dom = $this->db->get();
        $data = $get_dom->result();
        if (count($data) > 0):
            $dom_id = $data[0]->Dom_Code;
        else:
            $dom_id = '';
        endif;
        return $dom_id;
    }

    function getDomName($id) {
        $this->db->select("Dom_EN_Desc,Dom_TH_Desc");
        $this->db->where("Dom_ID", $id);
        $this->db->from("SYS_M_Domain");
        $get_dom = $this->db->get();
        $data = $get_dom->result();
        $name = $data[0]->Dom_EN_Desc . ' ' . $data[0]->Dom_TH_Desc;
        return $name;
    }

    function getDomHostCode($id) {
        $this->db->select("Dom_Host_Code");
        $this->db->where("Dom_ID", $id);
        $this->db->from("SYS_M_Domain");
        $get_dom = $this->db->get();
        $data = $get_dom->result();
        $dom_id = $data[0]->Dom_Host_Code;
        return $dom_id;
    }

    function getMainId($code) {
        $this->db->select("Dom_ID");
        $this->db->where("Dom_Code", $code);
        $this->db->from("SYS_M_Domain");
        $get_dom = $this->db->get();
        $data = $get_dom->result();
        $dom_id = $data[0]->Dom_ID;
        return $dom_id;
    }

    function getSystemDetail($code) {
        $this->db->select("Dom_ID AS Id,Dom_Code,Dom_TH_Desc,Dom_EN_Desc");
        //$this->db->select("*");
        $this->db->where("Dom_Host_Code", $code);
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        return $query;
    }

    # select option for System Type

    function selectSystem() {
        $this->db->select("Dom_Code AS Name");
        $this->db->where("Dom_Host_Code", "*");
        $this->db->from("SYS_M_Domain");
        $this->db->order_by("Dom_Code", "ASC");
        $query = $this->db->get();
        $rows = array();
        $i = 0;
        foreach ($query->result() as $row) {
            $rows[$i] = $row->Name;
            $i++;
        }
        return $rows;
    }

    function getSystemValue($id) {
        $this->db->select("Dom_ID,Dom_Host_Code,Dom_Code,Dom_TH_Desc,Dom_EN_Desc,Dom_Active");
        $this->db->where("Dom_ID", $id);
        $this->db->from("SYS_M_Domain");
        $query = $this->db->get();
        $rows = array();
        $row = $query->result();
        if (count($row) != 0) {
            $rows['Dom_ID'] = $row[0]->Dom_ID;
            $rows['Dom_Host_Code'] = $row[0]->Dom_Host_Code;
            $rows['Dom_Code'] = $row[0]->Dom_Code;
            $rows['Dom_TH_Desc'] = $row[0]->Dom_TH_Desc;
            $rows['Dom_EN_Desc'] = $row[0]->Dom_EN_Desc;
            $rows['Dom_Active'] = $row[0]->Dom_Active;
        }

        return $rows;
    }

    function save_system_code($value) {
        $data['Dom_Host_Code'] = $value['system_type'];
        $data['Dom_Code'] = $value['code'];
        $data['Dom_TH_Desc'] = $value['description_thai'];
        $data['Dom_EN_Desc'] = $value['description_eng'];
        $data['Dom_Active'] = ACTIVE;
        $query = $this->db->insert("SYS_M_Domain", $data);
        if ($query === true) :
            return TRUE;
        else :
            return FALSE;
        endif;
    }

    function edit_system_code($value) {
        $data['Dom_Host_Code'] = $value['system_type'];
        $data['Dom_Code'] = $value['code'];
        $data['Dom_TH_Desc'] = $value['description_thai'];
        $data['Dom_EN_Desc'] = $value['description_eng'];
        $data['Dom_Active'] = ACTIVE;
        //print_r($data);
        $this->db->where("Dom_ID", $value['edit_id']);
        $this->db->update('SYS_M_Domain', $data);
        $afftectedRows = $this->db->affected_rows();
        if ($afftectedRows > 0) {
            return TRUE; //Save Success.
        } else {
            return FALSE; //Save UnSuccess.
        }
    }

    function delete_system_code($id = NULL) {
        if (!empty($id)) :
            $code = $this->getDomHostCode($id);
            $main_id = $this->getMainId($code);
            $data['Dom_Active'] = INACTIVE;
            $this->db->where("Dom_ID", $id);
            $this->db->update('SYS_M_Domain', $data);
            return $main_id;
        else :
            return FALSE;
        endif;
    }

    //Get Code Lookup 
    function getCodeLookup($DOM_HOST_Code) {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", $DOM_HOST_Code);
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getReceiveType() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "RCV_TYPE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getReceiveStyle() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "RCV_STYLE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getDispatchType() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "DP_TYPE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    #add function getTransferType by kik : 14-10-2013

    function getTransferType() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "TF_TYPE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    #add function getAdjustType by kik : 14-10-2013

    function getAdjustType() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "ADJ_TYPE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getDispatchTypeByCondition() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "DP_TYPE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->where("Dom_Code", "DP1");
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getDispatchStyle() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "DP_STYLE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getConfirmList() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "Confirm");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    function getPutAwayStyle() {
        $this->db->select("Dom_Code,Dom_TH_Desc,Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PA_STYLE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    // Add By Akkarapol, 17/09/2013, DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg  
    function getNoChkSup() {
        $this->db->select("Dom_Code");
        $this->db->where("Dom_Host_Code", "NO_CHKSUP");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

    // END Add By Akkarapol, 17/09/2013, DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg  

    function getFindLocationPA() {
        $sql = "Select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='FindLocPA'";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function getReasonPA() {
        $sql = "Select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='ReasonPA'";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function getPickingStyle() {
        $this->db->select("Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc");
        $this->db->where("Dom_Host_Code", "PK_STYLE");
        $this->db->where("Dom_Active", ACTIVE);
        $this->db->order_by("Sequence");
        $query = $this->db->get("SYS_M_Domain");
        return $query->result();
    }

//    Comment Out by Ton! 20131031
//    function getPickingZone() {
//        $this->db->select("l.Location_Id as Dom_Code , l.Location_Code ,l.Location_NameTH as Dom_TH_Desc ,l.Location_NameEN as Dom_EN_Desc");
//        $this->db->from("STK_M_Location l ");
//        $this->db->join("STK_M_LocationType lt ", "lt.LocationType_Id = l.LocationType_Id");
//        $this->db->where("lt.LocationType_Code ", "Zone");
//        $this->db->where("l.Active ", "1");
//        $query = $this->db->get();
//        return $query->result();
//    }

    function getFindLocationPK() {
        $sql = "Select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='PICKING'";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function getReasonPK() {
        $sql = "Select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='PK_REASON'";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function getSearchBy() {
        $sql = "select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='Search'";
        $query = $this->db->query($sql);
        return $query->result();
    }

    #Product Status

    function getPendingStatus() {
        $sql = "select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='PROD_STATUS' AND Dom_Code = 'PENDING' ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    function getNormalStatus() {
        $sql = "select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='PROD_STATUS' AND Dom_Code = 'NORMAL' ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    #Sub Status

    function getNotSpecifiedStatus() {
        $sql = "select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='SUB_STATUS' AND Dom_Code = 'SS000' ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    #Receive Type

    function getNormalReceiveType() {
        $sql = "select Dom_ID, Dom_Code, Dom_TH_Desc, Dom_EN_Desc from SYS_M_Domain where Dom_Host_Code='RCV_TYPE' AND Dom_Code = 'RCV001' ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    // Add By Akkarapol, เพิ่มเพื่อหา Dom Code ด้วย Dom_EN_Desc
    function getDomCodeByDomENDesc($find, $where,$select="",$order_by="") {
        if(!empty($select)):
            $this->db->select($select);
        endif;
        
        $this->db->select("Dom_Code"); //อันนี้คือ select ตายตัวถ้าต้องการเพิ่มเติมให้เพิ่มมากับตัวแปร $select
        
        if(!empty($find)):
             $this->db->where("Dom_EN_Desc", $find);
        endif;
       
        if(!empty($where)):
            $this->db->where($where);
        endif;
        
        if(!empty($order_by)):
            $this->db->order_by($order_by);
        endif;
        return $this->db->get("SYS_M_Domain"); // Add By Akkarapol, 06/11/2013, เปลี่ยนการ return จากที่ส่งกลับไปเป็นพวก ->result, ->array, ->row อะไรต่างๆ กลับไปเป็น get เลย จะได้นำไปเรียกใช้กันต่อได้อยากที่ต้องการ เพื่อลดปัญหาการ duplicate function ด้วย
    }

    // END Add By Akkarapol, เพิ่มเพื่อหา Dom Code ด้วย Dom_EN_Desc
    // Add By Akkarapol, 21/10/2013, เพิ่มเพื่อหา Detail ด้วย Dom_Code
    function getDomDetailByDomCode($find) {
        $this->db->select("*");
        $this->db->where("Dom_Code", $find);
        $query = $this->db->get("SYS_M_Domain");
        return $query;
    }

    // END Add By Akkarapol, 21/10/2013, เพิ่มเพื่อหา Detail ด้วย Dom_Code

    /**
     * Manual Transaction Start
     */
    public function transaction_start() {
        $this->db->trans_begin();
    }

    /**
     * Manual Transaction Commit
     */
    public function transaction_commit() {
        $this->db->trans_commit();
    }

    /**
     * Manual Transaction Roll Back
     */
    public function transaction_rollback() {
        $this->db->trans_rollback();
    }

    public function transaction_status() {
        $this->db->trans_status();
    }

    /**
     * Manual Transaction End
     */
    public function transaction_end() {
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }

}

?>