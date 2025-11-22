jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Aucun_joueur'] = 'No player selected. unable to add !'
	langue['Certif'] = '(Med. Certificat)'
	langue['Cliquez_pour_modifier'] = 'Click to edit'
	langue['Joueur_vide'] = 'Player is empty. unable to add !'
	langue['Joueur_existant'] = 'Player is already selected. unable to add !'
	langue['MAJ_impossible'] = 'Unable to update'
	langue['Pagaie_couleur'] = '(Paddle level)'
	langue['Prenom_vide'] = 'Player first name is empty. unable to add !'
	langue['Saison_licence'] = '(Licence year)'
	langue['Surclassement'] = '(Mandatory upgrade)'
	langue['Selectionner_une_saison'] = 'Select a season'
	langue['Selectionner_une_competition'] = 'Select a competition'
	langue['Chargement'] = 'Loading...'
	langue['Aucune_competition_trouvee'] = 'No competition found'
	langue['Erreur'] = 'Error'
	langue['Erreur_chargement'] = 'Loading error'
	langue['Copie_en_cours'] = 'Copy in progress...'
	langue['Attention_remplacement_joueurs'] = 'Warning! This action will replace all current players of this team with those from the selected competition.\n\nAre you sure you want to continue?'
	langue['Joueurs_copies_succes'] = 'player(s) successfully copied'
	langue['Erreur_copie'] = 'Copy error'
	langue['Erreur_communication_serveur'] = 'Server communication error'
	langue['Veuillez_selectionner_saison_competition'] = 'Please select a season and a competition'
} else {
	langue['Aucun_joueur'] = 'Aucun joueur sélectionné, ajout impossible !'
	langue['Certif'] = '(Certificat CK)'
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
	langue['Joueur_vide'] = 'Joueur vide, ajout impossible !'
	langue['Joueur_existant'] = 'Joueur déjà sélectionné, ajout impossible !'
	langue['MAJ_impossible'] = 'Mise à jour impossible'
	langue['Pagaie_couleur'] = '(Pagaie couleur)'
	langue['Prenom_vide'] = 'Prénom joueur vide, ajout impossible!'
	langue['Saison_licence'] = '(Saison licence)'
	langue['Surclassement'] = '(Surclassement obligatoire)'
	langue['Selectionner_une_saison'] = 'Sélectionner une saison'
	langue['Selectionner_une_competition'] = 'Sélectionner une compétition'
	langue['Chargement'] = 'Chargement...'
	langue['Aucune_competition_trouvee'] = 'Aucune compétition trouvée'
	langue['Erreur'] = 'Erreur'
	langue['Erreur_chargement'] = 'Erreur de chargement'
	langue['Copie_en_cours'] = 'Copie en cours...'
	langue['Attention_remplacement_joueurs'] = 'Attention ! Cette action va remplacer tous les joueurs actuels de cette équipe par ceux de la compétition sélectionnée.\n\nÊtes-vous sûr de vouloir continuer ?'
	langue['Joueurs_copies_succes'] = 'joueur(s) copié(s) avec succès'
	langue['Erreur_copie'] = 'Erreur lors de la copie'
	langue['Erreur_communication_serveur'] = 'Erreur de communication avec le serveur'
	langue['Veuillez_selectionner_saison_competition'] = 'Veuillez sélectionner une saison et une compétition'
}


function validJoueur () {
	var matricJoueur = jq('#matricJoueur2').val()
	var existJoueurs = jq('#tableMatchs input[value="' + matricJoueur + '"]')
	if (existJoueurs.length > 0) {
		alert(langue['Joueur_existant'])
		return false
	}

	var nomJoueur = jq('#nomJoueur').val()
	if (nomJoueur.length == 0) {
		alert(langue['Joueur_vide'])
		return false
	}
	var prenomJoueur = jq('#prenomJoueur').val()
	if (prenomJoueur.length == 0) {
		alert(langue['Prenom_vide'])
		return false
	}
	return true
}

function validJoueur2 () {
	var matricJoueur = jq('#matricJoueur2').val()
	var existJoueurs = jq('#tableMatchs input[value="' + matricJoueur + '"]')
	if (existJoueurs.length > 0) {
		alert(langue['Joueur_existant'])
		return false
	}

	var nomJoueur2 = jq('#nomJoueur2').val()
	if (nomJoueur2.length == 0) {
		alert(langue['Prenom_vide'])
		return false
	}
	return true
}

