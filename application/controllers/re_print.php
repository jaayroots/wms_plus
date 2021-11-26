<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class re_print extends CI_Controller {

    public $settings;       //add by kik : 20140114

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $arr_page = array(
            'picking_job' => 'Picking Job',
            'putaway_job' => 'Putaway Job'
        );

        $site_url = site_url();
        echo '<h3>LIST</h3>';
        foreach ($arr_page as $key_page => $page):
            echo "<a href='{$site_url}/re_print/{$key_page}'>{$page}</a><br>";
        endforeach;
    }

    public function picking_job() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['form_name'] = 'picking_job';
        $parameter['action_pdf'] = 'report/export_picking_pdf';
        $parameter['action_excel'] = '';

        $button_cancel = '';
        $button_action = '';
        if ($parameter['action_pdf'] != ''):
            $button_cancel = '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" onClick="exportFile(' . "'PDF'" . ')"  />';
        endif;
        if ($parameter['action_excel'] != ''):
            $button_action = '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" onClick="exportFile(' . "'EXCEL'" . ')" />';
        endif;

        $str_form = $this->parser->parse("form_re_print", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Re-Print Picking Job'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => ''
            , 'button_back' => ''
            , 'button_cancel' => $button_cancel
            , 'button_action' => $button_action
        ));
    }

    public function putaway_job() {
        $this->output->enable_profiler($this->config->item('set_debug')); // Add By Akkarapol, Set ให้ $this->output->enable_profiler เป็น ค่าตามที่เซ็ตมาจาก config เพื่อใช้แสดง DebugKit

        $parameter['form_name'] = 'putaway_job';
        $parameter['action_pdf'] = 'report/export_putaway_pdf';
        $parameter['action_excel'] = '';

        $button_cancel = '';
        $button_action = '';
        if ($parameter['action_pdf'] != ''):
            $button_cancel = '<input type="button" value="Export To PDF" class="button dark_blue" id="pdfshow" onClick="exportFile(' . "'PDF'" . ')"  />';
        endif;
        if ($parameter['action_excel'] != ''):
            $button_action = '<input type="button" value="Export To Excel" class="button dark_blue" id="excelshow" onClick="exportFile(' . "'EXCEL'" . ')" />';
        endif;

        $str_form = $this->parser->parse("form_re_print", $parameter, TRUE);

        # PUT FORM IN TEMPLATE WORKFLOW  
        $this->parser->parse('workflow_template', array(
            'state_name' => 'Re-Print Putaway Job'
            , 'menu' => $this->menu_auth->loadMenuAuth()// Get Menu by Permission. Edit by Ton! 20140123
            , 'form' => $str_form
            , 'toggle' => ''
            , 'button_back' => ''
            , 'button_cancel' => $button_cancel
            , 'button_action' => $button_action
        ));
    }

}