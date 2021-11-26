<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class util_query_db  {

    function __construct() {
    }
    
    
    function select_table($table, $data, $where="",$group="",$orderby="") { 
        $CI = & get_instance();
        if(!empty($where)):
            $CI->db->where($where);
        endif;
        
    	$CI->db->select($data);
        $CI->db->from($table);
        
        if(!empty($group)):
           $CI->db->group_by($group); 
        endif;
        
        if(!empty($orderby)):
            $CI->db->order_by($orderby);
        endif;
        
    	$query=$CI->db->get();

        return $query;
        
    }
    
    function insert_table($table, $data) { 	
        $CI = & get_instance();
    	$CI->db->insert($table, $data);
    	$afftectedRows = $CI->db->affected_rows();
        if ($afftectedRows <= 0) :
            return FALSE; //Update unsuccess.
        else:
            return TRUE; //Update success.
        endif;
    }
    
    function update_table($table, $data, $where="") { 
        $CI = & get_instance();
        if(!empty($where)):
            $CI->db->where($where);
        endif;
        
    	$CI->db->update($table, $data);
    	$afftectedRows = $CI->db->affected_rows();
        if ($afftectedRows <= 0) :
            return FALSE; //Update unsuccess.
        else:
            return TRUE; //Update success.
        endif;
        
    }
    
    /** 
     * @author  kik : 20140728
     * @param string $table
     * @param string/array $where
     * @return boolean
     */
    function delete_table($table,  $where="") { 
        $CI = & get_instance();
        if(!empty($where)):
            $CI->db->where($where);
        endif;
        
    	$CI->db->delete($table);
    	$afftectedRows = $CI->db->affected_rows();
        if ($afftectedRows < 0) :
            return FALSE; //Update unsuccess.
        else:
            return TRUE; //Update success.
        endif;
        
    }
    
    
    /**
     * @author  Akkarapol
     * @date 2014/10/17
     * @param type $str
     * @return boolean
     */
    function query($str) {
        $CI = & get_instance();
        
    	$CI->db->query($str);
    	$afftectedRows = $CI->db->affected_rows();
        if ($afftectedRows < 0) :
            return FALSE; 
        else:
            return TRUE; 
        endif;
        
    }
    
}
?>