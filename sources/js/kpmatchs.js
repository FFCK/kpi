jq = jQuery.noConflict();
var listVal;
jq(document).ready(function(){
    table = jq('#tableMatchs_fr').DataTable( {
        "dom": 'lfrtip',
        responsive: true,
        stateSave: false,
        fixedHeader: true,
        "search": {
            "smart": false
        },
        searchHighlight: true,
        "language": {
            "lengthMenu": "Afficher _MENU_ lignes",
            "zeroRecords": "Aucun résultat",
            "info": "Page _PAGE_ sur _PAGES_",
            "infoEmpty": "Aucun résultat",
            "infoFiltered": "(filtré sur _MAX_ lignes)",
            "search": "Recherche",
            "emptyTable":     "Aucun résultat",
            "infoPostFix":    "",
            "thousands":      " ",
            "loadingRecords": "Chargement...",
            "processing":     "Chargement...",
            "paginate": {
                "first":      "Debut",
                "last":       "Fin",
                "next":       "Suiv.",
                "previous":   "Préc."
            }
        },
        "lengthMenu": [[6, 8, 10, 20, 50, -1], [6, 8, 10, 20, 50, "Tous"]],
        "pageLength": 10,
        "order": table_ordre,
        "columnDefs": [
            { "width": "130px", "targets": 6 }
//            { "orderable": false, "targets": 10 }
        ],
        initComplete: function () {
            // filtres date, categorie, lieu terrain
            this.api().columns([1,2,3,4]).every( function () {
                var column = this;
                var select = jq('<select><option value="">Tout</option></select>')
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
                    select.append( '<option value="'+d+'">'+d+'</option>' );
                } );
            } );
            
            // filtre équipes
            jq('#tableMatchs_fr_filter label').addClass('hidden-xs');
            this.api().columns([10]).every( function () {
                var column = this;
                var select = jq('<span class="filtres"><span><label>Filtre </label>\n\
                                    <select><option value="">Tout</option></select> </span><label> Date </label></span>')
                    .appendTo( jq('#tableMatchs_fr_filter') );
                    select.find('select').on( 'change', function () {
                        var val = jq.fn.dataTable.util.escapeRegex(
                            jq(this).val()
                        );
                        column
                            .search( val, true, false )
                            .draw();
                    } );
            } );
            var listTeams = [];
            jq('#tableMatchs_fr_filter select').append( '<optgroup label="Equipes">' );
            this.api().columns([5,7]).every( function () {
                var column = this;
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    // Si l'élément n'est pas déjà présent et le premier caractère différent d'une parenthèse
                    if(d.trim() != '' && jq.inArray(d.trim(), listTeams)<0 && d.trim()[0] != '(') { 
                        listTeams.push( d.trim() );
                    }
                } );
            } );
            listTeams.sort()
                    .forEach(function(item, index){
                        jq('#tableMatchs_fr_filter select').append( '<option value="'+item+'">'+item+'</option>' );
                    });
            jq('#tableMatchs_fr_filter select').append( '</optgroup>' );
            
            // filtre arbitres
            var listRefs = [];
            jq('#tableMatchs_fr_filter select').append( '<optgroup label="Arbitres">' );
            this.api().columns([8,9]).every( function () {
                var column = this;
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    nomArbitre = d.split("(", 1);
                    nomArbitre = nomArbitre[0].trim();
                    // Si l'élément n'est pas déjà présent
                    if(nomArbitre != '' && jq.inArray(nomArbitre, listRefs) === -1) {
                        listRefs.push( nomArbitre );
                    }
                } );
                
            } );
            listRefs.sort()
                    .forEach(function(item, index){
                        jq('#tableMatchs_fr_filter select').append( '<option value="'+item+'">'+item+'</option>' );
                    });
            jq('#tableMatchs_fr_filter select').append( '</optgroup>' );
            
            // copie filtre date
            this.api().columns([1]).every( function () {
                jq('#tableMatchs_fr_filter span.filtres').append(jq(this.footer()).find('select'));
            } );
            
            jq('#tableMatchs_fr_length').append('&nbsp;&nbsp;<a class="btn btn-default" href="" title="Réactualiser"><img src="img/glyphicons-82-refresh.png" width="16"></a>');
            jq('.dataTables_wrapper select, .dataTables_wrapper input').css('height', '34px').css('padding', '6px 2px');
        }
    } );

    table_en = jq('#tableMatchs_en').DataTable( {
        "dom": 'lfrtip',
        responsive: true,
        stateSave: false,
        fixedHeader: true,
        "search": {
            "smart": false
        },
        searchHighlight: true,
        "lengthMenu": [[6, 8, 10, 20, 50, -1], [6, 8, 10, 20, 50, "All"]],
        "pageLength": 10,
        "bPaginate" : true,
        "order": table_ordre,
        "columnDefs": [
            { "width": "130px", "targets": 6 }
//            { "orderable": false, "targets": 10 }
        ],
        initComplete: function () {
            // filtres date, categorie, lieu terrain
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
                    select.append( '<option value="'+d+'">'+d+'</option>' );
                } );
            } );
            
            // filtre équipes
            jq('#tableMatchs_en_filter label').addClass('hidden-xs');
            this.api().columns([10]).every( function () {
                var column = this;
                var select = jq('<span class="filtres"><span><label>Filter </label>\n\
                                    <select><option value="">All</option></select> </span><label> Date </label></span>')
                    .appendTo( jq('#tableMatchs_en_filter') );
                    select.find('select').on( 'change', function () {
                        var val = jq.fn.dataTable.util.escapeRegex(
                            jq(this).val()
                        );
                        column
                            .search( val, true, false )
                            .draw();
                    } );
            } );
            var listTeams = [];
            jq('#tableMatchs_en_filter select').append( '<optgroup label="Teams">' );
            this.api().columns([5,7]).every( function () {
                var column = this;
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    // Si l'élément n'est pas déjà présent et le premier caractère différent d'une parenthèse
                    if(d.trim() != '' && jq.inArray(d.trim(), listTeams)<0 && d.trim()[0] != '(') {
                        listTeams.push( d.trim() );
                    }
                } );
            } );
            listTeams.sort()
                    .forEach(function(item, index){
                        jq('#tableMatchs_en_filter select').append( '<option value="'+item+'">'+item+'</option>' );
                    });
            jq('#tableMatchs_en_filter select').append( '</optgroup>' );
            
            // filtre arbitres
            var listRefs = [];
            jq('#tableMatchs_en_filter select').append( '<optgroup label="Referees">' );
            this.api().columns([8,9]).every( function () {
                var column = this;
                column.cache( 'search' ).unique().sort().each( function ( d, j ) {
                    nomArbitre = d.split("(", 1);
                    nomArbitre = nomArbitre[0].trim();
                    if(nomArbitre != '' && jq.inArray(nomArbitre, listRefs) === -1) { // Si l'élément n'est pas déjà présent
                        listRefs.push( nomArbitre );
                    }
                } );
                
            } );
            listRefs.sort()
                    .forEach(function(item, index){
                        jq('#tableMatchs_en_filter select').append( '<option value="'+item+'">'+item+'</option>' );
                    });
            jq('#tableMatchs_en_filter select').append( '</optgroup>' );
            
            // copie filtre date
            this.api().columns([1]).every( function () {
                jq('#tableMatchs_en_filter span.filtres').append(jq(this.footer()).find('select'));
            } );
            
            jq('#tableMatchs_en_length').append('&nbsp;&nbsp;<a class="btn btn-default" href="" title="Refresh"><img src="img/glyphicons-82-refresh.png" width="16"></a>');
            jq('.dataTables_wrapper select, .dataTables_wrapper input').css('height', '34px').css('padding', '6px 2px')
        }
    } );


    jq('body').popover({
        selector: '.img2',
        html: true,
        trigger: 'hover',
        placement: 'left',
        content: function () {
            var temp = jq(this).attr('src');
            return '<img class="img-rounded" style="float:right;width:100px;max-width:100px;" src="'+temp+'" />';
        }
    });    

    jq('#share_btn').click(function(){
        toCopy = 'https://www.kayak-polo.info/' + jq('#btnkpmatch').attr('href');
        jq('#share_alert').remove();
        jq('#navTitle').after('<div class="alert alert-info alert-dismissible" role="alert" id="share_alert">'
                + ' <button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                + '    <span aria-hidden="true">&times;</span>'
                + ' </button><span>' + toCopy + '</span><input type="text" id="share_link" value="' + toCopy + '">'
                + '</div>');
        jq('#share_link').select();
        document.execCommand('copy');
        jq('#share_link').remove();
    });

    jq('#selects_toggle').click(function(e){
        e.preventDefault();
        jq('.selects').toggleClass('hidden-xs');
        jq('#selector article').toggleClass('article_sans_bg');
    });
    
    var subtitle = jq('#Saison').val() + ' - ' + jq('#Group option:selected').html();
    if(typeof(jq('#J').val()) != 'undefined' && jq('#J').val() != '*') {
        subtitle += ' (' + jq('#J option:selected').html() + ')';
    }
    if(typeof(jq('#Compet').val()) != 'undefined' && jq('#Compet').val() != '*') {
        subtitle += ' (' + jq('#Compet option:selected').html() + ')';
    }
    if(jq('#event').val() > 0) {
        subtitle = jq('#Saison').val() + ' - ' + jq('#event option:selected').html();
    }
    
