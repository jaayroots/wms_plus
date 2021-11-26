<!--//----------button s-->
    <!--<input type="button" value="Stock Count Tag" class="button dark_blue" id="stock_count_tag" style="" data-toggle="modal" data-target="#Modal_filter">-->
<!--//----------button e-->

<!--//----------modal s-->
    <div class="modal fade" id="Modal_filter" tabindex="1" role="dialog" aria-labelledby="Modal_filterLabel" aria-hidden="true" style="width: 800px; height: 500px;">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="Modal_filterLabel">Generate PDF Stock Count Tag</h5>
            <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
              <!--<span aria-hidden="true">&times;</span>-->
            <!--</button>-->
          </div>
          <div class="modal-body" style="height: 400px;">
         
                <table border="0" style="width: 100%;">
                    <tr style="height: 100%;">
                        <td style="width: 50%;">
                            <table border="0" style="width: 100%;">
                                <tr>
                                    <td style="text-align:right; width: 25%;">Zone :</td>
                                  <td>
                                        <select name="zone" id="zone" style="width: 100%;">                     
                                        </select>
                                </td>   
                                </tr>
                                <tr>
                                    <td style="text-align:right; width: 25%;">Storage :</td>
                                    <td>
                                        <select name="storage" id="storage" style="width: 100%;">                           
                                        </select>
                                    </td> 
                                </tr>
                     <tr>
                        <form CLASS="form-horizontal" ID="form" NAME="form" METHOD='post' ENCTYPE="multipart/form-data" ACTION="<?php echo site_url()?>/import_stock_count_tag/upload">
                         <td style="text-align:right; width: 25%;">ImportFile:</td>
                         <td  style="width: 100%;"><input type="file" name="xfile" id="xfile" onchange="checkfile(this);"/></td>
                        <td><input TYPE="submit" CLASS="button dark_blue" VALUE="SUBMIT" ONCLICK="" ID="submit"> </td>
                </form>
                </tr>   
                
         </table>
                        </td>
                        <td style="width: 20px;">
                          
                        </td>
                        <td>
                            <table border="0" style="width: 250px; margin-top: -25px;" > 
                                <thead style="width: 250px; background-color: #28a0ff; color: white;">
                                    <th>Location List</th>
                                </thead>    
                                <tbody id="list_locat" style="width: 250px; min-height:350px;  max-height:350px; position:fixed; overflow-y:scroll; overflow-x:hidden; background-color: #c4e5ff;">
                                </tbody>
                            </table>
                        </td>
                    </tr>
                  
                </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="gen_pdf()">Generate PDF</button>
          </div>
        </div>
      </div>
    </div>
<!--//----------modal e-->
<!--//----------script s-->
    <script>
    //      $(document).ready(function(){  
    //     $('#xfile').val('');
    //     $("input[type=submit]").attr("disabled", "disabled");
    // });
        $( document ).ready(function() {
             get_zone_list();
             get_storage_list(null);
             get_location_list();
            //  get_import_list();
           
            $('#xfile').val('');
            $("input[type=submit]").attr("disabled", "disabled");
            $("#zone").change(function () {
                var zone_val = this.value;
                if(zone_val == -1){
                   get_storage_list(); 
                }
                else{
                   get_storage_list(zone_val);
                }
                get_location_list();
            });
            
            $("#storage").change(function () {
                get_location_list();
            });
            




        });
        
        function get_zone_list(){
            $.post( "<?php echo site_url(); ?>"+"/pdf_report/get_zone")
            .done(function( data ) {
              zone_list =  jQuery.parseJSON( data );
                $('#zone').append($('<option></option>').val(null).html('ALL'));
                $.each(zone_list, function(index,val_data) {
                    $('#zone').append(
                        $('<option></option>').val(val_data.Zone_Id).html(val_data.Zone_NameEn)
                    );
                });
            });
        }
        
        function get_storage_list(zone_val){
            $.post( "<?php echo site_url(); ?>"+"/pdf_report/get_storage", { zone: zone_val})
            .done(function( data ) {
              storage_list =  jQuery.parseJSON( data );
              $('#storage').empty();
              $('#storage').append($('<option></option>').val(null).html('ALL'));
              $.each(storage_list, function(index,val_data) {
                    $('#storage').append(
                        $('<option></option>').val(val_data.Storage_Id).html(val_data.Storage_NameEn)
                    );
                });
            });
        }
        
        function get_location_list(){
            var zone_val = $('#zone').val();
            var storage_val = $('#storage').val();
            $.post( "<?php echo site_url(); ?>"+"/pdf_report/get_location", { zone: zone_val , storage: storage_val})
            .done(function( data ) {
              location_list =  jQuery.parseJSON( data );
            //   console.log(location_list);
              $('#list_locat').empty();
                    $.each(location_list, function(index,val_data) {                   
                        $('#list_locat').append('<tr><td style="width: 250px; text-align:center;">'+val_data.Location_Code+'</td></tr>')
                    });
            });
        }
       
    function checkfile(sender) {
        var validExts=new Array(".xlsx", ".xls", ".csv");
        var fileExt=sender.value;
        fileExt=fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            alert("Invalid file selected, valid files are of " + validExts.toString() + " types.");
            $("input[type=submit]").attr("disabled", "disabled");
            return false;
        }
        else $("input[type=submit]").removeAttr("disabled"); return true;
    }
    


        
        function gen_pdf(){
            $("#Modal_filter").hide();
            $('#preload').show();          
            var new_file_name = '<?php echo 'Tag-Count-' . date('Ymd') . '-' . date('His') . '.pdf' ?>';
            var zone_val = $('#zone').val();
            var storage_val = $('#storage').val();
            $.post( "<?php echo site_url(); ?>"+"/pdf_report/gen_pdf", { zone: zone_val , storage: storage_val , new_file_name : new_file_name})
            .done(function( data ) {
                 $('#preload').hide();
                      var url_d = '<?php echo site_url(); ?>'+'/../uploads/default/files/'+new_file_name;
//                      console.log(url_d);
                      window.open(url_d, '_blank');
                      
//                      window.location.href = url_d ;
//              storage_list =  jQuery.parseJSON( data );
//              $('#storage').empty();
//              $('#storage').append($('<option></option>').val('-1').html('ALL'));
//              $.each(storage_list, function(index,val_data) {
//                    $('#storage').append(
//                        $('<option></option>').val(val_data.Storage_Id).html(val_data.Storage_NameEn)
//                    );
//                });
                $('#preload').hide();
            });
        }
    </script>
    <style>
        .button2 {
	display: inline-block;
	zoom: 1; /* zoom and *display = ie7 hack for display:inline-block */
	*display: inline;
	vertical-align: baseline;
	margin: 3px; /* change 0px to 3px (Ak)*/
	min-width:90px;
	outline: none;
	cursor: pointer;
	text-decoration: none;
	padding: .3em .3em .33em;
	text-shadow: 0 1px 1px rgba(0,0,0,.3);
	-webkit-border-radius: .5em; 
	-moz-border-radius: .5em;
	-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	-moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
	box-shadow: 0 1px 2px rgba(0,0,0,.2);
	border-radius: .5em;
}


</style>
<!--//----------script e-->