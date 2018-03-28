<div class="container titre">
    <div class="col-md-12">
        <h2 class="col-md-12">
            <span class="label label-primary pull-right">{$Saison}</span>
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
</div>

{if $visuels.bandeau or $visuels.logo or $recordCompetition.Web}
    <div class="container logo_lien">
        <article class="padTopBottom table-responsive col-md-6 col-md-offset-3">
            <div class="text-center">
                {if $visuels.bandeau}
                    <img class="img2" id='logo' src='{$visuels.bandeau}' alt="logo">
                {else if $visuels.logo}
                    <img class="img2" id='logo' src='{$visuels.logo}' alt="logo">
                {/if}
                {if $recordCompetition.Web}
                    <p><a class="text-primary" href='{$recordCompetition.Web}' target='_blank'><i>{$recordCompetition.Web}</i></a></p>
                {/if}
            </div>
        </article>
    </div>
{/if}
<div class="container" id="selector">
    <article class="padTopBottom{if $recordCompetition.Code_typeclt != 'CHPT'} table-responsive col-md-6 col-md-offset-3{else} col-md-12{/if} tableClassement">
        <div class='pull-right'>
            {if $recordCompetition.Statut != 'END'}
                <div class="label label-warning">{#Classement_provisoire#}</div>
            {/if}
            {if $recordCompetition.Code_typeclt == 'CHPT'}
                <a class="btn btn-default" href='kpdetails.php?Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&Journee={$idSelJournee}&typ=CHPT'>{#Infos#}</a>
            {else}
                <a class="btn btn-default" href='kpdetails.php?Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&typ=CP'>{#Infos#}</a>
            {/if}
            <a class="btn btn-default btn-navigation" href='kpstats.php?Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}'>{#Stats#}</a>
            <a class="btn btn-default" title="{#Partager#}" data-link="https://www.kayak-polo.info/kpclassement.php?Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&lang={$lang}" id="share_btn"><img src="img/share.png" width="16"></a>
            <a class="btn btn-default btn-navigation" href='kpclassements.php?Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}'>{#Classement_general#}</a>
        </div>
        {if $recordCompetition.Statut == 'END'}
            <table class='table table-striped table-condensed table-hover'>
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
                                {if $recordCompetition.Code_typeclt == 'CHPT' && $recordCompetition.Code_tour == '10' && $arrayEquipe_publi[i].Clt <= 3 && $arrayEquipe_publi[i].Clt > 0 && $recordCompetition.Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].Clt}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt == 'CP' && $recordCompetition.Code_tour == '10' && $arrayEquipe_publi[i].CltNiveau <= 3 && $arrayEquipe_publi[i].CltNiveau > 0 && $recordCompetition.Statut == 'END'}
                                    <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].CltNiveau}.gif" alt="Podium" title="Podium" /></td>
                                {elseif $recordCompetition.Code_typeclt == 'CHPT'}
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
                                {/if}
                            </tr>
                        {/section}
                    </tbody>
            </table>
        {/if}
    </article>
    {if $recordCompetition.Code_typeclt == 'CP'}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
            {if $arrayJournee[i].Type == 'C'}
                <article class="padTopBottom table-responsive col-md-6 col-sm-12 tablePhase">
                    <h4>{$arrayJournee[i].Phase}</h4>
                    <table class='table table-striped table-condensed table-hover'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{#Equipes#}</th>
                                <th>{#Pts#}</th>
                                <th>{#J#}</th>
                                <th>{#G#}</th>
                                <th>{#N#}</th>
                                <th>{#P#}</th>
{*                                <th>{#F#}</th>*}
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
{*                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].F}</td>*}
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Plus}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Moins}</td>
                                    <td>{$arrayEquipe_journee_publi[$idJournee][j].Diff}</td>
                                </tr>
                            {/section}
                        </tbody>
                    </table>
                </article>
            {elseif $arrayMatchs[$idJournee]|@count > 0}
                <article class="padTopBottom table-responsive col-md-4 col-md-offset-4 tableMatch">
                    <h4 class="row text-center">{$arrayJournee[i].Phase}</h4>
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
            {/if}
        {/section}
    {else}
        {section name=i loop=$arrayJournee}
            {assign var='idJournee' value=$arrayJournee[i].Id_journee}
                <article class="padTopBottom table-responsive col-md-12 tableJournee">
                    <div class="page-header">
                        <h4>
                            {$arrayJournee[i].Lieu} ({$arrayJournee[i].Departement}) {$arrayJournee[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayJournee[i].Date_fin|date_format:'%d/%m/%Y'}
                            <a class="btn btn-xs btn-default pull-right" href="kpdetails.php?Saison={$Saison}&Group={$recordCompetition.Code_ref}&Compet={$codeCompet}&typ={$recordCompetition.Code_typeclt}&J={$arrayJournee[i].Id_journee}" title="{#Infos#}">{#Infos#}</a>
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
