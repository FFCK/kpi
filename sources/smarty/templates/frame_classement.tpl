{include file='frame_navgroup.tpl'}
{*{if $navGroup}
    <div class="container-fluid categorie">
        <div class="col-md-12">
            <a class="btn btn-default actif"
                href="kpmatchs.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round=*&Css={$Css}&navGroup=1">
                {#Matchs#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_phases.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round=*&Css={$Css}&navGroup=1">
                        {#Classement_par_phase#}
            </a>
            <a class="btn btn-primary">{#Classement#}</a>
            <a class="btn btn-default actif" 
                href="frame_stats.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Css={$Css}&navGroup=1">
                        {#Stats#}
            </a>
            <div class="pull-right">
                {section name=i loop=$arrayNavGroup}
                    {if $arrayNavGroup[i].Code == $codeCompet}
                        <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$arrayNavGroup[i].Code}&Css={$Css}&navGroup=1">
                            {$arrayNavGroup[i].Soustitre2}
                        </a>
                    {/if}
                {sectionelse}
                    <h2 class="col-md-12">
                        {$recordCompetition.Soustitre2}
                    </h2>
                {/section}
            </div>
        </div>
    </div>
{else}
    <div class="container categorie">
        <h2 class="col-md-12">
            {$recordCompetition.Soustitre2}
        </h2>
    </div>
{/if}*}
<div class="container">
    <article class="padTopBottom{if $recordCompetition.Code_typeclt != 'CHPT'} table-responsive col-md-6 col-md-offset-3{else} col-md-12{/if} tableClassement">
        {if $recordCompetition.Statut != 'END'}
            <div class="label label-warning">{#Classement_provisoire#}</div>
        {/if}
		{*if $recordCompetition.Statut != 'END'*}
            <table class='table table-striped table-condensed table-hover tableGeneral'>
                {if $recordCompetition.Code_typeclt == 'CHPT'}
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>
                                {#Equipes#}
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
                            </th>
                        </tr>
                    </thead>
                {/if}
                    <tbody>
                            {section name=i loop=$arrayEquipe_publi} 
                                <tr>
                                    {if $recordCompetition.Code_typeclt=='CHPT' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].Clt_publi <= 3 && $arrayEquipe_publi[i].Clt_publi > 0 && $arrayEquipe_publi[i].Statut == 'END'}
                                        <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].Clt_publi}.gif" alt="Podium" title="Podium" /></td>
                                    {elseif $recordCompetition.Code_typeclt=='CP' && $recordCompetition.Code_tour=='10' && $arrayEquipe_publi[i].CltNiveau_publi <= 3 && $arrayEquipe_publi[i].CltNiveau_publi > 0 && $recordCompetition.Statut == 'END'}
                                        <td class='medaille text-center'><img width="30" src="img/medal{$arrayEquipe_publi[i].CltNiveau_publi}.gif" alt="Podium" title="Podium" /></td>
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
                                            {$arrayEquipe_publi[i].Clt_publi}
                                            {if $arrayEquipe_publi[i].logo != ''}
                                                <img class="img2 pull-right" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
                                            {/if}
                                        </td>
                                        <td class="cliquableNomEquipe">
                                            <a class="btn btn-xs btn-default" href="frame_equipes.php?Equipe={$arrayEquipe_publi[i].Numero}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}">
                                                {$arrayEquipe_publi[i].Libelle}
                                            </a>
                                        </td>
                                        <td>{$arrayEquipe_publi[i].Pts_publi/100}</td>
                                        <td>{$arrayEquipe_publi[i].J_publi}</td>
                                        <td>{$arrayEquipe_publi[i].G_publi}</td>
                                        <td>{$arrayEquipe_publi[i].N_publi}</td>
                                        <td>{$arrayEquipe_publi[i].P_publi}</td>
                                        <td>{$arrayEquipe_publi[i].F_publi}</td>
                                        <td>{$arrayEquipe_publi[i].Plus_publi}</td>
                                        <td>{$arrayEquipe_publi[i].Moins_publi}</td>
                                        <td>{$arrayEquipe_publi[i].Diff_publi}</td>
                                    {else}
                                        <td class="centre">
                                            {$arrayEquipe_publi[i].CltNiveau_publi}
                                        </td>
                                        <td class="cliquableNomEquipe">
                                            {if $arrayEquipe_publi[i].logo != ''}
                                                <img class="img2 pull-left" width="30" src="{$arrayEquipe_publi[i].logo}" alt="{$arrayEquipe_publi[i].club}" />
                                            {/if}
                                            <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayEquipe_publi[i].Numero}&Compet={$codeCompet}&Css={$Css}" title="{#Palmares#}">
                                                {$arrayEquipe_publi[i].Libelle}
                                            </a>
                                        </td>
                                        {*<td width="40">{$arrayEquipe_publi[i].PtsNiveau}</td>*}
                                    {/if}
                                </tr>
                            {/section}
                    </tbody>
            </table>
        {*/if*}
    </article>
</div>