function Add () {
	if (!validJoueur())
		return
	jq('#Cmd').val('Add')
	jq('#ParamCmd').val('')
	jq('#formEquipeJoueur').submit()
}

function Add2 () {
	if (!validJoueur2())
		return
	jq('#Cmd').val('Add2')
	jq('#ParamCmd').val('')
	jq('#formEquipeJoueur').submit()
}

function AddCoureur (matric, categ) {
	jq('#Cmd').val('AddCoureur')
	jq('#ParamCmd').val(matric + '|' + categ)
	jq('#formEquipeJoueur').submit()
}

function Find () {
	jq('#Cmd').val('Find')
	jq('#ParamCmd').val('')
	jq('#formEquipeJoueur').submit()
}



jq(document).ready(function () { //Jquery + NoConflict='J'

	// Direct Input (numero joueur)
	// Ajout title
	jq('.directInput').attr('title', langue['Cliquez_pour_modifier'])
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableMatchs').bind('keydown', function (e) {
		if (e.which == 13) {
			validationDonnee()
			return false
		}
	})
	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function () {
		var Classe = jq(this).attr('class')
		validationDonnee(Classe)
	})
	// focus sur une cellule du tableau => remplace le span par un input
	jq('#tableMatchs td.directInput').focus(function (event) {
		event.preventDefault()
		var valeur = jq(this).text()
		var tabindexVal = jq(this).attr('tabindex')
		jq(this).attr('tabindex', tabindexVal + 1000)
		if (jq(this).hasClass('text')) {
			jq(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="' + tabindexVal + '" size="2" value="' + valeur + '">')
			jq('#inputZone').select().keyup(() => {
				jq('#inputZone').val(jq('#inputZone').val().toUpperCase().match(/[0-9]{0,2}/)[0])
			})
		}
		jq(this).children("span").hide()
		setTimeout(function () { jq('#inputZone').select() }, 0)
	})

	function validationDonnee (Classe) {
		var nouvelleValeur = jq('#inputZone').val()
		var tabindexVal = jq('#inputZone').attr('tabindex')
		if (Classe == 'directInputSpan') {
			jq('#inputZone + span').attr('tabindex', tabindexVal)
		} else if (Classe == 'directInputTd') {
			jq('#inputZone').parent('td').attr('tabindex', tabindexVal)
		}
		jq('#inputZone + span').show()
		var valeur = jq('#inputZone + span').text()
		var identifiant = jq('#inputZone + span').attr('id')
		var identifiant2 = identifiant.split('-')
		var typeValeur = identifiant2[0]
		var numJoueur = identifiant2[1]
		var numEquipe = identifiant2[2]
		if (valeur != nouvelleValeur) {
			valeurTransmise = nouvelleValeur
			var AjaxWhere = jq('#AjaxWhere').val()
			var AjaxTableName = jq('#AjaxTableName').val()
			var AjaxAnd = jq('#AjaxAnd').val();;
			var AjaxUser = jq('#AjaxUser').val()
			jq.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numJoueur,
					AjId2: numEquipe,
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function (data) {
					if (data != 'OK!') {
						alert(langue['MAJ_impossible'] + data)
					} else {
						jq('#' + identifiant).text(nouvelleValeur)
					}
				}
			)
		};
		jq('#inputZone').remove()
	}

	// directSelect (Capitaine)
	jq('#directSelecteur').hide()
	jq('.directSelect').click(function (e) {
		posX = e.pageX - 10
		posY = e.pageY - 15
		jq('#directSelecteur').css('left', posX + 'px')
		jq('#directSelecteur').css('top', posY + 'px')
		jq('#directSelecteur').toggle()
		var valeur = jq(this).children('span').text()
		var variables = jq(this).children('span').attr('id')
		jq(this).attr('id', 'directSelected')
		jq('#variables').val(variables)
		jq('#directSelecteurSelect option').removeAttr('selected')
		jq('#directSelecteurSelect option').each(function () {
			if (jq(this).val() == valeur) {
				jq(this).attr('selected', 'selected')
			};
		})
	})

	// Validation directSelect
	jq('#directSelecteurSelect').change(function () {
		var variables = jq('#variables').val()
		var variables = variables.split('-')
		var typeValeur = variables[0]
		var numJoueur = variables[1]
		var numEquipe = variables[2]
		var nouvelleValeur = jq('#directSelecteurSelect option:selected').val()
		valeurTransmise = nouvelleValeur
		var AjaxWhere = jq('#AjaxWhere').val()
		var AjaxTableName = jq('#AjaxTableName').val()
		var AjaxAnd = jq('#AjaxAnd').val();;
		var AjaxUser = jq('#AjaxUser').val()
		jq.get("UpdateCellJQ.php",
			{
				AjTableName: AjaxTableName,
				AjWhere: AjaxWhere,
				AjTypeValeur: typeValeur,
				AjValeur: valeurTransmise,
				AjAnd: AjaxAnd,
				AjId: numJoueur,
				AjId2: numEquipe,
				AjUser: AjaxUser,
				AjOk: 'OK'
			},
			function (data) {
				if (data != 'OK!') {
					alert(langue['MAJ_impossible'] + data)
					jq('#directSelected').removeAttr('id')
					jq('#directSelecteur').toggle()
				} else {
					jq('#directSelected').children('span').text(valeurTransmise)
					jq('#directSelected').removeAttr('id')
					jq('#directSelecteur').toggle()
				}
			}
		)
	})
	jq('#annulButton').click(function () {
		jq('#directSelected').removeAttr('id')
		jq('#directSelecteur').toggle()
	})

	jq('#irregularite').hide()
	jq('#addEquipeJoueurImpossible').hide()

	// Fonction commune pour gérer la sélection d'un joueur
	function handleJoueurSelect(item) {
		var saisonCompet = jq('#saisonCompet').val()
		var typeCompet = jq('#typeCompet').val()
		if (item) {
			jq("#matricJoueur2").val(item.matric)
			jq("#nomJoueur2").val(item.nom)
			jq("#prenomJoueur2").val(item.prenom)
			jq("#naissanceJoueur2").val(item.naissance)
			jq("#sexeJoueur2").val(item.sexe)
			catJoueurs2 = calculCategorie(item.naissance, saisonCompet)
			jq("#categJoueur2").val(catJoueurs2)
			jq("#categJoueur3").text('Cat: ' + catJoueurs2)
			surclassement = item.dateSurclassement
			if (surclassement != '') {
				jq(".surclassement3").html('<b>Surcl: ' + surclassement + '</b>')
			} else if (catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'
				&& catJoueurs2 != 'V1' && catJoueurs2 != 'V2' && catJoueurs2 != 'V3' && catJoueurs2 != 'V4') {
				jq(".surclassement3").html('Pas de surclassement')
			}
			jq("#origineJoueur2").text(item.origine)
			jq("#pagaieJoueur2").text(item.pagaieECA)
			jq("#CKJoueur2").text(item.certificatCK)
			jq("#APSJoueur2").text(item.certificatAPS)
			jq("#catJoueur2").text(catJoueurs2)
			if (typeCompet == 'CH' || typeCompet == 'CF' || typeCompet == 'MC') {
				var surcl_necess = jq('#surcl_necess').val()
				var motif = ''
				if (item.origine < saisonCompet) {
					motif = langue['Saison_licence']
				} else if (item.certificatCK != 'OUI') {
					motif = langue['Certif']
				} else if (item.pagaieECA == '' || item.pagaieECA == 'PAGB' || item.pagaieECA == 'PAGJ') {
					motif = langue['Pagaie_couleur']
				} else if (surclassement == '' && surcl_necess == 1 && catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'
					&& catJoueurs2 != 'V1' && catJoueurs2 != 'V2' && catJoueurs2 != 'V3' && catJoueurs2 != 'V4') {
					motif = langue['Surclassement']
				}
				if (motif != '') {
					jq('#motif').text(motif)
					jq('#irregularite').show()
					jq('#addEquipeJoueur2').hide()
					jq('#addEquipeJoueurImpossible').show()
				} else {
					jq('#motif').text(motif)
					jq('#irregularite').hide()
					jq('#addEquipeJoueur2').show()
					jq('#addEquipeJoueurImpossible').hide()
				}
			}
		}
	}

	if (jq('#choixJoueur').length > 0) {
		vanillaAutocomplete('#choixJoueur', 'Autocompl_joueur.php', {
			width: 550,
			maxResults: 50,
			dataType: 'json',
			extraParams: {
				format: 'json'
			},
			formatItem: (item) => item.label,
			formatResult: (item) => item.value,
			onSelect: handleJoueurSelect
		})
	}

	if (jq('#nomJoueur').length > 0) {
		vanillaAutocomplete('#nomJoueur', 'Autocompl_joueur.php', {
			width: 550,
			maxResults: 50,
			dataType: 'json',
			extraParams: {
				format: 'json'
			},
			formatItem: (item) => item.label,
			formatResult: (item) => item.value,
			onSelect: handleJoueurSelect
		})
	}


	// Actualiser
	jq('#actuButton').click(function () {
		jq('#formEquipeJoueur').submit()
	})

	jq('#changeEquipe').change(function () {
		jq(location).attr('href', "?idEquipe=" + jq(this).val())
	})

	// ===== Copie de composition d'équipe =====

	// Charger les saisons au chargement de la page
	if (jq('#saisonSource').length > 0) {
		jq.get('GetTeamCompetitions.php', { action: 'getSaisons' }, function (data) {
			if (data && !data.error) {
				jq('#saisonSource').empty()
				jq('#saisonSource').append('<option value="">-- ' + langue['Selectionner_une_saison'] + ' --</option>')
				data.forEach(function (saison) {
					jq('#saisonSource').append('<option value="' + saison.code + '">' + saison.libelle + '</option>')
				})
			}
		}, 'json')
	}

	// Quand une saison est sélectionnée, charger les compétitions
	jq('#saisonSource').change(function () {
		var saison = jq(this).val()
		var idEquipe = jq('#idEquipe').val()

		jq('#competitionSource').attr('disabled', 'disabled').empty().append('<option value="">-- ' + langue['Chargement'] + ' --</option>')
		jq('#copyComposition').attr('disabled', 'disabled')
		jq('#copyMessage').text('')

		if (saison && idEquipe) {
			jq.get('GetTeamCompetitions.php', {
				action: 'getCompetitions',
				saison: saison,
				idEquipe: idEquipe
			}, function (data) {
				if (data && !data.error) {
					jq('#competitionSource').empty()
					if (data.length === 0) {
						jq('#competitionSource').append('<option value="">-- ' + langue['Aucune_competition_trouvee'] + ' --</option>')
					} else {
						jq('#competitionSource').append('<option value="">-- ' + langue['Selectionner_une_competition'] + ' --</option>')
						data.forEach(function (competition) {
							jq('#competitionSource').append(
								'<option value="' + competition.id + '">' +
								competition.libelle + ' (' + competition.code_compet + ')</option>'
							)
						})
						jq('#competitionSource').removeAttr('disabled')
					}
				} else {
					jq('#competitionSource').empty().append('<option value="">-- ' + langue['Erreur'] + ' --</option>')
					if (data && data.error) {
						jq('#copyMessage').text(data.error)
					}
				}
			}, 'json').fail(function () {
				jq('#competitionSource').empty().append('<option value="">-- ' + langue['Erreur_chargement'] + ' --</option>')
			})
		} else {
			jq('#competitionSource').empty().append('<option value="">-- ' + langue['Selectionner_une_saison'] + ' --</option>')
		}
	})

	// Quand une compétition est sélectionnée, activer le bouton de copie
	jq('#competitionSource').change(function () {
		if (jq(this).val()) {
			jq('#copyComposition').removeAttr('disabled')
		} else {
			jq('#copyComposition').attr('disabled', 'disabled')
		}
		jq('#copyMessage').text('')
	})

})

