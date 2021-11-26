<?php
/*
 * Create by Ton! 20131213
 */
?>
<SCRIPT>
    $(document).ready(function() {
        document.getElementById("ImageCategory_Code").readOnly = true;
        $('#BrowseItem').val('');
        $('#ImageDesc').val('');
        $("input[type=submit]").attr("disabled", "disabled");

        $('.required').each(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });

        $('[name="BrowseItem"]').change(function() {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        showMessageBox('<?php echo $response ?>');
    });

    function showMessageBox(response) {
        switch (response) {
            case "OK" :
                {
                    alert("Save & Upload Image Successful.");
                }
                break;
            case "ERROR" :
                {
                    alert("Save & Upload Image Not successful!!");
                }
                break;
//            default :{
//
//                }
//                break;
        }
    }

    function backToList() {// back to list of image_gallery page.
        window.location = "<?php echo site_url() ?>/image_gallery";
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#preview_images').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function getImageItem($ImageItem_Id, $type) {
        $('#type').val('A');
        $('#ImageItem_Id').val('');
        $('#ImageDesc').val('');
        $("#Active").prop("checked", false);
        $('#BrowseItem').val('');
        $('#ImageName').val('');
        $('#ImageExt').val('');

        if ($type === 'E') {
            $('#type').val('E');
            $('#ImageItem_Id').val($ImageItem_Id);
            $.post('<?php echo site_url() . "/image_gallery/get_image_itme_detail" ?>', {ImageItem_Id: $ImageItem_Id}, function(data) {
                if (data.length > 0) {
                    $.each($.parseJSON(data), function(i, obj) {
                        $('#ImageDesc').val(obj.ImageDesc);
                        if (obj.Active === 1) {
                            $("#Active").prop("checked", true);
                        } else {
                            $("#Active").prop("checked", false);
                        }
                        $('#BrowseItem').removeClass('required');
                        $('#ImageName').val(obj.ImageName);
                        $('#ImageExt').val(obj.ImageExt);
//                        $('#preview_images').attr('src', '<?php // echo base_url()      ?>/uploads/ffi/images/' + obj.ImageName + obj.ImageExt);// Edit by Ton! 20140318
                        $('#preview_images').attr('src', '<?php echo base_url() . $display_path ?>' + obj.ImageName + obj.ImageExt);// Edit by Ton! 20140320
                        $("input[type=submit]").removeAttr("disabled");
                    });
                }
            });
        }
    }

    function checkfile(sender) {
        var validExts = new Array(".png", ".gif ", ".jpg");
        var fileExt = sender.value;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));

        if (validExts.indexOf(fileExt) < 0) {
            alert("Invalid file selected, valid files are of " + validExts.toString() + " types.");
            $('#BrowseItem').val('');
            $('#ImageName').val('');
            $('#ImageExt').val('');
            $("input[type=submit]").attr("disabled", "disabled");
            $('#preview_images').attr('src', '');
            return;
        }

        if ((sender.files[0].size / 1024) > 200) {
            alert("Invalid file selected, valid files no larger than 200 kb.");
            $('#BrowseItem').val('');
            $('#ImageName').val('');
            $('#ImageExt').val('');
            $("input[type=submit]").attr("disabled", "disabled");
            $('#preview_images').attr('src', '');
            return;
        }

        var str = $('#BrowseItem').val();
        var image = str.split(".");
        console.log(image);
        $("input[type=submit]").removeAttr("disabled");
        // Edit by Ton! 20140318
        $('#ImageName').val(image[0]);
        $('#ImageExt').val("." + image[1]);

        readURL(sender);
        return;
    }

    function submitFrm() {
        if (confirm("You want to save the data Image Gallery?")) {
            var form = $('#frmImageGallery');
            form.attr('action', 'uploadImage');
            form.submit();
        }
    }

</SCRIPT>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
</style>
<HTML>

    <TITLE> Image Gallery </TITLE>
