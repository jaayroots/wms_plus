<?php

class non_fefo_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_non_fefo_list(){

      $this->db->select("STK_T_Inbound.Product_Code, 
                         STK_M_Product.Product_NameEN, 
                         STK_T_Pallet.Pallet_Code, 
                         STK_M_Location.Location_Code ,
                         convert(varchar(10),stk_t_inbound.Product_Mfd,103) Product_Mfd, 
                         convert(varchar(10),data1.Dispatch_Mfd,103) Dispatch_Mfd,
                         convert(varchar(10),data1.Dispatch_Date,103) Dispatch_Date,
                         format(STK_T_Inbound.Balance_Qty, 'N3') AS Balance_Qty,
                         CTL_M_UOM_Template_Language.public_name as uom
                         ");
      $this->db->from("STK_T_Inbound");

      $this->db->join("(SELECT stock_id.Inbound_Id, stock_id.Product_Code, stock_id.Product_Mfd, Dispatch.Dispatch_Mfd, Dispatch.Dispatch_Date
                          FROM( SELECT min(inb_id.inbound_id) Inbound_Id,inb_id.Product_Code,inb_id.Product_Mfd
                          FROM STK_T_Inbound inb_id 
                          JOIN (SELECT inb.Product_Code , min(inb.Product_Mfd) as Stock_Mfd    
                                FROM STK_T_Inbound inb
                                WHERE inb.Active = 'Y' AND inb.Product_Status = 'normal'
                                GROUP BY inb.Product_Code) inb2 ON inb_id.Product_Code = inb2.Product_Code AND inb_id.Product_Mfd = inb2.Stock_Mfd     
                                WHERE inb_id.Active = 'Y' AND inb_id.Product_Status = 'normal'
                                GROUP BY inb_id.Product_Code,inb_id.Product_Mfd ) AS stock_id
                      JOIN (SELECT od2.Product_Code,Dispatch_Mfd,max(Actual_Action_Date) AS Dispatch_Date            
                            FROM STK_T_Order o2
                            LEFT JOIN STK_T_Workflow w2 ON w2.Flow_Id = o2.Flow_Id
                            LEFT JOIN STK_T_Order_Detail od2 ON o2.Order_Id = od2.Order_Id
                            JOIN (SELECT od.Product_Code, max(od.Product_Mfd) AS Dispatch_Mfd                
                                  FROM STK_T_Order o
                                  LEFT JOIN STK_T_Workflow w ON w.Flow_Id = o.Flow_Id
                                  LEFT JOIN STK_T_Order_Detail od ON o.Order_Id = od.Order_Id
                                  WHERE w.Process_Id = '2' AND w.Present_State = '-2' AND od.Active = 'Y'
                                  GROUP BY  od.Product_Code) AS dp_mfd ON od2.Product_Code = dp_mfd.Product_Code AND od2.Product_Mfd = dp_mfd.Dispatch_Mfd
                            WHERE w2.Process_Id = '2' AND w2.Present_State = '-2' AND od2.Active = 'Y'
                            GROUP BY  od2.Product_Code, Dispatch_Mfd) AS Dispatch on stock_id.Product_Code = Dispatch.Product_Code) AS data1","STK_T_Inbound.Inbound_Id = data1.Inbound_Id" );

       $this->db->join("STK_M_Product", "STK_T_Inbound.Product_Code = STK_M_Product.Product_Code", "LEFT");
       $this->db->join("STK_M_Location", "STK_T_Inbound.Actual_Location_Id = STK_M_Location.Location_Id", "LEFT");
       $this->db->join("STK_T_Pallet", "STK_T_Inbound.Pallet_Id = STK_T_Pallet.Pallet_Id", "LEFT");
       $this->db->join("CTL_M_UOM_Template_Language", "CTL_M_UOM_Template_Language.CTL_M_UOM_Template_id = STK_M_Product.Standard_Unit_Id AND CTL_M_UOM_Template_Language.language='eng'", "LEFT");
     
       $this->db->where("( stk_t_inbound.Product_Mfd < data1.Dispatch_Mfd)");
       $this->db->order_by("convert(varchar(10),data1.Dispatch_Date,120), STK_T_Inbound.Product_Code");

 
$query = $this->db->get();
    //   echo $this->db->last_query(); exit;

return $query;

    }
    
}
    ?>