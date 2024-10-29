	
jQuery( function($){

	//move to after apply button.
	$('.tablenav-pages').before($('#parent_search_wrap').clone());

	//show new filter form.
	$('#parent_search_wrap').show();

	//submit when dropdown changed.
	$('#parent_search').change(function(){
		$('#parent_search_form').submit();
	});

    if ($('#hidden_id_no_categories').val() !== undefined){

        var id_no_categories = $('#hidden_id_no_categories').val();
        $('#cb-select-'+id_no_categories).hide();
        $('tr#tag-'+id_no_categories+':first strong a').attr("href", "");
        $('#inline_'+id_no_categories).next(".row-actions").children(".edit").css("display", "none");
        $('#inline_'+id_no_categories).next(".row-actions").children(".delete").css("display", "none");
        $('#inline_'+id_no_categories).next(".row-actions").children(".view").css("display", "none");

        var esecuzioneDelayScript;
        function esecuzioneDelayScriptFunc() {
            var id_no_categories = $('#hidden_id_no_categories').val();
            $('#cb-select-'+id_no_categories).hide();
            $('tr#tag-'+id_no_categories+':first strong a').attr("href", "");
            $('#inline_'+id_no_categories).next(".row-actions").children(".edit").css("display", "none");
            $('#inline_'+id_no_categories).next(".row-actions").children(".delete").css("display", "none");
            $('#inline_'+id_no_categories).next(".row-actions").children(".view").css("display", "none");

        }

        $('.alignright').click(function () {
            esecuzioneDelayScript = setTimeout(esecuzioneDelayScriptFunc, 1000);
        });

    }

    if ((pagenow == 'edit-aip_magazine_issue_categories') || (pagenow == 'edit-aip_magazine_issue')){
        $( '#submit' ).click(function()
            { setTimeout(deleteValComboBoxIssueCategories, 1500); }

        );
    }
    /*******************************************************************/
    function deleteValComboBoxIssueCategories(){
        $("#parent").each(function()
            { $("#parent option[value="+$(this).val()+"]").next().remove(); }

        );
    }

});
