var $aip_magazine_admin = jQuery.noConflict();

$aip_magazine_admin(document).ready(function($) {

 /*--------------------------------------------------------------------------------------------------------------------*/
    // funzione che serve ha modificare i chekbox in radio button nella modifica rapida degli articoli
    $(document).find('.editinline').click(function(event){

        event.preventDefault();
        $('fieldset.inline-edit-col-center.inline-edit-categories').css('display','none');
    });

/*--------------------------------------------------------------------------------------------------------------------*/
    //eliminato il Permalink
    $('#edit-slug-box').css('display','none');

/*--------------------------------------------------------------------------------------------------------------------*/
    //disabilitare i check box dei commenti inerente agli articoli
    var str_name = $("input[name='_wp_http_referer']").val();

    if (str_name != undefined){
        checkCommenti(str_name);
         checkbox_in_radiobox(str_name);
    }
/*----------------------------------------------------------------------------------------------------------------------*/

    function checkCommenti(str){
        var post_new = str_name.indexOf('post-new.php?post_type=aip_article');
        if (post_new > 0){
            $('#comment_status').attr('checked', false);
            $('#ping_status').attr('checked', false);
        }
    }

/*--------------------------------------------------------------------------------------------------------------------*/
    // permette di effettuare l'upload del file pdf nell'edit dell'articolo
    $( 'form#post' ).attr( 'enctype', 'multipart/form-data' );

/*--------------------------------------------------------------------------------------------------------------------*/
    // disabilita il pulsante inserisci nuova rivista
    if (str_name!=null) {
        var str_wp_http_referer = str_name.indexOf('edit-tags.php?taxonomy=aip_magazine_issue_journals');
    }
    if (str_wp_http_referer > 0){
        $( "div.form-field" ).remove();
        $( "input#submit" ).prop( "disabled", true );
        $( "p.submit" ).html('<div class="pro_alert"><h2>You have to install the PRO version to manage multi-journals</h2></div>');
        $( "span.delete" ).remove();
    }




/*--------------------------------------------------------------------------------------------------------------------*/

 function checkbox_in_radiobox(str_name){

     // non visibile 'Add New Journal'
     $('#aip_magazine_issue_journals-adder').css('display','none');
     // non visibile 'Add New Issues'
     $('#aip_magazine_issue-adder').css('display','none');
     // non visibile 'Add New Category'
     $('#aip_magazine_issue_categories-adder').css('display','none');

     var str_wp_http_referer = str_name.indexOf('post-new.php?post_type=aip_article');

     //caso del new post o caso edit in cui il post è stato draft
    if (str_wp_http_referer > 0 ||$('#original_post_status').val() == 'draft' ){
        //nascondere i div che contengono i issue e categories
        $('#aip_magazine_issuediv').css('display','none');
        $('#aip_magazine_issue_categoriesdiv').css('display','none');
        // non checked i checkbox dei journals
        $("ul#aip_magazine_issue_journalschecklist li label input[type=checkbox]").attr('checked',false);
        $("ul#aip_magazine_issue_journalschecklist-pop li label input[type=checkbox]").attr('checked',false);
        // non visibili i checkbox degli issue
        $('ul#aip_magazine_issuechecklist li').css('display','none');
        $('ul#aip_magazine_issuechecklist-pop li').css('display','none');

        // non visibili i checkbox dei categories eccetto No Categories
        $("ul#aip_magazine_issue_journalschecklist li label input[type=checkbox]").each(function() {
            var id_categories_nocategories= $('#journal_categories_hidden-'+$(this).val()).val().split('-');
            for (var c = 0; c < id_categories_nocategories.length; c++){
                $('#popular-aip_magazine_issue_categories-'+id_categories_nocategories[c]).css('display','none');
                $('#aip_magazine_issue_categories-'+id_categories_nocategories[c]).css('display','none');
            }
        });
    }else{

        // caso dell'edit in cui i post sono in stato publish
        // rendo i journals solo visibili come label
        $("ul#aip_magazine_issue_journalschecklist li label").css('display','none');
        $("ul#aip_magazine_issue_journalschecklist li label input[type=checkbox]").each(function() {
            if ($(this).is(":checked")) {
                $('#aip_magazine_issue_journals-'+$(this).val()).children().css('display','block');
                $('#in-aip_magazine_issue_journals-'+$(this).val()).css('display', 'none');
                var id_issue_show = $('#journal_issue_hidden-'+$(this).val()).val().split('-');

                for (var k = 0; k < id_issue_show.length; k++){
                    $('#popular-aip_magazine_issue-'+id_issue_show[k]).css('display','block');
                    $('#aip_magazine_issue-'+id_issue_show[k]).css('display','block');
                }
                var id_categories_show = $('#journal_categories_hidden-'+$(this).val()).val().split('-');
                for (var b = 0; b < id_categories_show.length; b++){
                    $('#popular-aip_magazine_issue_categories-'+id_categories_show[b]).css('display','block');
                    $('#aip_magazine_issue_categories-'+id_categories_show[b]).css('display','block');
                }
            }else{
                if (typeof $('#journal_issue_hidden-'+$(this).val()).val() !== 'undefined'){
                    var id_issue_hidden = $('#journal_issue_hidden-'+$(this).val()).val().split('-');
                     for (var j = 0; j < id_issue_hidden.length; j++){
                        $('#popular-aip_magazine_issue-'+id_issue_hidden[j]).css('display','none');
                        $('#aip_magazine_issue-'+id_issue_hidden[j]).css('display','none');
                    }
                }
                if (typeof $('#journal_categories_hidden-'+$(this).val()).val() !== 'undefined'){
                    var id_categories_hidden = $('#journal_categories_hidden-'+$(this).val()).val().split('-');
                    for (var a = 0; a < id_categories_hidden.length; a++){
                        $('#popular-aip_magazine_issue_categories-'+id_categories_hidden[a]).css('display','none');
                        $('#aip_magazine_issue_categories-'+id_categories_hidden[a]).css('display','none');
                    }
                }
            }
        });
        $("ul#aip_magazine_issue_journalschecklist-pop li label").css('display','none');
        $("ul#aip_magazine_issue_journalschecklist-pop li label input[type=checkbox]").each(function() {
            if ($(this).is(":checked")) {
                $('#popular-aip_magazine_issue_journals-'+$(this).val()).children().css('display', 'block');
                $('#in-popular-aip_magazine_issue_journals-'+$(this).val()).css('display', 'none');
            }
        });

    }

     radioButtonJournals();
     // trasformo i check box dei issue in radio button nella modifica degli articoli
     radioButtonIssue(str_wp_http_referer);
     // trasformo i check box delle rubriche in radio button nella modifica degli articoli
     radioButtonCategories();

 }

/*---------------------------------------------------------------------------------------------------------------------*/
    // Check to see if the 'Delete File' link exists on the page...
    if($('a#wp_custom_attachment_delete').length === 1) {

        // Since the link exists, we need to handle the case when the user clicks on it...
        $('#wp_custom_attachment_delete').click(function(evt) {

            // We don't want the link to remove us from the current page
            // so we're going to stop it's normal behavior.
            evt.preventDefault();

            // Find the text input element that stores the path to the file
            // and clear it's value.
            $('#wp_custom_attachment_url').hide();
            $('#_view_pdf').val('0');

            // Hide this link so users can't click on it multiple times
            $(this).hide();

        });

    } // end if


/*--------------------------------------------------------------------------------------------------------------------*/
    //controlla che ad un nuovo articolo di aip_magazine venga sempre associato un issue
    $('form#post').submit(function() {

      if ( $('ul#aip_magazine_issue_journalschecklist li label input[type=radio]').attr('type')!= undefined){
          var selected = [];
          var selected_journals = [];
          $('ul#aip_magazine_issue_journalschecklist li label input[type=radio]').each(function() {
              if ($(this).is(":checked")) {
                  selected_journals.push($(this).attr('name'));
              }
          });

          if (selected_journals.length == 0){
              alert($('#message_journals').val());
              return false;
          }

          $('ul#aip_magazine_issuechecklist li label input[type=radio]').each(function() {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('name'));
            }
          });
          if (selected.length == 0){

            alert($('#message_issue').val());
            return false;
          }
           return true;

      }

    });


