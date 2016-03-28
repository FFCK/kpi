<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">{#Classement#}</h1>
    </div>
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
</div>

<div class="container" id="selector">
    <article class="col-md-12 padTopBottom">
			<form method="POST" action="kpclassements.php#selector" name="formClassement" enctype="multipart/form-data">
				<input type='hidden' name='Cmd' Value='' />
				<input type='hidden' name='ParamCmd' Value='' />
				<div class='col-md-4 col-sm-6 col-xs-12'>
								<label for="saisonTravail">{#Saison#} :</label>
								<select name="saisonTravail" onChange="submit()">
									{section name=i loop=$arraySaison} 
										<Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $sessionSaison}selected{/if}>{if $arraySaison[i].Code eq $sessionSaison}=> {/if}{$arraySaison[i].Code}</Option>
									{/section}
								</select>
                </div>
				<div class='col-md-4 col-sm-6 col-xs-12'>
                    <label for="codeCompetGroup">{#Competition#} :</label>
                    <select name="codeCompetGroup" onChange="submit();">
                            <Option Value="">{#Selectionnez#}...</Option>
                        {section name=i loop=$arrayCompetitionGroupe}
                            {assign var='temporaire' value=$arrayCompetitionGroupe[i][1]}
                            <Option Value="{$arrayCompetitionGroupe[i][1]}" {$arrayCompetitionGroupe[i][3]}>{$smarty.config.$temporaire|default:$arrayCompetitionGroupe[i][2]}</Option>
                        {/section}
                    </select>
                </div>
				<div class='col-md-4 col-sm-12 col-xs-12 text-center'>
					<div class="fb-like pull-right" data-href="http://www.kayak-polo.info/kpclassements.php?Group={$codeCompetGroup}&Saison={$sessionSaison}" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
                    <br>
                    <a class="btn btn-default pull-left" href='kphistorique.php?Compet={$idCompet}'>{#Historique#}...</a>
				</div>
            </form>
    </article>
</div>

{section  name=i loop=$arrayCompetition}
    {if $arrayCompetition[i].Statut != 'ATT'}
        {assign var='codetemp' value=$arrayCompetition[i].codeCompet}
        <div class="container-fluid">
            <article class="padTopBottom{if $arrayEquipe_publi[$codetemp][0].Code_typeclt != 'CHPT'} table-responsive col-md-6 col-md-offset-3{else} col-md-12{/if}">
                {if $recordCompetition[0].LogoLink != ''}
                    <div class="text-center">
                        {if $recordCompetition[0].Web != ''}
                            <a href='{$recordCompetition[0].Web}' target='_blank'>
                        {/if}
                        <img class="img2" width="700" id='logo' src='{$recordCompetition[0].LogoLink}' alt="logo" />
                        {if $recordCompetition[0].Web != ''}
                                <br />
                                {$recordCompetition[0].Web}
                            </a>
                            <br />
                        {/if}
                    </div>
                {/if}
                <div class="page-header">
                    {assign var='idCompet' value=$arrayEquipe_publi[$codetemp][0].CodeCompet}
                    {assign var='idTour' value=$arrayEquipe_publi[$codetemp][0].Code_tour}
                    {assign var='idSaison' value=$arrayEquipe_publi[$codetemp][0].CodeSaison}
                    <h3>
                        {if $arrayCompetition[i].Titre_actif == 'O'}
                            {$arrayCompetition[i].libelleCompet}
                        {else}
                            {$arrayCompetition[i].Soustitre}
                        {/if}
                        <div class='pull-right'>
                            <a class="btn btn-default" href='kpclassement.php?Compet={$idCompet}'>{#Details#}...</a>
                            {if $arrayEquipe_publi[$codetemp][0].existMatch == 1}
                                &nbsp;<a class="btn btn-default" href='kpmatchs.php?Compet={$idCompet}'>{#Matchs#}...</a>
                            {/if}
                        </div>

                    </h3>
                    {if $arrayCompetition[i].Statut != 'END'}
                        <div class="label label-warning">Classement provisoire</div>
                    {/if}
                </div>
                <table class='table table-striped table-hover' id='tableMatchs'>
                    <thead>
                        {if $arrayEquipe_publi[$codetemp][0].Code_typeclt=='CHPT'}
                            <tr>
                                <th></th>
                                <th>#</th>
                                <th>{#Equipe#}</th>
                                <th>{#Pts#}</th>
                                <th>{#J#}</th>
                                <th>{#G#}</th>
                                <th>{#N#}</th>
                                <th>{#P#}</th>
                                <th>{#F#}</th>
                                <th>+</th>
                                <th>-</th>
                                <th>{#Diff#}</th>
                            </tr>
                        {else}
                            {*	<th colspan=2 width="12%">{#Clt#}</th>
                                <th width="88%">{#Equipe#}</th>	*}
                        {/if}
                    </thead>
                    <tbody>        
                        {section  name=j loop=$arrayEquipe_publi[$codetemp]}
                            <tr>
                                {if $arrayEquipe_publi[$codetemp][j].Code_typeclt=='CHPT' && $arrayEquipe_publi[$codetemp][j].Code_tour=='10' && $arrayEquipe_publi[$codetemp][j].Clt <= 3 && $arrayEquipe_publi[$codetemp][j].Clt > 0 && $arrayEquipe_publi[$codetemp][j].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[$codetemp][j].Clt}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $arrayEquipe_publi[$codetemp][j].Code_typeclt=='CP' && $arrayEquipe_publi[$codetemp][j].Code_tour=='10' && $arrayEquipe_publi[$codetemp][j].CltNiveau <= 3 && $arrayEquipe_publi[$codetemp][j].CltNiveau > 0 && $arrayEquipe_publi[$codetemp][j].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[$codetemp][j].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $arrayEquipe_publi[$codetemp][j].Code_typeclt=='CHPT'}
                                    {if $smarty.section.j.iteration <= $arrayEquipe_publi[$codetemp][j].Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.j.iteration > $arrayEquipe_publi[$codetemp][j].Nb_equipes - $arrayEquipe_publi[$codetemp][j].Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {else}
                                    {if $smarty.section.j.iteration <= $arrayEquipe_publi[$codetemp][j].Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.j.iteration > $arrayEquipe_publi[$codetemp][j].Nb_equipes - $arrayEquipe_publi[$codetemp][j].Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {/if}

                                {if $arrayEquipe_publi[$codetemp][j].Code_typeclt=='CHPT'}
                                    <td class="droite">
                                        {$arrayEquipe_publi[$codetemp][j].Clt}
                                        {if $arrayEquipe_publi[$codetemp][j].logo != ''}
                                            <img class="img2 pull-right" width="30" src="{$arrayEquipe_publi[$codetemp][j].logo}" alt="{$arrayEquipe_publi[$codetemp][j].club}" />
                                        {/if}
                                    </td>
                                    <td class="cliquableNomEquipe"><a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe_publi[$codetemp][j].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[$codetemp][j].Libelle}</a></td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].Pts/100}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].J}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].G}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].N}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].P}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].F}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].Plus}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].Moins}</td>
                                    <td>{$arrayEquipe_publi[$codetemp][j].Diff}</td>
                                {else}
                                    <td class="droite">
                                        {$arrayEquipe_publi[$codetemp][j].CltNiveau}
                                    </td>
                                    <td class="cliquableNomEquipe">
                                        {if $arrayEquipe_publi[$codetemp][j].logo != ''}
                                            <img class="img2 pull-left" width="30" src="{$arrayEquipe_publi[$codetemp][j].logo}" alt="{$arrayEquipe_publi[$codetemp][j].club}" />
                                        {/if}
										<a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe_publi[$codetemp][j].Numero}' title='{#Palmares#}'>{$arrayEquipe_publi[$codetemp][j].Libelle}</a>
									</td>
                                {/if}
                            </tr>
                        {/section}
                    </tbody>
                </table>
            </article>
        </div>
    {/if}
{/section}
