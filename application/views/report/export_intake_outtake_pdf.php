<?php

if ($document_export_type == "intake") {
    $document_title = " INTAKE REPORT";
} else if ($document_export_type == "outtake") {
    $document_title = " OUTTAKE REPORT";
} else if ($document_export_type == "cross_dock") {
    $document_title = " CROSS DOCK REPORT";
}
$settings = native_session::retrieve();
$mpdf = new mPDF('th', 'A4-L', '10', '', 5, 5, 17, 9, 0, 4, "L");
$header = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 12pt; color: #000000;">
    <tr>
        <td height="30" width="200"><img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '"></td>
        <td style="text-align: center; font-weight: bold;">
            <table style="text-align: center; font-weight: bold;">
                <tr><td style="text-align: center; font-weight: bold;">' . iconv("TIS-620", "UTF-8", $document_title) . '</td></tr>
            </table>
        </td>
        <td width="200"> </td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: center; vertical-align: top; font-weight: bold;"></td>
        <td></td>
    </tr>
</table>';

$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
    <tr>
    <td width="50%" align="left">
            Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , 
            Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
            <td align="right">{PAGENO}/{nb}</td>
    </tr>
    </table>';

$footer_last = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
    <tr><td colspan="2">Remark ' . $remark . '</td></tr>
    <tr>
        <td width="50%" style="text-align: right;">.................................................... ( หัวหน้าแผนกคลังสินค้า )</td>
        <td width="50%" style="text-align: right;">.................................................... ( ผู้ช่วยผู้จัดการ / ผู้จัดการแผนกคลังสินค้า )</td>
    </tr>
    <tr>
    <td width="50%" align="left">
            Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
            Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
            <td align="right">{PAGENO}/{nb}</td>
    </tr>
    </table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer_last);

$page = '<style>
    .tborder{
        border-top:1px solid #333333;
        border-right:1px solid #333333;
    }
    .tborder td,
    .tborder th{
        border-left:1px solid #333333;
        border-bottom:1px solid #333333;
    }
</style>';

$page .= '<table width="100%" cellspacing="0" style="font-size:10pt; line-height: 12pt; font-family: arial; border: 1px solid #000;" class="tborder">
<thead>
    <tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
        <th width="15%" height="30">Date In</th>
        <th width="15%">Customer Name / Material Name</th>
        <th width="10%">Container No.</th>
        <th width="10%">Lot / Po No.</th>
        <th width="15%">Inspection Date</th>
        <th width="10%">Quantities</th>
        <th width="25%">Cargo Description</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td align="center">' . $date_in . '</td>
        <td>' . $company_name . '<br/>' . $product_name . '<br/>' . $document_external . '<br/>' . $document_invoice . '</td>
        <td>' . $container_name . '</td>
        <td>' . $product_lot . ' / ' . $po_no . '</td>
        <td align="center">' . $date_in . '</td>
        <td align="center">' . $qty . '</td>
        <td>
            <table class="noborder">
                <tr><td height="20" style="border: none;"><input type="checkbox" ' . $description_type_1 . ' /> สินค้าเสียหายจากในตู้ </td></tr>
                <tr><td height="20" style="border: none;"><input type="checkbox" ' . $description_type_2 . ' /> จำนวนสินค้าไม่ครบ </td></tr>
                <tr><td height="20" style="border: none;"><input type="checkbox" ' . $description_type_3 . ' /> อุบัติเหตุ (รายงานอุบัติเหตุ เลขที่ ' . $description_type_3_desc . ')</td></tr>
                <tr><td height="20" style="border: none;"><input type="checkbox" ' . $description_type_4 . ' /> อื่น ๆ ' . $description_type_4_desc . '</td></tr>
            </table>
        </td>
    </tr>
</tbody>
</table>';

$page .= '<table width="100%">';
$page .= '<tr><td colspan="4" height="30">Photo of Cargo</td></tr>';
$idx = 1;
$all = count($images);
$remain = ($all % 4);
foreach ($images as $i => $v) {
    $page .= ($idx % 4 == 1 ? "<tr>" : "");
    $page .= '<td width="25%"><img src="' . $v . '" height="235" /></td>';
    $page .= ($idx % 4 == 0 ? "</tr>" : "");
    $idx++;
}

if ($remain > 0) {
    for ($i = $remain; $i < 4; $i++) {
        $page .= '<td width="25%"><img src="' . base_url('images/blank.png') . '" height="235"/></td>';
    }
    $page .= "</tr>";
}

$page .= '</table>';

$this->load->helper('file');

$stylesheet = read_file('../libraries/pdf/style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->WriteHTML($page);
$filename = 'DG-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
$strSaveName = $settings['uploads']['upload_path'] . $filename;
$mpdf->Output($strSaveName, 'F');
if (!empty($email)) {

    $this->email->from('noreply@dits.co.th', 'SYSTEM');
    $this->email->to($email);
    $this->email->attach($strSaveName);
    $this->email->subject('Damage / Non Conform Report');
    $this->email->message('Auto Damage / Non Conform, \r\nPlease see attach file for your request report.');

    if ($this->email->send()) {
        echo "Send email to " . $email . ", Thank you";
    } else {
        echo "ERROR <br/>";
        echo $this->email->print_debugger();
    }
} else {

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    readfile($strSaveName);
}