/*-----------------------------------------------------------------------------------------------------------------------*/

    // cambio dei cehckbox in radio button in riferimento al issue
    function radioButtonIssue(str_wp_http_referer_out){
        var idx_issue = []
        $("ul#aip_magazine_issuechecklist li label input[type=checkbox]").each(function() {
            $('#in-aip_magazine_issue-'+$(this).val()).attr('type', 'radio');
            idx_issue.push($(this).val())
        });
        $("ul#aip_magazine_issuechecklist-pop li label input[type=checkbox]").each(function() {
            $('#in-popular-aip_magazine_issue-'+$(this).val()).attr('type', 'radio');

        });

        $("ul#aip_magazine_issuechecklist li label input[type=radio]").each(function() {
            $('#in-aip_magazine_issue-'+$(this).val())
                .click(function () {
                    for (var i = 0; i < idx_issue.length; i++){
                         if ($(this).val() != idx_issue[i] && $('#in-popular-aip_magazine_issue-'+idx_issue[i]).is(":checked")){
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('type','radio');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('checked','');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('checked',false);
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('type','radio');
                         }

                    }
                    $(this).attr('checked',true);
                    $('#in-popular-aip_magazine_issue-'+$(this).val()).attr('checked',true);

                    // per incrementare il valore di ordinamento dell'articolo in base al fascicolo selezionato
                    if (str_wp_http_referer_out > 0 ||$('#original_post_status').val() == 'draft' ) {
                        var num_menu_order = new Number($('#issue_hidden_menu_order_'+$(this).val()).val());
                        $('#menu_order').val(num_menu_order+1);
                    }

                });
        });
        $("ul#aip_magazine_issuechecklist-pop li label input[type=radio]").each(function() {
            $('#in-popular-aip_magazine_issue-'+$(this).val())
                .click(function () {
                    for (var i = 0; i < idx_issue.length; i++){
                        if ($(this).val() != idx_issue[i] && $('#in-popular-aip_magazine_issue-'+idx_issue[i]).is(":checked")){
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue-'+idx_issue[i]).attr('type','radio');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('checked','');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('checked',false);
                            $('#in-aip_magazine_issue-'+idx_issue[i]).attr('type','radio');
                        }

                    }
                    $(this).attr('checked',true);
                    $('#in-aip_magazine_issue-'+$(this).val()).attr('checked',true);

                    // per incrementare il valore di ordinamento dell'articolo in base al fascicolo selezionato
                    if (str_wp_http_referer_out > 0 ||$('#original_post_status').val() == 'draft' ) {
                        var num_menu_order = new Number($('#issue_hidden_menu_order_'+$(this).val()).val());
                        $('#menu_order').val(num_menu_order+1);
                    }
                });
        });
    };

