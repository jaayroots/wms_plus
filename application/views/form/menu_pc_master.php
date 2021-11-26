<SCRIPT>
    var callback_count;
    $(document).ready(function () {
<?php if ($Active == true): ?>
            $('#Active_p').val(1);
<?php else: ?>
            $('#Active_p').val(0);
<?php endif; ?>

<?php if ($mode == 'E'): ?>
            $('#btn_save').show();
            $('#add_child').show();
<?php else: ?>
            $('#btn_save').hide();
            $('#add_child').hide();

            document.getElementById("MenuBar_Code_p").readOnly = true;
            document.getElementById("MenuBar_NameEn_p").readOnly = true;
            document.getElementById("MenuBar_NameTh_p").readOnly = true;
<?php endif; ?>

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="MenuBar_Code_p"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('[name="MenuBar_NameEn_p"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
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

        $('[name="Module"]').change(function () {
            if ($(this).val() !== '') {
                $('.action_menu').hide();
            } else {
                $('.action_menu').show();
            }
        });
        $('[name="Module"]').trigger('change', false);

//        $('#myModal')
//                .on('hide', function () {
//                    clear_input_menu();
//                })
//                .on('hidden', function () {
//                    clear_input_menu();
//                })
//                .on('show', function () {
//                    clear_input_menu();
//                })
//                .on('shown', function () {
//                    clear_input_menu();
//                });

//        $("#Icon_Image_Id").change(function () {
//            if ($(this).val() == "0" || $(this).val() == "" || $(this).val() == null) {
//                $('#preview_images').hide();
//                
//            }else{
//                console.log('<?php // echo base_url() ?>' + 'css/images/' + $("#Icon_Image_Id option:selected").text());
//                 $.ajax({
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
//            
//            }
//        });
    });

    function clear_input_menu() {
//        $(":hidden").each(function () {
//            $(this).val("");
//        });
        
        $(":text").each(function () {
            $(this).val("");
        });

        $(":checkbox").each(function () {
            $(this).attr("checked", false);
        });

//        $("#Icon_Image_Id").val("");
        $("#Module").val("");

        $("#preview_images").removeAttr("src");
        $("#preview_images").hide();
    }

    function backToList() {// back to list menu_ber page.
        window.location = "<?php echo site_url() ?>/menu_bar/get_menu_bar_pc_list";
    }

    function validation() {
        $('#preloadfrm').fadeOut('slow', function () {
            $(this).show();
        });

        $("#btn_save").attr("disabled", "disabled");
        var MenuBarCode = $('#MenuBar_Code_p').val();
        var MenuBarNameEn = $('#MenuBar_NameEn_p').val();

        if (MenuBarCode === "") {
            $('#preloadfrm').fadeOut('slow', function () {
                $(this).hide();
                alert("Please input MenuBar Code.");
                $('#MenuBar_Code_p').focus();
                $("#btn_save").removeAttr("disabled");
            });
            return;
        }
        if (!check_special_character(MenuBarCode)) {
            $('#preloadfrm').fadeOut('slow', function () {
                $(this).hide();
                alert("MenuBar Code must not is special Character.");
                $('#MenuBar_Code_P').focus();
                $("#btn_save").removeAttr("disabled");
            });
            return;
        }
        if (MenuBarNameEn === "") {
            $('#preloadfrm').fadeOut('slow', function () {
                $(this).hide();
                alert("Please input MenuBar Name En.");
                $('#MenuBar_NameEn_p').focus();
                $("#btn_save").removeAttr("disabled");
            });
            return;
        }
//        submitFrm();
        check_MenuBar_Code_Parent();
    }

    function check_MenuBar_Code_Parent() {// Check MenuBar_Code_Parent Already.       
        var type = $('#type').val();
        var Current_Code_Parent = $('#Current_Code_Parent').val();
        var MenuBar_Code = $('#MenuBar_Code_p').val();

        if (type === 'A') {
            $.post('<?php echo site_url("/menu_bar/check_menu") ?>', $('#frmMenuPC').serialize(), function (dataCheckA) {
                if (dataCheckA === 1) {
                    $('#preloadfrm').fadeOut('slow', function () {
                        $(this).hide();
                        alert("Have MenuBar Code Already!!");
                        $('#MenuBar_Code_p').focus();
                        $("#btn_save").removeAttr("disabled");
                        return;
                    });
                } else {
                    submitFrm();
                }
            });
        } else {
            if (Current_Code_Parent === MenuBar_Code) {
                submitFrm();
            } else {
                $.post('<?php echo site_url("/menu_bar/check_menu") ?>', $('#frmMenuPC').serialize(), function (dataCheckE) {
                    if (dataCheckE === 1) {
                        $('#preloadfrm').fadeOut('slow', function () {
                            $(this).hide();
                            alert("Have MenuBar Code Already!!");
                            $('#MenuBar_Code_p').focus();
                            $("#btn_save").removeAttr("disabled");
                            return;
                        });
                    } else {
                        submitFrm();
                    }
                });
            }
        }
    }

    function submitFrm() {
        callback_count = 1;
        if (confirm("You want to save the data Menu Master?")) {
            $.post('<?php echo site_url("/menu_bar/save_menu_parent") ?>', $('#frmMenuPC').serialize(), function (dataSave) {
                if (dataSave === '1') {
                    alert("Save Menu PC Master successfully.");
                    backToList();
                } else {
                    callback_count--;
                    alert("Save Menu PC Master unsuccessfully.");
                    $("#btn_save").removeAttr("disabled");
                }
            });
        } else {
            callback_count--;
            $("#btn_save").removeAttr("disabled");
        }

        var initInterval = setInterval(function () {
            if (callback_count === 0) {
                clearInterval(initInterval);
                $('#preloadfrm').fadeOut('slow', function () {
                    $(this).hide();
                });
            }
        }, 1000);
    }

    function validation_Menu_Detail() {
        $('#preload').fadeOut('slow', function () {
            $(this).show();
        });

        $("#menu_save").attr("readonly", true);
        var MenuBarCode = $('#MenuBar_Code').val();
        var MenuBarNameEn = $('#MenuBar_NameEn').val();
        var NavigationUri = $('#NavigationUri').val();

        if (MenuBarCode === "") {
            $('#preload').fadeOut('slow', function () {
                $(this).hide();
                alert("Please input MenuBar Code.");
                $('#MenuBar_Code').focus();
                $("#menu_save").attr("readonly", false);
            });
            return;
        }

        if (!check_special_character(MenuBarCode)) {
            $('#preload').fadeOut('slow', function () {
                $(this).hide();
                alert("MenuBar Code must not is special Character.");
                $('#MenuBar_Code').focus();
                $("#menu_save").attr("readonly", false);
            });

            return;
        }

        if (MenuBarNameEn === "") {
            $('#preload').fadeOut('slow', function () {
                $(this).hide();
                alert("Please input MenuBar Name En.");
                $('#MenuBar_NameEn').focus();
                $("#menu_save").attr("readonly", false);
            });
            return;
        }

        if (NavigationUri === "") {
            $('#preload').fadeOut('slow', function () {
                $(this).hide();
                alert("Please input NavigationUri.");
                $('#NavigationUri').focus();
                $("#menu_save").attr("readonly", false);
            });
            return;
        }

//        if ($('[name="Module"]').val() == "") {
//            if (document.getElementById("IsAdd").checked === false
//                    && document.getElementById("IsEdit").checked === false
//                    && document.getElementById("IsDelete").checked === false) {
//                alert("Please check IsAdd or IsEdit or IsDelete.");
//                return;
//            }
//        }

        check_MenuBar_Code_Child();
    }

    function check_MenuBar_Code_Child() {// Check MenuBar_Code_Child Already.        
        $("#menu_save").attr("readonly", true);
        var type = $('#type').val();
        var Current_Code_Child = $('#Current_Code_Child').val();
        var MenuBar_Code = $('#MenuBar_Code').val();

        if (type === 'A') {
            $.post('<?php echo site_url("/menu_bar/check_menu") ?>', {MenuBar_Code: MenuBar_Code}, function (dataCheckA) {
                if (dataCheckA === 1) {
                    $('#preload').fadeOut('slow', function () {
                        $(this).hide();
                        alert("Have MenuBar Code Already!!");
                        $('#MenuBar_Code').focus();
                        $("#menu_save").attr("readonly", false);
                    });
                } else {
                    submitMenuDetail();
                }
                return;
            });
        } else {
            if (Current_Code_Child === MenuBar_Code) {
                submitMenuDetail();
            } else {
                $.post('<?php echo site_url("/menu_bar/check_menu") ?>', {MenuBar_Code: MenuBar_Code}, function (dataCheckE) {
                    if (dataCheckE === 1) {
                        alert("Have MenuBar Code Already!!");
                        $('#MenuBar_Code').focus();
                        $("#menu_save").attr("readonly", false);
                    } else {
                        submitMenuDetail();
                    }
                    return;
                });
            }
            return;
        }
    }

    function submitMenuDetail() {
        $("#menu_save").attr("readonly", true);
        var type = $('#type').val();
        var MenuBar_Id = $('#MenuBar_Id').val();
        var Parent_Id = $('#MenuBar_Id_p').val();
        var MenuBar_Code = $('#MenuBar_Code').val();
        var MenuBar_NameEn = $('#MenuBar_NameEn').val();
        var MenuBar_NameTh = $('#MenuBar_NameTh').val();
        var MenuBar_Desc = $('#MenuBar_Desc').val();
        var NavigationUri = $('#NavigationUri').val();
        var Sequence = $('#Sequence').val();
        var Module = $('#Module').val();

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

//        var IsView = 0;
//        if ($('#IsView').attr('checked')) {
//            IsView = 1;
//        } else {
//            IsView = 0;
//        }

        var IsAdd = 0;
        if ($('#IsAdd').attr('checked')) {
            IsAdd = 1;
        } else {
            IsAdd = 0;
        }

        var IsEdit = 0;
        if ($('#IsEdit').attr('checked')) {
            IsEdit = 1;
        } else {
            IsEdit = 0;
        }

        var IsDelete = 0;
        if ($('#IsDelete').attr('checked')) {
            IsDelete = 1;
        } else {
            IsDelete = 0;
        }

        callback_count = 1;
        if (confirm("You want to save the data Menu Master?")) {
            $.post('<?php echo site_url("/menu_bar/save_menu_child") ?>', {type: type, MenuBar_Id: MenuBar_Id, Parent_Id: Parent_Id
                , MenuBar_Code: MenuBar_Code, MenuBar_NameEn: MenuBar_NameEn, MenuBar_NameTh: MenuBar_NameTh
                , MenuBar_Desc: MenuBar_Desc, NavigationUri: NavigationUri, Sequence: Sequence, IsUri: IsUri
                , Active: Active, Menu_Type: 'PC', Module: Module
                , IsAdd: IsAdd, IsEdit: IsEdit, IsDelete: IsDelete}, function (dataSave) {
                if (dataSave === '1') {
//                    callback_count--;
                    alert("Save Menu PC Master successfully.");
                    window.location.reload();
                } else {
                    callback_count--;
                    alert("Save Menu PC Master unsuccessfully.");
                    $("#menu_save").attr("readonly", false);
                    return false;
                }
            });
        } else {
            callback_count--;
            $("#btn_save").removeAttr("disabled");
        }

        var initInterval = setInterval(function () {
            if (callback_count === 0) {
                clearInterval(initInterval);
                $('#preload').fadeOut('slow', function () {
                    $(this).hide();
                });
            }
        }, 1000);
    }

    function getMenuDetail($MenuBar_Id, $type) {
        clear_input_menu();
        
        callback_count = 1;
        $('#preload').fadeOut('slow', function () {
            $(this).show();
        });
        $.post('<?php echo site_url("/menu_bar/get_menu_detail") ?>', {MenuBarId: $MenuBar_Id, MenuType: 'PC'}, function (data) {
            if (data.length > 0) {
                var response = $.parseJSON(data);
                $.each(response, function (i, obj) {
                    $('#type').val($type);
                    var ImageId = '';
                    var ModuleId = '';

                    if ($type === 'E') {// Edit
                        $('#MenuBar_Id').val(obj['menu'][0].MenuBar_Id);
                        $('#Parent_Id').val($MenuBar_Id);
                        $('#MenuBar_Code').val(obj['menu'][0].MenuBar_Code);
                        $('#Current_Code_Child').val(obj['menu'][0].MenuBar_Code);
                        if (obj['menu'][0].MenuBar_Code !== '') {
                            $('#MenuBar_Code').removeClass('required');
                        }
                        $('#MenuBar_NameEn').val(obj['menu'][0].MenuBar_NameEn);
                        if (obj['menu'][0].MenuBar_NameEn !== '') {
                            $('#MenuBar_NameEn').removeClass('required');
                        }
                        $('#MenuBar_NameTh').val(obj['menu'][0].MenuBar_NameTh);
                        $('#MenuBar_Desc').val(obj['menu'][0].MenuBar_Desc);
                        $('#NavigationUri').val(obj['menu'][0].NavigationUri);
                        $('#Sequence').val(obj['menu'][0].Sequence);

                        if (obj['menu'][0].IsUri == "1") {
                            $("#IsUri").attr("checked", true);
                        } else {
                            $("#IsUri").attr("checked", false);
                        }
                        if (obj['menu'][0].Active == "1") {
                            $("#Active").attr("checked", true);
                        } else {
                            $("#Active").attr("checked", false);
                        }
                        if (obj['menu'][0].IsAdd == "1") {
                            $("#IsAdd").attr("checked", true);
                        } else {
                            $("#IsAdd").attr("checked", true);
                        }
                        if (obj['menu'][0].IsEdit == "1") {
                            $("#IsEdit").attr("checked", true);
                        } else {
                            $("#IsEdit").attr("checked", true);
                        }
                        if (obj['menu'][0].IsDelete == "1") {
                            $("#IsDelete").attr("checked", true);
                        } else {
                            $("#IsDelete").attr("checked", true);
                        }

//                        ImageId = obj['menu'][0].Icon_Image_Id;
                        ModuleId = obj['menu'][0].Module;

                        if (ModuleId !== '') {
                            $('.action_menu').hide();
                        } else {
                            $('.action_menu').show();
                        }
                    } else {// Add
                        clear_input_menu();
                        $('.action_menu').show();
                    }

//                    var image_list = obj['image'][0];
//                    var dropdownImage = $("#Icon_Image_Id");
//                    dropdownImage.find("option").remove();
//                    $.each(image_list, function (key, val) {
//                        var option = $("<option>").val(key).text(val);
//                        if (key === ImageId) {
//                            option.prop("selected", true);
//                        }
//                        dropdownImage.prepend(option);
//                    });

                    $('#preview_images').attr('src', '<?php echo base_url() ?>' + obj['menu'][0].ImageName);

                    var module_list = obj['module'][0];
                    var dropdownModule = $("#Module");
                    dropdownModule.find("option").remove();
                    $.each(module_list, function (key, val) {
                        var option = $("<option>").val(key).text(val);
                        if (key === ModuleId) {
                            option.prop("selected", true);
                        }
                        dropdownModule.prepend(option);
                    });
                });
            }
            callback_count--;
        });

        var initInterval = setInterval(function () {
            if (callback_count === 0) {
                clearInterval(initInterval);
                $('#preload').fadeOut('slow', function () {
                    $(this).hide();
                });
            }
        }, 1000);
    }

    $('#myModal').modal('toggle').css({
        // make width 90% of screen
        'width': function () {
            return ($(document).width() * 0.95) + 'px';
        },
        // center model
        'margin-left': function () {
            return -($(this).width() / 2);
        }
    });

//    function previewImages() {
//        $('#preview_images').attr('src', '<?php // echo base_url() ?>' + $("#Icon_Image_Id option:selected").text());
//    }

</SCRIPT>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }

    label {
        display: inline;
        margin-bottom: 2px;
    }
