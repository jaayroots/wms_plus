<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Validate Data function.
 */
class validate_data extends CI_Controller {

    
    /**
     * set construct
     */
    public function __construct() {
        $CI = & get_instance();
        $CI->load->model('stock_model', 'stock');
    }

    
    /**
     * 
     * @param type $product_code
     * @param type $exp_date
     * @param type $row
     * @param type $col
     * @return type
     */
    public function product_expire($product_code = NULL, $exp_date = NULL, $row = NULL, $col = NULL) {

        $return = array();
        
        if (!empty($exp_date)):
            
            $exp_date = explode('/', $exp_date);
            $exp_date = (mktime(0, 0, 0, $exp_date[1], $exp_date[0], $exp_date[2]) - mktime(0, 0, 0)) / ( 60 * 60 * 24 );

            if ($exp_date <= 0):

                $line_no = $row + 1;
                $set_return = array(
                    'message' => "In line NO. '{$line_no}' "._lang('product_code')." : '{$product_code}' has expired. ",
                    'row' => (string)$row,
                    'col' => (string)$col
                );

                $return['warning'][] = $set_return;

            endif;
        endif;
        
        return $return;
    }
    
    /**
    * function Check That is a number Before saving them to the database by type int or float
    * @author Apinya.P : 2014-03-04
    * @param type array $data ->Information needed to determine ex. $data=array("qty"=>"156.05")
    * @param type array $row -> It key to check  ex. $key = array('qty')
    * @param type string $chk_type -> type 'int' or 'float' for check data 
    * @return array
    * @example  
        /////////////////////////////
        $data = array();
	$key = array();
	
        //data you have
	$data=array(
	"qty"=>"156.05",
	"qty1"=>"156.05",
	"qty2"=>"156.05",
	"qty3"=>"1",
	"qty4"=>"22",
	"qty5"=>"156.00",
	"qty_z"=>"1234"
	);
	
        //key want check
	$key = array(
		'qty',
		'qty1',
		'qty2',
		'qty5'
	);
        
        //cal function
        $test=check_numeric($data, $key,'int');      
	if(empty($test['critical'])){ //if $test empty is yes because no have error message return
		echo "yes";
	}else{
		echo "no";
	}
    
    */
    function check_numeric($data, $row, $chk_type = 'float') {
        $CI = & get_instance();
        
        if($CI->config->item('format_number') =="0"): //EDIT BY POR type 'int' if config format_number =0 
            $chk_type='int';
        endif;
        
        $return=array();
       
        foreach($row as $key):
            if (is_numeric($data[$key])):
                if ($chk_type == 'int'): 
                    $tmp_float = (float) $data[$key];
                    $tmp_int = (int) $data[$key];

                    if ($tmp_float == $tmp_int):
                        //data is integer not return message
                    else:
                        $set_return['message'] = "$key  is not INT";
                        $return['critical'][] = $set_return;
                    endif;
                else: //กรณี float
                    //data is float not return message because integer can saving to the database type float
                endif;
            else:
                //not numeric
                $set_return['message'] = "$key  is not numeric";
                $return['critical'][] = $set_return;
            endif;
        endforeach;

        return $return;
    }

    /**
     * @function chk_doc_ext_duplicate for work check document ext duplicate data in Order table
     * @param array $data
     * @return array (Order data)
     * 
     * @last_midified Akkarapol : 20140321
     * 
     */
    function chk_doc_ext_duplicate($data = NULL) {
        
        $CI = & get_instance();
        
        $set_data['column'] = array('Doc_Refer_Ext');
        $set_data['where']['Doc_Refer_Ext'] = $data['doc_refer_ext'];
        $set_data['where']['Process_Type'] = $data['Process_Type'];
        if (!empty($data['flow_id'])):
            $set_data['where']['STK_T_Order.Flow_Id !='] = $data['flow_id'];
        endif;

//        $result = $CI->stock->getOrderTable($set_data['column'], $set_data['where']);
        $result = $CI->stock->get_duplicate_ext_doc($set_data['column'], $set_data['where'])->result();

        return $result;
        
    }
    
    function chk_unit_price_id($data, $row){
//        p($data);
//        p($row);
//        p($data[$row]);
        $CI = & get_instance();
        $return=array();
            $data_unit = 'Dom_Code';
            $where['Dom_Code'] = $data[$row];
            $where['Dom_Host_Code'] = 'PRICE_UNIT';
            
            $where['Dom_Active'] = 'Y';
            $result = $CI->util_query_db->select_table('SYS_M_Domain',$data_unit,$where)->row();
            if(empty($result)):
                $set_return['message'] = "Unit Price '$row' is not have";
                $return['critical'][] = $set_return;
            endif;
        return $return;
    }
    
