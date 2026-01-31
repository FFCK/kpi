jq = jQuery.noConflict()

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

function validGroupe () {
    //    var libelle = jq('#Libelle').value;
    //    if (libelle.length == 0)
    //    {
    //        alert(langue['Nom_evt_vide']);
    //        return false;
    //    }

    return true
}

function addGroupe () {
    if (!validGroupe())
        return

    jq('#Cmd').val('Add')
    jq('#ParamCmd').val()
    jq('#formGroupe').submit()
}

function updateGroupe () {
    if (!validGroupe())
        return

    var oldGroupe = jq('#oldGroupe').val()
    var newGroupe = jq('#Groupe').val()

    if (oldGroupe && newGroupe && oldGroupe !== newGroupe) {
        var message = 'Attention : Vous allez modifier le code du groupe de "' + oldGroupe + '" vers "' + newGroupe + '".\n\n' +
            'Cette modification va également mettre à jour toutes les compétitions (kp_competition.Code_ref) qui référencent ce groupe.\n\n' +
            'Voulez-vous continuer ?'
        if (!confirm(message)) {
            return
        }
    }

    jq('#Cmd').val('Update')
    jq('#ParamCmd').val()
    jq('#formGroupe').submit()
}

function razGroupe () {
    jq('#Cmd').val('Raz')
    jq('#ParamCmd').val()
    jq('#formGroupe').submit()
}


function editGroupe (idGroupe) {
    jq('#Cmd').val('Edit')
    jq('#ParamCmd').val(idGroupe)
    jq('#formGroupe').submit()
}

function removeGroupe (idGroupe) {
    if (confirm('Supprimer le groupe ?')) {
        jq('#Cmd').val('Remove')
        jq('#ParamCmd').val(idGroupe)
        jq('#formGroupe').submit()
    }
}

jq('.ordre_up').click(function () {
    jq('#Cmd').val('UpOrder')
    jq('#idGroupe').val(jq(this).data('id'))
    jq('#ParamCmd').val(jq(this).data('order'))
    jq('#formGroupe').submit()
})
function UpOrder (idGroupe) {
    if (confirm('Supprimer le groupe ?')) {
        jq('#Cmd').val('Remove')
        jq('#ParamCmd').val(idGroupe)
        jq('#formGroupe').submit()
    }
}