</HEAD>
<BODY>
    <FORM CLASS="form-horizontal" ID="frmImageGallery" NAME="frmImageGallery" METHOD='post' enctype="multipart/form-data">
        <input type="hidden" id="ImageCategory_Id" name="ImageCategory_Id" value="<?php echo $ImageCategory_Id ?>"/>
        <input type="hidden" id="type" name="type"/>
        <TABLE width='95%' align='center'>
            <TR>
                <TD> 
                    <FIELDSET class="well" ><LEGEND>Image Category</LEGEND>
                        <TABLE>
                            <TR>
                                <TD>ImageCategory Code : </TD>
                                <TD colspan="2">
                                    <INPUT TYPE="text" ID="ImageCategory_Code" NAME="ImageCategory_Code" VALUE="<?php echo $ImageCategory_Code . " " . $ImageCategory_NameEN ?>">
                                </TD>
                            </TR>
                        </TABLE>
                    </FIELDSET>
                    <FIELDSET class="well" >
                        <TABLE align="right">
                            <TR>
                                <TD>
                                    <a href="#myModal" role="button" class="button dark_blue" data-toggle="modal" id="AddMenu" ONCLICK="getImageItem(null, 'A');" style="text-align: center;">Add</a> 
                                </TD>
                            </TR>
                        </TABLE>
                    </FIELDSET>
                    <FIELDSET class="well" ><LEGEND>Image Items</LEGEND>
                        <table id="defDataTable" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                            <thead>
                                <tr>
                                    <th>ImageItem Name</th>
                                    <th>Image Desc</th>
                                    <th>Active</th>
                                    <th>Edit</th>  
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($image_item_list)) :
                                    foreach ($image_item_list as $value) :
                                        ?>
                                        <tr id="<?php echo $value->ImageItem_Id; ?>">
                                            <td><?php echo $value->ImageItemName; ?></td>
                                            <td><?php echo $value->ImageDesc; ?></td>
                                            <td><?php echo $value->Active; ?></td>
                                            <td><a href="#myModal" data-toggle="modal" ONCLICK="getImageItem(<?php echo $value->ImageItem_Id; ?>, 'E');"><?php echo img("css/images/icons/edit.png"); ?></a></td>
                                        </tr>
                                        <?php
                                    endforeach;
                                else :
                                    ?>
                                    <tr><td colspan="5">No Data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </FIELDSET>
                </TD>
            </TR>
        </TABLE>

        <!-- Modal -->
        <div style="min-height:500px;padding:5px 10px;display:none;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <!--    <form action="" method="post">-->

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="myModalLabel">Image Item Detail</h3>
            </div>
            <div class="modal-body">
                <TABLE width='100%' align='center'>
                    <TR><TD>
                            <FIELDSET class="well" ><LEGEND>Detail</LEGEND>
                                <TABLE>
                                    <input type="hidden" id="ImageItem_Id" name="ImageItem_Id"/>
                                    <input type="hidden" id="ImageName" name="ImageName"/>
                                    <input type="hidden" id="ImageExt" name="ImageExt"/>
                                    <TR><TD>
                                    <TR>
                                        <TD>Image Desc :</TD>
                                        <TD>
                                            <TEXTAREA TYPE="text" ID="ImageDesc" NAME="ImageDesc" style="resize:none; width:98%;" rows="2"></TEXTAREA>
                                                </TD>
                                            </TR>
                                            <TR>
                                                <TD></TD>
                                                <TD colspan="2">
                                                    <input type="checkbox" id="Active" name="Active">&nbsp;:&nbsp;Active&nbsp;&nbsp;&nbsp;
                                                </TD>
                                            </TR>
                                            <TR>
                                                <TD></TD>
                                                <TD colspan="2">Valid formats: jpeg, gif, png, Max upload: 200kb</TD>
                                            </TR>
                                            <TR>
                                                <TD>Image :</TD>
                                                <TD colspan="2">
                                                    <input type="file" CLASS="required" name="BrowseItem" id="BrowseItem" onchange="checkfile(this);"/>
                                                </TD>
                                            </TR>
                                            <TR>
                                                <TD></TD>
                                                <TD colspan="2">
                                                    <img id="preview_images" src="#" alt="" height="300" width="200"/>
                                                </TD>
                                            </TR>
                                        </TD></TR>
                                </TABLE>
                            </FIELDSET>
                        </TD>
                    </TR>
                </TABLE>                        <!-- // working area-->
            </div>
            <div class="modal-footer">
                <div style="float:right;">
                    <input class="btn btn-primary" value="Save" type="button" id="item_save" onclick="submitFrm();">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                </div>
            </div>
            <!--    </form>-->
        </div>
        </FORM>
        
    </BODY>
</HTML>
