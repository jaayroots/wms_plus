<script>
    $(document).ready(function () {
        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $('#Warehouse_Type').attr("disabled", true);
            $('#City_id').attr("disabled", true);

            $(":text").each(function () {
                $(this).attr("readonly", true);
            });

            $(":checkbox").each(function () {
                $(this).attr("disabled", "disabled");
            });
        } else if (type === 'A') {
            $('#City_id option[value=""]').attr('selected', 'selected');
            $('#Warehouse_Type option[value=""]').attr('selected', 'selected');
            $("#Active").attr("checked", true);
        } else if (type === 'E') {
            $('#btn_clear').hide();
        }

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Warehouse_Code"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Warehouse_NameEN"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var Warehouse_Code = $('#Warehouse_Code').val().trim();
        var Warehouse_NameEN = $('#Warehouse_NameEN').val().trim();

        if (Warehouse_Code.length <= 0) {
            alert('Please enter the Warehouse Code.');
            $('#Warehouse_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!check_special_character(Warehouse_Code)) {
            alert("Warehouse Code must not is special Character.");
            $('#Warehouse_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (Warehouse_NameEN.length <= 0) {
            alert('Please enter the Warehouse Name EN.');
            $('#Warehouse_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!check_special_character(Warehouse_NameEN)) {
            alert("Warehouse Name EN must not is special Character.");
            $('#Warehouse_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

//        submitFrm();
        validation_in_controllers();
        return;
    }

    function validation_in_controllers() {// Add by Ton! 20140228
        $.post('<?php echo site_url('/warehouse/validation') ?>', $('#frmWarehouse').serialize(), function (data) {
            if (data.result === 1) {
                submitFrm();
            } else {
                if (data.note === 'WH_CODE_ALREADY') {
                    alert('Warehouse Code already exists.');
                    $('#Warehouse_Code').focus();
                }

                if (data.note === 'WH_DEL') {
                    alert('Can not be inactive Warehouse. Warehouse is already in used. Do not Inactive!');
                    $('#Active').prop("checked", true);
                }

                $('#btn_save').removeAttr('disabled');
                return;
            }
        }, 'JSON');
    }

    function submitFrm() {
        if (confirm('You want to save the data Warehouse?')) {
            $.post('<?php echo site_url('/warehouse/saveWarehouse') ?>', $('#frmWarehouse').serialize(), function (data) {
                if (data === "1") {
                    alert('Save successfully.');
                    window.location = '<?php echo site_url('/warehouse') ?>';
                    return;
                } else {
                    alert('Save unsuccessfully.');
                    $('#btn_save').removeAttr('disabled');
                    return;
                }
            }, 'HTML');
        } else {
            $('#btn_save').removeAttr('disabled');
        }
    }

    function clearData() {
        $(":text").each(function () {
            $(this).val('');
        });

        $('#Warehouse_Type option[value=""]').attr('selected', 'selected');
        $('#City_id option[value=""]').attr('selected', 'selected');
        $('#Warehouse_Code').addClass('required');
        $('#Warehouse_NameEN').addClass('required');
        $("#Active").attr("checked", true);
    }

    function backToList() {
        window.location = '<?php echo site_url('/warehouse') ?>';
    }
</script>    
<STYLE tyle="css/text">
    .w120{ width: 120px;  padding: 5px; }
    .w150{ width: 150px;  padding: 5px; }
    .txt-r{ text-align: right; }
    .txt-l{ text-align: left; }
    .txt-c{ text-align: center; }
</style>
<HTML>
    <HEAD>
        <TITLE> Warehouse </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmWarehouse" NAME="frmWarehouse" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Warehouse_Id" name="Warehouse_Id" value="<?php echo $Warehouse_Id ?>"/>
            <TABLE>
                <tr>
                    <td class="w150 txt-r">Warehouse Type :</td>
                    <td><?php echo form_dropdown('Warehouse_Type', $optionWH_Type, $Warehouse_Type, 'id=Warehouse_Type') ?> </td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Warehouse Code :</td>
                    <td>
                        <input id='Warehouse_Code' name='Warehouse_Code' type='text' class="required string_special_characters-f" value="<?php echo $Warehouse_Code ?>"/>
                    </td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Warehouse Name EN :</td>
                    <td><input id='Warehouse_NameEN' name='Warehouse_NameEN' type='text' class="required string_Eng-f string_special_characters-f" value="<?php echo $Warehouse_NameEN ?>"/></td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Warehouse Name TH :</td>
                    <td><input id='Warehouse_NameTH' class="string_Thai-f string_special_characters-f" name='Warehouse_NameTH' type='text' value="<?php echo $Warehouse_NameTH ?>"/></td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Warehouse Desc :</td>
                    <td><input id='Warehouse_Desc' class="string_special_characters-f" name='Warehouse_Desc' type='text' value="<?php echo $Warehouse_Desc ?>"/></td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Address :</td>
                    <td><input id='Address' class="string_special_characters-f" name='Address' type='text' value="<?php echo $Address ?>"/></td>
                </tr>
                <tr>
                    <td class="w150 txt-r">City :</td>
                    <td><?php echo form_dropdown('City_id', $cityList, $City_id, 'id=City_id') ?> </td>
                </tr>
                <tr>
                    <td class="w150 txt-r">Zip Code :</td>
                    <td><input id='ZipCode' name='ZipCode' class="string_special_characters-f" type='text' value="<?php echo $ZipCode ?>"/></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <label>
                            <input type="checkbox" name="Active" id="Active" <?php echo (ISSET($Active) ? (($Active == 'Y' || $Active == '1') ? 'checked' : '') : '') ?>>&nbsp;Active&nbsp;&nbsp;
                        </label>
                    </td>
                </tr>
            </TABLE>
        </FORM>
    </BODY>

</HTML>