/*-----------------------------------------------------------------------------------------------------------------------*/
    // cambio dei cehckbox in radio button in riferimento alla rubrica
    function radioButtonCategories(){
        var idx_issue_categories = []
        $("ul#aip_magazine_issue_categorieschecklist li label input[type=checkbox]").each(function() {
            $('#in-aip_magazine_issue_categories-'+$(this).val()).attr('type', 'radio');
            idx_issue_categories.push($(this).val())

        });
        $("ul#aip_magazine_issue_categorieschecklist-pop li label input[type=checkbox]").each(function() {
            $('#in-popular-aip_magazine_issue_categories-'+$(this).val()).attr('type', 'radio');
        });

        $("ul#aip_magazine_issue_categorieschecklist li label input[type=radio]").each(function() {
            $('#in-aip_magazine_issue_categories-'+$(this).val())
                .click(function () {
                    for (var i = 0; i < idx_issue_categories.length; i++){
                        if ($(this).val() != idx_issue_categories[i] && $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).is(":checked")){
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','radio');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked','');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked',false);
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','radio');
                        }

                    }
                    $(this).attr('checked',true);
                    $('#in-popular-aip_magazine_issue_categories-'+$(this).val()).attr('checked',true);
                });
        });
        $("ul#aip_magazine_issue_categorieschecklist-pop li label input[type=radio]").each(function() {
            $('#in-popular-aip_magazine_issue_categories-'+$(this).val())
                .click(function () {
                    for (var i = 0; i < idx_issue_categories.length; i++){
                        if ($(this).val() != idx_issue_categories[i] && $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).is(":checked")){
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','radio');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked','');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('checked',false);
                            $('#in-aip_magazine_issue_categories-'+idx_issue_categories[i]).attr('type','radio');
                        }

                    }
                    $(this).attr('checked',true);
                    $('#in-aip_magazine_issue_categories-'+$(this).val()).attr('checked',true);
                });
        });

    };

