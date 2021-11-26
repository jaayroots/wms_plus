<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Datatable {

    function __construct() {

    }

    function loadModule() {
        $CI = & get_instance();
//        $CI->load->database();
        return $this->genDatatable();
    }

    function genDefaultDataTable($query = "") { // generate default data table
        $CI = & get_instance();
//        $CI->load->database();
        $CI->load->library('table');
        $query = $CI->db->query("SELECT TOP 22 *
		,'" . img('css/images/icons/view.png') . "' as '" . VIEW . "'
		,'" . img('css/images/icons/edit.png') . "' as '" . EDIT . "'
		,'" . img('css/images/icons/del.png') . "' as '" . DEL . "'
		FROM CTL_M_City ");
        $tmpl = array('table_open' => '<table cellpadding="0" cellspacing="0" border="0" class="display" id="defDataTable" >');
        $CI->table->set_template($tmpl);
        return $CI->table->generate($query);
    }

    #add module_del by kik : 2013-12-11
    #update Is_urgent for ISSUE 3312 : by kik : 20140121

    function genTableFixColumn($query, $data, $column, $module, $action, $module_del = "", $other = NULL) {
        $CI = & get_instance();
        $CI->load->library('table');
        $CI->load->model("workflow_model");
        $_xml = $CI->config->item("_xml");
        list($_module) = explode("/", $module);
        $array_result = array();
        $tmpl = array('table_open' => '<table cellpadding="0" cellspacing="0" border="0" class="display" id="defDataTable" >'
            , 'row_start' => '<tr class="list_row_click">'
            , 'row_end' => '</tr>'
            , 'row_alt_start' => '<tr class="list_row_click">'
            , 'row_alt_end' => '</tr>'
        );
        $CI->table->set_template($tmpl);
        if (count($action) > 0) :
            foreach ($action as $act) :
                $column[] = $act;
            endforeach;
        endif;
        $loop = 0;
        $CI->table->set_heading($column);
        if (count($data) > 0) :
            foreach ($data as $idx => $rows) :
                if (count($action) > 0) :

                    // duplicate rows id for multiple quick approve
                    $rows_id = $rows->Id;

                    foreach ($action as $act) :
                        if ('VIEW' == $act) :
                            $rows->VIEW = "<a class=\"view_item\" ONCLICK=\"openForm('view_form','" . $module . "','V','" . $rows->Id . "')\" >" . img("css/images/icons/view.png") . "</a>";
                        elseif ('EDIT' == $act) :
                            $rows->EDIT = "<a ONCLICK=\"openForm('edit_form','" . $module . "','E','" . $rows->Id . "')\" >" . img("css/images/icons/edit.png") . "</a>";
                        elseif ('DEL' == $act) :

                            //add condition check if $module_del != "" when goto $module_del url : add by kik : 2013-12-02
                            if ($module_del != "") :
                                $rows->DEL = "<a ONCLICK=\"openForm('del_form','" . $module_del . "','D','" . $rows->Id . "')\" >" . img("css/images/icons/del.png") . "</a>";
                            else :
                                $rows->DEL = "<a ONCLICK=\"openForm('del_form','" . $module . "','D','" . $rows->Id . "')\" >" . img("css/images/icons/del.png") . "</a>";
                            endif;
                        endif;
                    endforeach;
                endif;

                #add for ISSUE 3312 : by kik : 20140121
                if (!empty($rows->Is_urgent)):
                    if ($rows->Is_urgent == "Y"):
                        $rows->Id = img(array('src' => "css/images/icons/argent.png", 'alt' => 'Urgent', 'title' => 'Urgent', 'style' => 'margin:0px 0px 0px -20px;')) . $rows->Id;
                    endif;
                endif;
                unset($rows->Is_urgent);
                unset($rows->Create_Date);
                #end add for ISSUE 3312 : by kik : 20140121

                #Add By Akkarapol, 20140925, แก้ไขไม่ให้สามารถ EDIT หรือ DELETE ข้อมูลของ UOM Master ที่เป็น Standard ได้
                if (!empty($rows->code)):
                    if (strpos($rows->code,'STD') !== false && strpos($rows->code,'UNIT') !== false) :
                        $rows->EDIT = '';
                        $rows->DEL = '';
                    endif;
//                    unset($rows->code);
                endif;
                #END Add By Akkarapol, 20140925, แก้ไขไม่ให้สามารถ EDIT หรือ DELETE ข้อมูลของ UOM Master ที่เป็น Standard ได้


                #Add By Akkarapol, 20140925, แก้ไขไม่ให้สามารถ EDIT หรือ DELETE ข้อมูลของ UOM Template ที่เป็น Root Unit ได้
                if (!empty($rows->root_unit)):
                    if ($rows->root_unit == "Y"):
                        $rows->EDIT = '';
                        $rows->DEL = '';
                    endif;
                endif;
                unset($rows->root_unit);
                #END Add By Akkarapol, 20140925, แก้ไขไม่ให้สามารถ EDIT หรือ DELETE ข้อมูลของ UOM Template ที่เป็น Root Unit ได้


                $array_result[] = (array) $rows;

                // If have checkbox
                if ( $other == "checkbox_picking" && $_module == "picking") :
                //if ( ( ( isset($_xml['quick_picking_approve']) && (!empty($_xml['quick_picking_approve']) )  ) || ( isset($_xml['consolidate_picking']) && $_xml['consolidate_picking'] == TRUE ) ) && $_module == "picking") :
                    array_unshift($array_result[$loop], "<input type=\"checkbox\" name=\"fl_box[]\" value=\"" .$rows_id. "\" data-document=\"".$rows->Document_No."\" class=\"check_list\" />");
                endif;
                // end checkbox;

                // If have checkbox
                if (@$_xml['quick_dispatch_approve'] == TRUE && $_module == "dispatch") :
                    array_unshift($array_result[$loop], "<input type=\"checkbox\" name=\"fl_box[]\" value=\"" .$rows_id. "\" class=\"check_list\" />");
                endif;
                // end checkbox;

                $loop++;
            endforeach;

            return $CI->table->generate($array_result);
        else :
            return $CI->table->generate($query);
        endif;
    }

    function genCustomiztable($data, $column, $module, $action, $module_del = "") {
        $CI = & get_instance();
        $CI->load->library('table');
        $array_result = array();
        $tmpl = array('table_open' => '<table cellpadding="0" cellspacing="0" border="0" class="display" id="defDataTable" >'
            , 'row_start' => '<tr class="list_row_click">'
            , 'row_end' => '</tr>'
            , 'row_alt_start' => '<tr class="list_row_click">'
            , 'row_alt_end' => '</tr>'
        );
        $CI->table->set_template($tmpl);
        if (count($action) > 0) {
            foreach ($action as $act) {
                $column[] = $act;
            }
        }
        $CI->table->set_heading($column);
        if (count($data) > 0) {
            foreach ($data as $rows) {
                if (count($action) > 0) {
                    foreach ($action as $act) {
                        if (VIEW == $act) {
                            $rows->VIEW = "<a class=\"view_item\" ONCLICK=\"openForm('view_form','" . $module . "','V','" . $rows->Id . "')\" >" . img("css/images/icons/view.png") . "</a>";
                        } else if (EDIT == $act) {
                            $rows->EDIT = "<a ONCLICK=\"openForm('edit_form','" . $module . "','E','" . $rows->Id . "')\" >" . img("css/images/icons/edit.png") . "</a>";
                        } else if (DEL == $act) {
                            //add condition check if $module_del != "" when goto $module_del url : add by kik : 2013-12-02
                            if ($module_del != "") {
                                $rows->DEL = "<a ONCLICK=\"openForm('del_form','" . $module_del . "','D','" . $rows->Id . "')\" >" . img("css/images/icons/del.png") . "</a>";
                            } else {
                                $rows->DEL = "<a ONCLICK=\"openForm('del_form','" . $module . "','D','" . $rows->Id . "')\" >" . img("css/images/icons/del.png") . "</a>";
                            }
                        } else if (ADD == $act) {
                            $rows->ADD = "<a ONCLICK=\"openForm('add_form','" . $module . "','A','" . $rows->Id . "')\" >" . img("css/images/icons/add.png") . "</a>";
                        }
                    }
                }

                #add for ISSUE 3312 : by kik : 20140121
                if (!empty($rows->Is_urgent)):
                    if ($rows->Is_urgent == "Y"):
                        $rows->Id = img(array('src' => "css/images/icons/argent.png", 'alt' => 'Urgent', 'title' => 'Urgent', 'style' => 'margin:0px 0px 0px -20px;')) . $rows->Id;
                    endif;
                endif;


                unset($rows->Is_urgent);
                unset($rows->Create_Date);
                #end add for ISSUE 3312 : by kik : 20140121

                $array_result[] = (array) $rows;
            }

            return $CI->table->generate($array_result);
        } else {
            return $CI->table->generate(array());
        }
    }

    function genTableByArray($query, $data) {
        $CI = & get_instance();
        $CI->load->library('table');
        $array_result = array();
        $tmpl = array('table_open' => '<table cellpadding="0" cellspacing="0" border="0" class="display" id="defDataTable" >');
        $CI->table->set_template($tmpl);
        if (count($data) > 0) {
            foreach ($query->list_fields() as $field) {
                $array_header[] = $field;
            }
            $array_header[] = VIEW;
            $array_header[] = EDIT;

            $array_header[] = DEL;

            foreach ($data as $rows) {
                $rows->VIEW = img('css/images/icons/view.png');
                $rows->EDIT = img('css/images/icons/edit.png');
                $rows->DEL = img('css/images/icons/del.png');
                $array_result[] = (array) $rows;
            }
            $CI->table->set_heading($array_header);
            return $CI->table->generate($array_result);
        } else {
            return $CI->table->generate($query);
        }
    }

    function genDataTable($query = "") {
        $CI = & get_instance();
//        $CI->load->database();
        $CI->load->library('table');
        $query = $CI->db->query($query);
        $result = $query->result();
        $array_header = array();
        $array_result = array();
        if (count($result) > 0) {
            foreach ($query->list_fields() as $field) {
                $array_header[] = $field;
            }
            $array_header[] = VIEW;
            $array_header[] = EDIT;
            $array_header[] = DEL;
            foreach ($result as $rows) {
                $rows->VIEW = img('css/images/icons/view.png');
                $rows->EDIT = img('css/images/icons/edit.png');
                $rows->DEL = img('css/images/icons/del.png');
                $array_result[] = (array) $rows;
            }
        }
        $tmpl = array('table_open' => '<table cellpadding="0" cellspacing="0" border="0" class="display" id="defDataTable" >');
        $CI->table->set_template($tmpl);
        $CI->table->set_heading($array_header);
        return $CI->table->generate($array_result);
    }

    function genDataServSide() {
        /* Ordering */
        $sOrder = "";
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
						" . addslashes($_GET['sSortDir_' . $i]) . ", ";
                }
            }
            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        /* Filtering */
        $sWhere = "";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . addslashes($_GET['sSearch']) . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . addslashes($_GET['sSearch_' . $i]) . "%' ";
            }
        }

        // ********** Start Hieng Add ************//
        if ($_GET["conField"] != "") {
            if ($sWhere == "") {
                $sWhere = "WHERE " . $_GET["conField"] . " " . $_GET["conOper"] . " '" . addslashes($_GET['conVal']) . "'";
            } else {
                $sWhere .= " AND " . $_GET["conField"] . " " . $_GET["conOper"] . " '" . addslashes($_GET['conVal']) . "'";
            }
        }
        // ********** End Hieng Add ************//

        /* Paging */
        $top = (isset($_GET['iDisplayStart'])) ? ((int) $_GET['iDisplayStart']) : 0;
        $limit = (isset($_GET['iDisplayLength'])) ? ((int) $_GET['iDisplayLength'] ) : 10;
        $sQuery = "SELECT TOP $limit " . implode(",", $aColumns) . "
		FROM $sTable
		$sWhere " . (($sWhere == "") ? " WHERE " : " AND ") . " $sIndexColumn NOT IN
		(
			SELECT $sIndexColumn FROM
			(
				SELECT TOP $top " . implode(",", $aColumns) . "
				FROM $sTable
				$sWhere
				$sOrder
			)
			as [virtTable]
		)
		$sOrder";

        $rResult = odbc_exec($gaSql['link'], $sQuery) or die("$sQuery: " . odbc_error());

        $sQueryCnt = "SELECT * FROM $sTable $sWhere";
        $rResultCnt = odbc_exec($gaSql['link'], $sQueryCnt, $params, $options) or die(" $sQueryCnt: " . odbc_error());
        $iFilteredTotal = odbc_num_rows($rResultCnt);

        $sQuery = " SELECT * FROM $sTable ";
        $rResultTotal = odbc_exec($gaSql['link'], $sQuery, $params, $options) or die(odbc_error());
        $iTotal = odbc_num_rows($rResultTotal);

        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        while ($aRow = odbc_fetch_array($rResult)) {
            $row = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] != ' ') {
                    $v = $aRow[$aColumns[$i]];
                    $v = iconv("tis-620", "utf-8", $aRow[$aColumns[$i]]);
                    $v = mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v);
                    $row[] = $v;
                }
            }
            $row[] = "<a href=\"#\"><img src='system/img/icons/view.png' alt='" . VIEW . "'></a>";
            $row[] = "<a href=\"#\"><img src='system/img/icons/edit.png' alt='" . EDIT . "'></a>";
            $row[] = "<a href=\"#\" onclick=\"if (confirm('" . iconv("tis-620", "utf-8", DOU2DEL) . "')) location.href='" . PROCESS_FILE . "?mod=" . $ini . "&act=D&id=" . $row[0] . "';\"><img src='system/img/icons/del.png' alt='" . DEL . "'></a>";
            If (!empty($row)) {
                $output['aaData'][] = $row;
            }
        }
        echo json_encode($output);
    }

    function genOnlyTableByArray($query, $data) {
        $CI = & get_instance();
        $CI->load->library('table');
        $array_result = array();
        $array_header = array();
        $tmpl = array('table_open' => '<table cellpadding="2" cellspacing="0" border="0" class="display" id="defDataTable">');
        $CI->table->set_template($tmpl);
        if (count($data) > 0) {
            foreach ($query->list_fields() as $field) {
                $array_header[] = $field;
            }
            foreach ($data as $rows) {
                $array_result[] = (array) $rows;
            }
            $CI->table->set_heading($array_header);
            return $CI->table->generate($array_result);
        } else {
            return $CI->table->generate($query);
        }
    }

    public function genDynamicColumn ($page = NULL) {
        $CI =& get_instance();
        $xml = $CI->config->item("_xml");
        $master_column_file = APPPATH . 'config/xml/column.xml';

        $doc = new DOMDocument();

        if ($CI->config->item('cryptography')) :
            $encode_data = read_file($master_column_file);
            $decode_data = $CI->cryptography->DecryptSource($encode_data);
            $doc->loadXML($decode_data);
        else :
            $doc->load($master_column_file); //xml file loading here
        endif;

        $objXML = $this->xml_to_array($doc);
        $xml_data = $objXML['application']['inbound'][$page];
        $column = $xml['show_column'][$page];

        $response = array();
        $data = array();
        $column_index = array();
        $idx_data = array();
        $i = 0;
        foreach ($column as $idx => $val) :

            $column_index[$idx] = $i;
            $idx_data[$i] = $xml_data[$idx]['idx_new'];

            if (isset($xml_data[$idx])) :
                $data[$idx] = $xml_data[$idx];
            else :
                $data[$idx] = NULL;
            endif;

        $i++;
        endforeach;

        if ($CI->config->item('price_per_unit') != TRUE) :
            $xml_data['price_per_unit']['price_per_unit'] = NULL;
            $xml_data['unit_price']['onblur'] = "submit";
            $xml_data['unit_price']['event'] = "click";
        endif;

        $response['data'] = $data;
        $response['idx_data'] = $idx_data;
        $response['column_index'] = $column_index;

        return $response;
    }

    /**
     * DOMDocument
     *
     * @param unknown $root
     * @return multitype:NULL |Ambigous <multitype:NULL multitype:Ambigous
     *         <multitype:> , multitype:, multitype:multitype: multitype:Ambigous
     *         <multitype:> NULL >
     */
    public function xml_to_array ($root)
    {
    $result = array();

    if ($root->hasAttributes()) :
        $attrs = $root->attributes;
        foreach ($attrs as $attr) :
            $result['@attributes'][$attr->name] = $attr->value;
        endforeach;
    endif;

    if ($root->hasChildNodes()) :
        $children = $root->childNodes;
        if ($children->length == 1) :
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) :
                $result['_value'] = $child->nodeValue;
                return count($result) == 1 ? $result['_value'] : $result;
            endif;
        endif;
        $groups = array();
        foreach ($children as $child) :
            if ($child->nodeName != "#text") :
                if (! isset($result[$child->nodeName])) :
                    $result[$child->nodeName] = $this->xml_to_array($child);
                 else :
                    if (! isset($groups[$child->nodeName])) :
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;

            endif;
                    $result[$child->nodeName][] = $this->xml_to_array($child);
                endif;

            endif;
        endforeach;

            endif;

    return $result;
    }
}
