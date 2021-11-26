<?php
set_time_limit(0);
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,30,25,5,5);
#mPDF(mode,format,default_font_size,default_font,margin_left,margin_right,margin_top,margin_bottom,margin_header,margin_footer)
	$header = '

		<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
			<tr>
				<td height="30" width="200">
                                    <img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '">
                                </td>
                                <td style="text-align: center;font-weight: bold;">LOCATION REPORT</td>
				<td width="200"> </td>
			</tr>
			<tr>
				<td align="center" height="10"></td>
			</tr>
		</table>

		<table width="100%" style="font-size: 14px;font-family: arial;">
			<tr>
				<td width="15%" height="25" align="right">ผุ้เช่าคลัง : </td>
				<td width="50%">'.$Company_NameEN.'</td>
				<td width="15%" align="right">วันที่ : </td><td>'.date('d/m/Y').'</td>
			</tr>
		</table>

		';


		$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  ,
                                                Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
						<td align="right">{PAGENO}/{nb}</td>
					</tr>
					</table>';

		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLHeader($header,'E');
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetHTMLFooter($footer,'E');

		$page='';
		$count_row=count($datas);

		# calculate page number
		# max_line is number of table row in 1 page

        ########## start loop show page ########
		# head_table is header of table
			$head_table='
			<style>
				.tborder{
					border:1px solid #333333;
				}
	
				.tborder td,
				.tborder th{
					font-size:10px;
					border-right:1px solid #333333;
					border-bottom:1px solid #333333;
					padding:5px 2px 5px 2px;
				}
				.tdData {
					font-size:10px;
					border-right:1px solid #333333;
					border-bottom:1px solid #333333;
					padding:5px 2px 5px 2px;
					text-align:center;
				}
				table.tborder thead tr th{
					border-bottom:1px solid #666666;
				}
			</style>
	
			  <table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
				<thead>
					<tr style="border:1px solid #333333;" bgcolor="#B0C4DE">
						<th width="3%" height="30">'._lang('no').'</th>
						<th width="7%">'._lang('product_code').'</th>
						<th width="15%">'._lang('product_name').'</th>
						<th width="7%">'._lang('product_status').'</th>
						<th width="7%">'._lang('total').'</th>
						<th width="7%">'._lang('RSV').'</th>
						<th width="7%">'._lang('Pre-Dispatch').'</th>
						<th width="7%">'._lang('Remain').'</th>
						<th width="7%">'._lang('unit').'</th>
						<th width="7%">'._lang('uom_qty').'</th>
						<th width="7%">'._lang('uom_unit_prod').'</th>
					</tr>
				</thead>
				<tbody>	';
			
		$page.=$head_table;

		  if(!empty($datas)){ //p($datas);exit();
                        $sum_bal = 0;
                        $sum_booked = 0;
                        $sum_dispatch = 0;
                        $sum_remain = 0;
                        $sum_uom_qty = 0;
                        foreach($datas as $key => $data){ // p($data);exit();

                            $page .= '<tr>';
								$page.='<td colspan="3" height="23" valign="top" class="tdData" style="text-align:left"><b>'.$key.'</b></td>';
								$page.='<td valign="top" class="tdData" style="text-align:right">'.$val['Product_Status'].'</td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"><b>'.set_number_format($data['sum_balance']).'</b></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"><b>'.set_number_format($data['sum_booked']).'</b></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"><b>'.set_number_format($data['sum_dispatch']).'</b></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"><b>'.set_number_format($data['sum_remain']).'</b></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"><b>'.set_number_format($data['sum_uom_qty']).'</b></td>';
                                $page.='<td valign="top" class="tdData" style="text-align:right"></td>';
                            $page .= '</tr>';

                            $sum_bal += $data['sum_balance'];
                            $sum_booked += $data['sum_booked'];
                            $sum_dispatch += $data['sum_dispatch'];
                            $sum_remain += $data['sum_remain'];
                            $sum_uom_qty += $data['sum_uom_qty'];
                            $i=1;
                            foreach ($data['prod'] as $val)://p($val);exit();

                                $page .= '<tr>';
                                    $page.='<td height="23" valign="top" class="tdData" style="text-align:left">'.$i++.'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:left">'.$val['product_code'].'</td>';
									$page.='<td valign="top" class="tdData" style="text-align:left">'.iconv("TIS-620","UTF-8",$val['product_name']).'</td>';
									$page.='<td valign="top" class="tdData" style="text-align:right">'.$val['product_status'].'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.set_number_format($val['balance_qty']).'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.set_number_format($val['booked_qty']).'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.set_number_format($val['dispatch_qty']).'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.set_number_format($val['remain_qty']).'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.$val['unit_value'].'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.set_number_format($val['uom_qty']).'</td>';
                                    $page.='<td valign="top" class="tdData" style="text-align:right">'.$val['uom_unit_val'].'</td>';
                                $page .= '</tr>';

                            endforeach;
                        }

                        $page .= '<tr bgcolor="#F5F5F5">
                                    <td colspan="4" class="tdData"><b>Total Balance</b></td>
                                    <td  align="right" class="tdData"><b>'.set_number_format($sum_bal).'</b></td>
                                    <td  align="right" class="tdData"><b>'.set_number_format($sum_balance_all).'</b></td>
                                    <td  align="right" class="tdData"><b>'.set_number_format($sum_dispatch).'</b></td>
                                    <td  align="right" class="tdData"><b>'.set_number_format($sum_remain).'</b></td>
                                	<td  align="right" class="tdData"></td>
                                	<td  align="right" class="tdData"><b>'.set_number_format($sum_uom_qty).'</b></td>
                                	<td  align="right" class="tdData"></td>
                                  </tr>';

		  }

                    $page.='</tbody>';

		$page.='</table>';

//              echo $page;exit();

		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($page);

                $filename = 'Location-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F');
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName);
//
?>