/*-----------------------------------------------------------------------------------------------------------------------*/
    // cambio dei cehckbox in radio button in riferimento alla rivista
    function radioButtonJournals(){
        var idx_issue_journals = []

        $("ul#aip_magazine_issue_journalschecklist li label input[type=checkbox]").each(function() {
            $('#in-aip_magazine_issue_journals-'+$(this).val()).attr('type', 'radio');
            idx_issue_journals.push($(this).val())

        });
        $("ul#aip_magazine_issue_journalschecklist-pop li label input[type=checkbox]").each(function() {
           $('#in-popular-aip_magazine_issue_journals-'+$(this).val()).attr('type', 'radio');
        });


        $("ul#aip_magazine_issue_journalschecklist li label input[type=radio]").each(function() {
            //idx_issue_journals.push($(this).val())
            $('#in-aip_magazine_issue_journals-'+$(this).val())
                .click(function () {
                    $('#aip_magazine_issuediv').css('display','block');
                    $('#aip_magazine_issue_categoriesdiv').css('display','block');

                    for (var i = 0; i < idx_issue_journals.length; i++){
                        if ($(this).val() != idx_issue_journals[i] && ($('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).is(":checked")== false|| $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).is(":checked") == false)){
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','radio');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked','');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked',false);
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','radio');
                            var id_issue_hidden = $('#journal_issue_hidden-'+idx_issue_journals[i]).val().split('-');
                            for (var j = 0; j < id_issue_hidden.length; j++){
                                $('#popular-aip_magazine_issue-'+id_issue_hidden[j]).css('display','none');
                                $('#aip_magazine_issue-'+id_issue_hidden[j]).css('display','none');
                            }
                            var id_categories_hidden = $('#journal_categories_hidden-'+idx_issue_journals[i]).val().split('-');
                            //alert('hidden:'+id_categories_hidden);
                            for (var a = 0; a < id_categories_hidden.length; a++){
                                $('#popular-aip_magazine_issue_categories-'+id_categories_hidden[a]).css('display','none');
                                $('#aip_magazine_issue_categories-'+id_categories_hidden[a]).css('display','none');
                            }
                        }
                    }
                    $(this).attr('checked',true);
                    $('#in-popular-aip_magazine_issue_journals-'+$(this).val()).attr('checked',true);
                    var id_issue_show = $('#journal_issue_hidden-'+$(this).val()).val().split('-');

                    for (var k = 0; k < id_issue_show.length; k++){
                        $('#popular-aip_magazine_issue-'+id_issue_show[k]).css('display','block');
                        $('#aip_magazine_issue-'+id_issue_show[k]).css('display','block');
                    }
                    var id_categories_show = $('#journal_categories_hidden-'+$(this).val()).val().split('-');
                    //alert('show:'+id_categories_show);
                    for (var b = 0; b < id_categories_show.length; b++){
                        $('#popular-aip_magazine_issue_categories-'+id_categories_show[b]).css('display','block');
                        $('#aip_magazine_issue_categories-'+id_categories_show[b]).css('display','block');
                    }
                });
        });

        $("ul#aip_magazine_issue_journalschecklist-pop li label input[type=radio]").each(function() {
            $('#in-popular-aip_magazine_issue_journals-'+$(this).val())
                .click(function () {
                    $('#aip_magazine_issuediv').css('display','block');
                    for (var i = 0; i < idx_issue_journals.length; i++){
                        if (($(this).val() != idx_issue_journals[i] && $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).is(":checked")== false|| $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).is(":checked") == false)){
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked','');
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','checkbox');
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked',false);
                            $('#in-popular-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','radio');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked','');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','checkbox');
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('checked',false);
                            $('#in-aip_magazine_issue_journals-'+idx_issue_journals[i]).attr('type','radio');
                            var id_issue_hidden_pop = $('#journal_issue_hidden-'+idx_issue_journals[i]).val().split('-');
                            for (var j = 0; j < id_issue_hidden_pop.length; j++){
                                $('#popular-aip_magazine_issue-'+id_issue_hidden_pop[j]).css('display','none');
                                $('#aip_magazine_issue-'+id_issue_hidden_pop[j]).css('display','none');
                            }
                            var id_categories_hidden_pop = $('#journal_categories_hidden-'+idx_issue_journals[i]).val().split('-');
                            for (var a = 0; a < id_categories_hidden_pop.length; a++){
                                $('#popular-aip_magazine_issue_categories-'+id_categories_hidden_pop[a]).css('display','none');
                                $('#aip_magazine_issue_categories-'+id_categories_hidden_pop[a]).css('display','none');
                            }

                        }
                    }
                    $(this).attr('checked',true);
                    $('#in-aip_magazine_issue_journals-'+$(this).val()).attr('checked',true);
                    var id_issue_show_pop = $('#journal_issue_hidden-'+$(this).val()).val().split('-');
                    for (var k = 0; k < id_issue_show_pop.length; k++){
                        $('#popular-aip_magazine_issue-'+id_issue_show_pop[k]).css('display','block');
                        $('#aip_magazine_issue-'+id_issue_show_pop[k]).css('display','block');
                    }
                    var id_categories_show_pop = $('#journal_categories_hidden-'+$(this).val()).val().split('-');
                    for (var b = 0; b < id_categories_show_pop.length; b++){
                        $('#popular-aip_magazine_issue_categories-'+id_categories_show_pop[b]).css('display','block');
                        $('#aip_magazine_issue_categories-'+id_categories_show_pop[b]).css('display','block');
                    }

            });
        });

    };
});






