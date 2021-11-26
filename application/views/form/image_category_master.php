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
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("Parent_Id").readOnly = true;
            document.getElementById("ImageCategory_Code").readOnly = true;
            document.getElementById("ImageCategory_NameEN").readOnly = true;
            document.getElementById("ImageCategory_NameTH").readOnly = true;
            document.getElementById("ImageCategory_Desc").readOnly = true;
    //            document.getElementById("Active").readOnly = true;
            document.getElementById("Active").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="ImageCategory_Code"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="ImageCategory_NameEN"]').keyup(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
    });

    function clearData() {
        $('#Parent_Id').val('');
        $('#ImageCategory_Code').val('');
        $('#ImageCategory_Code').addClass('required');
        $('#ImageCategory_NameEN').val('');
        $('#ImageCategory_NameEN').addClass('required');
        $('#ImageCategory_NameTH').val('');
        $('#ImageCategory_Desc').val('');
        $("#Active").prop("checked", false);
    }

    function backToList() {// back to list of image_category page.
        window.location = '<?php echo site_url('/image_category') ?>';
    }

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var ImageCategoryCode = $('#ImageCategory_Code').val();
        var ImageCategoryNameEN = $('#ImageCategory_NameEN').val();

        if (ImageCategoryCode == "") {
            alert("Please input ImageCategory Code.");
            $('#ImageCategory_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (!check_special_character(ImageCategoryCode)) {
            alert("ImageCategory Code must not is special Character.");
            $('#ImageCategory_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (ImageCategoryNameEN == "") {
            alert("Please input ImageCategory Name En.");
            $('#ImageCategory_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        check_ImageCategory_Code();
    }

    function check_ImageCategory_Code() {// Check ImageCategory_Code Already.        
        $("#btn_save").attr("disabled", "disabled");
        var type = $('#type').val();
        var Current_Code = $('#Current_Code').val();
        var ImageCategory_Code = $('#ImageCategory_Code').val();

        if (type === 'A') {
            $.post('<?php echo site_url('/image_category/check_image_category') ?>', $('#frmImageCategory').serialize(), function(dataCheckA) {
                if (dataCheckA == 1) {
                    alert("Have ImageCategory Code Already!!");
                    $('#ImageCategory_Code').focus();
                    $("#btn_save").removeAttr("disabled");
                } else {
                    submitImageCategory();
                }
                return;
            });
        } else {
            if (Current_Code === ImageCategory_Code) {
                submitImageCategory();
            } else {
                $.post('<?php echo site_url('/image_category/check_image_category') ?>', $('#frmImageCategory').serialize(), function(dataCheckE) {
                    if (dataCheckE == 1) {
                        alert("Have ImageCategory Code Already!!");
                        $('#ImageCategory_Code').focus();
                        $("#btn_save").removeAttr("disabled");
                    } else {
                        submitImageCategory();
                    }
                    return;
                });
            }
            return;
        }
    }

    function submitImageCategory() {
        if (confirm("You want to save the data Image Category?")) {
            $.post('<?php echo site_url('/image_category/save_image_category') ?>', $('#frmImageCategory').serialize(), function(dataSave) {
                if (dataSave == 1) {
                    alert("Save ImageCategory Master successfully.");
                    window.location = "<?php echo site_url() ?>/image_category";
                } else {
                    alert("Save ImageCategory Master unsuccessfully.");
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
        <TITLE> Image Category </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmImageCategory" NAME="frmImageCategory" METHOD='post'>
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="ImageCategory_Id" name="ImageCategory_Id" value="<?php echo $ImageCategory_Id ?>"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $ImageCategory_Code ?>"/>
            <TABLE>
                <TR>    
                    <TD>ImageCategory Code : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ImageCategory_Code" NAME="ImageCategory_Code" VALUE="<?php echo $ImageCategory_Code ?>"></TD>
                    <TD colspan="2">
                        <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                    </TD>  
                <TR>
                    <TD>Parent : </TD>
                    <TD colspan="3">
                        <INPUT TYPE="text" class="integer-f string_special_characters-f" ID="Parent_Id" NAME="Parent_Id" VALUE="<?php echo $Parent_Id ?>">
                    </TD>
                </TR>
                <TR>
                    <TD>ImageCategory Name EN : </TD>
                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="ImageCategory_NameEN" NAME="ImageCategory_NameEN" VALUE="<?php echo $ImageCategory_NameEN ?>"></TD>
                    <TD>ImageCategory Name TH : </TD>
                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="ImageCategory_NameTH" NAME="ImageCategory_NameTH" VALUE="<?php echo $ImageCategory_NameTH ?>"></TD>
                </TR>
                <TR>
                    <TD>ImageCategory Desc:</TD>
                    <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="ImageCategory_Desc" NAME="ImageCategory_Desc" style="resize:none; width:98%;" rows="2"><?php echo $ImageCategory_Desc ?></TEXTAREA></TD>
                </TR>                    
            </TABLE>
        </FORM>
    </BODY>
</HTML>
