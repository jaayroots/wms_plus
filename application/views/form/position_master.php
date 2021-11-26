<?php
/*
 * Create by Ton! 20131204
 */
?>
<SCRIPT>
    $(document).ready(function() {
<?php if ($Active == true): ?>
            $("#Active").prop("checked", true);
<?php else: ?>
            $("#Active").prop("checked", false);
<?php endif; ?>

<?php if ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#btn_clear').hide();
<?php elseif ($mode == 'A'): ?>
            $('#btn_clear').show();
            document.getElementById("Active").setAttribute("checked", "checked");
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("Position_Code").readOnly = true;
            document.getElementById("Position_NameEN").readOnly = true;
            document.getElementById("Position_NameTH").readOnly = true;
            document.getElementById("Position_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="Position_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="Position_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Position_Code').val('');
        $('#Position_Code').addClass('required');
        $('#Position_NameEN').val('');
        $('#Position_NameEN').addClass('required');
        $('#Position_NameTH').val('');
        $('#Position_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of position page.
        window.location = "<?php echo site_url() ?>/position";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var PositionCode = $('#Position_Code').val();
        var PositionNameEN = $('#Position_NameEN').val();

        if (PositionCode == "") {
            alert("Please input Position Code.");
            $('#Position_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(PositionCode)) {
            alert("Position Code must not is special Character.");
            $('#Position_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (PositionNameEN == "") {
            alert("Please input Position Name En.");
            $('#Position_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/position/validation' ?>', $("#frmPosition").serialize(), function(data) {
            if (data.result === 1) {
                submitPosition();
            } else {
                if (data.note === "POS_CODE_ALREADY") {
                    alert("Position Code already exists.");
                    $('#Position_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }


    function submitPosition() {
        if (confirm("You want to save the data Position?")) {
            $.post('<?php echo site_url() . "/position/save_position" ?>', $('#frmPosition').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save Position Master successfully.");
                    window.location = "<?php echo site_url() ?>/position";
                } else {
                    alert("Save Position Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
                return;
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }
</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Position </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmPosition" NAME="frmPosition" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Position_Id" name="Position_Id" value="<?php echo $Position_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $Position_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>Position Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Position_Code" NAME="Position_Code" VALUE="<?php echo $Position_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>Position Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="Position_NameEN" NAME="Position_NameEN" VALUE="<?php echo $Position_NameEN ?>"></TD>
                    <TD>Position Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="Position_NameTH" NAME="Position_NameTH" VALUE="<?php echo $Position_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>Position Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="Position_Desc" NAME="Position_Desc" style="resize:none; width:98%;" rows="2"><?php echo $Position_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
