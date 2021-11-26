<?php

class searchmodel_reprint extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function getLocationBySKU($sku) {// Add by Ton! 20131014
        $this->db->select("STK_M_Location.Location_Id"
                . ", STK_M_Location.Location_Code"
                . ", STK_M_Location.Warehouse_Id"
                . ", STK_M_Warehouse.Warehouse_Code"
                . ", STK_M_Warehouse.Warehouse_NameEN"
                . ", STK_M_Warehouse.Warehouse_Desc"
                . ", vw_inbound.Est_Balance_Qty as Qty");
        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_M_Location", "STK_M_Location.Location_Id = STK_T_Inbound.Actual_Location_Id", "LEFT");
        $this->db->join("STK_M_Warehouse", "STK_M_Warehouse.Warehouse_Id = STK_M_Location.Warehouse_Id", "LEFT");
        $this->db->join("vw_inbound", "vw_inbound.Inbound_Id = STK_T_Inbound.Inbound_Id");
//            $this->db->where_in("STK_T_Inbound.Product_Code", $sku);
        $this->db->like("STK_T_Inbound.Product_Code", $sku); // Edit Code by Ton! 20131105
        $this->db->where("STK_T_Inbound.Active", 'Y');
        $this->db->where("STK_M_Location.Active", 'Y');
        $this->db->where("STK_M_Warehouse.Active", 'Y');
        $this->db->order_by("STK_M_Location.Warehouse_Id, STK_M_Location.Location_Id");
        $query = $this->db->get();
//            echo $this->db->last_query();exit();
        return $query->result();
    }

    function getOrderDetail($flowId, $SKU) {// Add by Ton! 20130701
        $this->db->select("STK_T_Order_Detail.*"
                . ", STK_T_Order.Document_No"
                . ", STK_T_Order.Doc_Refer_Ext");
        $this->db->from("STK_T_Order_Detail");
        $this->db->join("STK_T_Order", "STK_T_Order.Order_Id = STK_T_Order_Detail.Order_Id ");
        if (!empty($flowId)) :
            $this->db->where_in("STK_T_Order.Flow_Id", $flowId);
        endif;
        $this->db->where("STK_T_Order_Detail.Product_Code", $SKU);
        $this->db->where("STK_T_Order_Detail.Active", "Y");
        $this->db->order_by("STK_T_Order.Flow_Id, STK_T_Order_Detail.Order_Id, STK_T_Order_Detail.Item_Id");
        $query = $this->db->get();
//        echo $this->db->last_query();exit();
        return $query->result();
    }

    function getSearchByLocate($locateId) {
        $this->db->select("STK_T_Inbound.Inbound_Id"
                . ", STK_T_Inbound.Product_Code");
        $this->db->select("(STK_T_Inbound.Receive_Qty - STK_T_Inbound.PD_Reserv_Qty - STK_T_Inbound.Dispatch_Qty - STK_T_Inbound.Adjust_Qty) AS Balance,STK_T_Pallet.Pallet_Code"); //ADD BY POR 2014-03-13 ดึง estmate จากตาราง inbound โดยตรง ไม่ต้องเรียกใน view เนื่องจากใน view จะไม่แสดงสถานะ pending
        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id"); //Add by joke show Pallet Code
        $this->db->where("STK_T_Inbound.Actual_Location_Id", $locateId);
        $this->db->where("STK_T_Inbound.Active", "Y");
        $this->db->order_by("STK_T_Inbound.Product_Code");
        $query = $this->db->get()->result();
        
        $set_return = array();
        foreach($query as $key_qr => $qr):
            if($qr->Balance != 0):
                $set_return[] = $qr;
            endif;
        endforeach;
        
        return $set_return;
    }

    function getSearchByPallet($palletId) {
        $this->db->select("STK_T_Inbound.Inbound_Id"
                . ", STK_T_Inbound.Product_Code"
                . ", STK_T_Pallet.Pallet_Code"
                . ", vw_inbound.Est_Balance_Qty AS Balance");
        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_T_Pallet", "STK_T_Pallet.Pallet_Id = STK_T_Inbound.Pallet_Id");
        $this->db->join("vw_inbound", "vw_inbound.Inbound_Id = STK_T_Inbound.Inbound_Id");
        $this->db->where("STK_T_Inbound.Pallet_Id", $palletId);
        $this->db->where("STK_T_Inbound.Active", "Y");
        $this->db->order_by("STK_T_Inbound.Product_Code");
        $query = $this->db->get();
        return $query->result();
    }

    function getSearchByPack($packId) {
        $this->db->select("STK_T_Inbound.Inbound_Id"
                . ", STK_T_Inbound.Product_Code"
                . ", STK_T_Pack.Pack_Code"
                . ", vw_inbound.Est_Balance_Qty AS Balance");
        $this->db->from("STK_T_Inbound");
        $this->db->join("STK_T_Pack", "STK_T_Pack.Pack_Id = STK_T_Inbound.Pack_Id");
        $this->db->join("vw_inbound", "vw_inbound.Inbound_Id = STK_T_Inbound.Inbound_Id");
        $this->db->where("STK_T_Inbound.Pack_Id", $packId);
        $this->db->where("STK_T_Inbound.Active", "Y");
        $this->db->order_by("STK_T_Inbound.Product_Code");
        $query = $this->db->get();
        return $query->result();
    }
    
    
    function get_search_pallet($pallet_code = NULL){

        $pallet_data = array();
                
        if(!empty($pallet_code)):
            
            if($pallet_code[0] == 'R'):
                $this->db->join("STK_T_Relocate", "STK_T_Pallet.Order_Id = STK_T_Relocate.Order_Id", "LEFT");
                $this->db->join("CTL_M_Company Owner", "STK_T_Relocate.Owner_Id = Owner.Company_Id", "LEFT");
                $this->db->join("CTL_M_Company Renter", "STK_T_Relocate.Renter_Id = Renter.Company_Id", "LEFT");
                $this->db->select("STK_T_Relocate.Doc_Relocate AS Document_No, 'This pallet build from RE-PALLET.' AS Doc_Refer_Ext");
            else:
                $this->db->join("STK_T_Order", "STK_T_Pallet.Order_Id = STK_T_Order.Order_Id", "LEFT");
                $this->db->join("CTL_M_Company Owner", "STK_T_Order.Owner_Id = Owner.Company_Id", "LEFT");
                $this->db->join("CTL_M_Company Renter", "STK_T_Order.Renter_Id = Renter.Company_Id", "LEFT");
                $this->db->select("STK_T_Order.Document_No, STK_T_Order.Doc_Refer_Ext");
            endif;
            
            $this->db->select("
                STK_T_Pallet.Pallet_Id
                , STK_T_Pallet.Pallet_Code
                , SYS_M_Domain.Dom_EN_SDesc AS Pallet_Type
                , CTL_M_Pallet_Master.Pallet_Master_Name
                , CTL_M_Pallet_Master.Pallet_Width
                , CTL_M_Pallet_Master.Pallet_Lenght
                , CTL_M_Pallet_Master.Pallet_Height
                , STK_M_Location.Location_Code
                , CTL_M_Container.Cont_No
                , CTL_M_Container_Size.Cont_Size_No
                , CTL_M_Container_Size.Cont_Size_Unit_Code
                , STK_T_Invoice.Invoice_No
                , Owner.Company_NameEN AS Owner_Name
                , Renter.Company_NameEN AS Renter_Name
            ");

            $this->db->from("STK_T_Pallet");
            $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code", "LEFT");
            $this->db->join("CTL_M_Pallet_Master", "STK_T_Pallet.Pallet_Master_Id = CTL_M_Pallet_Master.Pallet_Master_Id", "LEFT");
            $this->db->join("CTL_M_Container", "STK_T_Pallet.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
            $this->db->join("STK_M_Location", "STK_T_Pallet.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
//            $this->db->join("STK_T_Invoice", "STK_T_Pallet.Order_Id = STK_T_Order.Order_Id", "LEFT"); // Comment by Akkarapol, 20150618, มันต้อง JOIN ไปที่ STK_T_Invoice ไม่ใช่ STK_T_Order
            $this->db->join("STK_T_Invoice", "STK_T_Pallet.Order_Id = STK_T_Invoice.Order_Id", "LEFT");
            $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);

            $pallet_data = $this->db->get()->row_array();
             $sql = $this->db->last_query();
//            p($pallet_data);
            
            if(!empty($pallet_data)):
                $this->db->select("
                    STK_T_Inbound.Item_Id
                    , STK_T_Inbound.Product_Code
                    , STK_T_Inbound.Product_Lot
                    , STK_T_Inbound.Product_Serial
                    , STK_T_Inbound.Product_Mfd
                    , STK_T_Inbound.Product_Exp
                    , STK_T_Inbound.Balance_Qty
                    , STK_M_Product.Product_NameEN
                    , CTL_M_UOM_Template_Language.public_name AS Unit_Name
		    , CTL_M_UOM_Template_Language.public_name AS Unit_Value
                    , STK_T_Invoice.Invoice_No
		    , g.ProductGroup_NameEN As product_class
            	    , STK_M_Product.User_Defined_4 As product_cate
                ");
            
                if($pallet_code[0] == 'R'):
//                    $this->db->select("(
//                        SELECT Old_Pallet.Pallet_Code
//                        FROM STK_T_Pallet
//                        JOIN STK_T_Relocate ON STK_T_Pallet.Order_Id = STK_T_Relocate.Order_Id
//                        JOIN STK_T_Relocate_Detail ON STK_T_Relocate.Order_Id = STK_T_Relocate_Detail.Order_Id
//                        JOIN STK_T_Inbound AS Old_Inbound ON STK_T_Relocate_Detail.Inbound_Item_Id = Old_Inbound.Inbound_Id
//                        JOIN STK_T_Pallet AS Old_Pallet ON Old_Inbound.Pallet_Id = Old_Pallet.Pallet_Id
//                        WHERE STK_T_Pallet.Pallet_Id = STK_T_Inbound.Pallet_Id
//                        ) AS Old_Pallet_Code
//                    ");
                    
                    $this->db->select("Old_Pallet.Pallet_Code as Old_Pallet_Code");
                    $this->db->join("STK_T_Relocate_Detail","STK_T_Inbound.Item_Id = STK_T_Relocate_Detail.Item_Id","LEFT");
                    $this->db->join("STK_T_Inbound AS Old_Inbound","STK_T_Relocate_Detail.Inbound_Item_Id = Old_Inbound.Inbound_Id","LEFT");
                    $this->db->join("STK_T_Pallet AS Old_Pallet","Old_Inbound.Pallet_Id = Old_Pallet.Pallet_Id","LEFT");
                    
                endif;
            
                $this->db->from("STK_T_Inbound");
                $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Code = STK_M_Product.Product_Code", "INNER");
                $this->db->join("CTL_M_UOM_Template_Language", "STK_T_Inbound.Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
                $this->db->join("STK_T_Invoice","STK_T_Inbound.Invoice_Id = STK_T_Invoice.Invoice_Id","LEFT");
	        $this->db->join('STK_M_Product product', 'STK_T_Inbound.Product_Code = product.Product_Code', 'LEFT');
	        $this->db->join('CTL_M_ProductGroup g', 'g.ProductGroup_Id = product.ProductGroup_Id', 'LEFT');

                $this->db->where("STK_T_Inbound.Pallet_Id", $pallet_data['Pallet_Id']);
                $this->db->where("STK_T_Inbound.Active", ACTIVE);
                $pallet_data['item_lists'] = $this->db->get()->result_array();

            //    p($pallet_data);
            //    exit;
            //    echo $this->db->last_query(); exit;
                
            endif;
        endif;
        
        return $pallet_data;
    }

    function get_search_pallet_loading_area($pallet_code = NULL){

        $pallet_data = array();
                
        if(!empty($pallet_code)):
            
            if($pallet_code[0] == 'R'):
                $this->db->join("STK_T_Relocate", "STK_T_Pallet.Order_Id = STK_T_Relocate.Order_Id", "LEFT");
                $this->db->join("CTL_M_Company Owner", "STK_T_Relocate.Owner_Id = Owner.Company_Id", "LEFT");
                $this->db->join("CTL_M_Company Renter", "STK_T_Relocate.Renter_Id = Renter.Company_Id", "LEFT");
                $this->db->select("STK_T_Relocate.Doc_Relocate AS Document_No, 'This pallet build from RE-PALLET.' AS Doc_Refer_Ext");

                            $this->db->select("
                STK_T_Pallet.Pallet_Id
                , STK_T_Pallet.Pallet_Code
                , SYS_M_Domain.Dom_EN_SDesc AS Pallet_Type
                , CTL_M_Pallet_Master.Pallet_Master_Name
                , CTL_M_Pallet_Master.Pallet_Width
                , CTL_M_Pallet_Master.Pallet_Lenght
                , CTL_M_Pallet_Master.Pallet_Height
                , STK_M_Location.Location_Code
                , CTL_M_Container.Cont_No
                , CTL_M_Container_Size.Cont_Size_No
                , CTL_M_Container_Size.Cont_Size_Unit_Code
                , STK_T_Invoice.Invoice_No
                , Owner.Company_NameEN AS Owner_Name
                , Renter.Company_NameEN AS Renter_Name
            ");

            $this->db->from("STK_T_Pallet");
            $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code", "LEFT");
            $this->db->join("CTL_M_Pallet_Master", "STK_T_Pallet.Pallet_Master_Id = CTL_M_Pallet_Master.Pallet_Master_Id", "LEFT");
            $this->db->join("CTL_M_Container", "STK_T_Pallet.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
            $this->db->join("STK_M_Location", "STK_T_Pallet.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
            $this->db->join("STK_T_Invoice", "STK_T_Pallet.Order_Id = STK_T_Invoice.Order_Id", "LEFT");
            $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
            $pallet_data = $this->db->get()->row_array();
            //
            //
            else:
                


                $this->db->select("TOP 1
                od.Document_No
                ,od.Doc_Refer_Ext
                ,odd.Pallet_Id
                ,pal.Pallet_Code
                ,pal.Pallet_Id
                ,sysdom.Dom_EN_SDesc as Pallet_Type
                ,palm.Pallet_Master_Name
                ,palm.Pallet_Width
                ,palm.Pallet_Lenght
                ,palm.Pallet_Height
                ,locat.Location_Code
                ,CASEWHEN wf.Process_Id = 2 AND wf.Present_State = 4 THEN 'Loading Area'
                WHEN wf.Process_Id = 2 AND wf.Present_State = 5 THEN 'Loading Area'
                WHEN wf.Process_Id = 2 AND wf.Present_State = 6 THEN 'Loading Area'
                ELSE locat.Location_Code
                END AS Location_Code
                ,cont.Cont_No
                ,conts.Cont_Size_No
                ,conts.Cont_Size_Unit_Code
                ,inv.Invoice_No
                ,Owner.Company_Id
                , Owner.Company_NameEN AS Owner_Name
                , Renter.Company_NameEN AS Renter_Name
            ");

            $this->db->from("STK_T_Pallet pal");
            $this->db->join("CTL_M_Pallet_Master palm", "pal.Pallet_Master_Id = palm.Pallet_Master_Id", "LEFT");
            $this->db->join("SYS_M_Domain sysdom", "pal.Pallet_Type = sysdom.Dom_Code", "LEFT");
            $this->db->join("STK_M_Location locat", "pal.Actual_Location_Id = locat.Location_Id", "LEFT");
            $this->db->join("CTL_M_Container cont", "pal.Cont_Id = cont.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size conts", "cont.Cont_Size_Id = conts.Cont_Size_Id", "LEFT");
            $this->db->join("STK_T_Invoice inv", "pal.Order_Id = inv.Order_Id", "LEFT");

            $this->db->join("STK_T_Inbound inb", "pal.Pallet_Id = inb.Pallet_Id");
            $this->db->join("STK_T_Order_Detail odd", "inb.Inbound_Id = odd.Inbound_Item_Id");
            $this->db->join("STK_T_Order od", "odd.Order_Id = od.Order_Id");

            $this->db->join("CTL_M_Company Owner", "od.Owner_Id = Owner.Company_Id", "LEFT");
            $this->db->join("CTL_M_Company Renter", "od.Renter_Id = Renter.Company_Id", "LEFT");

            $this->db->join("STK_T_Workflow wf", "od.Flow_Id = wf.Flow_Id");

            $this->db->where("pal.Pallet_Code", $pallet_code);
            $this->db->order_by("odd.Item_Id DESC");

            $pallet_data = $this->db->get()->row_array();
            $sql = $this->db->last_query();
            // p($pallet_data);
            // exit;
            endif;
            
            // $this->db->select("
            //     STK_T_Pallet.Pallet_Id
            //     , STK_T_Pallet.Pallet_Code
            //     , SYS_M_Domain.Dom_EN_SDesc AS Pallet_Type
            //     , CTL_M_Pallet_Master.Pallet_Master_Name
            //     , CTL_M_Pallet_Master.Pallet_Width
            //     , CTL_M_Pallet_Master.Pallet_Lenght
            //     , CTL_M_Pallet_Master.Pallet_Height
            //     , STK_M_Location.Location_Code
            //     , CTL_M_Container.Cont_No
            //     , CTL_M_Container_Size.Cont_Size_No
            //     , CTL_M_Container_Size.Cont_Size_Unit_Code
            //     , STK_T_Invoice.Invoice_No
            //     , Owner.Company_NameEN AS Owner_Name
            //     , Renter.Company_NameEN AS Renter_Name
            // ");

            // $this->db->from("STK_T_Pallet");
            // $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code", "LEFT");
            // $this->db->join("CTL_M_Pallet_Master", "STK_T_Pallet.Pallet_Master_Id = CTL_M_Pallet_Master.Pallet_Master_Id", "LEFT");
            // $this->db->join("CTL_M_Container", "STK_T_Pallet.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
            // $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
            // $this->db->join("STK_M_Location", "STK_T_Pallet.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
            // $this->db->join("STK_T_Invoice", "STK_T_Pallet.Order_Id = STK_T_Invoice.Order_Id", "LEFT");
            // $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);

            // $pallet_data = $this->db->get()->row_array();
           
        //    p($pallet_data);
        //    exit;
            
            if(!empty($pallet_data)):
                $this->db->select("
                    STK_T_Inbound.Item_Id
                    , STK_T_Inbound.Product_Code
                    , STK_T_Inbound.Product_Lot
                    , STK_T_Inbound.Product_Serial
                    , STK_T_Inbound.Product_Mfd
                    , STK_T_Inbound.Product_Exp
                    , STK_T_Inbound.Balance_Qty
                    , STK_M_Product.Product_NameEN
                    , CTL_M_UOM_Template_Language.public_name AS Unit_Name
                    , STK_T_Invoice.Invoice_No
                ");
            
                if($pallet_code[0] == 'R'):
//                    $this->db->select("(
//                        SELECT Old_Pallet.Pallet_Code
//                        FROM STK_T_Pallet
//                        JOIN STK_T_Relocate ON STK_T_Pallet.Order_Id = STK_T_Relocate.Order_Id
//                        JOIN STK_T_Relocate_Detail ON STK_T_Relocate.Order_Id = STK_T_Relocate_Detail.Order_Id
//                        JOIN STK_T_Inbound AS Old_Inbound ON STK_T_Relocate_Detail.Inbound_Item_Id = Old_Inbound.Inbound_Id
//                        JOIN STK_T_Pallet AS Old_Pallet ON Old_Inbound.Pallet_Id = Old_Pallet.Pallet_Id
//                        WHERE STK_T_Pallet.Pallet_Id = STK_T_Inbound.Pallet_Id
//                        ) AS Old_Pallet_Code
//                    ");
                    
                    $this->db->select("Old_Pallet.Pallet_Code as Old_Pallet_Code");
                    $this->db->join("STK_T_Relocate_Detail","STK_T_Inbound.Item_Id = STK_T_Relocate_Detail.Item_Id","LEFT");
                    $this->db->join("STK_T_Inbound AS Old_Inbound","STK_T_Relocate_Detail.Inbound_Item_Id = Old_Inbound.Inbound_Id","LEFT");
                    $this->db->join("STK_T_Pallet AS Old_Pallet","Old_Inbound.Pallet_Id = Old_Pallet.Pallet_Id","LEFT");
                    
                endif;
            
                $this->db->from("STK_T_Inbound");
                $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Code = STK_M_Product.Product_Code", "INNER");
                $this->db->join("CTL_M_UOM_Template_Language", "STK_T_Inbound.Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
                $this->db->join("STK_T_Invoice","STK_T_Inbound.Invoice_Id = STK_T_Invoice.Invoice_Id","LEFT");
                $this->db->where("STK_T_Inbound.Pallet_Id", $pallet_data['Pallet_Id']);
                $this->db->where("STK_T_Inbound.Active", ACTIVE);
                $pallet_data['item_lists'] = $this->db->get()->result_array();

            //    p($pallet_data);
            //    exit;
            //    echo $this->db->last_query(); exit;
                
            endif;
        endif;
        
        return $pallet_data;
    }


        /**
     * function for Get search data of Pallet
     * @param type $column
     * @param type $where
     * @param type $order_by
     * @return type
     */
    function _get_search_pallet($pallet_code = NULL){
        $pallet_data = array();
        
        if(!empty($pallet_code)):
            $this->db->select("
                STK_T_Pallet.Pallet_Id
                , STK_T_Pallet.Pallet_Code
                , SYS_M_Domain.Dom_EN_SDesc AS Pallet_Type
                , CTL_M_Pallet_Master.Pallet_Master_Name
                , CTL_M_Pallet_Master.Pallet_Width
                , CTL_M_Pallet_Master.Pallet_Lenght
                , CTL_M_Pallet_Master.Pallet_Height
                , STK_M_Location.Location_Code
                , CTL_M_Container.Cont_No
                , CTL_M_Container_Size.Cont_Size_No
                , CTL_M_Container_Size.Cont_Size_Unit_Code
                , STK_T_Invoice.Invoice_No
                , STK_T_Order.Document_No
                , STK_T_Order.Doc_Refer_Ext
                , Owner.Company_NameEN AS Owner_Name
                , Renter.Company_NameEN AS Renter_Name
            ");

            $this->db->from("STK_T_Pallet");
            $this->db->join("SYS_M_Domain", "STK_T_Pallet.Pallet_Type = SYS_M_Domain.Dom_Code", "LEFT");
            $this->db->join("CTL_M_Pallet_Master", "STK_T_Pallet.Pallet_Master_Id = CTL_M_Pallet_Master.Pallet_Master_Id", "LEFT");
            $this->db->join("CTL_M_Container", "STK_T_Pallet.Cont_Id = CTL_M_Container.Cont_Id", "LEFT");
            $this->db->join("CTL_M_Container_Size", "CTL_M_Container.Cont_Size_Id = CTL_M_Container_Size.Cont_Size_Id", "LEFT");
            $this->db->join("STK_M_Location", "STK_T_Pallet.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
            $this->db->join("STK_T_Order", "STK_T_Pallet.Order_Id = STK_T_Order.Order_Id", "LEFT");
            $this->db->join("STK_T_Invoice", "STK_T_Pallet.Order_Id = STK_T_Order.Order_Id", "LEFT");
            $this->db->join("CTL_M_Company Owner", "STK_T_Order.Owner_Id = Owner.Company_Id", "LEFT");
            $this->db->join("CTL_M_Company Renter", "STK_T_Order.Renter_Id = Renter.Company_Id", "LEFT");
            $this->db->where("STK_T_Pallet.Pallet_Code", $pallet_code);
            $pallet_data = $this->db->get()->row_array();
            // p($pallet_data);
            // exit;
            if(!empty($pallet_data)):
                $this->db->select("
                    STK_T_Inbound.Item_Id
                    , STK_T_Inbound.Product_Code
                    , STK_T_Inbound.Product_Lot
                    , STK_T_Inbound.Product_Serial
                    , STK_T_Inbound.Product_Mfd
                    , STK_T_Inbound.Product_Exp
                    , STK_T_Inbound.Receive_Qty
                    , STK_T_Inbound.Receive_Qty AS Balance_Qty
                    , CTL_M_UOM_Template_Language.public_name AS Unit_Name
                ");
                $this->db->from("STK_T_Inbound");
                $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Code = STK_M_Product.Product_Code", "INNER");
                $this->db->join("CTL_M_UOM_Template_Language", "STK_T_Inbound.Unit_Id = CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id AND CTL_M_UOM_Template_Language.language = '" . $this->config->item('lang3digit') . "'");
                $this->db->where("STK_T_Inbound.Pallet_Id", $pallet_data['Pallet_Id']);
                $this->db->where("STK_T_Inbound.Active", ACTIVE);
                $pallet_data['item_lists'] = $this->db->get()->result_array();
            //    echo $this->db->last_query();
            //    exit;
   
                $this->load->model('pallet_model', 'pallet');
                $item_explode = $this->pallet->get_item_explode_from_pallet_id($pallet_data['Pallet_Id'])->result_array();
    //            p($item_explode);
                $item_explodes = array();
                foreach($item_explode as $key_itemex => $itemex):
                    $set_new_key = $itemex['Item_Id_Receive'];
                    unset($itemex['Item_Id_Receive']);
                    $item_explodes[$set_new_key][] = $itemex;
                endforeach;
    //            p($item_explodes);
                foreach($pallet_data['item_lists'] as $key_itemlist => $itemlist):
                    if(array_key_exists($itemlist['Item_Id'], $item_explodes)):
                        foreach($item_explodes[$itemlist['Item_Id']] as $key_listexp => $listexp):
                            $pallet_data['item_lists'][$key_itemlist]['Balance_Qty'] -= $listexp['Confirm_Qty'];
                            if($pallet_data['item_lists'][$key_itemlist]['Balance_Qty'] <= 0):
                                unset($pallet_data['item_lists'][$key_itemlist]);
                            endif;
    //                        p($listexp);
                        endforeach;
                    endif;
                endforeach;                
            endif;
            
        endif;
            
//                p($pallet_data);exit();
        return $pallet_data;
    }

    public function getDetailByUserId($userId) {
        $this->db->select("*");
        $this->db->from("ADM_M_UserLogin UserLogin ");
        $this->db->join("CTL_M_Contact Contact ", "Contact.Contact_Id = UserLogin.Contact_Id");
        $this->db->where("UserLogin_Id", $userId);
        $query = $this->db->get();
        return $query->row_array();
    }

}

?>
