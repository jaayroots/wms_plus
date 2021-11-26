<script>
    $(document).ready(function () {
        $('#loader-wrapper').attr("hidden", "true");
        var table = $('#defDataTable2').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "bLengthChange": false, //used to hide the property               
            "sPaginationType": "full_numbers"});
        /**
         * Function for save email
         * @returns {undefined}
         */
        $("#Add").click(function () {
//            $("#data_email_name").text((val1 / val2).toFixed(3));
            var email_name = $("#data_email_name").val().trim();
            if (email_name.length > 0) {
                var array = email_name.split(",");
                var temp_array = [];
                var err_response = "";
                $.each(array, function (i, val) {
                    var email = val.trim();
                    if (email.length > 0) {
                        if (isValidEmailAddress(email)) {
                            temp_array.push(email);
                        } else {
                            err_response += "\rInvalid email format : " + email;
                        }
                    }
                });
                if (confirm("You want to save the data?\r" + err_response)) {
                    $.post("<?php echo site_url("/manage_email/add_email") ?>", {temp_array: temp_array}, function (data) {
                        if (data === "Empty email address, Please check your data") {
                            alert(data);
                        } else {
                            if (data !== "") {
                                alert(data);
                                alert("Jobs complete.");
                                location.reload();
                            } else {

                                alert("Jobs complete.");
                                location.reload();
                            }
                        }
                    });
                }
            } else {
                alert("Please enter your email address atlease one item.");
            }
        });
        //end Add

        $("#btn_save").click(function () {
            var textbox_modify_email = $("#modify_email").val();
            if (textbox_modify_email != "") {
                if (isValidEmailAddress(textbox_modify_email)) { /* do stuff here */
                    $.post("<?php echo site_url("/manage_email/modify_email") ?>", {"modify_email": textbox_modify_email, "email_id": $("#email_id").val()}, function (data) {
                        if (data == "success") {
                            location.reload();
                        } else if (data == "Duplicate") {
                            alert("This e-mail duplicates in the database.");
                        }
                    });
                } else {
                    alert("Please check your e-mail format.");
                }
            } else {
                alert("Email format is no valid.");
            }
        });
        $("#btn_confirm").click(function () {
            $.post("<?php echo site_url("/manage_email/Delete_email") ?>", {"id_image_trash": $("#id_image_trash").val()}, function (data) {
                console.log(data);
                if (data == "success") {
                    location.reload();
                }
            });
        });
        $.post("<?php echo site_url("/manage_email/Get_data_table") ?>", function (data) {
            table.fnDestroy();
            if (!$.isEmptyObject(data)) {
                var newHTML = [];
                var i = 1;
                $.each(data.list, function (key, value) {
                    newHTML.push("<tr>" +
                            "<td align='center'>" + i + "</td>" +
                            "<td align='center'>" + value.Email_Name + "</td>" +
                            "<td align='center'>" + value.btn_edit + "</td>" +
                            "<td align='center'>" + value.btn_delete + "</td>" +
                            "</tr>"
                            );
                    i++;
                });
                $("#div_t_body").html(newHTML.join(""));
            }

            $('#defDataTable2').dataTable({
                "bJQueryUI": true,
                "bSort": false,
                "bAutoWidth": false,
                "bLengthChange": false, //used to hide the property               
                "sPaginationType": "full_numbers"});
            $(".btn_edit").click(function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                $("#email_id").val(id); // เก็บ ID ไว้ใน Input

                $.post("<?php echo site_url("/manage_email/Get_modify_email") ?>", {"email_id": id}, function (data) {
                    if (data != "") {
                        $("#modify_email").val(data);
                    } else {
                        alert("Not list email name");
                    }
                });
            });
            $(".btn_delete").click(function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                $("#id_image_trash").val(id); // เก็บ ID ไว้ใน Input
            });
        }, "json");

        $("#send_mail").click(function () {
            if (confirm('Do you to send email?')) {
                $('#loader-wrapper').removeAttr('hidden');
                $.post("<?php echo site_url("manage_email/Send_email") ?>", function (data) {
                    if (data == 'success') {
                        $('#loader-wrapper').attr("hidden", "true");
                        alert("Send data success.");
                    } else if (data == 'no_data') {
                        $('#loader-wrapper').attr("hidden", "true");
                        alert("No email. Please add email");
                    }
                }, 'json');
            }
        });

    }); //end document

    function isValidEmailAddress(emailAddress) {

        var pattern = new RegExp(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+((\.[a-zA-Z]{2,30})|(\.[a-zA-Z]{2,30}\.[a-zA-Z]{2,30}))$/ig);
        return pattern.test(emailAddress);
    }


