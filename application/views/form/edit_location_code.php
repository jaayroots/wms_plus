<SCRIPT>
    $(document).ready(function() {
        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Location_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        
        $('#Active').live('change', function () {
            if (!$(this).is(':checked')) {
                $.post('<?php echo site_url("/location/ajax_check_item_in_location") ?>', {Location_Id: $('#Location_Id').val()}, function (data) {
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

        if ($('#Location_Id').val() !== "" && !$('#Active').is(':checked')) {
            $.post('<?php echo site_url("/location/ajax_check_item_in_location") ?>', {Location_Id: $('#Location_Id').val()}, function (data) {
                if (data.result == "0") {
                    alert("Can't set inactive." + "\n" + data.note);
                    $("#Active").prop("checked", true);
                    $("#btn_save").removeAttr("disabled");
                    return;
                }
            }, "JSON");
        }

        var LocationCode = $('#Location_Code').val();
        if (LocationCode === "") {
            alert("Please input Location Code.");
            $('#Location_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!checkSpecialCharacterOnForm(LocationCode)) {
            alert("Location Code must not is special Character.");
            $('#Location_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Active').is(':checked')) {
            check_Location_Code();
        }else{
            submitFrm();
        }
    }

    function check_Location_Code() {
        $.post('<?php echo site_url() . "/location/check_exist_location" ?>', {location_code: $('#Location_Code').val(), location_id: $('#Location_Id').val()}, function (data) {
            if (data == "null") {
                submitFrm();
            } else {
                alert("Have Location Code Already!!");
                $('#Location_Code').focus();
                $("#btn_save").removeAttr("disabled");
            }
        });
    }

    function submitFrm() {
        var LocationCode = $('#Location_Code').val();
        var LocationId = $('#Location_Id').val();
        var Active = $("#Active").is(":checked");
        if (confirm("You want to save the data Location Code?")) {
            $.post('<?php echo site_url("/location/save_edit_location_code") ?>', {Location_Code: LocationCode, Location_Id: LocationId, Active: Active}, function (dataSave) {
                if (dataSave == true) {
                    alert("Save Edit Location Code successfully.");
                    window.location = "<?php echo site_url() ?>/location/edit_location_code_list";
                } else {
                    alert("Save Edit Location Code unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
                return;
            }, 'HTML');
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function backToList() {
        window.location = "<?php echo site_url() ?>/location/edit_location_code_list";
    }

    function checkSpecialCharacterOnForm($str) {
        var iChars = "~`!#$%^&*+=[]\\\';,/{}|\":<>?";
        if (!$str) {
            return false;
        }

        for (var i = 0; i < $str.length; i++) {
            if (iChars.indexOf($str.charAt(i)) !== -1) {
                return false;
            }
        }

        return true;
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Edit Location Code Already. </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmEditLocation" NAME="frmEditLocation" METHOD='post'>
            <input type="hidden" id="Location_Id" name="Location_Id" value="<?php echo (ISSET($parameter["Location_Id"]) ? $parameter["Location_Id"] : '') ?>"/>
            <TABLE>
                <TR>
                    <TD style="text-align: right; width: 120px;">Warehouse Code:&nbsp;&nbsp;</TD>
                    <TD colspan="2"><INPUT TYPE="text" ID="Warehouse_Code" NAME="Warehouse_Code" VALUE="<?php echo (ISSET($parameter["Warehouse_Code"]) ? $parameter["Warehouse_Code"] : '') ?>" disabled="disabled"></TD>
                </TR>
                <TR>
                    <TD style="text-align: right; width: 120px;">Zone Code:&nbsp;&nbsp;</TD>
                    <TD colspan="2"><INPUT TYPE="text" ID="Zone_Code" NAME="Zone_Code" VALUE="<?php echo (ISSET($parameter["Zone_Code"]) ? $parameter["Zone_Code"] : '') ?>" disabled="disabled"></TD>
                </TR>
                <TR>
                    <TD style="text-align: right; width: 120px;">Storage Name:&nbsp;&nbsp;</TD>
                    <TD colspan="2"><INPUT TYPE="text" ID="Storage_NameEn" NAME="Storage_NameEn" VALUE="<?php echo (ISSET($parameter["Storage_NameEn"]) ? $parameter["Storage_NameEn"] : '') ?>" disabled="disabled"></TD>
                </TR>
                <TR>
                    <TD style="text-align: right; width: 120px;">Storage Code:&nbsp;&nbsp;</TD>
                    <TD colspan="2"><INPUT TYPE="text" ID="Storage_Code" NAME="Storage_Code" VALUE="<?php echo (ISSET($parameter["Storage_Code"]) ? $parameter["Storage_Code"] : '') ?>" disabled="disabled"></TD>
                </TR>
                <TR>
                    <TD style="text-align: right; width: 120px;">Location Code:&nbsp;&nbsp;</TD>
                    <TD><INPUT TYPE="text" ID="Location_Code" CLASS="required" NAME="Location_Code" VALUE="<?php echo (ISSET($parameter["Location_Code"]) ? $parameter["Location_Code"] : '') ?>"></TD>
                    <TD><input type="checkbox" name="Active" id="Active" <?php echo (ISSET($parameter["Active"]) ? (($parameter["Active"] == 'Y' || $parameter["Active"] == '1') ? 'checked' : '') : '') ?>>&nbsp;Active&nbsp;&nbsp;</TD>
                </TR>
            </TABLE>
        </FORM>
    </BODY>
</HTML>