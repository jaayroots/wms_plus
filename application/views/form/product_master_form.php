<?php

// Temp use by ball
$config_master = $this->config->item("_xml");
?>
<style>
    .close{
        margin-left: 15px;
        font-weight: 900;
        color: red;
        opacity: 0.6;
        font-size: 16px;
    }
</style>
<SCRIPT>

    $(document).ready(function () {
        var type = $('#type').val();
        var hs_code_flag = '<?php echo $hs_code_flag ?>'; // Add by Ton! 20131126 พิกัดศุลกากร Show Or Hide.
//        console.log(hs_code_flag);
        if (hs_code_flag === false) {
            $('#HS_Code_Text').hide();
            $('#HS_Code_Input').hide();
            $('#HS_Code').hide();
        }

        var check_data_dimension = <?php echo (!empty($DimensionUnitList) ? 'true' : 'false'); ?>;
        if (type === 'V') {
            $('#btn_clear').hide();
            $('#btn_save').hide();
            $('#ProductCategory_Id').attr("disabled", true);
            $('#ProductGroup_Id').attr("disabled", true);
            $('#ProductBrand_Id').attr("disabled", true);
            document.getElementById("Product_Code").readOnly = true;
            document.getElementById("Product_NameEN").readOnly = true;
            document.getElementById("Product_NameTH").readOnly = true;
            document.getElementById("Product_Desc").readOnly = true;
            document.getElementById("Product_Barcode").readOnly = true;
            document.getElementById("HS_Code").readOnly = true;
            
            document.getElementById("Internal_Barcode1").readOnly = true;
            document.getElementById("Internal_Barcode2").readOnly = true;
            
            $('#Standard_Unit_Id').attr("disabled", true);
            $('#select_template_uom').attr("disabled", true);
            $('#Standard_Unit_In_Id').attr("disabled", true);
            $('#Standard_Unit_Out_Id').attr("disabled", true);
            document.getElementById("Standard_Price").readOnly = true;
            document.getElementById("Min_Aging").readOnly = true;
            document.getElementById("Min_ShelfLife").readOnly = true;
            document.getElementById("Min_Temporary").readOnly = true;
            document.getElementById("Max_Temporary").readOnly = true;
//            document.getElementById("FG_LICSE").readOnly = true;
            $('#FG_LICSE').attr("disabled", true); // Edit by Ton! 20130912
            $('#PickUp_Rule').attr("disabled", true);
            $('#PutAway_Rule').attr("disabled", true);
            document.getElementById("ShapeCode").readOnly = true;
            document.getElementById("STD_Weight").readOnly = true;
            $('#STD_WeightUnit').attr("disabled", true);
            document.getElementById("Width").readOnly = true;
            document.getElementById("Length").readOnly = true;
            document.getElementById("Height").readOnly = true;
            document.getElementById("Cubic_Meters").readOnly = true; // Add by Ton! 20130729
            $('#Dimension_Unit').attr("disabled", true);
            $('#Supplier_Id').attr("disabled", true); // Add by Ton! 20130827
            $("#btn_gen_product_code").attr("disabled", true);
            // Add By Joke 16/06/2016
            $('#Proper_Shipping_Name').attr("disabled", true);
            $('#UN_No').attr("disabled", true);
            $('#IMO_Class').attr("disabled", true);
            $('#Flashpoint').attr("disabled", true);
            $('#case_per_layer').attr("disabled", true);
            $('#layer_per_pallet').attr("disabled", true);
            $('#individual_max_stcking').attr("disabled", true);
            $('#unit_per_pallet').attr("disabled", true);
            $('#PKG').attr("disabled", true);
            $('#DIW_Class').attr("disabled", true);
            $('#Type_of_License').attr("disabled", true);
            $('#Emergency_Contact').attr("disabled", true);
            $("#Safety_Data_Sheet").hide();
            $("#loadfileProduct").show();
            $(".close").hide();
            //END Add By Joke 16/06/2016            

        } else if (type == 'A') {

            if (!check_data_dimension) {
                $("#btn_save").hide();
                alert("UOM Of Dimension is not SET, Please check data.");
            }

            $('#Standard_Unit_Id option[value="PCs"]').attr('selected', 'selected');
            $('#STD_WeightUnit option[value="WU001"]').attr('selected', 'selected');
            $('#Dimension_Unit option[value="DIM002"]').attr('selected', 'selected');
            $('#PickUp_Rule option[value="FIFO"]').attr('selected', 'selected');
            $('#PutAway_Rule option[value="FIFO"]').attr('selected', 'selected');
            $('#Supplier_Id option[value=""]').attr('selected', 'selected'); // Add by Ton! 20130827
            $(".close").hide();
            $("#Safety_Data_Sheet").show();
            $("#loadfileProduct").hide();
            $('#pradStatus').show();
            $("#NotFile").hide();

        } else if (type == 'E') {
            $(".close").show();
            document.getElementById("Product_Code").readOnly = true;
            $('#btn_clear').hide();
            $('#pradStatus').show();
            if (!check_data_dimension) {
                $("#btn_save").hide();
                alert("UOM Of Dimension is not SET, Please check data.");
            }
        }

        $('.required').each(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            }
        });
        $('#Product_Code').keyup(function () {
            var $th = $(this);
            $th.val($th.val().replace(/[^a-zA-Z0-9-_%]/g, function (str) {
                return '';
            })
                    );
            $('#Product_Barcode').val($(this).val());
            if ($(this).val() !== '') {
                $(this).removeClass('required');
                $('#Product_Barcode').removeClass('required');
            } else {
                $(this).addClass('required');
                $('#Product_Barcode').addClass('required');
            }
        });
        $('[name="Product_NameEN"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        $('[name="Product_Barcode"]').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        $('#Supplier_Id').change(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        $('#Emergency_Contact').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });

        $('#product_type').keyup(function () {

            if ($(this).val() !== '') {
                $(this).removeClass('required');
        
            } else {
                $(this).addClass('required');
              
            }
 
        });

        $('#Type_of_License').keyup(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        $('#select_template_uom').change(function () {
            if ($(this).val() !== '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        
        $("#Internal_Barcode1,#Internal_Barcode2,#Product_Barcode").change(function(){
            if($(this).val() !== ''){
                if(($("#Product_Barcode").val() === $("#Internal_Barcode1").val()) || $("#Product_Barcode").val() === $("#Internal_Barcode2").val())
                {
                    alert('Sorry. Alternate Barcode that is already there.');
                    $(this).val('')
                }
                else if(($("#Internal_Barcode1").val() !== '') && ($("#Internal_Barcode2").val() !== '')) //Edit By Wannaporn 09-03-2020
                {
                    if($("#Internal_Barcode1").val() === $("#Internal_Barcode2").val())
                    {
                    alert('Sorry. Alternate Barcode that is already there.');
                    $("#Internal_Barcode2").val('');
                    }
                }
            }
        });
        
        // $("#layer_per_pallet").keyup(function(){
        //     var t = $(this);
        //         t.val(t.val().replace(/^[0-9]*\.?[0-9]*$/, function (str) {
        //             return '';
        //         })
        //     );
        // });
        // $("#case_per_layer").keyup(function(){
        //     var t = $(this);
        //         t.val(t.val().replace(/^[0-9]*\.?[0-9]*$/, function (str) {
        //             return '';
        //         })
        //     );
        // });
        set_list_of_standard_unit();
        $("#loadfileProduct").click(function () {
            window.open('<?php echo site_url('product_master/loadFileProduct?FileName=' . $LinkSafetyDataSheet); ?>', '_blank');
        });
        $("#UN_No").blur(function () {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#UN_No').val()) == false) {
                alert("Please UN No. Fill Number Only.");
                $('#UN_No').val("");
                $('#UN_No').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        });
        $("#Flashpoint").blur(function () {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Flashpoint').val()) == false) {
                alert("Please Flashpoint Fill Number Only.");
                $('#Flashpoint').val("");
                $('#Flashpoint').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        });
//        $("#IMO_Class").blur(function () {
//            var numericReg = /^[0-9]*\.?[0-9]*$/;
//            if (numericReg.test($('#IMO_Class').val()) == false) {
//                alert("Please Class Fill Number Only.");
//                $('#IMO_Class').val("");
//                $('#IMO_Class').focus();
//                $("#btn_save").removeAttr("disabled");
//                return false;
//            }
//        });
        $("#PKG").blur(function () {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#PKG').val()) == false) {
                alert("Please PKG Fill Number Only.");
                $('#PKG').val("");
                $('#PKG').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        });
        $("#Type_of_License").blur(function () {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Type_of_License').val()) == false) {
                alert("Please Type of License Fill Number Only.");
                $('#Type_of_License').val("");
                $('#Type_of_License').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        });

        $(".close").click(function () {
            if (confirm('Confirm delete file?')) {
                $("#Delete_Logfile").val("DeleteFile");
                $("#displayFile").hide();
//                $("#displayFile").html("");
//                $.post('<?php echo site_url() . '/product_master/delete_File' ?>', {fileName: name, Product_id: Product_id}, function (data) {
//                    if (data == "success") {
//                        location.reload();
//                    } else {
//
//                    }
//                }, "json");
            }
        });
    });

 
 


    function checkSpecialCharacterOnForm($str) {
        var iChars = "~`!#$^&*+=[]\\\';,/{}|\":<>?"; //"~`!#$%^&*+=-[]\\\';,/{}|\":<>?";

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

    function isDigit(obj) {
        var strValue = obj.value;
        var strId = obj.id;
//        var numericReg = /^\d*[0-9](|.\d*[0-9]|,\d*[0-9])?$/;
        var numericReg = /^[0-9]*\.?[0-9]*$/;
        if (strValue != "") {
            if (numericReg.test(strValue) == false) {
                alert("Please Fill Number Only.");
//                setFocus(strId);
                $('#' + strId).focus();
                return false;
            }
        }
        cal_cbm();
    }

    function isDigitCBM(obj) {
        var strValue = obj.value;
        var strId = obj.id;
//        var numericReg = /^\d*[0-9](|.\d*[0-9]|,\d*[0-9])?$/;
        var numericReg = /^[0-9]*\.?[0-9]*$/;
        if (strValue != "") {
            if (numericReg.test(strValue) == false) {
                alert("Please Fill Number Only.");
//                setFocus(strId);
                $('#' + strId).focus();
                return false;
            }
        }
        cal_cbm_division();
    }

    function cal_cbm() {
        var mul = 1;
        var cal_cbm = true;
        $('.input_of_cbm').each(function () {
            var this_val = $(this).val();
            if (!this_val || 0 === this_val.length) {
                this_val = 0;
            }
            if (parseFloat(this_val) == 0) {
                cal_cbm = false;
            }
            mul *= this_val;
        });

        if (cal_cbm) {
            $('#Cubic_Meters').val(mul.toFixed(6));
        }
    }

    function cal_cbm_division() {
        $('.input_of_cbm').val('');
    }

    //-------------------------------------------------------//
    function isDigitCase_Layer(obj) {

    var strValue = obj.value;
    var strId = obj.id;

     //var numericReg = /^[0-9]*\.?[0-9]*$/;
     var numericReg =  /^-?\d*$/;
    
    if (strValue != "") {
        if (numericReg.test(strValue) == false) {
            
            var value = $('#' + strId).val();
            var count_str = value.length-1;
            var res = value.substring(-1, count_str);
           
            $('#' + strId).val(res);
            var id_case_per_pallet = $('#layer_per_pallet').val();
         
            var cal_result = id_case_per_pallet * res
        }else if(strValue == "."){
            $('#case_per_layer').val('');
        }else{
            var id_case_per_pallet = $('#layer_per_pallet').val();
            var id_case_per_layer = $('#case_per_layer').val();
            var cal_result = id_case_per_pallet * id_case_per_layer
        }
    }

  
    
    if (cal_result == 0) {
            $('#unit_per_pallet').val('');
        }else if(cal_result == undefined){
            $('#unit_per_pallet').val('');
        }else if(isNaN(cal_result)){
            $('#case_per_layer').val('');
            $('#layer_per_pallet').val('');
            $('#unit_per_pallet').val('');
        }else{
            $('#unit_per_pallet').val(cal_result);
        }

}

function isDigitLayer_Pallet(obj) {

    var strValue = obj.value;
    var strId = obj.id;

    //var numericReg = /^[0-9]*\.?[0-9]*$/;
    var numericReg = /^-?\d*$/;
    if (strValue != "") {
      
        if (numericReg.test(strValue) == false) {
           
            var value = $('#' + strId).val();
            var count_str = value.length-1;
            var res = value.substring(-1, count_str);
         
            $('#' + strId).val(res);
            var id_case_per_layer = $('#case_per_layer').val();
            var cal_result = id_case_per_layer * res
        }else if(strValue == "."){
            $('#layer_per_pallet').val('');
       
        }else{
            var id_case_per_pallet = $('#layer_per_pallet').val();
            var id_case_per_layer = $('#case_per_layer').val();
            var cal_result = id_case_per_pallet * id_case_per_layer
        }
    }
    
       if (cal_result == 0) {
            $('#unit_per_pallet').val('');
        }else if(cal_result == undefined){
            $('#unit_per_pallet').val('');
        }else if(isNaN(cal_result)){
            $('#case_per_layer').val('');
            $('#layer_per_pallet').val('');
            $('#unit_per_pallet').val('');
        }else{
            $('#unit_per_pallet').val(cal_result);
        }
  
    }

    function cal_unit_pallet(obj) {

        var strValue = obj.value;
        var strId = obj.id;

      //var numericReg = /^[0-9]*\.?[0-9]*$/;
       var numericReg = /^-?\d*$/;
                       
      
        if (strValue != "") {
  
        if (numericReg.test(strValue) == false) {
        
            var value = $('#' + strId).val();
            
            var count_str = value.length-1;
            var res = value.substring(-1, count_str);
            $('#' + strId).val(res);
        
            var id_case_per_layer = $('#unit_per_pallet').val();

                }

        }

}

//-----------------------------------------------------------//

    function validation() {
        $("#btn_save").attr("disabled", "disabled");
        var prodCode = $('#Product_Code').val();
        var prodName = $('#Product_NameEN').val();
        var prodType = $('#product_type').val();
        var prodBarcode = $('#Product_Barcode').val();
        var supplier = $('#Supplier_Id').val();
        var standardunit = $('#select_template_uom').val();
        if (prodCode === "") {
            alert("Please input <?php echo _lang('product_code'); ?>");
            $('#Product_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (!checkSpecialCharacterOnForm(prodCode)) {
            alert("<?php echo _lang('product_code'); ?> must not is special Character.");
            $('#Product_Code').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if (prodName == "") {
            alert("Please input Material Description English.");
            $('#Product_NameEN').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (prodType == "") {
            alert("Please input Product type.");
            $('#product_type').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (prodBarcode == "") {
            alert("Please input Material Barcode.");
            $('#Product_Barcode').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if (supplier == "") {
            alert("Please select Supplier.");
            $('#Supplier_Id').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($("#Type_of_License").val() == "") {
            alert("Please input Type of License.");
            $('#Type_of_License').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($("#Emergency_Contact").val() == "") {
            alert("Please input Emergency Contact.");
            $('#Emergency_Contact').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
//
        if (standardunit == "") {
            alert("Please select Standard Unit.");
            $('#standardunit').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#PickUp_Rule option:selected').val() == "") {
            alert("Please select PickUp Rule.");
            $('#PickUp_Rule').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if ($('#PutAway_Rule option:selected').val() == "") {
            alert("Please select PutAway Rule.");
            $('#PutAway_Rule').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }
        if ($('#PickUp_Rule option:selected').val() != $('#PutAway_Rule option:selected').val()) {
            alert("PickUp Rule must be equal PutAway Rule.");
            $('#PickUp_Rule').focus();
            $("#btn_save").removeAttr("disabled");
            return;
        }

        if ($('#Standard_Price').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Standard_Price').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Standard_Price').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Min_Aging').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Min_Aging').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Min_Aging').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Min_ShelfLife').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Min_ShelfLife').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Min_ShelfLife').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Min_Temporary').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Min_Temporary').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Min_Temporary').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Max_Temporary').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Max_Temporary').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Max_Temporary').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#STD_Weight').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#STD_Weight').val()) == false) {
                alert("Please Fill Number Only.");
                $('#STD_Weight').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Width').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Width').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Width').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Length').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Length').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Length').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Height').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Height').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Height').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        if ($('#Cubic_Meters').val() != "") {
            var numericReg = /^[0-9]*\.?[0-9]*$/;
            if (numericReg.test($('#Cubic_Meters').val()) == false) {
                alert("Please Fill Number Only.");
                $('#Cubic_Meters').focus();
                $("#btn_save").removeAttr("disabled");
                return false;
            }
        }

        validation_in_controllers();
    }

    function validation_in_controllers() {// Add by Ton! 20140305
        $.post('<?php echo site_url() . '/product_master/validation' ?>', $("#frmProdMaster").serialize(), function (data) {
            if (data.result === 1) {
                submitFrm();
            } else {
                if (data.note === "PROD_DEL") {
                    alert("Can not be inactive Zone. Zone is already in used. Do not Inactive!");
                }

                if (data.note === "PROD_CODE_ALREADY") {
                    alert("Save unsuccessfully. <?php echo _lang('product_code'); ?> that is already there.");
                }
                
                 var alt = (data.note1 != null ? "<?php echo _lang('Product Barcode') ?>" : '') ;
                    alt += (data.note1 != null ? ' ' : '') + (data.note2 != null ? "<?php echo _lang('Alternate Barcode1') ?>" : '');
                    alt += (data.note1 != null ? ' ' : '') + (data.note2 != null ? ' ' : '') + (data.note3 != null ? "<?php echo _lang('Alternate Barcode2') ?>" : '');
                    if (alt !== '') {
                        alert("Unsuccessfully."+ alt +" that is already there.");
                    }
                
                $("#btn_save").removeAttr("disabled");
                return;
            }
        }, "json");
    }

    function submitFrm() {
        var close = $('#close').val();

         
        if (confirm("You want to save the data Product Master?")) {
            $('#frmProdMaster').submit();
//            window.location.href = "<?php echo site_url() . "/product_master" ?>";
//            $.post('<?php echo site_url() . "/product_master" ?>', $('#frmProdMaster').serialize(), function (data) {
//                if (data === "1") {
//                    if (close == 'C') {
//                        window.close();
//                    } else {
//                        alert("Save successfully.");
//                        window.location = "<?php echo site_url() ?>/product_master";
//                        return;
//                    }
//                } else {
//                    alert("Save Unsuccessfully.");
//                    $("#btn_save").removeAttr("disabled");
//                    return;
//                }
//            }, "html");
        } else {
            $("#btn_save").removeAttr("disabled");
        }
    }

    function clearData() {
        var type = $('#type').val();
        $('#ProductCategory_Id option[value=""]').attr('selected', 'selected');
        $('#ProductGroup_Id option[value=""]').attr('selected', 'selected');
        $('#ProductBrand_Id option[value=""]').attr('selected', 'selected');
        if (type != 'E') {
            $('#Product_Code').val('');
            $('#Product_Code').addClass('required');
        }
        $('#Product_NameEN').val('');
        $('#Product_NameEN').addClass('required');
        $('#Product_NameTH').val('');
        $('#Product_Desc').val('');
        $('#Product_Barcode').val('');
        $('#Product_Barcode').addClass('required');
        $('#Standard_Unit_Id option[value="PCs"]').attr('selected', 'selected');
        $('#select_template_uom option[value="custom"]').attr('selected', 'selected');
        $('#Standard_Unit_In_Id option[value=""]').attr('selected', 'selected');
        $('#Standard_Unit_Out_Id option[value=""]').attr('selected', 'selected');
        $('#Standard_Price').val('');
        $('#Supplier_Id option[value=""]').attr('selected', 'selected');
        $('#Min_Aging').val('');
        $('#Min_ShelfLife').val('');
        $('#Min_Temporary').val('');
        $('#Max_Temporary').val('');
        $('#FG_LICSE option[value=""]').attr('selected', 'selected');
        $('#PickUp_Rule option[value="FIFO"]').attr('selected', 'selected');
        $('#PutAway_Rule option[value="FIFO"]').attr('selected', 'selected');
        $('#ShapeCode').val('');
        $('#STD_Weight').val('');
        $('#STD_WeightUnit option[value="WU001"]').attr('selected', 'selected');
        $('#Width').val('');
        $('#Length').val('');
        $('#Height').val('');
        $('#Dimension_Unit option[value="DIM002"]').attr('selected', 'selected');
        $('#Cubic_Meters').val('');
        $('#HS_Code').val('');
    }

    function backToList() {
        window.location = "<?php echo site_url() ?>/product_master";
    }


    function set_list_of_standard_unit() {
        var dataSet = {
            ProductGroup_Id: $('#ProductGroup_Id').val(),
            ProductBrand_Id: $('#ProductBrand_Id').val(),
            Product_Code: $('#Product_Code').val()
        }

        $.post('<?php echo site_url() . "/uom/ajax_list_of_standard_unit" ?>', dataSet, function (data) {
            var custom_option = '<option value="custom">Custom UOM</option>';
            $('#select_template_uom').html(custom_option); // clear old list

            var tmp_select_template_uom = '';
            $.each(data, function (index, value)
            {
                if (tmp_select_template_uom != value.show_text) {
                    var lists = $('<option>').attr('value', value.val).html(value.show_text);
                    $('#select_template_uom').append(lists);
                }
                tmp_select_template_uom = value.show_text;
            });
            set_template_to_custom();
        }, "json");
    }

    function select_uom() {
        var spl = $('#select_template_uom').val().split('<?php echo SEPARATOR; ?>');
        $('#Standard_Unit_In_Id option').filter(function () {
            return $(this).val() == spl[0];
        }).attr('selected', true);
        $('#Standard_Unit_Out_Id option').filter(function () {
            return $(this).val() == spl[1];
        }).attr('selected', true);
    }


    function set_template_to_custom() {

        var in_id = $('#Standard_Unit_In_Id option:selected').val();
        var out_id = $('#Standard_Unit_Out_Id option:selected').val();
        var str_id = in_id + '<?php echo SEPARATOR; ?>' + out_id;
        var set_selected = 'custom';
        $('#select_template_uom option').each(function () {
            if ($(this).val() == str_id) {
                set_selected = str_id;
            }
        });
        $('#select_template_uom option').filter(function () {
            return $(this).val() === set_selected;
        }).attr('selected', true);
    }

    function add_re_order_point() { // function for append new child in container_re_order_point when need add Re Order Point
        var set_id = $('#container_re_order_point').children().length;
        var type_of_re_order_point = $('#type_of_re_order_point').html();
        var str = '<tr class="container_re_order_point" id="container_re_order_point_' + set_id + '"><td><span style="float:left;font-size: 15px;font-weight: bold;margin: 5px 0 5px 5px;">New</span><span style="float:right;"><a onclick="$(\'#container_re_order_point_' + set_id + '\').remove();" href="javascript:;"> Del </a></span><input type="hidden" name="Re_Order_Point_List[' + set_id + '][id]" id="Re_Order_Point_' + set_id + '_id">' +
                '<table><tbody>' +
                '<tr><td>alias</td><td> : </td><td><input type="text" name="Re_Order_Point_List[' + set_id + '][alias]" id="Re_Order_Point_' + set_id + '_alias"></td></tr>' +
                '<tr><td>type</td><td> : </td><td><select id="Re_Order_Point_' + set_id + '_type" name="Re_Order_Point_List[' + set_id + '][type]">' + type_of_re_order_point + '</select></td></tr>' +
                '<tr><td>name</td><td> : </td><td><input type="text" name="Re_Order_Point_List[' + set_id + '][name]" id="Re_Order_Point_' + set_id + '_name"></td></tr>' +
                '<tr><td>warning_text</td><td> : </td><td><input type="text" name="Re_Order_Point_List[' + set_id + '][warning_text]" id="Re_Order_Point_' + set_id + '_warning_text"></td></tr>' +
                '<tr><td>qty</td><td> : </td><td><input type="text" name="Re_Order_Point_List[' + set_id + '][qty]" id="Re_Order_Point_' + set_id + '_qty" onkeyup="isDigit(this);"></td></tr>' +
                '<tr><td>pass</td><td> : </td><td><input type="radio" value="Y" name="Re_Order_Point_List[' + set_id + '][pass]" id="Re_Order_Point_' + set_id + '_pass" checked="checked"> True <input type="radio" value="N" name="Re_Order_Point_List[' + set_id + '][pass]" id="Re_Order_Point_' + set_id + '_pass"> False</td></tr>' +
                '</tbody></table>' +
                '</td></tr>';
        $('#container_re_order_point').append(str);
    }

    function toggle_re_order_point(toggle) {
        if ($('.Re_Order_Point_radio:checked').val() == 'Y') {
            if (toggle == 1) {
                $('#table_container_re_order_point').toggle();
                $('#manual_re_order_point').toggle();
                if ($('#span_toggle').html() == '-') {
                    $('#span_toggle').html('+');
                } else {
                    $('#span_toggle').html('-');
                }
            } else {
                $('#table_container_re_order_point').show();
                $('#manual_re_order_point').show();
            }
        } else {
            $('#table_container_re_order_point').hide();
            $('#manual_re_order_point').hide();
        }
    }

    $(document).ready(function () {
        toggle_re_order_point();
    });</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Product Master </TITLE>
        <STYLE>
            #container_re_order_point tr.container_re_order_point{
                border:1px solid black;;
            }
        </STYLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmProdMaster" NAME="frmProdMaster" accept-charset="UTF-8" METHOD='post' ENCTYPE="multipart/form-data" ACTION="<?php echo site_url() ?>/product_master/saveProduct">
            <input type="hidden" id="type" name="type" value="<?php echo $mode ?>"/>
            <input type="hidden" id="Product_Id" name="Product_Id" value="<?php echo $Product_Id ?>"/>
            <input type="hidden" id="close" name="close" value="<?php echo $close ?>"/><!--Add by Ton! 20130827-->
            <?php
            if (!isset($ProductCategory_Id)) :
                $ProductCategory_Id = "";
            endif;
            if (!isset($ProductGroup_Id)) :// Add by Ton! 20131209
                $ProductGroup_Id = "";
            endif;
            if (!isset($ProductBrand_Id)) :// Add by Ton! 20131209
                $ProductBrand_Id = "";
            endif;
            if (!isset($Standard_Unit_Id)) :
                $Standard_Unit_Id = "";
            endif;
            if (!isset($PickUp_Rule)) :
                $PickUp_Rule = "";
            endif;
            if (!isset($PutAway_Rule)) :
                $PutAway_Rule = "";
            endif;
            if (!isset($STD_WeightUnit)) :
                $STD_WeightUnit = "";
            endif;
            if (!isset($Dimension_Unit)) :
                $Dimension_Unit = "";
            endif;
            if (!isset($Supplier_Id)) :
                $Supplier_Id = "";
            endif;
            if (!isset($FG_LICSE)) :
                $FG_LICSE = "";
            endif;

            if ((!isset($IsFG)) || ($IsFG != 0)) :
                $IsFG = true;
            else :
                $IsFG = false;
            endif;

            $extra_disabled = "";
            if ($mode === 'V'):
                $extra_disabled = " disabled=disabled ";
            endif;
            ?>
            <!--Start P'Nook change field layout 16 Aug 2013-->
            <TABLE width='95%' align='center'>
                <TR><TD>
                        <FIELDSET  class="well" ><LEGEND>Naming</LEGEND>
                            <TABLE>
                                <TR>
                                    <!--<TD><?php echo _lang('product_category'); ?> : </TD>-->
                                    <TD>DIW Class : </TD>
                                    <TD><?php echo form_dropdown('ProductCategory_Id', $prodCateList, $ProductCategory_Id, 'id=ProductCategory_Id') ?></TD>
                                    <TD><?php echo _lang('product_group'); ?> : </TD><!-- Add by Ton! 20131209 -->
                                    <TD><?php echo form_dropdown('ProductGroup_Id', $ProdGroupList, $ProductGroup_Id, 'id=ProductGroup_Id onChange="set_list_of_standard_unit();"') ?></TD><!-- Add by Ton! 20131209 -->
                                    <TD><?php echo _lang('product_brand'); ?> : </TD><!-- Add by Ton! 20131209 -->
                                    <TD><?php echo form_dropdown('ProductBrand_Id', $ProdBrandList, $ProductBrand_Id, 'id=ProductBrand_Id onChange="set_list_of_standard_unit();"') ?></TD><!-- Add by Ton! 20131209 -->
                                </TR>
                                <TR>
                                    <TD colspan='6'>
                                        <?php
                                        $prod_Active = TRUE;
                                        $prod_InActive = FALSE;
                                        if ($prodActive === 'N'):
                                            $prod_Active = FALSE;
                                            $prod_InActive = TRUE;
                                        endif;
                                        ?>
                                        &nbsp;&nbsp;&nbsp;&nbsp;<?php echo form_radio('pro_active', 'Y', $prod_Active, $extra_disabled); ?>&nbsp;Active&nbsp;&nbsp;
                                        <?php echo form_radio('pro_active', 'N', $prod_InActive, $extra_disabled); ?>&nbsp;InActive&nbsp;&nbsp;
                                    </TD>
                                </TR>
                                <TR>
                                    <TD><?php echo _lang('product_code'); ?> : </TD>
                                    <!--<TD> <INPUT TYPE="text" ID="Product_Code" NAME="Product_Code" VALUE="<?php echo $Product_Code ?>" ONKEYUP="isDigit(this)"></TD>  Comment By Akkarapol, 04/12/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องใช้การเช็ค isDigit แล้วเนื่องจาก Product Code นั้นอาจไม่ได้เอา SKU มาใช้เสมอไป -->
                                    <TD style="width: 400px;"> <INPUT TYPE="text" CLASS="required" ID="Product_Code" NAME="Product_Code" <?php echo (!empty($config_master['length_of_product_code']) ? "MAXLENGTH='{$config_master['length_of_product_code']}'" : ""); ?> VALUE="<?php echo $Product_Code ?>" style="width: 250px;"> <?php if (@$config_master['gen_product_code'] == TRUE) : ?><input type="button" class="button dark_blue" value="Gen ID" id="btn_gen_product_code"><?php endif; ?></TD>
                                    <TD><?php echo _lang('product_barcode'); ?> : </TD>
                                    <!--<TD><INPUT TYPE="text" ID="Product_Barcode" NAME="Product_Barcode" VALUE="<?php echo $Product_Barcode ?>" ONKEYUP="isDigit(this)"></TD> Comment By Akkarapol, 04/12/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องใช้การเช็ค isDigit แล้วเนื่องจาก Product Code นั้นอาจไม่ได้เอา SKU มาใช้เสมอไป ทำให้เกิดผลพวงมาที่ Product Barcode ด้วย -->
                                    <TD>
                                        <!--<INPUT TYPE="text" CLASS="required" ID="Product_Barcode" NAME="Product_Barcode" VALUE="<?php echo $Product_Barcode ?>">-->
                                    </TD>
                                    <TD id="HS_Code_Text">HS Code : </TD>
                                    <TD id="HS_Code_Input"><INPUT TYPE="text" ID="HS_Code" NAME="HS_Code" VALUE="<?php echo $HS_Code ?>"></TD><!-- Add by Ton! 20131126 พิกัดศุลกากร -->
                                </TR>
                                 <TR>
                                    <TD><?php echo _lang('product_barcode'); ?> : </TD>
                                    <!--<TD><INPUT TYPE="text" ID="Product_Barcode" NAME="Product_Barcode" VALUE="<?php echo $Product_Barcode ?>" ONKEYUP="isDigit(this)"></TD> Comment By Akkarapol, 04/12/2013, คอมเม้นต์ทิ้งเพราะไม่ต้องใช้การเช็ค isDigit แล้วเนื่องจาก Product Code นั้นอาจไม่ได้เอา SKU มาใช้เสมอไป ทำให้เกิดผลพวงมาที่ Product Barcode ด้วย -->
                                    <TD><INPUT TYPE="text" CLASS="required" ID="Product_Barcode" NAME="Product_Barcode" VALUE="<?php echo $Product_Barcode ?>"></TD>
                                    
                                    <!-- --------- Alternate Barcode || Add By Wannaporn 06/02/2020----------- -->
                                    <TD><?php echo _lang('Alternate Barcode1'); ?> : </TD>
                                    <TD><INPUT TYPE="text" ID="Internal_Barcode1" NAME="Internal_Barcode1" VALUE="<?php echo $Internal_Barcode1 ?>"></TD>
                                    <TD><?php echo _lang('Alternate Barcode2'); ?> : </TD>
                                    <TD><INPUT TYPE="text" ID="Internal_Barcode2" NAME="Internal_Barcode2" VALUE="<?php echo $Internal_Barcode2 ?>"></TD>

                                    <INPUT TYPE="hidden" ID="TempBarcode1" NAME="TempBarcode1" VALUE="<?php echo $Internal_Barcode1 ?>">
                                    <INPUT TYPE="hidden" ID="TempBarcode2" NAME="TempBarcode2" VALUE="<?php echo $Internal_Barcode2 ?>">
                                    <!-- ------------------------------------------------------------- -->
                               </TR>
                                <TR>
                                    <TD><?php echo _lang('product_name_en'); ?> : </TD>
                                    <TD><INPUT TYPE="text" CLASS="required" ID="Product_NameEN" NAME="Product_NameEN" VALUE="<?php echo $Product_NameEN ?>"></TD>
                                    <TD><?php echo _lang('product_name_th'); ?> : </TD>
                                    <TD><INPUT TYPE="text" style="margin-right: 15px" ID="Product_NameTH" NAME="Product_NameTH" VALUE="<?php echo $Product_NameTH ?>"></TD>
                                    <TD>Proper Shipping Name : </TD>                                   
                                    <TD><input type="text" class="" id="Proper_Shipping_Name" style="margin-right: 15px" name="Proper_Shipping_Name" value="<?php echo $Proper_Shipping_Name ?>"></TD>
                                </TR>
                                <TR>
                                    <TD><?php echo _lang('product_desc'); ?> : </TD>
                                    <!--                                    <TD colspan='3'><INPUT TYPE="text" ID="Product_Desc" NAME="Product_Desc" VALUE="<?php //echo $Product_Desc                                                                                                                                                                                                                                                         ?>"></TD>-->
                                    <TD><INPUT TYPE="text" ID="Product_Desc" NAME="Product_Desc" VALUE="<?php echo $Product_Desc ?>"></TD>
                                    <!------- START ISSUE#2429 by Ton! 20130822 ------->
                                    <TD><?php echo _lang('supplier'); ?> : </TD>
                                    <TD colspan='3'><?php echo form_dropdown('Supplier_Id', $supplierList, $Supplier_Id, 'CLASS="required" id=Supplier_Id') ?></TD>
                                    <!------- END ISSUE#2429 by Ton! 20130822 ------->
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>

                <!--Add By Joke 16/06/2016-->
                <TR>
                    <TD>
                        <FIELDSET  class="well" ><LEGEND>Detail</LEGEND>
                            <TABLE>                              
                                <TR>
                                    <TD>UN No. : </TD>                                   
                                    <TD><input type="text" class="" id="UN_No" style="margin-right: 15px" name="UN_No" value="<?php echo $UN_No ?>"></TD>

                                    <TD>Class : </TD>                                  
                                    <TD><input type="text" id="IMO_Class" style="margin-right: 10px;" name="IMO_Class"  value="<?php echo $IMO_Class ?>"></TD>

                                    <TD>Flashpoint : </TD>
                                    <TD><input type="text" id="Flashpoint" name="Flashpoint" value="<?php echo $Flashpoint ?>" ></TD>

                                    <TD>Case/Layer : </TD>
                                    <TD><input type="text"  id="case_per_layer" style="margin-right: 10px;margin-top: 5px;" name="case_per_layer" value="<?php echo $case_per_layer ?>" ONKEYUP="isDigitCase_Layer(this)" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');"></TD>

                                </TR>
                                <TR>
                                    <TD>PKG : </TD>
                                    <TD><input type="text" class="" id="PKG" style="margin-right: 15px;margin-top: 5px;" name="PKG" value="<?php echo $PKG ?>" ></TD>

                                    <!--                                    <TD>DIW Class : </TD>
                                                                        <TD><input type="text" class="required" id="DIW_Class" style="margin-right: 10px;margin-top: 5px;" name="DIW_Class" value="<?php echo $DIW_Class ?>"></TD>-->

                                    <TD>Type of License : </TD>
                                    <TD><input type="text" class="required" style="margin-right: 10px;margin-top: 5px;" id="Type_of_License" name="Type_of_License" value="<?php echo $Type_of_License ?>" ></TD>

                                    <TD>Emergency Contact : </TD>
                                    <TD><input type="text" class="required" id="Emergency_Contact" style="margin-right: 10px;margin-top: 5px;" name="Emergency_Contact" value="<?php echo $Emergency_Contact; ?>" placeholder="Name"></TD>

                                    <TD>Layer / Pallet : </TD>
                                    <TD><input type="text"    id="layer_per_pallet"  style="margin-right: 10px;margin-top: 5px;" name="layer_per_pallet" value="<?php echo $layer_per_pallet ?>" placeholder="" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');"  ONKEYUP="isDigitLayer_Pallet(this)" ></TD>

                                </TR>
                                <TR>
                                    <TD>Safety Data Sheet : </TD>                                                            
                                    <TD >
                                        <input type="file" style="margin-right: 15px;margin-top: 5px;" id="Safety_Data_Sheet" name="Safety_Data_Sheet" >
                                        <input type="hidden" style="" id="LogFile" name="Log_Data_Sheet"  value="<?php echo $LinkSafetyDataSheet ?>">
                                        <input type="hidden" style="" id="Delete_Logfile" name="Delete_Logfile"  value="">
                                        <?php if ($SafetyDataSheet == "Not_File") { ?>
                                            <LABEL id="NotFile">No File</LABEL>
                                        <?php } else { ?>
                                            <div style="display:-webkit-box" id="displayFile">
                                                <a id="loadfileProduct" style="display: -moz-box;">
                                                    <LABEL style="display: inline;"><?php echo $SafetyDataSheet; ?></LABEL>                                              
                                                </a>
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="float: inherit;">X</button>
                                            </div>
                                        <?php } ?>
                                    </TD>
                                    <TD>Product Type : </TD>
                                    <TD>
                                       
                                        <select class = "required"  name="product_type" id="product_type" required>
                                            <option value="">Please Select</option>
                                            <option value="FG" <?php echo ($product_type == "FG" ? 'selected="selected"' : '');?>>FG</option>
                                            <option value="RM" <?php echo ($product_type == "RM" ? 'selected="selected"' : '');?>>RM</option>
                                            <option value="PM" <?php echo ($product_type == "PM" ? 'selected="selected"' : '');?>>PM</option>
                                            <option value="EQ" <?php echo ($product_type == "EQ" ? 'selected="selected"' : '');?>>EQ</option>
                                            <option value="DWM" <?php echo ($product_type == "DWM" ? 'selected="selected"' : '');?>>DWM</option>
                                            <option value="BOOSTER" <?php echo ($product_type == "BOOSTER" ? 'selected="selected"' : '');?>>Booster</option>
                                        </select>
                                    </TD>

                                    <TD>Individual Max Stacking : </TD>
                                    <TD><input type="text" id="individual_max_stacking" style="margin-right: 10px;margin-top: 5px;" name="individual_max_stacking" value="<?php echo $individual_max_stacking; ?>" placeholder=""   oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"></TD>

                                    <TD>Unit / Pallet : </TD>
                                    <TD><input type="text"  id="unit_per_pallet" name="unit_per_pallet" value="<?php echo $unit_per_pallet ?>" oninput="this.value = this.value.replace(/[^0-9]+/g, '').replace(/(\..*)\./g, '$1');"  ONKEYUP="cal_unit_pallet(this)"></TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD>
                </TR>
                <!--END Add By Jok 16/06/2016-->

                <TR><TD>
                        <FIELDSET  class="well"><LEGEND>Measures</LEGEND>
                            <TABLE>

                                <!--Add By Akkarapol, 16/01/2014, เพิ่มส่วนของ Standar Unit ที่ใช้จาก UOM พร้อมทั้งแยก In และ Out เพื่อนำไปใช้งานต่อไป-->
                                <!--<TR style="border: 1px solid gray;">-->
                                <TR>
                                    <TD>
                                        Standard Unit :
                                    </TD>
                                    <TD>
                                        <?php echo form_dropdown('select_template_uom', $all_uoms, $selected_template_uom, 'CLASS="required" id="select_template_uom" onChange="select_uom();"') ?>
                                    </TD>
                                    <TD>
                                        Standard Unit (IN) :
                                    </TD>
                                    <TD>
                                        <?php echo form_dropdown('Standard_Unit_In_Id', $prodUnitList, $STD_Unit_In_Id, 'id=Standard_Unit_In_Id onChange="set_template_to_custom();"') ?>
                                    </TD>
                                    <TD>
                                        Standard Unit (OUT) :
                                    </TD>
                                    <TD>
                                        <?php echo form_dropdown('Standard_Unit_Out_Id', $prodUnitList, $STD_Unit_Out_Id, 'id=Standard_Unit_Out_Id onChange="set_template_to_custom();"') ?>
                                    </TD>
                                </TR>
                                <!--END Add By Akkarapol, 16/01/2014, เพิ่มส่วนของ Standar Unit ที่ใช้จาก UOM พร้อมทั้งแยก In และ Out เพื่อนำไปใช้งานต่อไป-->

                                <!--Edit By Akkarapol, 16/01/2014, เปลี่ยนการจัดเรียง field ต่างๆ ในหน้าจอให้เหมาะสม-->
                                <!--                            <TR>
                                <TD>Standard Unit : </TD>
                                <TD><?php //echo form_dropdown('Standard_Unit_Id', $prodUnitList, $Standard_Unit_Id, 'id=Standard_Unit_Id')                                                                                                                                                                                                                                    ?>    </TD>
                                <TD>Standard Price : </TD>
                                <TD colspan='3'><INPUT TYPE="text" ID="Standard_Price" NAME="Standard_Price" VALUE="<?php //echo $Standard_Price                                                                                                                                                                                                                                    ?>" ONKEYUP="isDigit(this)"></TD>
                                <TD>&nbsp;</TD>
                                </TR>
                                <TR>
                                <TD>Standard Weight Unit : </TD>
                                <TD><?php //echo form_dropdown('STD_WeightUnit', $weightUnitList, $STD_WeightUnit, 'id=STD_WeightUnit')                                                                                                                                                                                                                                    ?>  </TD>
                                <TD>Standard Weight : </TD>
                                <TD colspan='3'><INPUT TYPE="text" ID="STD_Weight" NAME="STD_Weight" VALUE="<?php //echo $STD_Weight                                                                                                                                                                                                                                    ?>" ONKEYUP="isDigit(this)"></TD>
                                <TD>&nbsp;</TD>
                                </TR>-->

                                <TR>
                                    <TD>Standard Price : </TD>
                                    <TD><INPUT TYPE="text" ID="Standard_Price" style="margin-right: 15px;" NAME="Standard_Price" VALUE="<?php echo $Standard_Price ?>" ONKEYUP="isDigit(this)"></TD>
                                    <TD>Standard Weight Unit : </TD>
                                    <TD><?php echo form_dropdown('STD_WeightUnit', $weightUnitList, $STD_WeightUnit, 'id=STD_WeightUnit') ?>  </TD>
                                    <TD>Standard Weight : </TD>
                                    <TD><INPUT TYPE="text" ID="STD_Weight" NAME="STD_Weight" VALUE="<?php echo $STD_Weight ?>" ONKEYUP="isDigit(this)"></TD>
                                    <TD>&nbsp;</TD>
                                </TR>
                                <!--END Edit By Akkarapol, 16/01/2014, เปลี่ยนการจัดเรียง field ต่างๆ ในหน้าจอให้เหมาะสม-->

                                <TR>
                                    <TD>Dimension Unit : </TD>
                                    <TD><?php echo form_dropdown('Dimension_Unit', $DimensionUnitList, $Dimension_Unit, 'id=Dimension_Unit') ?>  </TD>
                                    <TD>Cubic Meters : </TD>
                                    <TD colspan='3'><INPUT TYPE="text" ID="Cubic_Meters" NAME="Cubic_Meters" VALUE="<?php echo $Cubic_Meters ?>" ONKEYUP="isDigitCBM(this)"></TD>
                                    <TD>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD>Width : </TD>
                                    <TD><INPUT TYPE="text"  size='10' class="input_of_cbm" ID="Width" NAME="Width" VALUE="<?php echo $Width ?>" ONKEYUP="isDigit(this);"></TD>
                                    <TD>Length : </TD>
                                    <TD><INPUT TYPE="text" size='10' class="input_of_cbm" style="margin-right: 15px;" ID="Length" NAME="Length" VALUE="<?php echo $Length ?>" ONKEYUP="isDigit(this);"></TD>
                                    <TD>Height : </TD>
                                    <TD><INPUT TYPE="text" size='10' class="input_of_cbm" ID="Height" NAME="Height" VALUE="<?php echo $Height ?>" ONKEYUP="isDigit(this);"></TD>
                                    <TD>&nbsp;</TD>
                                </TR>
                                <TR>
                                    <TD>Shape : </TD>
                                    <TD><INPUT TYPE="text" ID="ShapeCode" NAME="ShapeCode" VALUE="<?php echo $ShapeCode ?>"></TD>
                                    <TD>FG License : </TD>
                                    <!--<TD><INPUT TYPE="text" ID="FG_LICSE" NAME="FG_LICSE" VALUE="<?php //echo $FG_LICSE                                                                                                                                                                                                                                                 ?>"></TD>-->
                                    <TD colspan='3'><?php echo form_dropdown('FG_LICSE', $FG_LICSE_List, $FG_LICSE, 'id=FG_LICSE') ?></TD><!-- Edit by Ton! -->
                                    <TD>&nbsp;</TD>
                                </TR>
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>
                <TR><TD>
                        <FIELDSET  class="well"><LEGEND>Stock Control</LEGEND>
                            <TABLE>
                                <TR>
                                    <TD>Min Aging : </TD>
                                    <TD><INPUT TYPE="text" ID="Min_Aging" NAME="Min_Aging" VALUE="<?php echo $Min_Aging ?>" ONKEYUP="isDigit(this)"></TD>
                                    <TD>Min ShelfLife : </TD>
                                    <TD colspan='3'><INPUT TYPE="text" ID="Min_ShelfLife" NAME="Min_ShelfLife" VALUE="<?php echo $Min_ShelfLife ?>" ONKEYUP="isDigit(this)"></TD>
                                </TR>
                                <TR>
                                    <TD>Min Temporature : </TD>
                                    <TD><INPUT TYPE="text" ID="Min_Temporary" NAME="Min_Temporary" VALUE="<?php echo $Min_Temporary ?>" ONKEYUP="isDigit(this)"></TD>
                                    <TD>Max Temporature : </TD>
                                    <TD colspan='3'><INPUT TYPE="text" ID="Max_Temporary" NAME="Max_Temporary" VALUE="<?php echo $Max_Temporary ?>" ONKEYUP="isDigit(this)"></TD>
                                </TR>
                                <!--                                <TR>
                                <TD>Re Order Point : </TD>
                                <TD colspan='5'><INPUT TYPE="text" ID="Re_Order_Point" NAME="Re_Order_Point" VALUE="<?php echo $Re_Order_Point ?>" ONKEYUP="isDigit(this)"></TD>
                                </TR>-->
                            </TABLE>
                        </FIELDSET>
                    </TD></TR>

                <?php if ($this->settings['re_order_point']['active']): ?>
                    <TR><TD>
                            <FIELDSET class="well"><LEGEND>Re Order Point <span style='margin:0 0 0 10px;font-size: 25px;font-weight: bold; cursor: pointer;' onclick="toggle_re_order_point(1);" id="span_toggle">-</span></LEGEND>
                                <?php
                                if ($Re_Order_Point == ACTIVE):
                                    $checked_y = 'checked="checked"';
                                    $checked_n = '';
                                else:
                                    $checked_y = '';
                                    $checked_n = 'checked="checked"';
                                endif;
                                ?>
                                <?php
                                echo nbs(4);
                                echo form_radio('Re_Order_Point', ACTIVE, TRUE, 'class="Re_Order_Point_radio" onclick="toggle_re_order_point();" ' . $checked_y . $extra_disabled);
                                ?> Active
                                    <?php
                                    echo nbs(2);
                                    echo form_radio('Re_Order_Point', INACTIVE, FALSE, 'class="Re_Order_Point_radio" onclick="toggle_re_order_point();" ' . $checked_n . $extra_disabled);
                                    ?> InActive
                                <div style="margin:10px 0 0 0;" id="manual_re_order_point" >
                                    <span style="font-size: 1.5em;font-weight: bold;color:red;">
                                        Manual
                                    </span>
                                    <span style="font-size: 1em;font-weight: bold;color:red;">
                                        (warning_text) :
                                    </span>
                                    <span>
                                        product_code = "{product_code}", re_order_point = "{re_order_point}", Total QTY in Stock = "{total_qty}", selected_qty(reserve_qty) = "{selected_qty}", Remain ="{remain_qty}"
                                    </span>
                                </div>

                                <TABLE id='table_container_re_order_point' style='margin: 10px 0 0 0;'>
                                    <TBODY id="container_re_order_point">
                                        <?php foreach ($Re_Order_Point_List as $key_point => $point): ?>
                                            <TR class='container_re_order_point' id='container_re_order_point_<?php echo $key_point; ?>'>
                                                <TD>
                                                    <span style='float:left;font-size: 15px;font-weight: bold;margin: 5px 0 5px 5px;'><?php echo $point['alias']; ?></span>
                                                    <?php if ($mode !== "V"): ?>
                                                        <span style='float:right;'><a href="javascript:;" onClick="$('#container_re_order_point_<?php echo $key_point; ?>').remove();"> Del </a></span>
                                                    <?php endif; ?>
                                                    <INPUT TYPE="hidden" ID="Re_Order_Point_<?php echo $key_point; ?>_id" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][id]" VALUE="<?php echo @$point['id']; ?>">
                                                    <TABLE>
                                                        <TR>
                                                            <TD>alias</TD>
                                                            <TD> : </TD>
                                                            <TD><INPUT TYPE="text" ID="Re_Order_Point_<?php echo $key_point; ?>_alias" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][alias]" VALUE="<?php echo $point['alias']; ?>" readonly='true'></TD>
                                                        </TR>
                                                        <TR>
                                                            <TD>type</TD>
                                                            <TD> : </TD>
                                                            <TD><?php echo form_dropdown('Re_Order_Point_List[' . $key_point . '][type]', $type_of_re_order_point, $point['type'], 'id=Re_Order_Point_' . $key_point . '_type' . $extra_disabled) ?></TD>
                                                        </TR>
                                                        <TR>
                                                            <TD>name</TD>
                                                            <TD> : </TD>
                                                            <TD><INPUT TYPE="text" ID="Re_Order_Point_<?php echo $key_point; ?>_name" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][name]" VALUE="<?php echo $point['name']; ?>" <?php echo $extra_disabled ?>></TD>
                                                        </TR>
                                                        <TR>
                                                            <TD>warning_text</TD>
                                                            <TD> : </TD>
                                                            <TD>
                                                            <!--<INPUT TYPE="text" ID="Re_Order_Point_<?php //echo $key_point;                                                                                                                                                                                                           ?>_warning_text" NAME="Re_Order_Point_List[<?php //echo $key_point;                                                                                                                                                                                                           ?>][warning_text]" VALUE='<?php //echo $point['warning_text'];                                                                                                                                                                                                           ?>'>-->
                                                                <TEXTAREA ID="Re_Order_Point_<?php echo $key_point; ?>_warning_text" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][warning_text]" style="width: 500px; min-height: 100px;" <?php echo $extra_disabled ?>><?php echo $point['warning_text']; ?></TEXTAREA>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD>qty</TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD> : </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD><INPUT TYPE="text" ID="Re_Order_Point_<?php echo $key_point; ?>_qty" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][qty]" VALUE="<?php echo $point['qty']; ?>" onkeyup="isDigit(this);" <?php echo $extra_disabled ?>></TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD>pass</TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD> : </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <INPUT TYPE="radio" ID="Re_Order_Point_<?php echo $key_point; ?>_pass" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][pass]" VALUE="<?php echo ACTIVE; ?>" <?php echo ($point['pass'] == ACTIVE ? 'checked = "checked"' : ''); ?> <?php echo $extra_disabled ?>> Yes
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <INPUT TYPE="radio" ID="Re_Order_Point_<?php echo $key_point; ?>_pass" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][pass]" VALUE="<?php echo INACTIVE; ?>" <?php echo ($point['pass'] == INACTIVE ? 'checked = "checked"' : ''); ?> <?php echo $extra_disabled ?>> No
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD>Active</TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD> : </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <INPUT TYPE="radio" ID="Re_Order_Point_<?php echo $key_point; ?>_active" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][active]" VALUE="<?php echo ACTIVE; ?>" <?php echo (empty($point['active']) == ACTIVE ? 'checked = "checked"' : ($point['active'] == ACTIVE ? 'checked = "checked"' : '')); ?> <?php echo $extra_disabled ?>> True
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <INPUT TYPE="radio" ID="Re_Order_Point_<?php echo $key_point; ?>_active" NAME="Re_Order_Point_List[<?php echo $key_point; ?>][active]" VALUE="<?php echo INACTIVE; ?>" <?php echo (@$point['active'] == INACTIVE ? 'checked = "checked"' : ''); ?> <?php echo $extra_disabled ?>> False
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TABLE>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TR>
                                        <?php endforeach; ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </TBODY>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <TFOOT>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <TD colspan='2'>
                                                <?php if ($mode !== "V"): ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <a onclick="add_re_order_point();" href="javascript:;">Add Re Order Point</a>
                                                <?php endif; ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </TD>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </TR>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </TFOOT>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </TABLE>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </FIELDSET>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </TD></TR>
                <?php endif; ?>
                    <TR><TD>
                            <FIELDSET class="well"><LEGEND>Rules</LEGEND>
                                <TABLE>
                                    <TR>
                                        <TD>PutAway Rule : </TD>
                                        <TD><?php echo form_dropdown('PutAway_Rule', $putAwayRuleList, $PutAway_Rule, 'id=PutAway_Rule') ?></TD>
                                        <TD>PickUp Rule : </TD>
                                        <TD colspan='3'><?php echo form_dropdown('PickUp_Rule', $pickUpRuleList, $PickUp_Rule, 'id=PickUp_Rule') ?></TD>
                                    </TR>
                                    <TR>
                                        <TD Colspan='6'>
                                        <?php echo form_checkbox('MustQC', 1, $MustQC, $extra_disabled); ?>&nbsp;Must QC&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsRawMat', 1, $IsRawMat, $extra_disabled); ?>&nbsp;Is RawMat&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsFG', 1, $IsFG, $extra_disabled); ?>&nbsp;Is FG&nbsp;&nbsp;
                                        <?php echo form_checkbox('IsMachine', 1, $IsMachine, $extra_disabled); ?>&nbsp;Is Machine&nbsp;&nbsp;
                                        </TD>
                                    </TR>
                                </TABLE>
                            </FIELDSET>
                        </TD></TR>
                    <!-- comment ไว้ก่อน By Por 2013-09-27