</script>
<style>
    #modal_edit {
        width: 1024px; /* SET THE WIDTH OF THE MODAL MODIFY E-MAIL */
        margin: -250px 0 0 -212px; 
    }
    #modal_delete {
        width: 1024px; /* SET THE WIDTH OF THE MODAL DELETE E-MAIL */
        margin: -250px 0 0 -312px;
    }

    #loader-wrapper .loader-section {
        position: fixed;
        top: 0;
        width: 50%;
        height: 100%;
        background: #222222;
        z-index: 1000;
    }

    #loader-wrapper .loader-section.section-left {
        left: 0;
        opacity: 0.95;
    }

    #loader-wrapper .loader-section.section-right {
        right: 0;
        opacity: 0.95;
    }

    .loader {
        position:absolute;             
        border: 5px solid #f3f3f3;
        border-radius: 50%;
        border-top: 5px solid #3498db;
        width: 40px;
        height: 40px;
        top: 30%;
        left: 50%;
        z-index: 1001;       
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

</style>

<div id="loader-wrapper" hidden>
    <div class="loader"></div>
    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>

</div>

<TR class="content" style='height:100%' >
    <TD>

        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="email_form" name="email_form">
            <fieldset style="margin:0px auto;">
                <legend>Add E-mail Address </legend>
                <table cellpadding="1" cellspacing="1" border="0" style="width:50%; margin:15px auto;" align="left">
                    <tr >
                        <td style="width: 120px;" align="center">
                            Email Address:
                        </td>
                        <td  style="width: 320px;" align="left" >
                            <textarea rows="3" cols="100" warp="virtual" type="text" ID="data_email_name" NAME="EmailAddress"  placeholder="Example : xxx@gmail.com"></textarea>
                        </td>
                        <td align="left" >
                            <input type="button" name="add" value="add" id="Add" class="button dark_blue"/>
                            <input type="button" value="send mail" id="send_mail" class="button dark_blue"/>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="margin:0px auto;">
                <legend>Result</legend>
                <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                    <thead>
                        <tr>
                            <th name="number" id="number">NO.</th>
                            <th name="list_email" id="list_email">Email Address</th>
                            <th name="edit" id="edit">Edit</th>
                            <th name="delete" id="del">Delete</th>
                        </tr>
                    </thead>
                    <tbody id="div_t_body">

                    </tbody>
                </table>
            </fieldset>

        </form> 
    </TD> 
</tr>  

<!--Modal--> 
<div style="width: 350px" id="modal_edit" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Edit Email</h3>
    </div>
    <div class="modal-body">
        <p><input type="text" style="width: 260px"  ID="modify_email" NAME="Email Address" placeholder="Example : xxx@gmail.com" VALUE=""></p>
        <input type="hidden" style="width: 260px"  ID="email_id" NAME="email_id">

    </div>
    <div class="modal-footer">
        <button class="btn btn-primary"  id = "btn_save" name = "btn_save" aria-hidden="true">Save</button>

        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>

<div style="width: 550px" id="modal_delete" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-body">
        <p  style="font-size: 18px;" id = "label_delete">Confirm delete this email?</p>

        <input type="hidden" style="width: 260px"  ID="id_image_trash" NAME="id_image_trash">
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" id = "btn_confirm" name = "btn_confirm">Confirm</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>  
    </div>
</div>

<!--End Modal--> 