</style>
<HTML>
    <HEAD>
        <TITLE> Menu PC </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmMenuPC" NAME="frmMenuPC" METHOD='post' >
            <input type="hidden" id="type_p" name="type_p" value="<?php echo $mode ?>"/>
            <input type="hidden" id="MenuBar_Id_p" name="MenuBar_Id_p" value="<?php echo $MenuBar_Id ?>"/>
            <input type="hidden" id="Current_Code_Parent" name="Current_Code_Parent" value="<?php echo $MenuBar_Code ?>"/>
            <TABLE width='95%' align='center'>
                <TR>
                    <TD>
                        <FIELDSET class="well" ><LEGEND>Menu Parent</LEGEND>
                            <TABLE>
                                <?php
                                $extra_disabled = "";
                                $extra_active = "";
                                if ($mode == 'V'):
                                    $extra_disabled = " disabled=disabled ";
                                endif;
                                if ($Active == TRUE):
                                    $extra_active = " checked=checked ";
                                endif;
                                ?>
                                <TR>
                                    <TD>Menu Code :</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="MenuBar_Code_p" NAME="MenuBar_Code_p" VALUE="<?php echo $MenuBar_Code ?>"></TD>
                                    <TD colspan="2"><?php echo form_checkbox('Active_p', 1, $Active, $extra_disabled . $extra_active); ?>&nbsp;Active&nbsp;&nbsp;</TD>        
                                </TR>
                                <TR>
                                    <TD>MenuBar Name En :</TD>
                                    <TD><INPUT TYPE="text" CLASS="required string_special_characters-f" ID="MenuBar_NameEn_p" NAME="MenuBar_NameEn_p" VALUE="<?php echo $MenuBar_NameEn ?>"></TD>                    
                                    <TD>MenuBar Name Th :</TD>
                                    <TD><INPUT TYPE="text" class="string_special_characters-f" ID="MenuBar_NameTh_p" NAME="MenuBar_NameTh_p" VALUE="<?php echo $MenuBar_NameTh ?>"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                        <FIELDSET class="well" >
                            <TABLE id="add_child" align="right">
                                <TR>
                                    <TD>
                                        <a href="#myModal" role="button" class="button dark_blue" data-toggle="modal" id="AddMenu" ONCLICK="getMenuDetail(null, 'A');" style="text-align: center;">Add</a>            
                                    </TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                        <FIELDSET class="well" >
                            <table id="defDataTable" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                                <thead>
                                    <tr>
                                        <!--<th>Id</th>-->
                                        <th>Menu Code</th>
                                        <th>MenuBar Name En</th>
                                        <th>MenuBar Name Th</th>
                                        <th>Active</th>
                                        <?php if ($mode == 'E'): ?>
                                            <th>Edit</th>    
                                        <?php endif; ?>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($MenuBar_List)) {
                                        foreach ($MenuBar_List as $value) {
                                            ?>
                                            <tr id="<?php echo $value->MenuBar_Id; ?>">
                                                <!--<td><?php //echo $value->MenuBar_Id;                                                                                                                                                                                                                                                                            ?></td>-->
                                                <td><?php echo $value->MenuBar_Code; ?></td>
                                                <td><?php echo $value->MenuBar_NameEn; ?></td>
                                                <td><?php echo $value->MenuBar_NameTh; ?></td>
                                                <td><?php
                                                    if ($value->Active == 1):
                                                        echo 'YES';
                                                    else:
                                                        echo 'NO';
                                                    endif;
                                                    ?>
                                                </td>
                                                <?php if ($mode == 'E'): ?>
                                                    <td><a href="#myModal" data-toggle="modal" ONCLICK="getMenuDetail(<?php echo $value->MenuBar_Id; ?>, 'E');"><?php echo img("css/images/icons/edit.png"); ?></a></td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php
                                        }
                                    }else {
                                        ?>
                                        <tr><td colspan="5">No Data.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </FIELDSET>
                    </TD>
                </TR>
            </TABLE>
        </FORM>
        <!-- Modal -->
        <div style="min-height:500px;padding:5px 10px;display:none;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <!--    <form action="" method="post">-->

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="myModalLabel">Menu Detail</h3>
            </div>
            <div class="modal-body">
                <TABLE width='100%' align='center'>
                    <TR><TD>
                            <FIELDSET class="well" ><LEGEND>Menu Detail</LEGEND>
                                <TABLE>
                                    <input type="hidden" id="type" name="type"/>
                                    <input type="hidden" id="MenuBar_Id" name="MenuBar_Id"/>
                                    <input type="hidden" id="Parent_Id" name="Parent_Id"/>
                                    <input type="hidden" id="Current_Code_Child" name="Current_Code_Child"/>
                                    <TR>
                                        <TD>MenuBar Code :</TD>
                                        <TD><INPUT TYPE="text" class="required string_special_characters-f" ID="MenuBar_Code" NAME="MenuBar_Code"></TD>
                                        <TD colspan="2">
                                            <input type="checkbox" id="Active" name="Active"><label> : Active</label>
                                            <input type="checkbox" id="IsUri" name="IsUri"><label> : IsUri</label>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD>MenuBar Name En :</TD>
                                        <TD><INPUT TYPE="text" class="required string_special_characters-f" ID="MenuBar_NameEn" NAME="MenuBar_NameEn"></TD>                    
                                        <TD>MenuBar Name Th :</TD>
                                        <TD><INPUT TYPE="text" class="string_special_characters-f" ID="MenuBar_NameTh" NAME="MenuBar_NameTh"></TD>
                                    </TR>
                                    <TR>
                                        <TD>MenuBar Desc :</TD>
                                        <TD colspan="3">
                                            <TEXTAREA TYPE="text" class="string_special_characters-f" ID="MenuBar_Desc" NAME="MenuBar_Desc" style="resize:none; width:98%;" rows="2"></TEXTAREA>
                                        </TD>
                                    </TR>
                                    <TR>
<!--                                        <TD>Icon Image :</TD>
                                        <TD>
                                            <select id="Icon_Image_Id" name="Icon_Image_Id" style="width:200;"></select>
                                        </TD>  -->
                                        <TD>Sequence :</TD>
                                        <TD colspan="3"><INPUT TYPE="text" class="integer-f string_special_characters-f" ID="Sequence" NAME="Sequence" style="width:20%;"></TD>     
                                    </TR>
                                    <TR>
                                        <TD>Navigation Uri :</TD>
                                        <TD colspan="3">
                                            <INPUT TYPE="text" class="" ID="NavigationUri" NAME="NavigationUri" style="width:98%;">
                                        </TD>  
                                    </TR>
                                    <TR>
                                        <TD>Module :</TD>
                                        <TD>
                                            <!--<INPUT TYPE="text" class="" ID="Module" NAME="Module">-->
                                            <select id="Module" name="Module" style="width:200;" onchange=""></select>
                                        </TD>  
                                        <TD colspan="2">
<!--                                            <input type="checkbox" id="IsView" name="IsView" class="action_menu action_check" checked="checked" readOnly><label class="action_menu"> : IsView</label>-->
                                            <input type="checkbox" id="IsAdd" name="IsAdd" class="action_menu action_check"><label class="action_menu"> : IsAdd</label>
                                            <input type="checkbox" id="IsEdit" name="IsEdit" class="action_menu action_check"><label class="action_menu"> : IsEdit</label>
                                            <input type="checkbox" id="IsDelete" name="IsDelete" class="action_menu action_check"><label class="action_menu"> : IsDelete</label>
                                        </TD>
                                    </TR>
                                    <TR>
                                        <TD></TD>
                                        <TD colspan="3">
                                            <img id="preview_images" src="#" alt="" height="300" width="200"/>
                                        </TD>
                                    </TR>
                                </TABLE>
                            </FIELDSET>
                        </TD></TR>
                </TABLE>                        <!-- // working area-->
            </div>
            <div class="modal-footer">
                <div style="float:right;">
                    <input class="btn btn-primary" value="Save" type="submit" id="menu_save" ONCLICK='validation_Menu_Detail();'>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                </div>
            </div>
            <!--    </form>-->
            <div id="preload" style="display:none; position: fixed; left: 0; top: 0; z-index: 999; width: 100%; height: 100%; overflow: visible; opacity: 0.5; background: #333 url('<?php echo base_url() ?>/images/loading.gif') no-repeat center center; "></div>
        </div>
        <div id="preloadfrm" style="display:none; position: fixed; left: 0; top: 0; z-index: 999; width: 100%; height: 100%; overflow: visible; opacity: 0.5; background: #333 url('<?php echo base_url() ?>/images/loading.gif') no-repeat center center; "></div>
    </BODY>
</HTML>