//    jq('#selects_toggle:visible').click();
    jq('#subtitle label').html(subtitle);
    jq('#subtitle').removeClass('hidden-xs');
    
    jq('html, body').animate({
        scrollTop: jq("#navGroup").prev().offset().top
    }, 700)

});




/*!
 SearchHighlight for DataTables v1.0.1
 2014 SpryMedia Ltd - datatables.net/license
*/
(function (h, g, a) {
    function e(d, c) {
        d.unhighlight();
        /* REACTIVER LE SPLIT POUR UNE RECHERCHE SUR DES TERMES MULTIPLES */
        c.rows({filter: "applied"}).data().length && (c.columns().every(function () {
            this.nodes().flatten().to$().unhighlight({className: "column_highlight"});
            this.nodes().flatten().to$().highlight(a.trim(this.search()) /* .split(/\s+/) */, {className: "column_highlight"}) 
        }), d.highlight(a.trim(c.search()) /* .split(/\s+/) */ ))
    }
    a(g).on("init.dt.dth", function (d, c) {
        if ("dt" === d.namespace) {
            var b = new a.fn.dataTable.Api(c), f = a(b.table().body());
            if (a(b.table().node()).hasClass("searchHighlight") ||
                    c.oInit.searchHighlight || a.fn.dataTable.defaults.searchHighlight)
                b.on("draw.dt.dth column-visibility.dt.dth column-reorder.dt.dth", function () {
                    e(f, b)
                }).on("destroy", function () {
                    b.off("draw.dt.dth column-visibility.dt.dth column-reorder.dt.dth")
                }), b.search() && e(f, b)
        }
    })
})(window, document, jQuery);

