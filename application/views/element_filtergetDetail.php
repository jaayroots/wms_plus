<form id="formProductCode" action="" method="post">
    <tr>
        <td class="Stitle">
            <?php echo _lang('product_code') ?> :

            <?php // if (@$this->settings['pre_dispatch_auto_qty']): ?>
            <!--<BR/> QTY :-->
            <?php // endif; ?>

            <?php
                $width_set_product_code = " width: 200px;";
                if (@$this->settings['pre_dispatch_auto_qty']):
                    if($this->router->class == 'pre_dispatch'):
                        $width_set_product_code = " width: 130px;";
                    else:
                        $this->settings['pre_dispatch_auto_qty'] = FALSE;
                    endif;
                endif;
            ?>
        </td>
        <td class="Stxt">
            <table>
                <tr>
                    <td width="220">
                        <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; margin-left: 10px; padding: 0px; <?php echo $width_set_product_code; ?>">
                            <div style="position: relative; <?php echo $width_set_product_code; ?>">
                                <?php echo form_input("productCode", "", "id='productCode' placeholder='" . _lang('product_code') . "' autocomplete='off' style='border: none; padding: 4px 6px; margin: 0px; height: auto; {$width_set_product_code}  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='padding: 4px 6px; margin: 0px; height: auto; {$width_set_product_code} position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                            </div>

                        </table>
                        <?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
                            <input type="text" placeholder="<?php echo _lang('qty'); ?>" class="validateNumeric" id="qty_of_sku" style="float: left; width:35px; margin-left: 10px;" /> <a role="button" class="btn" style="display: none;" onClick="$('#formProductCode').submit();" >Get</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <input type="hidden" id="product_id" name="product_code" />
        </td>
        <td class="Stitle">
            <?php echo _lang('product_status') ?> :
        </td>
        <td class="Stxt">
            {productStatus_select}
        </td>
        <td class="Stitle">
            <?php echo _lang('product_sub_status') ?> :
        </td>
        <td class="Stxt">
            {productSubStatus_select}
        </td>
        <td class="Stitle" style="width:70px;">
            <?php echo _lang('lot'); ?> :
        </td>
        <td class="Stxt">
            <input type="text" name="productLot" id="productLot" class="neditable-input" placeholder="<?php echo _lang('lot'); ?>">
        </td>
    </tr>
    <tr style="text-align:left; height: 34px;">
        <td class="Stitle">
            <?php echo _lang('serial'); ?> :
        </td>
        <td class="Stxt">
            <input type="text" name="productSerial" id="productSerial" class="neditable-input" placeholder="<?php echo _lang('serial'); ?>">
        </td>
        <td class="Stitle">
            <?php echo _lang('product_mfd'); ?> :
        </td>
        <td class="Stxt">
            <input type="text" name="productMfd" id="productMfd" class="neditable-input" placeholder="<?php echo _lang('product_mfd'); ?>" style="width: 180px;">
        </td>
        <td class="Stitle">
            <?php echo _lang('product_exp'); ?> :
        </td>
        <td class="Stxt">
            <input type="text" name="productExp" id="productExp" class="neditable-input" placeholder="<?php echo _lang('product_exp'); ?>" style="width: 180px;">
        </td>
        <td class="Stitle">Doc Ref :</td>
        <td class="Stxt">
            <input type="text" name="docRefExt" id="docRefExt" class="neditable-input" placeholder="Document Refer Ext">
        </td>
    </tr>
    <tr style="text-align:left; height: 34px;" id="show_search_pallet">
        <td><?php echo _lang('pallet_code'); ?> : </td>
        <td><input type="text" name="palletCode" id="palletCode" class="neditable-input" placeholder="<?php echo _lang('pallet_code'); ?>">            </td>
        <td>Pallet Type : </td>
        <td>
            <input style="margin:0" checked="checked" type="radio" name="palletIsFull" value="PT001"> Mix
            <input style="margin:0" type="radio" name="palletIsFull" value="PT002"> Full
        <td>Dispatch Type : </td>
        <td>
            <input style="margin:0" checked="checked" type="radio" name="palletDispatchType" value="PARTAIL"> Partail
            <input style="margin:0" type="radio" name="palletDispatchType"  value="FULL"> Full
        </td>
        <td colspan='2'></td>
    </tr>
    <tr style="text-align:left; height: 34px;">
        <td colspan='5'></td>
        <td colspan='2' align="right" style="padding-right: 20px;" ><?php if ($this->config->item('build_pallet')): echo '<input type="checkbox" name="chkPallet" id="chkPallet"> Search by pallet'; endif ?></td>
        <td><a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a></td>

    </tr>
</form>
<script>
    $('#productCode').keyup(function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {

            <?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
                $('#qty_of_sku').focus().select();
            <?php else: ?>
                $('#formProductCode').submit();
            <?php endif; ?>

        }
    });
    $('#qty_of_sku').keyup(function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            $('#formProductCode').submit();
        }
    });

</script>