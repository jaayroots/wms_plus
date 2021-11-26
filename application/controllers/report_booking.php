<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Report_booking extends CI_Controller {
 
    public $settings;
    public $revision;

    public function __construct() {
        parent::__construct();
        $this->settings = native_session::retrieve();
        $this->load->library('encrypt');
        $this->load->library('session');
        $this->load->library('pagination_ajax');
        $this->load->helper('form');
        $this->load->model("company_model", "company");
        $this->load->model("report_booking_model", "r");
        
        $this->config->set_item('renter_id', $this->session->userdata('renter_id'));
        $this->config->set_item('owner_id', $this->session->userdata('owner_id'));

        $status = @shell_exec('svnversion ' . realpath(__FILE__));
        if (preg_match('/\d+/', $status, $match)) {
            $this->revision = $match[0];
        }
    }

	/**
	*
	* Booking Controller
	*/
    function booking_report() {
        $this->output->enable_profiler($this->config->item('set_debug'));
        $parameter['renter_id'] = $this->config->item('renter_id');

        $q_renter = $this->company->getRenterAll();
        $r_renter = $q_renter->result();
        $renter_list = genOptionDropdown($r_renter, "COMPANY", FALSE, FALSE);
        $parameter['renter_list'] = $renter_list;

        $parameter['user_id'] = $this->session->userdata('user_id');
        $str_form = $this->parser->parse("form/booking_form_report", $parameter, TRUE);

        $this->parser->parse('workflow_template', array(
            'state_name' => 'Report : Booking '
            , 'menu' => $this->menu_auth->loadMenuAuth()
            , 'form' => $str_form
            , 'toggle' => '<i class="icon-minus-sign icon-white toggleForm" data-target="frmReceive"></i>'
            , 'button_back' => ''
            , 'button_cancel' => '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" style="display:none;" onClick="exportFile(' . "'PDF'" . ')"  />'
            , 'button_action' => '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" style="display:none;" onClick="exportFile(' . "'EXCEL'" . ')" />'
        ));
    }

    function booking_query_Report() {
        $search = $this->input->get();
        $pages = new Pagination_ajax;
        $num_rows = sizeof($this->r->search_booking($search));
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

        $view['data'] = $this->r->search_booking($search);
        $view['pagination'] = $pages->display_pages();
        $view['search'] = $search;

        $this->load->view("report/booking_report.php", $view);
    }

	//=====แสดงรายงาน PDF
    function export_booking2PDF() {
        date_default_timezone_set('Asia/Bangkok');
        $this->load->library('mpdf/mpdf');
        $search = $this->input->post();
        $conf = $this->config->item('_xml');
        $conf_show_column = empty($conf['show_column_report']['object']['booking_pdf']) ? array() : @$conf['show_column_report']['object']['booking_pdf'];

        $column_result = colspan_report($conf_show_column, $conf);
        $view['all_column'] = $column_result['all_column'];
        $view['colspan'] = $column_result['colspan_all'];
        $view['show_hide'] = json_decode($column_result['show_hide']);
        $view['set_css_for_show_column'] = $column_result['set_css_for_show_column'];

        $report = $this->r->search_booking_pdf($search);
        $view['datas'] = $report;
        foreach ($report as $keyRow => $data) {
            $view['Prepared_by'] = $data[0]['Create_By_Name'];
            break;
        }
        $doc_type = $this->input->post("doc_type");
//        $docval = $this->conv->tis620_to_utf8($this->input->post("doc_value"));

        if ($doc_type == 'Document_No'):
            $doc_type = "Document No";
        elseif ($doc_type == 'Doc_Refer_Ext'):
            $doc_type = "Refer External No.";
        elseif ($doc_type == 'Doc_Refer_Int'):
            $doc_type = "Refer Internal No.";
        elseif ($doc_type == 'Doc_Refer_Inv'):
            $doc_type = "Invoice No.";
        elseif ($doc_type == 'Doc_Refer_CE'):
            $doc_type = "Customs Entry";
        elseif ($doc_type == 'Doc_Refer_BL'):
            $doc_type = "BL No.";
        endif;

        $view['doc_type'] = $doc_type;
        $view['doc_value'] = (!empty($docval) || $docval != "") ? $docval : "-"; 
        $renter_detail = $this->company->getRenterAll($this->input->post("renter_id"))->result();
        $view['renter_name'] = $renter_detail[0]->Company_NameEN;
        $view['renter_code'] = $renter_detail[0]->Company_Code;

        $this->load->model("user_model", "user");
        $detail = $this->user->getDetailByUserId($this->session->userdata('user_id'));
        $printBy = $detail['First_NameEN'] . ' ' . $detail['Last_NameEN'];

        $view['printBy'] = $printBy;

        $revision = $this->revision;
        $view['revision'] = $revision;

        if (!empty($search['showfooter'])):
            $view['showfooter'] = $search['showfooter'];
        else:
            $view['showfooter'] = 'no';
        endif;

        $view['signature_report'] = empty($conf['signature_report']['receive_report']) ? array() : @$conf['signature_report']['receive_report'];
        $view['statusprice'] = $this->settings['price_per_unit'];
        $this->load->view("report/exportBookingReport", $view);
    }

//=====แสดงรายงาน EXCEL
    function export_booking2Excel() {

        $search = $this->input->post();

        /*
         * add get method use for send dairy email report
         */
        if (empty($search)):
            $search = $this->input->get();
        endif;

        $datas = $this->r->search_booking($search);
        $report_group = array();
        
        if (!empty($datas)) {
            foreach ($datas as $k => $columns) {
            
                $columns = (Array) $columns;
                
                $report['Running_No'] = $k + 1;
                $report['Product_Code'] = $columns['Product_Code'];
                $report['Product_NameEN'] = $columns['Product_NameEN'];
                $report['Product_Status'] = $columns['Product_Status'];
                $report['Product_Sub_Status'] = $columns['Product_Sub_Status'];
                $report['Receive_Date'] = $columns['Receive_Date'];
                $report['Doc_Refer_CE'] = $columns['Doc_Refer_CE'];
                $report['Customs_Sequence'] = $columns['Customs_Sequence'];
                $report['HS_Code'] = $columns['HS_Code'];                
                $report['Product_Lot'] = $columns['Product_Lot'];
                $report['Product_Serial'] = $columns['Product_Serial'];
                $report['Invoice_No'] = $columns['Invoice_No'];
                $report['PD_Reserv_Qty'] = $columns['PD_Reserv_Qty'];
                $report['Unit_Value'] = $columns['name'];
		$report['Price_Per_Unit'] = $columns['PD_Reserv_Qty'] * $columns['Price_Per_Unit'];
                $report['Pallet_Code'] = $columns['Pallet_Code'];
                $report['Dispatch'] = $columns['Dispatch'];
                $report['remark'] = $columns['Remark'];
                $report_group[] = $report;
            }
        }

        $view['file_name'] = 'booking_report';
        $view['body'] = $report_group;

        $view['header'] = array(
            _lang('no')
            , _lang('product_code')
            , _lang('product_name')
            , _lang('product_status')
            , _lang('product_sub_status')
            , _lang('receive_date')
            , _lang('custom_entry')
            , _lang('custom_sequence')
            , _lang('hs_code')
            , _lang('lot')
            , _lang('serial')
            , _lang('invoice_no')
            , _lang('pd_reserv_qty')
            , _lang('unit')
	    , _lang('price_per_unit')
            , _lang('pallet_code')
            , _lang('remark')
        );
        
        $this->load->view('excel_template', $view);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
