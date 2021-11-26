<SCRIPT>
    $(document).ready(function () {
<?php if ($Active == true): ?>
            $("#Active").prop("checked", true);
<?php else: ?>
            $("#Active").prop("checked", false);
<?php endif; ?>
<?php if ($IsUri == true): ?>
            $("#IsUri").prop("checked", true);
<?php else: ?>
            $("#IsUri").prop("checked", false);
<?php endif; ?>
<?php if ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#btn_clear').hide();
<?php elseif ($mode == 'A'): ?>
            $('#btn_clear').show();
            document.getElementById("Active").setAttribute("checked", "checked");
            document.getElementById("IsUri").setAttribute("checked", "checked");
<?php else: ?>
            $('#btn_save').hide();
            $('#btn_clear').hide();

            document.getElementById("MenuBar_Code").readOnly = true;
            document.getElementById("MenuBar_NameEn").readOnly = true;
            document.getElementById("MenuBar_NameTh").readOnly = true;
            document.getElementById("MenuBar_Desc").readOnly = true;
            document.getElementById("NavigationUri").readOnly = true;
            document.getElementById("Sequence").readOnly = true;
            $('#Icon_Image_Id').attr("readonly", "readonly");
            document.getElementById("Active").setAttribute("disabled", "disabled");
            document.getElementById("IsUri").setAttribute("disabled", "disabled");
<?php endif; ?>

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="MenuBar_Code"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="MenuBar_NameEn"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="NavigationUri"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

//        $('#preview_images').attr('src', '<?php // echo base_url()  ?>' + '<?php // echo $ImageName  ?>');

//        $("#Icon_Image_Id").change(function () {
//            if ($(this).val() == "0" || $(this).val() == "" || $(this).val() == null) {
//                $('#preview_images').hide();
//            } else {
//                console.log('<?php // echo base_url() ?>' + 'css/images/' + $("#Icon_Image_Id option:selected").text());
//                $.ajax({
//                    url: '<?php // echo base_url() ?>' + 'css/images/' + $("#Icon_Image_Id option:selected").text(),
//                    type: 'HEAD',
//                    error: function ()
//                    {
//                        $('#preview_images').hide();
//                    },
//                    success: function ()
//                    {
//                        $('#preview_images').attr('src', '<?php // echo base_url() ?>' + 'css/images/' + $("#Icon_Image_Id option:selected").text());
//                        $('#preview_images').show();
//                    }
//                });
//            }
//        });

    });

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var MenuBarCode = $('#MenuBar_Code').val();
        var MenuBarNameEn = $('#MenuBar_NameEn').val();
        var NavigationUri = $('#NavigationUri').val();

        if (MenuBarCode === "") {
            alert("Please input MenuBar Code.");
            $('#MenuBar_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!check_special_character(MenuBarCode)) {
            alert("MenuBar Code must not is special Character.");
            $('#MenuBar_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (MenuBarNameEn === "") {
            alert("Please input MenuBar Name En.");
            $('#MenuBar_NameEn').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (NavigationUri === "") {
            alert("Please input NavigationUri.");
            $('#NavigationUri').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
//        submitMenu();
        check_MenuBar_Code();
    }

    function check_MenuBar_Code() {// Check MenuBar_Code Already.        
        var type = $('#type').val();
        var Current_Code = $('#Current_Code').val();
        var MenuBar_Code = $('#MenuBar_Code').val();

        if (type === 'A') {
            $.post('<?php echo site_url("/menu_bar/check_menu") ?>', $('#frmMenuHH').serialize(), function (dataCheckA) {
                if (dataCheckA == 1) {
                    alert("Have MenuBar Code Already!!");
                    $('#MenuBar_Code').focus();
                    $("#btn_save").removeAttr("disabled");
                } else {
                    submitMenu();
                }
                return;
            });
        } else {
            if (Current_Code === MenuBar_Code) {
                submitMenu();
            } else {
                $.post('<?php echo site_url("/menu_bar/check_menu") ?>', $('#frmMenuHH').serialize(), function (dataCheckE) {
                    if (dataCheckE == 1) {
                        alert("Have MenuBar Code Already!!");
                        $('#MenuBar_Code').focus();
                        $("#btn_save").removeAttr("disabled");
                    } else {
                        submitMenu();
                    }
                    return;
                });
            }
            return;
        }
    }

    function submitMenu() {
        var type = $('#type').val();
        var MenuBar_Id = $('#MenuBar_Id').val();
        var Parent_Id = $('#Parent_Id').val();
        var MenuBar_Code = $('#MenuBar_Code').val();
        var MenuBar_NameEn = $('#MenuBar_NameEn').val();
        var MenuBar_NameTh = $('#MenuBar_NameTh').val();
        var MenuBar_Desc = $('#MenuBar_Desc').val();
        var NavigationUri = $('#NavigationUri').val();
        var Sequence = $('#Sequence').val();

        var IsUri = 0;
        if ($('#IsUri').attr('checked')) {
            IsUri = 1;
        } else {
            IsUri = 0;
        }

        var Active = 0;
        if ($('#Active').attr('checked')) {
            Active = 1;
        } else {
            Active = 0;
        }

//        var Icon_Image_Id = $('#Icon_Image_Id').val();

        if (confirm("You want to save the data Menu HH?")) {
            $.post('<?php echo site_url("/menu_bar/save_menu_child") ?>', {type: type, MenuBar_Id: MenuBar_Id, Parent_Id: Parent_Id
                , MenuBar_Code: MenuBar_Code, MenuBar_NameEn: MenuBar_NameEn, MenuBar_NameTh: MenuBar_NameTh
                , MenuBar_Desc: MenuBar_Desc, NavigationUri: NavigationUri, Sequence: Sequence, IsUri: IsUri
                , Active: Active, Menu_Type: 'HH'}, function (dataSave) {
                if (dataSave == '1') {
                    alert("Save Menu HH Master successfully.");
                    backToList();
                } else {
                    alert("Save Menu HH Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                    return false;
                }
            });
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {
        $('#frmMenuHH').find("input[type=text], textarea").val("");
//        $('#Icon_Image_Id').val('');

        $('#MenuBar_Code').addClass('required');
        $('#MenuBar_NameEn').addClass('required');
        $('#NavigationUri').addClass('required');

        $(":checkbox").each(function () {
            $(this).prop("checked", false);
        });
    }

    function backToList() {// back to list menu_ber page.
        window.location = "<?php echo site_url() ?>/menu_bar/get_menu_bar_hh_list";
    }

//    function previewImages() {
//        $('#preview_images').attr('src', '<?php // echo base_url()   ?>' + $("#Icon_Image_Id option:selected").text());
//    }

</SCRIPT>

<HTML>
    <HEAD>
        <TITLE> Menu HH </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmMenuHH" NAME="frmMenuHH" METHOD='post' >
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="MenuBar_Id" name="MenuBar_Id" value="<?php echo $MenuBar_Id ?>"/>
            <input type="hidden" id="Parent_Id" name="Parent_Id" value="0"/>
            <input type="hidden" id="Current_Code" name="Current_Code" value="<?php echo $MenuBar_Code ?>"/>
            <?php
//            if (!isset($Icon_Image_Id)) {
//                $Icon_Image_Id = "";
//            }
            ?>
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <TABLE>
                            <TR>
                                <TD>Menu Code :</TD>
                                <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="MenuBar_Code" NAME="MenuBar_Code" VALUE="<?php echo $MenuBar_Code ?>"></TD>
                                <TD colspan="2">
                                    <input type="checkbox" name="Active" id="Active">&nbsp;Active&nbsp;&nbsp;
                                    <input type="checkbox" name="IsUri" id="IsUri">&nbsp;IsUri&nbsp;&nbsp;
                                </TD> 
                            </TR>
                            <TR>
                                <TD>MenuBar Name En :</TD>
                                <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="MenuBar_NameEn" NAME="MenuBar_NameEn" VALUE="<?php echo $MenuBar_NameEn ?>"></TD>                    
                                <TD>MenuBar Name Th :</TD>
                                <TD><INPUT TYPE="text" class="string_special_characters-f" ID="MenuBar_NameTh" NAME="MenuBar_NameTh" VALUE="<?php echo $MenuBar_NameTh ?>"></TD>
                            </TR>
                            <TR>
                                <TD>MenuBar Desc :</TD>
                                <TD colspan="3"><TEXTAREA TYPE="text" class="string_special_characters-f" ID="MenuBar_Desc" NAME="MenuBar_Desc" style="resize:none; width:98%;" rows="2"><?php echo $MenuBar_Desc ?></TEXTAREA></TD>
                            </TR>
                            <TR>
<!--                                <TD>Icon Image :</TD>
                                <TD><?php // echo form_dropdown('Icon_Image_Id', $Image_List, $Icon_Image_Id, 'id=Icon_Image_Id') ?></TD>-->
                                <TD>Sequence :</TD>
                                <TD colspan="3"><INPUT TYPE="text" class="integer-f string_special_characters-f" ID="Sequence" NAME="Sequence" style="width:20%;" VALUE="<?php echo $Sequence ?>"></TD>     
                            </TR>
                            <TR>
                                <TD>NavigationUri :</TD>
                                <TD colspan="3">
                                    <INPUT TYPE="text" class="required" ID="NavigationUri" NAME="NavigationUri" style="width:98%;" VALUE="<?php echo $NavigationUri ?>">
                                </TD>  
                            </TR>
<!--                            <TR>
                                <TD></TD>
                                <TD colspan="3">
                                    <img id="preview_images" src="#" alt="" height="300" width="200"/>
                                </TD>
                            </TR>-->
                        </TABLE>
                    </TD></TR>
            </TABLE>
        </FORM>
    </BODY>
</HTML>
