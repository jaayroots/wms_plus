<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="'.$file_name.'.xls"');# �������
header("Pragma: no-cache");
header("Expires: 0");
set_time_limit(5000);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<table x:str BORDER="1">
<?php // p($data) ;?>
    <thead>
        <tr>
            <th><?php echo _lang('product_code'); ?></th>
            <th><?php echo _lang('product_name'); ?></th>
            <th><?php echo _lang('product_status'); ?></th>
            <th><?php echo _lang('lot'); ?></th>
            <th><?php echo _lang('serial'); ?></th>
            <th><? echo $doc_ref_td; ?></th>
            <?php if($this->config->item('build_pallet')): ?>
            <th><?php echo _lang('pallet_code'); ?></th>
            <?php endif; ?>
            <th><?php echo _lang('date'); ?></th>
            <th><?php echo _lang('location'); ?></th>
            <th><?php echo _lang('qty'); ?></th>
            <?php if($statusprice):?>
            <th><?php echo _lang('price_per_unit'); ?></th>
            <th><?php echo _lang('unit_price'); ?></th>
            <th><?php echo _lang('all_price'); ?></th>
            <?php endif; ?>
            <th><?php echo _lang('date'); ?></th>
            <th><?php echo _lang('location'); ?></th>
            <th><?php echo _lang('qty'); ?></th>
            <th><?php echo _lang('date'); ?></th>
            <th><?php echo _lang('location'); ?></th>
            <th><?php echo _lang('qty'); ?></th>
            <th><?php echo _lang('date'); ?></th>
            <th><?php echo _lang('location'); ?></th>
            <th><?php echo _lang('qty'); ?></th>
            <th><?php echo _lang('date'); ?></th>
            <th><?php echo _lang('location'); ?></th>
            <th><?php echo _lang('qty'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
$i=1;
$doc_ref_td = $condition_value["doc_type"];
foreach( (array) $data as $cols){
?>
        <tr>
            <td ><?php echo $cols->Product_Code;?></td>
            <td style="text-align: left;"><?php echo $this->conv->tis620_to_utf8($cols->Product_NameEN) ?></td>
            <td style="text-align:center;"><?php echo $cols->Product_Status ?></td>
            <td style="text-align:center;"><?php echo $cols->Product_Lot ?></td>
            <td style="text-align:center;"><?php echo $cols->Product_Serial ?></td>
            <td style="text-align:center;" id="doc_ref_td"> <?php echo $cols->doc_type; ?></td>
            <?php if($this->config->item('build_pallet')): ?>
            <td style="text-align:center;"><?php echo $cols->Pallet_Code ?></td>
            <?php endif; ?>
            <td style='mso-number-format:"Short Date";background-color: #FFFFCC;text-align:center;'><?php echo $cols->currentdate ?></td>
            <td style="background-color: #FFFFCC;text-align:center;"><?php echo $cols->actual_now ?></td>
            <td style="background-color: #FFFFCC;text-align:right;"><?php echo set_number_format($cols->current_qty) ?></td>
            <?php if($statusprice):?>
            <td style="background-color: #FFFFCC;text-align:right;"><?php echo  (!empty($cols->current_price))?set_number_format($cols->current_price):''; ?></td>
            <td style="background-color: #FFFFCC;"><?php echo (!empty($cols->current_Unit_price))?$cols->current_Unit_price:''; ?></td>
            <td style="background-color: #FFFFCC;text-align:right;"><?php echo (!empty($cols->current_All_price))?set_number_format($cols->current_All_price):''; ?></td>
            <?php endif; ?>
            <td style='mso-number-format:"Short Date";background-color: #FFEFD5;text-align:center;'><?php echo $cols->date4 ?></td>
            <td style="background-color: #FFEFD5;text-align:center;"><?php echo $cols->actual4 ?></td>
            <td style="background-color: #FFEFD5;text-align:right;"><?php echo set_number_format($cols->qty4); ?></td>
            <td style='mso-number-format:"Short Date";background-color: #FFF5EE;text-align:center;'><?php echo $cols->date3 ?></td>
            <td style="background-color: #FFF5EE;text-align:center;"><?php echo $cols->actual3 ?></td>
            <td style="background-color: #FFF5EE;text-align:right;"><?php echo set_number_format($cols->qty3); ?></td>
            <td style='mso-number-format:"Short Date";background-color: #FFFACD;text-align:center;'><?php echo $cols->date2 ?></td>
            <td style="background-color: #FFFACD;text-align:center;"><?php echo $cols->actual2 ?></td>
            <td style="background-color: #FFFACD;text-align:right;"><?php echo set_number_format($cols->qty2); ?></td>
            <td style='mso-number-format:"Short Date";background-color: #FFFFAA;text-align:center;'><?php echo $cols->date1 ?></td>
            <td style="background-color: #FFFFAA;text-align:center;"><?php echo $cols->actual1 ?></td>
            <td style="background-color: #FFFFAA;text-align:right;"><?php echo set_number_format($cols->qty1); ?></td>
            <?php $i++; } ?>
        </tr>
    </tbody>
</table>