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

            document.getElementById("TitleName_Code").readOnly = true;
            document.getElementById("TitleName_EN").readOnly = true;
            document.getElementById("TitleName_TH").readOnly = true;
            document.getElementById("TitleName_Desc").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="TitleName_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="TitleName_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#TitleName_Code').val('');
        $('#TitleName_Code').addClass('required');
        $('#TitleName_EN').val('');
        $('#TitleName_EN').addClass('required');
        $('#TitleName_TH').val('');
        $('#TitleName_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of title_name page.
        window.location = "<?php echo site_url() ?>/title_name";
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var TitleNameCode = $('#TitleName_Code').val();
        var TitleNameEN = $('#TitleName_EN').val();

        if (TitleNameCode == "") {
            alert("Please input TitleName Code.");
            $('#TitleName_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(TitleNameCode)) {
            alert("TitleName Code must not is special Character.");
            $('#TitleName_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (TitleNameEN == "") {
            alert("Please input TitleName En.");
            $('#TitleName_EN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140306
        $.post('<?php echo site_url() . '/title_name/validation' ?>', $("#frmTitleName").serialize(), function(data) {
            if (data.result === 1) {
                submitTitleName();
            } else {
                if (data.note === "TITLE_CODE_ALREADY") {
                    alert("TitleName Code already exists.");
                    $('#Province_Code').focus();
                }

                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitTitleName() {
        if (confirm("You want to save the data Title Name?")) {
            $.post('<?php echo site_url() . "/title_name/save_title_name" ?>', $('#frmTitleName').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save TitleName Master successfully.");
                    window.location = "<?php echo site_url() ?>/title_name";
                } else {
                    alert("Save TitleName Master unsuccessfully.");
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
        <TITLE> Title Name </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmTitleName" NAME="frmTitleName" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="TitleName_Id" name="TitleName_Id" value="<?php echo $TitleName_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $TitleName_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>TitleName Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="TitleName_Code" NAME="TitleName_Code" VALUE="<?php echo $TitleName_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>TitleName EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="TitleName_EN" NAME="TitleName_EN" VALUE="<?php echo $TitleName_EN ?>"></TD>
                    <TD>TitleName TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="TitleName_TH" NAME="TitleName_TH" VALUE="<?php echo $TitleName_TH ?>"></TD>
                </TR>
                <TR>
                    <TD>TitleName Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="TitleName_Desc" NAME="TitleName_Desc" style="resize:none; width:98%;" rows="2"><?php echo $TitleName_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
