<?php
$order_by = $_GET['o'];
date_default_timezone_set('Asia/Bangkok') ;
$settings = native_session::retrieve();
$mpdf=new mPDF('th','A4-L','11','',5,5,20,25,5,5); 

$header = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 16px; color: #000000;">
	<tr>
		<td height="30" width="200"><img style="width:200px;height:auto;" alt="' . $this->config->item('logo_alt') . '" src="' . $this->config->item('logo_patch') . '"></td>
		<td style="text-align: center;font-weight: bold;">Counting Report '.date("M Y").'</td>
		<td width="200"> </td>
	</tr>
</table>';
		
$footer = '<table width="100%" style="vertical-align: bottom; font-family: arial; font-size: 9pt; color: #666666;">
	<tr>
		<td width="50%" align="left">Print By : ' . iconv("TIS-620", "UTF-8", $printBy) . '  , Date : ' . date("d M Y") . ' Time : ' . date("H:i:s") . '</td>
		<td align="right">{PAGENO}/{nb}</td>
	</tr>
</table>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLHeader($header,'E');
$mpdf->SetHTMLFooter($footer);
$mpdf->SetHTMLFooter($footer,'E');

		# head_table is header of table
		$head_table='<style>
			.tborder{
				/*border:1px solid #333333;*/
				border-top:1px solid #333333;
				border-right:1px solid #333333;
			}

			.tborder td,
			.tborder th{
				/*border-top:1px solid #333333;*/
				border-left:1px solid #333333;
				border-bottom:1px solid #333333;
				font-size:10px;
			}
		  </style>
<table width="100%" cellspacing="0" style="border:1px solid #666666;font-size:10px;font-family: arial;" class="tborder">
	<thead>';
		
		if ($order_by == "location") {
			$head_table .= '<tr style="border:1px solid #333333;">
			<th width="7%" height="35">'._lang('no').'</th>
			<th width="10%">'._lang('product_code').'</th>
			<th width="50%">'._lang('product_name').'</th>
			<th width="5%">'._lang('booked').'</th>
			<th width="5%">'._lang('dispatch').'</th>
			<th width="5%">'._lang('system').'</th>
			<th width="5%">'._lang('physical').'</th>
			</tr>';
		} else if ($order_by == "sku") {
			$head_table .= '<tr style="border:1px solid #333333;">
			<th width="7%" height="35">'._lang('no').'</th>
			<th width="10%" colspan="2">'._lang('location_code').'</th>			
			<th width="5%">'._lang('booked').'</th>
			<th width="5%">'._lang('dispatch').'</th>
			<th width="5%">'._lang('system').'</th>
			<th width="5%">'._lang('physical').'</th>
			</tr>';
		}
		
		$head_table .= '</thead><tbody>';
		
		$page='';
		$page.=$head_table;
		
		// Create temp
		$idx_key = 0;
		$pdfArray = Array();
		$total_booked = 0;
		$total_dispatch = 0;
		$total_qty = 0;
						
		foreach($data as $key => $row) {
			
			if ($order_by == "location") {
				$pdfArray[$row->Location_Code][$row->Product_Code]['name'] = $row->Product_NameEN;
				$pdfArray[$row->Location_Code][$row->Product_Code]['booked'] += $row->booked;
				$pdfArray[$row->Location_Code][$row->Product_Code]['dispatch'] += $row->dispatch;
				$pdfArray[$row->Location_Code][$row->Product_Code]['qty'] += $row->QTY;				
			} else if ($order_by == "sku") {
				$pdfArray[$row->Product_Code]['0'] = iconv("TIS-620", "UTF-8//IGNORE", $row->Product_NameEN);
				$pdfArray[$row->Product_Code][$row->Location_Code]['name'] = "";
				$pdfArray[$row->Product_Code][$row->Location_Code]['booked'] += $row->booked;
				$pdfArray[$row->Product_Code][$row->Location_Code]['dispatch'] += $row->dispatch;
				$pdfArray[$row->Product_Code][$row->Location_Code]['qty'] += $row->QTY;				
			}

		}
		
				
		foreach ($pdfArray as $location_key => $products) {

			// If get order by is sku show name of product in this head.
			$description = ($order_by == "sku" ? $products['0'] : "");

			$page .= '<tr><td colspan="7">'.$location_key . ' : ' .$description. '</td></tr>';
			
			unset($products['0']);
			
			foreach ($products as $product_key => $product_data) {
				
				
				
				$page.='<tr>';
				$page.='<td align="center">'.($index+1).'</td>';
				if ($order_by == "sku") {
					$page.='<td align="center" colspan="2" align="left">'.$product_key.'</td>';
				} else if ($order_by == "location") {
					$page.='<td align="center">'.$product_key.'</td>';
					$page.='<td>'.iconv("TIS-620", "UTF-8//IGNORE", $product_data["name"]).'</td>';						
				}
				$page.='<td style="text-align:right;">'.set_number_format($product_data["booked"]).'</td>';
				$page.='<td style="text-align:right;">'.set_number_format($product_data["dispatch"]).'</td>';
				$page.='<td style="text-align:right;">'.set_number_format($product_data["qty"]).'</td>';
				$page.='<td style="text-align:right;"></td>';
				$page.='</tr>';
				
				$total_booked += $product_data["booked"];
				$total_dispatch += $product_data["dispatch"];
				$total_qty += $product_data["qty"];
												
				$index++;
			}
		}

		// SUMMARY 
		$page.='<tr>';
		$page.='<td align="left" colspan="3">Total</td>';
		$page.='<td style="text-align:right;">'.set_number_format($total_booked).'</td>';
		$page.='<td style="text-align:right;">'.set_number_format($total_dispatch).'</td>';
		$page.='<td style="text-align:right;">'.set_number_format($total_qty).'</td>';
		$page.='<td style="text-align:right;"></td>';
		$page.='</tr>';		
		
		if($index!=0){
		   $page.='
				</tbody>
			</table>';
		}	
        		
		$this->load->helper('file');
		$stylesheet = read_file('../libraries/pdf/style.css');
		$mpdf->WriteHTML($stylesheet, 1);
		//		$page = iconv("TIS-620","UTF-8",$page);
		$mpdf->WriteHTML($page);
//		$mpdf->Output($strSaveName, 'I'); // *** file name

                $filename = 'Counting-Report-' . date('Ymd') . '-' . date('His') . '.pdf';
                $strSaveName = $settings['uploads']['upload_path'] . $filename;
                $tmp = $mpdf->Output($strSaveName, 'F'); 
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . $filename . "\"");
                readfile($strSaveName); 
		