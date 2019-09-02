{include file='kpnavgroup.tpl'}
        
<div class="container" id="containor">
    {if $type == 'CP' || $event > 0 }
        <article class="col-md-6 padTopBottom">
            <div class="page-header">
                <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
            </div>
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-4">Date</label>
                    <div class="col-sm-8" id="date_debut">{$journee[0].Date_debut|date_format:'%d/%m/%Y'} - {$journee[0].Date_fin|date_format:'%d/%m/%Y'}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Lieu#}</label>
                    <div class="col-sm-8" id="lieu">{$journee[0].Lieu} ({$journee[0].Departement})</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Club_organisateur#}</label>
                    <div class="col-sm-8" id="organisateur">{$journee[0].Organisateur}</div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-4">{#RC#}</label>
                    <div class="col-sm-8" id="rc">{$journee[0].Responsable_insc}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#R1#}</label>
                    <div class="col-sm-8" id="r1">{$journee[0].Responsable_R1}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Delegue#}</label>
                    <div class="col-sm-8" id="delegue">{$journee[0].Delegue}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Chef_arbitres#}</label>
                    <div class="col-sm-8" id="chefarbitre">{$journee[0].ChefArbitre}</div>
                </div>
                {if $schema}
                    <label>{#Schema#}</label>
                    <hr>
                    <a href="{$schema}" target="_blank"><img class="img-responsive img-thumbnail" src="{$schema}"></a>
                {/if}
            </div>
            <div class="form-horizontal">
                <div class="page-header">
                    <h3 class="text-info">{#Autres_categories#}</h3>
                </div>
                {section name=i loop=$arrayListJournees}
                    <p class="col-sm-12">
                        <a class="btn {if $arrayListJournees[i].Code_competition == $journee[0].Code_competition}btn-primary{else}btn-default{/if}" 
                            href="?Saison={$arrayListJournees[i].Code_saison}&event={$event}&Group={$arrayListJournees[i].Code_ref}&Compet={$arrayListJournees[i].Code_competition}&typ={$arrayListJournees[i].Code_typeclt}&J=*&Css={$Css}" role="button">
                            {if $arrayListJournees[i].Titre_actif != 'O' && $arrayListJournees[i].Soustitre2 != ''}
                                {$arrayListJournees[i].Soustitre} - {$arrayListJournees[i].Soustitre2}
                            {else}
                                {$arrayListJournees[i].Libelle} - {$arrayListJournees[i].Soustitre2}
                            {/if}
                        </a>
                    </p>
                {sectionelse}
                    <p class="col-sm-12 text-info">Aucune autre compétition</p>
                {/section}
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="page-header">
                <h3 class="text-info">{#Equipes_engagees#}</h3>
            </div>
            <div class="row">
                {section name=i loop=$arrayPoule}
                    <div class="col-md-6 {if $arrayPoule[i] == $lastpoule}col-md-offset-3{/if} text-center text-info">
                        {if $arrayPoule[i] != '-'}
                            <h4>{#Poule#} {$arrayPoule[i]}</h4>
                        {/if}
                        {assign var='poule' value=$arrayPoule[i]}
                        <table class='table table-striped table-hover'>
                            {section  name=j loop=$arrayEquipe[$poule]}
                                <tr>
                                    <td class="cliquableNomEquipe">
                                        {if $arrayEquipe[$poule][j].logo != ''}
                                            <img class="img2 pull-left" width="28" src="{$arrayEquipe[$poule][j].logo}" alt="{$arrayEquipe[$poule][j].club}" />
                                        {/if}
                                        <a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe[$poule][j].Numero}&Compet={$codeCompet}&Css={$Css}' title='{#Palmares#}'>{$arrayEquipe[$poule][j].Libelle}</a>
                                    </td>
                                </tr>
                            {/section}
                        </table>
                    </div>
                    {if $smarty.section.i.iteration % 2 == 0}
                        </div>
                        <div class="row">
                    {/if}
                {sectionelse}
                    <div class="col-md-12 text-center">{#Information_non_disponible#}</div>
                {/section}
            </div>
        </article>
    {else}
        <article class="col-md-6 padTopBottom">        
            <div class="page-header">
                <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
            </div>
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-4">Date</label>
                    <div class="col-sm-8" id="date_debut">{$journee[0].Date_debut|date_format:'%d/%m/%Y'} - {$journee[0].Date_fin|date_format:'%d/%m/%Y'}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Lieu#}</label>
                    <div class="col-sm-8" id="lieu">{$journee[0].Lieu} ({$journee[0].Departement})</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Organisateur#}</label>
                    <div class="col-sm-8" id="organisateur">{$journee[0].Organisateur}</div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-4">{#RC#}</label>
                    <div class="col-sm-8" id="rc">{$journee[0].Responsable_insc}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#R1#}</label>
                    <div class="col-sm-8" id="r1">{$journee[0].Responsable_R1}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Delegue#}</label>
                    <div class="col-sm-8" id="delegue">{$journee[0].Delegue}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Chef_arbitres#}</label>
                    <div class="col-sm-8" id="chefarbitre">{$journee[0].ChefArbitre}</div>
                </div>
            </div>
            <div class="form-horizontal">
                <div class="page-header">
                    <h3 class="text-info">{#Autres_journees#}</h3>
                </div>
                {section name=i loop=$arrayListJournees}
                    <p class="col-sm-12">
                        <a class="btn {if $arrayListJournees[i].Id_journee == $journee[0].Id_journee}btn-primary{else}btn-default{/if}" 
                           href="?Saison={$arrayListJournees[i].Code_saison}&event={$event}&Group={$arrayListJournees[i].Code_ref}&Compet={$arrayListJournees[i].Code_competition}&typ={$arrayListJournees[i].Code_typeclt}&J={$arrayListJournees[i].Id_journee}&Css={$Css}" role="button">
                            {$arrayListJournees[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayListJournees[i].Date_fin|date_format:'%d/%m/%Y'} à {$arrayListJournees[i].Lieu} ({$arrayListJournees[i].Departement})
                        </a>
                    </p>
                {sectionelse}
                    <p class="col-sm-12 text-info">Aucune autre journée</p>
                {/section}
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="page-header">
                <h3 class="text-info">{#Equipes_engagees#}</h3>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class='table table-striped table-hover'>
                        {assign var='col' value=1}
                        {section name=i loop=$arrayEquipe}
                            {if $col == 1 && $smarty.section.i.iteration > $smarty.section.i.loop / 2}
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class='table table-striped table-hover'>
                                {assign var='col' value=2}
                            {/if}
                            <tr>
                                <td class="cliquableNomEquipe">
                                    {if $arrayEquipe[i].logo != ''}
                                        <img class="img2 pull-left" width="28" src="{$arrayEquipe[i].logo}" alt="{$arrayEquipe[i].club}" />
                                    {/if}
                                    <a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe[i].Numero}&Compet={$codeCompet}&Css={$Css}' title='{#Palmares#}'>{$arrayEquipe[i].Libelle}</a>
                                </td>
                            </tr>
                        {sectionelse}
                            <div class="col-md-12 text-center">{#Information_non_disponible#}</div>
                        {/section}
                    </table>
                </div>
            </div>
        </article>
    {/if}
</div>
