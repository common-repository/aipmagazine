var $aip_magazine_widgets = jQuery.noConflict();

$aip_magazine_widgets(document).ready(function($) {

    readyValcomboBox()
    changeValcomboBox()



    $("input[name='savewidget']").click(function(){

        //imposto una variabile e ci associo l'attributo id del trigger
        //che ho cliccato (in questo caso .testo)
        var recupero_id = $(this).attr("id");


        //da qui in poi potete usare l'id recuperato per fare qualcosa
        //in questo caso faccio apparire un alert con dentro l'id recuperato.
        var count = recupero_id.indexOf('aipmagazine_article_list');
        if (count > 0){
            setTimeout(readyValcomboBox, 3000);
            setTimeout(changeValcomboBox, 3000);

        }


    }); //fine click function

/*----------------------------------------------------------------------------------------------------*/
  /*  funzione che serve per trovare il valore selezionato della combobx al ready del DOM*/
    function readyValcomboBox(){
        var val_combox_box = [];
        $(".journal-select option:selected").each(function () {
            val_combox_box.push($(this).val());

        });
        var idx_trovato = ''
        for (var i = 0; i <= val_combox_box.length; i++){
            if (val_combox_box[i] != 'none'){
                idx_trovato = val_combox_box[i];
                break
            }
        }

        if (idx_trovato == 'none') {
            $(".issue-select option").each(function () {
                if ($(this).val() != 'all')  $(this).hide();

            });
            $(".category-select option").each(function () {
                if ($(this).val() != 'all') $(this).hide();

            });
        } else {
            $(".issue-select option").each(function () {
                if ($(this).data("journal-id") == idx_trovato || $(this).val() == 'all') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $(".category-select option").each(function () {
                if ($(this).data("journal-id") == idx_trovato || $(this).val() == 'all') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

        }
    }

    function changeValcomboBox(){
        $( ".journal-select" ).change(function() {
            var journalValue = $(this).val();
            $(".issue-select option").each(function() {

                $(this).removeAttr( "selected" );

                if ( $(this).data("journal-id")==journalValue || $(this).val()=='all'){
                    $(this).show();

                }else{
                    $(this).hide();
                }

            });

            $(".category-select option").each(function() {

                $(this).removeAttr( "selected" );

                if ( $(this).data("journal-id")==journalValue || $(this).val()=='all'){
                    $(this).show();

                }else{
                    $(this).hide();
                }

            });

        });
    }

});
