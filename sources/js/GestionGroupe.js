jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Confirmer_MAJ'] = 'Confirm update ?';
    langue['Nom_evt_vide'] = 'Event name is empty, unable to create';
} else {
    langue['Confirmer_MAJ'] = 'Confirmez-vous le changement ?';
    langue['Nom_evt_vide'] = 'Le Nom de l\'événement est vide, ajout impossible';
}

function validGroupe()
{
//    var libelle = jq('#Libelle').value;
//    if (libelle.length == 0)
//    {
//        alert(langue['Nom_evt_vide']);
//        return false;
//    }

    return true;
}

function addGroupe()
{
	if (!validGroupe())
		return;

	jq('#Cmd').val('Add');
    jq('#ParamCmd').val();
    jq('#formGroupe').submit();
}
		
function updateGroupe()
{
	if (!validGroupe())
		return;
						
	jq('#Cmd').val('Update');
    jq('#ParamCmd').val();
    jq('#formGroupe').submit();
}

function razGroupe()
{
	jq('#Cmd').val('Raz');
    jq('#ParamCmd').val();
    jq('#formGroupe').submit();
}


function editGroupe(idGroupe)
{
	jq('#Cmd').val('Edit');
    jq('#ParamCmd').val(idGroupe);
    jq('#formGroupe').submit();
}
	
function removeGroupe(idGroupe)
{
	if(confirm('Supprimer le groupe ?')) {
        jq('#Cmd').val('Remove');
        jq('#ParamCmd').val(idGroupe);
        jq('#formGroupe').submit();
    }
}
	
