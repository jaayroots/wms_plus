<?php // Create by Ton! 20130424                                                                                         ?>
<SCRIPT>
    //CONFIG STORAGE MAC :ADD BY POR 2014-07-22
    var max_row = '<?php echo $max_row; ?>';
    var max_column = '<?php echo $max_column; ?>';
    var max_level = '<?php echo $max_level; ?>';
    //END ADD CONFIG

    $(document).ready(function () {
        var type = $('#type').val();
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();

            $('#Warehouse_Id').attr("disabled", true);
            $('#Zone_Id').attr("disabled", true);
            $('#StorageType_Id').attr("disabled", true);
            document.getElementById("Storage_NameTh").readOnly = true;
            document.getElementById("Storage_NameEn").readOnly = true;
            document.getElementById("Storage_Height").readOnly = true;
            document.getElementById("Storage_Width").readOnly = true;
            document.getElementById("Storage_Lenght").readOnly = true;
            document.getElementById("Storage_Row").readOnly = true;
            document.getElementById("Storage_Column").readOnly = true;
            document.getElementById("Storage_Level").readOnly = true;
            document.getElementById("Location_Height").readOnly = true;
            document.getElementById("Location_Width").readOnly = true;
            document.getElementById("Location_Lenght").readOnly = true;
            document.getElementById("Max_Capacity").readOnly = true;
            document.getElementById("Suggest_Allow_Merge").disabled = true;
            document.getElementById("Capacity_Max_Pallet").readOnly = true;

        } else if (type === 'A') {
            $('#Warehouse_Id option[value=""]').attr('selected', 'selected');
            $('#StorageType_Id option[value=""]').attr('selected', 'selected');
            $('#Zone_Id option[value=""]').attr('selected', 'selected');// Add by Ton! 20140107
        } else if (type === 'E') {
            $('#Warehouse_Id').attr("disabled", true);
            $('#Zone_Id').attr("disabled", true);
            $('#StorageType_Id').attr("disabled", true);

            document.getElementById("Storage_Height").readOnly = true;
            document.getElementById("Storage_Width").readOnly = true;
            document.getElementById("Storage_Lenght").readOnly = true;
            document.getElementById("Storage_Row").readOnly = true;
            document.getElementById("Storage_Column").readOnly = true;
            document.getElementById("Storage_Level").readOnly = true;
            document.getElementById("Location_Height").readOnly = true;
            document.getElementById("Location_Width").readOnly = true;
            document.getElementById("Location_Lenght").readOnly = true;

            $('#btn_clear').hide();
        }

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Warehouse_Id"]').change(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }

            $("#wh_id").val($(this).val());
        });

        $('[name="Zone_Id"]').change(function () { // Add by Ton! 20150610
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }

            $("#z_id").val($(this).val());
        });

        $('[name="StorageType_Id"]').change(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }

            $("#st_id").val($(this).val());
        });

        $('[name="Storage_NameEn"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Storage_Row"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Storage_Column"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Storage_Level"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Max_Capacity"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Capacity_Max_Pallet"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('#Active').live('change', function () {
            if (!$(this).is(':checked')) {
                $.post('<?php echo site_url("/storage/ajax_check_item_in_storage") ?>', {Storage_Id: $('#Storage_Id').val()}, function (data) {
                    if (data.result == "0") {
                        alert("Can't set inactive." + "\n" + data.note);
                        $("#Active").prop("checked", true);
                    }
                }, "JSON");
            }
        });

    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");

        if ($('#Warehouse_Id option:selected').val() === "") {
            alert("Please select Warehouse.");
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if ($('#check_WH').val() == 1) {// Add by Ton! 20140107
            if ($('#Zone_Id option:selected').val() === "") {
                alert("Please select Zone.");
                $('#Zone_Id').addClass('required');
                $("#btn_save").removeAttr("disabled");
                return;
            } else {
                $('#Zone_Id').removeClass('required');
            }
        }

//        if ($('#Zone_Id option:selected').val() === "") { // Add by Ton! 20150610
//            alert("Please select Zone.");
//            $("#btn_save").removeAttr("disabled");
//            return;
//        }

        if ($('#StorageType_Id option:selected').val() === "") {
            alert("Please select Storage.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Storage_NameEn').val() === "") {
            alert("Please input Storage Name English.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Storage_Row').val() === "") {
            alert("Please input Storage Row.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Row').val()) <= 0) {
            alert("Storage Row. Not less than 0.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Row').val()) > max_row) {
            alert("Storage Row. Not more than " + max_row);
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Storage_Column').val() === "") {
            alert("Please input Storage Column.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Column').val()) <= 0) {
            alert("Storage Column. Not less than 0.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Column').val()) > max_column) {
            alert("Storage Column. Not more than " + max_column);
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Storage_Level').val() === "") {
            alert("Please input Storage Level.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Level').val()) <= 0) {
            alert("Storage Level. Not less than 0.");
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (parseInt($('#Storage_Level').val()) > max_level) {
            alert("Storage Level. Not more than " + max_level);
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Storage_Id').val() !== "" && !$('#Active').is(':checked')) {
            $.post('<?php echo site_url("/storage/ajax_check_item_in_storage") ?>', {Storage_Id: $('#Storage_Id').val()}, function (data) {
                if (data.result == "0") {
                    alert("Can't set inactive." + "\n" + data.note);
                    $("#Active").prop("checked", true);
                    $("#btn_save").removeAttr("disabled");
                    return;
                }
            }, "JSON");
        }

        submitFrm();
//        validation_in_controllers();
    }

//    function validation_in_controllers() {// Add by Ton! 20140303
//        $.post('<?php // echo site_url('/storage/validation')   ?>', $("#frmStorage").serialize(), function (data) {
//            if (data.result == "1") {
//                submitFrm();
//            } else {
//                if (data.note == "STOR_DEL_PROD") {
//                    alert("Save unsuccessfully. Can not be inactive. Because location have product is placed.");
//                }
//
//                if (data.note == "STOR_DEL_FOUND") {
//                    alert("Save unsuccessfully. Can not inactive. Because location not found.");
//                }
//
//                $("#btn_save").removeAttr("disabled");
//                return;
//            }
//        }, "json");
//    }

    function submitFrm() {// save & edit storage (call storage/save_storage)
        var type = $('#type').val();
        var Warehouse_Id = $('#Warehouse_Id option:selected').val();
        var Zone_Id = $('#Zone_Id option:selected').val();
        if (confirm("You want to save the data Storage?")) {
            $.post('<?php echo site_url() . "/storage/save_storage" ?>', $('#frmStorage').serialize(), function (dataSave) {
                if (dataSave == "1") {
                    if (type === 'A') {
                        $.post('<?php echo site_url() . "/storage/checkStorage" ?>', {Warehouse_Id: Warehouse_Id, Zone_Id: Zone_Id}, function (dataChk) {
                            if (parseInt(dataChk) > 2) {
                                if ($('#check_WH').val() === 1) {
                                    if (confirm("Save successfully. Location Code Already, Must Edit Code.")) {
                                        window.location = "<?php echo site_url() ?>/location/edit_location_code_list";
                                        return;
                                    }
                                }
                            }

                            alert("Save successfully.");
                            window.location = "<?php echo site_url() ?>/storage";
                            return;
                        }, "html");
                    } else {
                        alert("Save successfully.");
                        window.location = "<?php echo site_url() ?>/storage";
                        return;
                    }
                } else {
                    alert("Save unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return;
                }
            }, "html");
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {// define input = "".
        $('#Warehouse_Id option[value=""]').attr('selected', 'selected');
        $('#StorageType_Id option[value=""]').attr('selected', 'selected');
        $('#Zone_Id option[value=""]').attr('selected', 'selected');// Add by Ton! 20140107
        $('#Storage_NameTh').val('');
        $('#Storage_NameEn').val('');
        $('#Storage_Height').val('');
        $('#Storage_Width').val('');
        $('#Storage_Lenght').val('');
        $('#Storage_Row').val('');
        $('#Storage_Column').val('');
        $('#Storage_Level').val('');
        $('#Location_Height').val('');
        $('#Location_Width').val('');
        $('#Location_Lenght').val('');
        $('#Max_Capacity').val('');
        $('#Capacity_Max_Pallet').val('');
    }

    function backToList() {// back to list storage page.
        window.location = "<?php echo site_url() ?>/storage";
    }

    function setZone() {// Add by Ton! 20140107
        var Warehouse_Id = $('#Warehouse_Id option:selected').val();
//        var Zone_Id = $('#Zone_Id option:selected').val();

        $.post('<?php echo site_url() . "/location/get_zone_by_warehouse_ID" ?>', $('#frmStorage').serialize(), function (html) {
            $('#Zone_Id').html(html);
        }, 'html');

        $.post('<?php echo site_url() . "/storage/checkStorage" ?>', {Warehouse_Id: Warehouse_Id}, function(data) {
            if (data >= 1) {
                $('#check_WH').val(1);
                $('#Zone_Id').addClass('required');
                $('#Zone_Id').attr("disabled", false);
            } else {
                $('#check_WH').val(0);
                $('#Zone_Id').removeClass('required');
                $('#Zone_Id').attr("disabled", true);
            }
        }, 'html');

        $('#Zone_Id').focus();
    }

</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Storage </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmStorage" NAME="frmStorage" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Storage_Id" name="Storage_Id" value="<?php echo $Id ?>"/>
            <input type="hidden" id="check_WH" name="check_WH"/>

            <input type="hidden" id="wh_id" name="wh_id" value="<?php echo $Warehouse_Id; ?>"/> <!-- Warehouse_Id -->
            <input type="hidden" id="z_id" name="z_id" value="<?php echo $Zone_Id; ?>"/><!-- Zone_Id -->
            <input type="hidden" id="st_id" name="st_id" value="<?php echo $StorageType_Id; ?>"/><!-- StorageType_Id -->
            <?php
            $extra_disabled = "";
            $extra_active = "";
            if ($mode === 'V'):
                $extra_disabled = " disabled=disabled ";
            elseif ($mode === 'A'):
                $extra_active = " checked=checked ";
            endif;
            ?>
            <TABLE width='95%' align='center'>
                <TR>
                    <TD>
                        <FIELDSET class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Warehouse </TD>
                                    <TD>
                                        <?php echo form_dropdown('Warehouse_Id', $WHList, $Warehouse_Id, 'id=Warehouse_Id onChange="setZone()" class="required"') ?>
                                        <?php echo form_checkbox('Active', 1, $Active, $extra_disabled . $extra_active . 'id="Active"'); ?>&nbsp;&nbsp;&nbsp;Active&nbsp;&nbsp;&nbsp;
                                    </TD>
                                </TR>
                                <TR><!-- Add by Ton! 20140107 -->
                                    <TD>Zone </TD>
                                    <TD><?php echo $ZONEList; ?></TD>
                                </TR>
                                <TR>
                                    <TD>Condition Type </TD>
                                    <TD>
                                        <select name="Suggest_Allow_Merge" id="Suggest_Allow_Merge" style="width: auto;">
                                            <option value="1" <?php echo $Suggest_Allow_Merge == 1 ? "selected" : "" ?>>Mix</option>
                                            <option value="2" <?php echo $Suggest_Allow_Merge == 2 ? "selected" : "" ?>>Only Item</option>
                                            <option value="3" <?php echo $Suggest_Allow_Merge == 3 ? "selected" : "" ?>>Only Pallet</option>
                                        </select>
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                        <FIELDSET class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Storage Type </TD>
                                    <TD>
                                        <?php echo form_dropdown('StorageType_Id', $StorTypeList, $StorageType_Id, 'id=StorageType_Id class="required"') ?>
                                    </TD>
                                </TR>
                                <TR>
                                    <TD>Storage Name Th </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_NameTh" NAME="Storage_NameTh" VALUE="<?php echo $Storage_NameTh ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Storage Name En </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_NameEn" NAME="Storage_NameEn" CLASS="required" VALUE="<?php echo $Storage_NameEn ?>"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
                <TR>
                    <TD>
                        <FIELDSET class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Storage Height </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Height" NAME="Storage_Height" VALUE="<?php echo $Storage_Height ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                                <TR>
                                    <TD>Storage Width </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Width" NAME="Storage_Width" VALUE="<?php echo $Storage_Width ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                                <TR>
                                    <TD>Storage Lenght </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Lenght" NAME="Storage_Lenght" VALUE="<?php echo $Storage_Lenght ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                        <FIELDSET class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Location Height </TD>
                                    <TD><INPUT TYPE="text" ID="Location_Height" NAME="Location_Height" VALUE="<?php echo $Location_Height ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                                <TR>
                                    <TD>Location Width </TD>
                                    <TD><INPUT TYPE="text" ID="Location_Width" NAME="Location_Width" VALUE="<?php echo $Location_Width ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                                <TR>
                                    <TD>Location Lenght </TD>
                                    <TD><INPUT TYPE="text" ID="Location_Lenght" NAME="Location_Lenght" VALUE="<?php echo $Location_Lenght ?>" class="numeric-f not_zero103-f"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
                <TR>
                    <TD>
                        <FIELDSET class="well" >
                            <TABLE>
                                <TR>
                                    <TD>Storage Row </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Row" NAME="Storage_Row" CLASS="required integer-f" VALUE="<?php echo $Storage_Row ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Storage Column </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Column" NAME="Storage_Column" class="required integer-f" VALUE="<?php echo $Storage_Column ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Storage Level </TD>
                                    <TD><INPUT TYPE="text" ID="Storage_Level" NAME="Storage_Level" class="required integer-f" VALUE="<?php echo $Storage_Level ?>"></TD>
                                </TR>
                                <TR>
                                    <TD>Max Capacity </TD>
                                    <TD><INPUT TYPE="text" ID="Max_Capacity" NAME="Max_Capacity" VALUE="<?php echo $Max_Capacity ?>" class="integer-f not_zero-f required"></TD>
                                </TR>
                                <TR>
                                    <TD>Max Pallet Capacity </TD>
                                    <TD><INPUT TYPE="text" ID="Capacity_Max_Pallet" NAME="Capacity_Max_Pallet" VALUE="<?php echo $Capacity_Max_Pallet ?>" class="integer-f not_zero-f required"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
            </TABLE>
        </FORM>
        <script>
            $(document).ready(function () {
                $("#StorageType_Id").change(function (e) {
                    if ($(this).val() == 11) {
                        $("#Storage_Row").val(1).prop("readonly", true).removeClass("required");
                    } else {
                        $("#Storage_Row").prop("readonly", false);
                    }
                });
            });
        </script>