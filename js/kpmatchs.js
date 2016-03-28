jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#tableMatchs_fr').DataTable( {
        responsive: true,
        "language": {
            "lengthMenu": "Afficher _MENU_ lignes par page",
            "zeroRecords": "Aucun résultat",
            "info": "Page _PAGE_ sur _PAGES_",
            "infoEmpty": "Aucun résultat",
            "infoFiltered": "(filtré sur _MAX_ enregistrements)",
            "search": "Recherche",
            "emptyTable":     "Aucun résultat",
            "infoPostFix":    "",
            "thousands":      " ",
            "loadingRecords": "Chargement...",
            "processing":     "Chargement...",
            "paginate": {
                "first":      "Debut",
                "last":       "Fin",
                "next":       "Suiv..",
                "previous":   "Préc."
            }
        },
        "lengthMenu": [[6, 8, 10, 20, 50, -1], [6, 8, 10, 20, 50, "Tous"]],
        initComplete: function () {
            this.api().columns([1,2,3,4]).every( function () {
                var column = this;
                var select = jq('<select><option value="">Tous</option></select>')
                    .appendTo( jq(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = jq.fn.dataTable.util.escapeRegex(
                            jq(this).val()
                        );
 
                        column
                            .search( val, true, false )
                            .draw();
                    } );
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
    } );

    jq('#tableMatchs_en').DataTable( {
        responsive: true,
        "lengthMenu": [[6, 8, 10, 20, 50, -1], [6, 8, 10, 20, 50, "Tous"]],
        initComplete: function () {
            this.api().columns([1,2,3,4]).every( function () {
                var column = this;
                var select = jq('<select><option value="">All</option></select>')
                    .appendTo( jq(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = jq.fn.dataTable.util.escapeRegex(
                            jq(this).val()
                        );
 
                        column
                            .search( val, true, false )
                            .draw();
                    } );
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
    } );

//    jq('.logoA').each(function(){
//        var logoA = jq(this).attr('data-logo');
//        var clubA = jq(this).attr('data-club');
//        jq(this).append('<br><img class="img2" width="28" src="'+logoA+'" alt="'+clubA+'" />')
//    });
//    jq('.logoB').each(function(){
//        var logoB = jq(this).attr('data-logo');
//        var clubB = jq(this).attr('data-club');
//        jq(this).append('<br><img class="img2" width="28" src="'+logoB+'" alt="'+clubB+'" />')
//    });
    jq('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'left',
        content: function () {
            var temp = jq(this).attr('src');
            //alert(temp);
            return '<img class="img-rounded" style="float:right;width:100px;max-width:100px;" src="'+temp+'" />';
        }
    });    

});