/*
 * jQuery Highlight plugin
 *
 * Based on highlight v3 by Johann Burkard
 * http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html
 *
 * Code a little bit refactored and cleaned (in my humble opinion).
 * Most important changes:
 *  - has an option to highlight only entire words (wordsOnly - false by default),
 *  - has an option to be case sensitive (caseSensitive - false by default)
 *  - highlight element tag and class names can be specified in options
 *
 * Usage:
 *   // wrap every occurrance of text 'lorem' in content
 *   // with <span class='highlight'> (default options)
 *   $('#content').highlight('lorem');
 *
 *   // search for and highlight more terms at once
 *   // so you can save some time on traversing DOM
 *   $('#content').highlight(['lorem', 'ipsum']);
 *   $('#content').highlight('lorem ipsum');
 *
 *   // search only for entire word 'lorem'
 *   $('#content').highlight('lorem', { wordsOnly: true });
 *
 *   // don't ignore case during search of term 'lorem'
 *   $('#content').highlight('lorem', { caseSensitive: true });
 *
 *   // wrap every occurrance of term 'ipsum' in content
 *   // with <em class='important'>
 *   $('#content').highlight('ipsum', { element: 'em', className: 'important' });
 *
 *   // remove default highlight
 *   $('#content').unhighlight();
 *
 *   // remove custom highlight
 *   $('#content').unhighlight({ element: 'em', className: 'important' });
 *
 *
 * Copyright (c) 2009 Bartek Szopka
 *
 * Licensed under MIT license.
 *
 */
jQuery.extend({
    highlight: function (node, re, nodeName, className) {
        if (node.nodeType === 3) {
            var match = node.data.match(re);
            if (match) {
                var highlight = document.createElement(nodeName || 'span');
                highlight.className = className || 'highlight';
                var wordNode = node.splitText(match.index);
                wordNode.splitText(match[0].length);
                var wordClone = wordNode.cloneNode(true);
                highlight.appendChild(wordClone);
                wordNode.parentNode.replaceChild(highlight, wordNode);
                return 1; //skip added node in parent
            }
        } else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
                !/(script|style)/i.test(node.tagName) && // ignore script and style nodes
                !(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
            for (var i = 0; i < node.childNodes.length; i++) {
                i += jQuery.highlight(node.childNodes[i], re, nodeName, className);
            }
        }
        return 0;
    }
});

jQuery.fn.unhighlight = function (options) {
    var settings = { className: 'highlight', element: 'span' };
    jQuery.extend(settings, options);

    return this.find(settings.element + "." + settings.className).each(function () {
        var parent = this.parentNode;
        parent.replaceChild(this.firstChild, this);
        parent.normalize();
    }).end();
};

jQuery.fn.highlight = function (words, options) {
    var settings = { className: 'highlight', element: 'span', caseSensitive: false, wordsOnly: true };
    jQuery.extend(settings, options);
    
    if (words.constructor === String) {
        words = [words];
//        words[0] = words;
    }
    words = jQuery.grep(words, function(word, i){
      return word != '';
    });
    // DESACTIVATION REGEX
//    words = jQuery.map(words, function(word, i) {
//      return word.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
//    });
    if (words.length == 0) { return this; };

    var flag = settings.caseSensitive ? "" : "i";
    var pattern = "(" + words.join("|") + ")";
    if (settings.wordsOnly) {
        pattern = "\\b" + pattern + "\\b";
    }
    var re = new RegExp(pattern, flag);
    
    return this.each(function () {
        jQuery.highlight(this, re, settings.element, settings.className);
    });
};
