<?php

class migrate_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function insert_inbound($params) {
        $this->db->insert('STK_T_Inbound', $params);
        $aff = $this->db->affected_rows();
        if ($aff == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function getProductIdByCode($product_code) {
        $rs = $this->db->get_where('STK_M_Product', array('Product_Code' => $product_code))->row();
        if ($rs) {
            return $rs->Product_Id;
        } else {
            return FALSE;
        }
    }

    public function getLocationIdByCode($location_code) {
        $rs = $this->db->get_where('STK_M_Location', array('Location_Code' => $location_code, 'Active' => 'Y'))->row();
        if ($rs) {
            return $rs->Location_Id;
        } else {
            return FALSE;
        }
    }

    public function getUnitIdByUnit($unit) {

        $this->db->select('CTL_M_UOM_Select_id As Id');
        $this->db->join('CTL_M_UOM_Template', 'CTL_M_UOM_Template.id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id');
        $this->db->where('CTL_M_UOM_Template_Language.name', $unit);
        $query = $this->db->get('CTL_M_UOM_Template_Language');
        $rs = $query->row();

        if ($rs) {
            return $rs->Id;
        } else {
            return FALSE;
        }
    }

    public function getNormalReceiveType() {
        $rs = $this->db->get_where('SYS_M_Domain', array('Dom_Host_Code' => 'RCV_TYPE', 'Dom_Code' => 'RCV001'))->row();
        if ($rs) {
            return $rs->Dom_Code;
        } else {
            return FALSE;
        }
    }

    public function getNotSpecifiedStatus() {
        $rs = $this->db->get_where('SYS_M_Domain', array('Dom_Host_Code' => 'SUB_STATUS', 'Dom_Code' => 'SS000'))->row();
        if ($rs) {
            return $rs->Dom_Code;
        } else {
            return FALSE;
        }
    }



    public function genPalletIdFromPalletCode($pallet_code) {
        $rs = $this->db->get_where('STK_T_Pallet', array('Pallet_Code' => $pallet_code))->row();
        if ($rs) {
            $pallet = $this->db->get_where('STK_T_Inbound', array('Pallet_Id' => $rs->Pallet_Id))->row();
            if ($pallet) {
                return FALSE;
            } else {
                return $rs->Pallet_Id;
            }
        } else {
            $params['build_type'] = "INBOUND";
            $params['pallet_code'] = $pallet_code;
            return $this->getPalletCode($params);
        }
    }

    private function getPalletCode($params) {
        $data['Pallet_Code'] = $params['pallet_code'];
        $data['Min_Load'] = '';
        $data['Create_Date'] = date("Y-m-d H:i:s");
        $this->db->insert('STK_T_Pallet', $data);
        return $this->db->insert_id();
    }

}
