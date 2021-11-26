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
                                <td style="text-align: center;font-weight: bold;">INVENTORY REPORT</td>
				<td width="200"> </td>
			</tr>
			<tr>
				<td align="center" height="10"></td>
			</tr>
		</table>

		<table width="100%" style="font-size: 14px;font-family: arial;">
			<tr>
				<td width="15%" height="25" align="right">ผู้เช่าคลัง : </td>
				<td width="50%">'.$Company_NameEN.'</td>
				<td width="15%" align="right">วันที่ : </td><td>'.date('d/m/Y').'</td>
			</tr>
		</table>

		';


		$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
					<tr>
                                        <td width="50%" align="left">
                                                Print By : ' . tis620_to_utf8($printBy) . '  ,
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
                        '.$set_css_for_show_column.'
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
					<div class="product_code"><th width="7%">'._lang('product_code').'</th></div>
					<div class="product_name"><th width="15%">'._lang('product_name').'</th></div>
					<div class="lot"><th width="7%">'._lang('lot').'</th></div>
					<div class="serial"><th width="7%">'._lang('serial').'</th></div>
					<div class="product_mfd"><th width="7%">'._lang('product_mfd').'</th></div>
					<div class="product_exp"><th width="7%">'._lang('product_exp').'</th></div>
                                        <div class="invoice"><th width="7%">'._lang('invoice_no').'</th></div>
                                        <div class="container"><th width="7%">'._lang('container').'</th></div>
                                        <div class="pallet_code"><th  width="7%">'._lang('pallet_code').'</th></div>';



			foreach ($ranges as $range){
				$head_table.= '<th bgcolor="#CDCDB4" width="5%" >'.$range.'</th>';
			}

                            $head_table .= '
                                            <th width="7%">'._lang('Root Unit').'</th>
                                            <th width="7%">'._lang('total').'</th>
                                            <th width="7%">'._lang('booked').'</th>
                                            <th width="7%">'._lang('dispatch_qty').'</th>

                            ';
                        $head_table.='<div class="unit"><th width="7%">'._lang('unit').'</th></div>
                                      <div class="uom_qty"><th  width="7%">'._lang('uom_qty').'</th></div>
                                      <div class="uom_unit_prod"><th width="7%">'. _lang('uom_unit_prod').'</th></div>
                                 </tr>';

		$head_table.='
			</thead>
			<tbody>	';

		$page.=$head_table;

		  if(!empty($datas)){

		  $sum_balance=array();

                  $sum_balance_all=0;
                  $sum_booked_all=0;
                  $sum_dispatch_all=0;
                  $sum_qty_uom_all=0;
		  $i=1;
//                  p($datas);exit();
				foreach($datas as $key=>$data){

					$page.="<tr>";
					$page.='<td height="23" valign="top" class="tdData" style="text-align:center">'.$i.'</td>';
					$page.='<div class="product_code"><td class="tdData" style="text-align:center">'.$data->Product_Code.'</td></div>';
					$page.='<div class="product_name"><td class="tdData" style="text-align:left">'.tis620_to_utf8($data->Product_NameEN).'</td></div>';
					$page.='<div class="lot"><td class="tdData" style="text-align:center">'.tis620_to_utf8($data->Product_Lot).'&nbsp;</td></div>';
					$page.='<div class="serial"><td class="tdData" style="text-align:center">'.tis620_to_utf8($data->Product_Serial).'&nbsp;</td></div>';
					$page.='<div class="product_mfd"><td class="tdData" style="text-align:center">'.$data->Product_Mfd.'&nbsp;</td></div>';
					$page.='<div class="product_exp"><td class="tdData" style="text-align:center">'.$data->Product_Exp.'&nbsp;</td></div>';
                                        $page.='<div class="invoice"><td class="tdData" style="text-align:center">'.$data->Invoice_No.'&nbsp;</td></div>';
                                        $page.='<div class="container"><td class="tdData" style="text-align:center">'.tis620_to_utf8($data->Cont).'&nbsp;</td></div>';
                                        $page.='<div class="pallet_code"><td class="tdData" style="text-align:center">'.tis620_to_utf8($data->Pallet_Code).'&nbsp;</td></div>';

					$n=1;
					foreach($ranges as $key_index => $range){
						$balance='counts_'.$n;

						$sum_balance[$n]+=$data->{$balance};
                                                $sum_balance_all+=$data->{$balance}; //ADD BY POR 2013-11-12 à¹€à¸žà¸´à¹ˆà¸¡ BALANCE à¸—à¸¸à¸ product

                                                $num_balance=($data->{$balance}!=0)?set_number_format($data->{$balance}):'';

						$page.='<td class="tdData" style="text-align:right">'.$num_balance.'</td>';
						$n++;
					}
                                        $page.='<td class="tdData" style="text-align:right">'.set_number_format($data->PKG*$data->totalbal ).tis620_to_utf8($data->Unit_Value).'&nbsp;</td>';
                                        $page.='<td class="tdData" style="text-align:right">'.set_number_format($data->totalbal).'&nbsp;</td>';
                                        $page.='<td class="tdData" style="text-align:right">'.set_number_format($data->Booked).'&nbsp;</td>';
                                        $page.='<td class="tdData" style="text-align:right">'.set_number_format($data->Dispatch).'&nbsp;</td>';

                                        $page.='<div class="unit"><td class="tdData" style="text-align:right">'.tis620_to_utf8($data->Unit_Value).'&nbsp;</td></div>';
                                        $page.='<div class="uom_qty"><td class="tdData" style="text-align:right">'.set_number_format($data->Uom_Qty).'&nbsp;</td></div>';
                                        $page.='<div class="uom_unit_prod"><td class="tdData" style="text-align:right">'.tis620_to_utf8($data->Uom_Unit_Val).'&nbsp;</td></div>';

					$page.="</tr>";

					$i++;
                                        $sum_booked_all     += $data->Booked;
                                        $sum_dispatch_all   += $data->Dispatch;
                                        $sum_qty_uom_all    += $data->Uom_Qty;
                                        $sum_root_unit    += $data->PKG*$data->totalbal;
				}
		  }


		$page.='
						<tr bgcolor="#F5F5F5">
							<td colspan="'.($colspan[1]).'" class="tdData"><b>Total Balance</b></td>';

							foreach($sum_balance as $sb){

								$page.='<td class="tdData"  style="text-align:right"><b>'.set_number_format($sb).'</b></td>';
							}
		$page.='
                                                        <td  align="right" class="tdData"><b>'.set_number_format($sum_root_unit).'</b></td>
                                                        <td  align="right" class="tdData"><b>'.set_number_format($sum_balance_all).'</b></td>
                                                        <td  align="right" class="tdData"><b>'.set_number_format($sum_booked_all).'</b></td>
                                                        <td  align="right" class="tdData"><b>'.set_number_format($sum_dispatch_all).'</b></td>
                                                        ';
                $page.='                                <div class="unit"><td  class="tdData" style="text-align:right;width: 80px;"> </td></div>
                                                        <div class="uom_qty"><td  class="tdData" style="text-align:right;width: 100px;"><b>'.set_number_format($sum_qty_uom_all).'</b></td></div>
                                                        <div class="uom_unit_prod"><td class="tdData" style="text-align:right;width: 80px;"> </td></div>';

						$page.='</tr>';
                    $page.='</tbody>';

		$page.='</table>';
//                     echo $page;exit();
		$this->load->helper('file');
		$stylesheet =  read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($page);

                $filename = 'Inventory-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F');
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName);
//
?>