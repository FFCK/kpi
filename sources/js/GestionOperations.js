jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Cliquez_pour_modifier'] = 'Click to edit'
} else {
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
}

function ExportEvt () {
	jq("#ParamCmd").val(jq('#evenementExport').val())
	jq("#Cmd").val('ExportEvt')
	jq("#formOperations").submit()
}

function ImportEvt () {
	jq("#ParamCmd").val(jq('#evenementImport').val())
	jq("#Cmd").val('ImportEvt')
	jq("#formOperations").submit()
}
jq(document).ready(function () {


})

