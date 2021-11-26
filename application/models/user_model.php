<?php

class user_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getUserAll() {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin");
        $query = $this->db->get();
        return $query;
    }

    public function getDetailByUserId($userId) {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin UserLogin ");
        $this->db->join("CTL_M_Contact Contact ", "Contact.Contact_Id = UserLogin.Contact_Id");
        $this->db->where("UserLogin_Id", $userId);
        $query = $this->db->get();
        return $query->row_array();
    }

    function get_user_permission_list($UserLogin_Id = NULL) {// Add by Ton! 20140113
        $this->db->select("ADM_M_UserLogin.UserLogin_Id AS Id
            , CTL_M_TitleName.TitleName_EN + ' ' + CTL_M_Contact.First_NameEN + ' ' + CTL_M_Contact.Last_NameEN AS Contact_Name
            , ADM_M_UserLogin.UserAccount
            , CTL_M_Department.Department_NameEN
            , CTL_M_Position.Position_NameEN
            , CTL_M_Company.Company_NameEN");
        $this->db->from("CTL_M_Contact");
        $this->db->join("ADM_M_UserLogin", "CTL_M_Contact.Contact_Id = ADM_M_UserLogin.Contact_Id", "LEFT");
        $this->db->join("CTL_M_TitleName", "CTL_M_Contact.TitleName_Id = CTL_M_TitleName.TitleName_Id", "LEFT");
        $this->db->join("CTL_M_Department", "CTL_M_Contact.Deparment_Id = CTL_M_Department.Department_Id", "LEFT");
        $this->db->join("CTL_M_Position", "CTL_M_Contact.Position_Id = CTL_M_Position.Position_Id", "LEFT");
        $this->db->join("CTL_M_Company", "CTL_M_Contact.Company_Id = CTL_M_Company.Company_Id", "LEFT");
        if ($UserLogin_Id != NULL):
            $this->db->where("ADM_M_UserLogin.UserLogin_Id", $UserLogin_Id);
        endif;
        $this->db->where("ADM_M_UserLogin.Active", TRUE);
        $this->db->order_by("ADM_M_UserLogin.UserLogin_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();
        return $query;
    }
}

?>