// Fonction pour copier la composition d'équipe
function copyTeamComposition() {
	var idEquipeSource = jq('#competitionSource').val()
	var idEquipeCible = jq('#idEquipe').val()

	if (!idEquipeSource || !idEquipeCible) {
		alert(langue['Veuillez_selectionner_saison_competition'])
		return
	}

	if (!confirm(langue['Attention_remplacement_joueurs'])) {
		return
	}

	jq('#copyComposition').attr('disabled', 'disabled')
	jq('#copyMessage').text(langue['Copie_en_cours'])

	jq.post('CopyTeamComposition.php', {
		idEquipeSource: idEquipeSource,
		idEquipeCible: idEquipeCible
	}, function (data) {
		if (data && data.success) {
			jq('#copyMessage').text(data.nbJoueurs + ' ' + langue['Joueurs_copies_succes']).css('color', 'green')
			// Recharger la page après 2 secondes pour afficher les nouveaux joueurs
			setTimeout(function () {
				location.reload()
			}, 2000)
		} else {
			jq('#copyMessage').text(data.error || langue['Erreur_copie']).css('color', 'red')
			jq('#copyComposition').removeAttr('disabled')
		}
	}, 'json').fail(function () {
		jq('#copyMessage').text(langue['Erreur_communication_serveur']).css('color', 'red')
		jq('#copyComposition').removeAttr('disabled')
	})
}