<TR><TD>
<div id="pradStatus" style="display:none;">
<FIELDSET class="well"><LEGEND>Putaway by Product Status</LEGEND>
<TABLE>
<TR>
-->
<!--BY POR 2013-09-26 แก้ไขจาก form_checkbox('xxx', 1, TRUE) เป็น FALSE เนื่องจากจะไม่มีการปรับระบบใหม่แล้ว-->
<!-- comment ไว้ก่อน
<TD>Normal&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_Normal', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Pending&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_Pending', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Credit Note&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_CreditNote', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Non-Conform&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_NonConform', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Damage&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_Damage', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Grade&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_Grade', 1, FALSE); ?>&nbsp;&nbsp;</TD>
<TD>Shortage&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_Shortage', 1, FALSE); ?>&nbsp;&nbsp;</TD>
-->
<!--<TD>Re-Pack&nbsp;&nbsp;:&nbsp;&nbsp;<?php echo form_checkbox('prodStatus_RePack', 1, FALSE); ?>&nbsp;&nbsp;</TD>-->
<!--
</TR>
</TABLE>
</FIELDSET>
</div>
</TD></TR>
-->
            </TABLE>
            <!--End P'Nook change field layout 16 Aug 2013-->
        </FORM>
<script>
    $(document).ready(function () {
        $("#btn_gen_product_code").click(function () {
            if ($("#Product_NameEN").val().length == 0) {

                //alert("Please specific your product description first!!");
                $("#confirm_new_product").trigger("click");
            } else {
                //                            alert("BBBB"); 
                $.post("<?php echo site_url("/product_master/search_product_code") ?>", {criteria: $("#Product_NameEN").val()}, function (r) {
                    if (r[0] == "OK") {
                        $("#Product_Code").val(r[1]).removeClass("required");
                        $("#Product_Barcode").val(r[1]).removeClass("required");
                    } else {
                        $("#product_code_data tr").remove();
                        $.each(r[1], function (i, v) {
                            $("#product_code_data").append("<tr><td>" + v.Product_Code + "</td><td>" + v.Product_NameEN + "</td></tr>");
                        });
                        $("#modal_product_code").modal("show");
                        $("#product_code_table").dataTable({
                            "bDestroy": true,
                            "bJQueryUI": true,
                            "sPaginationType": "full_numbers",
                            "aaSorting": [[0, "desc"]]
                        });
                    }
                }, "json");
            }
        });
        $("#confirm_new_product").click(function () {
            //alert("rAAA");
            $.post("<?php echo site_url("/product_master/generate_product_code") ?>", {}, function (r) {
                alert(r);
                $("#Product_Code").val(r).removeClass("required");
                $("#Product_Barcode").val(r).removeClass("required");
                $("#modal_product_code").modal("hide");
            }, "json");
        });
        $('#modal_product_code').on('hidden.bs.modal', function () {
            $("#product_code_table").dataTable().fnDestroy();
        });
    });

                                                                                                                                                                                                        </script>
<div class="modal fade bs-modal-lg" id="modal_product_code" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Product Code Search</h4>
			</div>
			<div class="modal-body" style="overflow-x:auto;overflow-y:scroll; height: 300px;">
				<table id="product_code_table" class="dataTable">
					<thead>
						<tr>
							<th>Product Code</th>
							<th>Product Name</th>
						</tr>
					</thead>
					<tbody id="product_code_data"></tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
				<button type="button" class="btn blue" id="confirm_new_product">Confirm New Product</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

        <?php
        echo form_dropdown('type_of_re_order_point', $type_of_re_order_point, NULL, 'id="type_of_re_order_point" style="display:none;"') // Add By Akkarapol, 15+16/01/2014, Add Template Of UOM for use in another field
        ?>
    </BODY>
</HTML>