    function check_last_mfd($product_id,$new_mfd){
//            select inb.Product_Code ,max(Product_Mfd) --, min(Product_Mfd)
//            from STK_T_Inbound inb
//            join STK_M_Product mp on mp.Product_Code = inb.Product_Code
//                                                    and mp.PickUp_Rule = 'FEFO'
//                                                    --and mp.PutAway_Rule = 'FEFO'
//            where 1=1
//
//            group by inb.Product_Code

        $CI = & get_instance();
//        $CI->load->database();
        $CI->db->select("inb.Product_Code ,max(inb.Product_Mfd) as last_mfd");
        $CI->db->from("STK_T_Inbound inb");
        $CI->db->join("STK_M_Product mp", "mp.Product_Code = inb.Product_Code and mp.PutAway_Rule = 'FEFO'");
        $CI->db->where("inb.Product_Id", $product_id);
       // $CI->db->where("inb.Product_Code > $new_mfd");
        $CI->db->group_by("inb.Product_Code"); 
        $CI->db->having("max(inb.Product_Mfd) > convert(datetime,'$new_mfd')");  
        $query = $CI->db->get();
        // p($CI->db->last_query()); 
        $result = $query->result_array();

        if (count($result) > 0) :   ///// mfd <= 
            return $result[0];     
        else://// all null = can't find //// retrun true
            return FALSE;
        endif;
        
        
    }
    
    function check_last_mfd_dispatch($product_id, $new_mfd, $des_id){

            $CI = & get_instance();

            $CI->db->select("o.Destination_Id, convert(varchar,max(od.Product_Mfd),103) as last_mfd");
            $CI->db->from("STK_T_Order o");
            $CI->db->join("STK_T_Workflow wf", "wf.Flow_Id = o.Flow_Id and wf.Process_Id = 2 and wf.Present_State = -2 ");
            $CI->db->join("STK_T_Order_Detail od", "od.Order_Id = o.Order_Id and od.Active = 'Y' ");
            $CI->db->join("STK_M_Product mp", "mp.Product_Id = od.Product_Id and mp.PutAway_Rule = 'FEFO'");
            $CI->db->where("od.Product_Id", $product_id);
            $CI->db->where("o.Destination_Id", $des_id);
            $CI->db->group_by("o.Destination_Id"); 
            $CI->db->having("max(od.Product_Mfd) > convert(datetime,'$new_mfd')");  
            $query = $CI->db->get();
            $result = $query->result_array();
//            p( $result);
//            p( $CI->db->last_query());
            if (count($result) > 0) :   ///// mfd <= 
                return $result[0];     
            else://// all null = can't find //// retrun true
                return FALSE;
            endif;
    }
    
     function check_last_mfd_for_dispatch($data = NULL) {
         $CI = & get_instance();
         $CI->load->library('validate_data');
         $result = array();
            $product_list = $data['prod_list']; 
        foreach ($data['prod_list'] as $key_product_list => $product_list):
            $line_no = $key_product_list+1;
            $product = explode(SEPARATOR, $product_list);
            $m_data['Product_Code'] = $product[1];
            $m_data['Product_Id'] = $product[22];
            $m_data['New_Mfd'] = $product[8];
            $m_data['New_Mfd'] = convertDate($m_data['New_Mfd'],'eng','usa','/');
            $result = $CI->validate_data->check_last_mfd_dispatch($m_data['Product_Id'],$m_data['New_Mfd'],$data['to_warehouse_select']);
//            p($result);
            if(!empty($result)){
                $set_return = array(
                  //  'message' => "Product " . $product[$data['ci_product_code']] . " in line " . $line . " not match pick face  streatery [" . $location .  "].",
                    'message' => "Found code '". $m_data['Product_Code'] ."' in line " . $line_no . " not match FEFO rule \n last MFD '".$result['last_mfd']."' , current MFD '".$product[8]."'",
                    'row' => (string) '1',
                    'col' => (string) '1'
                );
                $response['warning'][] = $set_return;
            }
            
        endforeach;
        return $response;
        
    }

}

/* End of file validate.php */
/* Location: ./application/libraries/validate.php */