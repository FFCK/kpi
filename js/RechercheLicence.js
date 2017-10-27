function changeComiteReg()
{
    changeCombo('formRerchercheLicence', 'comiteReg', 'codeComiteReg', true);
}

function changeComiteDep()
{
    changeCombo('formRerchercheLicence', 'comiteDep', 'codeComiteDep', false);
    changeComiteReg();
}

function changeClub()
{
    changeCombo('formRerchercheLicence', 'club', 'codeClub', false);
    changeCombo('formRerchercheLicence', 'comiteDep', 'codeComiteDep', false);
    changeCombo('formRerchercheLicence', 'comiteReg', 'codeComiteReg', false);
}

function Find()
{
    changeClub();

    document.forms['formRerchercheLicence'].elements['Cmd'].value = 'Find';
    document.forms['formRerchercheLicence'].elements['ParamCmd'].value = '';

    document.forms['formRerchercheLicence'].submit();
}

function Ok()
{
    var elts = document.forms['formRerchercheLicence'].elements['checkCoureur'];
    var elts_count = (typeof (elts.length) != 'undefined') ? elts.length : 0;

    var str = '';

    if (elts_count) {
        for (var i = 0; i < elts_count; i++)
        {
            if (elts[i].checked) {
                if (str.length > 0)
                    str += ',';
                str += elts[i].value;
            }
        }
    } else {
        str = elts.value;
    }

    if (str.length == 0) {
        alert("Aucune ligne sélectionnée !!!");
        return false;
    }

    document.forms['formRerchercheLicence'].elements['Cmd'].value = 'Ok';
    document.forms['formRerchercheLicence'].elements['ParamCmd'].value = str;
    document.forms['formRerchercheLicence'].submit();
}

function Cancel()
{
    document.forms['formRerchercheLicence'].elements['Cmd'].value = 'Cancel';
    document.forms['formRerchercheLicence'].elements['ParamCmd'].value = '';
    document.forms['formRerchercheLicence'].submit();
}
	