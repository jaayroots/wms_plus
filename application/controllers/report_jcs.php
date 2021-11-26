<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Report_jcs extends CI_Controller {

    public $settings;
    public $revision;

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->library("encrypt");
        $this->load->library("session");
        $this->load->library("pagination_ajax");
        $this->load->helper("form");
        $this->load->model("report_jcs_model", "report");
        $this->load->model("report_model", "r");
        $this->load->model("encoding_conversion", "conv");
        $this->load->model("company_model", "company");
        $this->load->model("product_model", "product");
        $this->load->model("user_model", "user");
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));


        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
    }

    function ajax_show_product_list() {
        $text_search = $this->input->post('text_search');
        $product = $this->r->searchProduct($text_search, 100);
        $list = array();
        foreach ($product as $key_p => $p) {
            $list[$key_p]['product_id'] = $p['product_id'];
            $list[$key_p]['product_code'] = $p['product_code'];
            $list[$key_p]['product_name'] = thai_json_encode($p['product_name']);
        }
        echo json_encode($list);
    }

    function showProductList() {
        $text_search = $this->input->post('text_search');
        $product = $this->r->searchProduct($text_search);
        $list = '';
        foreach ($product as $p) {
            $list.='<li onClick="fill(\'' . $p['product_id'] . '\',\'' . $p['product_code'] . '\',\'\');">' . $p['product_code'] . ' ' . $this->conv->tis620_to_utf8($p['product_name']) . '</li>';
        }
        echo $list;
    }

    function showLocationList() {
        $location = $this->locationHis->showLocationAll($this->input->post('text_search'));
        $list = '';
        foreach ($location as $p) {
            $list.='<li onClick="fill_location(\'' . $p['Location_Code'] . '\');">' . $p['Location_Code'] . '</li>';
        }
        echo $list;
    }

    /**
     * Dispatch Report JCS
     */
    function dispatch() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $conf = $this->config->item('_xml');
        $parameter['conf_change_dp_date'] = (!empty($conf['can_change_dispatch_date']) ? $conf['can_change_dispatch_date'] : false);
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("report/customer/form_dispatch", $parameter, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Stock out report'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function dispatchReport() {
        $search = $this->input->post();
        $data = $this->report->search_dispatch($search);
        $view['data'] = $data;
        $view['search'] = $search;
        $this->load->view("report/customer/report_dispatch.php", $view);
    }

    function exportDispatchToPDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post($search);
        $report = $this->report->search_dispatch($search, 'PDF');
        $view['datas'] = $report;

        //เรียกชื่อของคนที่ออกรายงาน
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        $view['fdate'] = $search['fdate'];
        $view['tdate'] = $search['tdate'];
        $this->load->view("report/customer/export_dispatch_pdf", $view);
    }

    function exportDispatchToExcel() {
        $search = $this->input->post();
        $report = $this->report->search_dispatch($search);
        $view['search'] = $search;
        $view['file_name'] = 'stock-out-report';
        $view['header'] = array("Date In"
            , "Date Load"
            , "Description"
            , "Doc Ext."
            , "Batch No."
            , "Lot No."
            , "Type."
            , "Invoice No."
            , "Sum of Qty"
            , "Dimension"
            , "CBM"
            , "Sum of Total/CBM"
            , "Type UOM"
        );

        $view['body'] = $report;
        $this->load->view('report/customer/export_dispatch_excel', $view);
    }

    /**
     *
     * Receive Report for Customer
     *
     */
    function receive() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("report/customer/form_receive", $parameter, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Stock In Report '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmReceive"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function receiveReport() {
        $search = $this->input->get();
        $pages = new Pagination_ajax;

        $num_rows = sizeof($this->report->search_receive($search, null, null));
        $pages->items_total = $num_rows;
        $pages->mid_range = 5;
        $pages->paginate();

        $limit = json_decode($pages->limit);
        $view['low'] = $pages->low;
        $view['items_total'] = $num_rows;

        if ($pages->current_page == $pages->num_pages):
            $view['show_to'] = $num_rows;
        else:
            $view['show_to'] = $pages->low + $pages->items_per_page;
        endif;


        $view['display_items_per_page'] = $pages->display_items_per_page();

        if (!empty($limit)):
            $view['data'] = $this->report->search_receive($search, $limit[0], $limit[1]);
        else:
            $view['data'] = $this->report->search_receive($search, null, null);
        endif;

        $view['pagination'] = $pages->display_pages();

        $view['search'] = $search;

        #Load config
        $conf = $this->config->item('_xml');
        $receiving_report = empty($conf['show_column_report']['object']['receiving_report']) ? false : @$conf['show_column_report']['object']['receiving_report'];
        $column_result = colspan_report($receiving_report, $conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = $column_result['show_hide'];
        $this->load->view("report/customer/report_receive.php", $view);
    }

    /**
     * Receive Report to PDF
     */
    function exportreceiveToPDF() {

        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post();
        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['receiving_pdf']) ? array() : @$conf['show_column_report']['object']['receiving_pdf'];

        $column_result = colspan_report($conf_show_column, $conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];
        $view['datas'] = $this->report->search_receive_pdf($search);
        $view['from_date'] = $this->input->post("fdate");
        $view['to_date'] = $this->input->post("tdate");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $revision = $this->revision;
        $view['revision'] = $revision;
        $view['statusprice'] = $this->settings['price_per_unit'];
        $this->load->view("report/customer/export_receive_pdf", $view);
    }

    /**
     * Receive Report to Excel
     */
    function exportreceiveToExcel() {
        $search = $this->input->post();
        $conf = $this->config->item('_xml');
        $conf_inv = empty($conf['invoice']) ? false : @$conf['invoice'];
        $conf_cont = empty($conf['container']) ? false : @$conf['container'];
        $conf_price_per_unit = empty($conf['price_per_unit']) ? false : @$conf['price_per_unit'];
        $conf_pallet = empty($conf['build_pallet']) ? false : @$conf['build_pallet'];
        $conf_show_column = empty($conf['show_column_report']['object']['receiving_report']) ? array() : @$conf['show_column_report']['object']['receiving_report'];

        $reports = $this->report->search_receive($search, null, null);

        $data_views = array();

        foreach ($reports as $data):

            $temp = new stdClass();
            $temp->Receive_Date = $data->Receive_Date;
            $temp->Product_Name = $data->Product_Name;
            $temp->Doc_Refer_Ext = $data->Doc_Refer_Ext;
            $temp->Product_Lot = $data->Product_Lot;
            $temp->Product_Serial = $data->Product_Serial;
            $temp->TypeLicense = $data->TypeLicense;
            $temp->Invoice_No = $data->Invoice_No;
            $temp->Receive_Qty = $data->Receive_Qty;
            $temp->Dimension = (!empty($data->w) && !empty($data->h) && !empty($data->l) ? $data->w . " x " . $data->h . " x " . $data->l : "");
            $temp->cbm = $data->cbm;
            $temp->sum_cbm = ($data->Receive_Qty * $data->cbm);
            $temp->Unit_Value = $data->Unit_Value;

            if ($data->Is_reject == 'Y'):
                $temp->Receive_Qty = - $temp->Receive_Qty;
            endif;

            array_push($data_views, $temp);

        endforeach;

        $view['search'] = $search;
        $view['file_name'] = 'stock_in_report';
        $view['header'] = array("Date In"
            , "Description"
            , "Doc Ext."
            , "Batch No."
            , "Lot No."
            , "Type."
            , "Invoice No."
            , "Sum of Qty"
            , "Dimension"
            , "CBM"
            , "Sum of Total/CBM"
            , "Type UOM"
        );

        $view['statusprice'] = $this->settings['price_per_unit'];

        $view['body'] = $data_views;

        $this->load->view('report/customer/export_receive_excel', $view);
    }

    /**
     * Inventory Report for Customer
     */
    public function inventory() {
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("report/customer/form_inventory", $parameter, TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Stock Balance'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmPreDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    /**
     * Inventory Report Data for Customer
     */
    function showInventoryReport() {
        $search = $this->input->post();
        $renter_id = $this->company->getCompanyIsRenter()->result();
        $data = "";

        $search["renter_id"] = $renter_id[0]->Company_Id;
        $search["status_id"] = "";
        $search["product_id"] = "";
        if ($search["search_date"] == date("d/m/Y")) {
            $data = $this->report->invertory_stock_balance_today($search);
        } else {
            $data = $this->report->invertory_stock_balance($search);
        }

        // sum QTY and sum CBM
        $total_qty = 0;
        $total_cbm = 0;
        $total_cbm_all = 0;
        foreach ($data as $key => $value) {
            $value->totalCBM_All = "";
            $value->Product_NameEN = tis620_to_utf8($value->Product_NameEN);

            if (!empty($value->Doc_Refer_Ext) && !is_null($value->Doc_Refer_Ext)) {
                $value->Doc_Refer_Ext = tis620_to_utf8($value->Doc_Refer_Ext);
            } else {
                $value->Doc_Refer_Ext = "";
            }

            if (!empty($value->Product_Lot) && !is_null($value->Product_Lot)) {
                $value->Product_Lot = tis620_to_utf8($value->Product_Lot);
            } else {
                $value->Product_Lot = "";
            }

            if (!empty($value->Product_Serial) && !is_null($value->Product_Serial)) {
                $value->Product_Serial = tis620_to_utf8($value->Product_Serial);
            } else {
                $value->Product_Serial = "";
            }

            $value->Invoice_No = tis620_to_utf8($value->Invoice_No);
            $value->Unit_Value = tis620_to_utf8($value->Unit_Value);

            if ($value->Width > 0 && $value->Length > 0 && $value->Height > 0) {
                $value->Dimension = $value->Width . " x " . $value->Length . " x " . $value->Height;
            } else {
                $value->Dimension = "";
            }

            $value->CBM = set_number_format($value->CBM);
            $sum = (intval($value->totalbal) * floatval($value->CBM));
            $value->totalCBM_All = set_number_format($sum);

            $value->totalbal = set_number_format($value->totalbal);

            $total_qty += floatval($value->totalbal);
            $total_cbm += floatval($value->CBM);
            $total_cbm_all += floatval($value->totalCBM_All);
        }

        $view['total_qty'] = set_number_format($total_qty);
        $view['total_cbm'] = set_number_format($total_cbm);
        $view['total_cbm_all'] = set_number_format($total_cbm_all);
        $view['is_today'] = TRUE;
        $view['data'] = $data;
        $view['search'] = $search;
        echo json_encode($view);
    }

    /**
     * Inventory export excel
     */
    function exportInventoryToExcel() {


        // p($conf_stockbalance_report);exit;
        $search = $this->input->post();
        $renter_id = $this->company->getCompanyIsRenter()->result();
        $datas = array();
        if ($search["search_date"] == date("d/m/Y")) {
            $datas = $this->report->invertory_stock_balance_today($search, $renter_id[0]->Company_Id);
        } else {
            $datas = $this->report->invertory_stock_balance($search, $renter_id[0]->Company_Id);
        }

        // calculate qty x cbm
        foreach ($datas as $key => $value) {
            $value->totalCBM_All = "";
            $value->Dimension = "";
            $value->Product_NameEN = tis620_to_utf8($value->Product_NameEN);

            if (!empty($value->Doc_Refer_Ext)) {
                $value->Doc_Refer_Ext = tis620_to_utf8($value->Doc_Refer_Ext);
            } else {
                $value->Doc_Refer_Ext = "";
            }

            if (!empty($value->Product_Lot)) {
                $value->Product_Lot = tis620_to_utf8($value->Product_Lot);
            } else {
                $value->Product_Lot = "";
            }

            if (!empty($value->Product_Serial) && !is_null($value->Product_Serial)) {
                $value->Product_Serial = tis620_to_utf8($value->Product_Serial);
            } else {
                $value->Product_Serial = "";
            }

            $value->Invoice_No = tis620_to_utf8($value->Invoice_No);
            $value->Unit_Value = tis620_to_utf8($value->Unit_Value);

            if ($value->Width > 0 && $value->Length > 0 && $value->Height > 0) {
                $value->Dimension = $value->Width . " x " . $value->Length . " x " . $value->Height;
            }

            $sum = (((float) $value->totalbal) * ((float) $value->CBM));
            $value->totalCBM_All = set_number_format($sum);
            $value->CBM = set_number_format($value->CBM);
        }

        $view['header'] = array(
            'date_in' => 'Date In'
            , 'product_code' => 'Material No.'
            , 'product_name' => 'Description'
            , 'jobno' => 'Doc Ext.'
            , 'lot' => 'Batch No.'
            , 'serial' => 'Lot No.'
            , 'class' => "Type"
            , 'invoice' => _lang('invoice_no')
            , 'qty' => "Sum of Qty"
            , 'dimension' => "Dimension"
            , 'cbm' => "CBM"
            , 'sum_cbm' => "Sum of Total / CBM"
            , 'unit' => "Unit"
        );
        $results = array();
        foreach ($datas as $idx => $val) {
            $temp = array();
            $temp[] = array("align" => "center", "value" => utf8_to_tis620($val->Receive_Date));
            $temp[] = array("align" => "left", "value" => utf8_to_tis620($val->Product_Code));
            $temp[] = array("align" => "left", "value" => utf8_to_tis620($val->Product_NameEN));
            $temp[] = array("align" => "center", "value" => tis620_to_utf8($val->Doc_Refer_Ext));
            $temp[] = array("align" => "center", "value" => utf8_to_tis620($val->Product_Lot));
            $temp[] = array("align" => "center", "value" => utf8_to_tis620($val->Product_Serial));
            $temp[] = array("align" => "center", "value" => $val->DIW_Class);
            $temp[] = array("align" => "left", "value" => utf8_to_tis620($val->Invoice_No));
            $temp[] = array("align" => "right", "value" => $val->totalbal);
            $temp[] = array("align" => "center", "value" => tis620_to_utf8($val->Dimension));
            $temp[] = array("align" => "right", "value" => $val->CBM);
            $temp[] = array("align" => "right", "value" => $val->totalCBM_All);
            $temp[] = array("align" => "center", "value" => utf8_to_tis620($val->Unit_Value));
            $results[] = $temp;
        }
        $view['file_name'] = 'Stock_Balance-' . date('Ymd-His');
        $view['body'] = $results;
        // p($view);exit;
        $this->load->view('excel_StockBalance_template', $view);
    }

    /**
     * Inventory Export to PDF
     */
    function exportInventoryPdf() {
        $this->load->library('mpdf/mpdf');
        $this->load->model("company_model", "company");
        date_default_timezone_set('Asia/Bangkok');

        $search = $this->input->post();
        $renter_id = $this->company->getCompanyIsRenter()->result();
        $temp_company = $this->company->getCompanyByID($renter_id[0]->Company_Id);
        $company_name = $temp_company->result();

        $datas = array();
        if ($search["search_date"] == date("d/m/Y")) {
            $datas = $this->report->invertory_stock_balance_today($search, $renter_id[0]->Company_Id);
        } else {
            $datas = $this->report->invertory_stock_balance($search, $renter_id[0]->Company_Id);
        }

        foreach ($datas as $key => $value) {
            $value->totalCBM_All = "";
            $value->Dimension = "";
            $value->Product_NameEN = tis620_to_utf8($value->Product_NameEN);

            if (!empty($value->Doc_Refer_Ext) && !is_null($value->Doc_Refer_Ext)) {
                $value->Doc_Refer_Ext = tis620_to_utf8($value->Doc_Refer_Ext);
            } else {
                $value->Doc_Refer_Ext = "";
            }

            if (!empty($value->Product_Lot) && !is_null($value->Product_Lot)) {
                $value->Product_Lot = tis620_to_utf8($value->Product_Lot);
            } else {
                $value->Product_Lot = "";
            }

            if (!empty($value->Product_Serial) && !is_null($value->Product_Serial)) {
                $value->Product_Serial = tis620_to_utf8($value->Product_Serial);
            } else {
                $value->Product_Serial = "";
            }

            $value->Invoice_No = tis620_to_utf8($value->Invoice_No);
            $value->Unit_Value = tis620_to_utf8($value->Unit_Value);

            if ($value->Width > 0 && $value->Length > 0 && $value->Height > 0) {
                $value->Dimension = $value->Width . " x " . $value->Length . " x " . $value->Height;
            }

            $sum = (((float) $value->totalbal) * ((float) $value->CBM));
            $value->totalCBM_All = set_number_format($sum);
            $value->CBM = set_number_format($value->CBM);
        }

        $view['upload_path'] = $this->settings['uploads']['upload_path'];
        $view['search'] = $search;
        $view['datas'] = $datas;
        $view['Company_NameEN'] = $company_name[0]->Company_NameEN;
        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];
        $view['printBy'] = $printBy;
        $view['colspan'] = 9;
        $this->load->view("report/customer/report_inventory_pdf", $view);
    }

    function stock_report() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("report/customer/form_stock", $parameter, TRUE);
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Stock Report'
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmDispatch"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function show_stock_report() {
        $input = $this->input->post();
        $result = $this->report->search_stock($input);
        echo json_encode($result);
    }

    function exportStockToExcel() {
        $input = $this->input->post();
        $result = $this->report->search_stock($input);
        $view['header'] = array(
            'soe_no' => 'SOE No.'
            , 'order_id' => 'Order ID'
            , 'load_date' => 'Load Date'
            , 'container_no' => _lang("container_no")
            , 'container_size' => _lang("container_size")
            , 'product_code' => _lang("product_code")
            , 'product_name' => _lang("product_name")
            , 'lot' => _lang('lot')
            , 'date_in' => 'Date In'
            , 'storage_days' => 'Storage Days'
            , 'dispatch_qty' => _lang("dispatch_qty")
        );
        $results = array();
        foreach ($result as $idx => $value) {
            unset($value["group_head"]);
            foreach ($value as $key => $val) {
                $temp = array();
                $temp[] = array("align" => "center", "value" => utf8_to_tis620($val["Doc_Refer_Ext"]));
                $temp[] = array("align" => "center", "value" => utf8_to_tis620($val["Document_No"]));
                $temp[] = array("align" => "center", "value" => $val["Real_Action_Date"]);
                $temp[] = array("align" => "center", "value" => $val["Cont_No"]);
                $temp[] = array("align" => "center", "value" => $val["Cont_Size"]);
                $temp[] = array("align" => "center", "value" => utf8_to_tis620($val["Product_Code"]));
                $temp[] = array("align" => "center", "value" => utf8_to_tis620($val["Product_NameEN"]));
                $temp[] = array("align" => "center", "value" => utf8_to_tis620($val["Product_Lot"]));
                $temp[] = array("align" => "center", "value" => $val["Receive_Date"]);
                $temp[] = array("align" => "right", "value" => number_format($val["Storage_Day"]));
                $temp[] = array("align" => "right", "value" => set_number_format($val["DP_Qty"]));
                $results[] = $temp;
            }
        }
        $view['file_name'] = 'Stock_' . date('Ymd-His');
        $view['body'] = $results;
        $this->load->view('excel_template', $view);
    }

    function exportStockToPdf() {
        $this->load->library('mpdf/mpdf');
        date_default_timezone_set('Asia/Bangkok');

        $input = $this->input->post();
        $result = $this->report->search_stock($input);

        $view['upload_path'] = $this->settings['uploads']['upload_path'];
        $view['datas'] = $result;
        $this->load->view("report/customer/report_stock_pdf", $view);
    }

}
