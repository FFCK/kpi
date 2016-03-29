<div class="container titre">
    <div class="col-md-9">
        <h2 class="col-md-11 col-xs-9">
            {if $recordCompetition.Titre_actif != 'O' && $recordCompetition.Soustitre2 != ''}
                <span>{$recordCompetition.Soustitre}
                    <br />
                    {$recordCompetition.Soustitre2}
                </span>
            {else}
                <span>{$recordCompetition.Libelle}
                    <br />
                    {$recordCompetition.Soustitre2}
                </span>
            {/if}
            
        </h2>
    </div>
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
</div>

<div class="container-fluid">
    <article class="padTopBottom{if $recordCompetition.Code_typeclt != 'CHPT'} table-responsive col-md-6 col-md-offset-3{else} col-md-12{/if}">
        {if $recordCompetition.Statut != 'END'}
            <div class="label label-warning">Classement provisoire</div>
        {/if}
        <a class="btn btn-default pull-right" href='kpclassements.php?Compet={$codeCompet}'>{#Classement_General#}Classement général...</a>

		{*if $recordCompetition.Statut != 'END'*}
            <table class='table table-striped table-hover' id='tableMatchs'>
                {if $recordCompetition.Code_typeclt == 'CHPT'}
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>
                                {#Equipes#}
                                <a class="pdfLink badge pull-right" href="PdfCltChpt.php?S={$Saison}" Target="_blank"><img width="20" src="img/pdf.gif" alt="{#Classement#} (pdf)" title="{#Classement#} (pdf)" /></a>
                            </th>
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
                    </thead>
                {else}
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>
                                {#Equipes#}
                                <a class="pdfLink badge pull-right" href="PdfCltNiveauPhase.php?S={$Saison}" Target="_blank"><img width="20" src="img/pdf.gif" alt="{#Classement#} (pdf)" title="{#Classement#} (pdf)" /></a>
                            </th>
                        </tr>
                    </thead>
                {/if}
                    <tbody>
                                {section name=i loop=$arrayEquipe_publi} 
                                    <tr>
                                {if $recordCompetition.Code_typeclt=='CHPT' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].Clt <= 3 && $arrayEquipe_publi[i].Clt > 0 && $arrayEquipe_publi[i].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].Clt}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt=='CP' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].CltNiveau <= 3 && $arrayEquipe_publi[i].CltNiveau > 0 && $arrayEquipe_publi[i].Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt=='CHPT'}
                                    {if $smarty.section.i.iteration <= $recordCompetition.Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.i.iteration > $recordCompetition.Nb_equipes - $recordCompetition.Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {else}
                                    {if $smarty.section.i.iteration <= $recordCompetition.Qualifies}
                                        <td class='qualifie text-center'><img width="30" src="img/up.gif" alt="Qualifié" title="Qualifié" /></td>
                                    {elseif $smarty.section.i.iteration > $recordCompetition.Nb_equipes - $recordCompetition.Elimines}
                                        <td class='elimine text-center'><img width="30" src="img/down.gif" alt="Eliminés" title="Eliminés" /></td>
                                    {else}
                                        <td>&nbsp;</td>
                                    {/if}
                                {/if}

                                        {if $recordCompetition.Code_typeclt == 'CHPT'}
                                            <td class="droite">
                                                {$arrayEquipe_publi[i].Clt}
                                                {if $arrayEquipe_publi[i].logo != ''}
                                                    <img class="img2 pull-right" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
                                                {/if}
                                            </td>
                                            <td class="cliquableNomEquipe"><a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayEquipe_publi[i].Numero}" title="{#Palmares#}">{$arrayEquipe_publi[i].Libelle}</a></td>
                                            <td>{$arrayEquipe_publi[i].Pts/100}</td>
                                            <td>{$arrayEquipe_publi[i].J}</td>
                                            <td>{$arrayEquipe_publi[i].G}</td>
                                            <td>{$arrayEquipe_publi[i].N}</td>
                                            <td>{$arrayEquipe_publi[i].P}</td>
                                            <td>{$arrayEquipe_publi[i].F}</td>
                                            <td>{$arrayEquipe_publi[i].Plus}</td>
                                            <td>{$arrayEquipe_publi[i].Moins}</td>
                                            <td>{$arrayEquipe_publi[i].Diff}</td>
                                        {else}
                                            <td class="droite">
                                                {$arrayEquipe_publi[i].CltNiveau}
                                            </td>
                                            <td class="cliquableNomEquipe">
												{if $arrayEquipe_publi[i].logo != ''}
													<img class="img2 pull-left" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
												{/if}
												<a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayEquipe_publi[i].Numero}" title="{#Palmares#}">{$arrayEquipe_publi[i].Libelle}</a>
											</td>
                                            {*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
                                        {/if}


                                    </tr>
                                {/section}
                    </tbody>
            </table>
        {*/if*}
    </article>
    {if $recordCompetition.Code_typeclt == 'CP'}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
            {if $arrayJournee[i].Type == 'C'}
                <article class="padTopBottom table-responsive col-md-12">
                    <h4>{$arrayJournee[i].Phase}</h4>
                    <table class='table table-striped table-hover' id='tableMatchs'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{#Equipes#}</th>
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
                        </thead>
                        <tbody>
                            {section name=j loop=$arrayEquipe_journee_publi[$idJournee]}
                                <tr>
                                    <td>
                                        {$arrayEquipe_journee_publi[$idJournee][j].Clt}
                                        {if $arrayEquipe_journee_publi[$idJournee][j].logo != ''}
                                            <img class="img2 pull-right" width="30" src="{$arrayEquipe_journee_publi[$idJournee][j].logo}" alt="{$arrayEquipe_journee_publi[$idJournee][j].club}" />
                                        {/if}

                                    </td>
                                    <td class="cliquableNomEquipe">
                                        <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayEquipe_journee_publi[$idJournee][j].Numero}" title="{#Palmares#}">{$arrayEquipe_journee_publi[$idJournee][j].Libelle}</a>
                                    </td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Pts/100}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].J}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].G}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].N}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].P}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].F}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Plus}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Moins}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Diff}</td>
                                </tr>
                            {/section}
                        </tbody>
                    </table>
                </article>
            {else}
                <article class="padTopBottom table-responsive col-md-6 col-md-offset-3">
                    <h4 class="row">{$arrayJournee[i].Phase}</h4>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        <div class="row cliquableNomEquipe">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary">{$kpequipes[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-primary" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </article>
            {/if}
        {/section}
    {else}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
                <article class="padTopBottom table-responsive col-md-12">
                    <div class="page-header">
                        <h4>
                            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
                            <a class="btn btn-xs btn-default pull-right" href="kpdetails.php?Compet={$codeCompet}&J={$arrayJournee[i].Id_journee}" title="{#Details#}">{#Details#}</a>
                        </h4>
                    </div>
                    {section name=j loop=$arrayMatchs[$idJournee]}
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            {if $arrayMatchs[$idJournee][j].ScoreA > $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-primary" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {elseif $arrayMatchs[$idJournee][j].ScoreA < $arrayMatchs[$idJournee][j].ScoreB}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-primary">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-primary" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {else}
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumA}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeA}</a>
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreA}</span>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-left">
                                    <span class="btn btn-xs btn-default">{$arrayMatchs[$idJournee][j].ScoreB}</span>
                                    <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[$idJournee][j].NumB}" title="{#Palmares#}">{$arrayMatchs[$idJournee][j].EquipeB}</a>
                                </div>
                            {/if}
                        </div>
                    {/section}
                </article>
        {/section}
        
    {/if}
</div>
