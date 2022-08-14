{include file='frame_navgroup.tpl'}
{section name=i loop=$arrayDates}
    {assign var='Date' value=$arrayDates[i].date}
    <div class="section terrains">
        {assign var='categorie' value=$numCategorie[0]}
        <div class="container-fluid" id="containor">
            <article class="table-responsive col-md-12 padTopBottom">
                <table class='tableau table table-striped table-condensed table-responsive table-hover display compact'>
                    <thead>
                        <tr class="text-center">
                            <th colspan="9" class="text-center bg-primary text-white" width="100%">
                                {if $lang == 'fr'}{$arrayDates[i].date_fr}{else}{$arrayDates[i].date}{/if}
                                {if $codeCompet != ''} - {$categorie}{/if}{if $Pg} - Page {$Pg}{/if}</th>
                        </tr>
                        <tr>
                            <th>#</th>
                            <th class="text-center" width="4%">{#Heure#}</th>
                            <th class="text-center" width="4%">{#Terrain#}</th>
                            <th>{#Poules#}</th>
                            <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                            <th class="cliquableScore">{#Score#}</th>
                            <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                            <th class="cliquableNomEquipe">{#Arbitre_1#}</th>
                            <th class="cliquableNomEquipe">{#Arbitre_2#}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {section name=j loop=$arrayMatchs}
                            {if $smarty.section.j.iteration >= $start
                                                                                                                        && ($len <= 0 or $smarty.section.j.iteration <= $len)
                                                                                                                        && $arrayMatchs[j].Date_EN == $arrayDates[i].date}
                            {assign var='Heure' value=$arrayMatchs[j].Heure_match}
                            {assign var='Match1' value=$arrayMatchs[j]}
                            {assign var='validation1' value=$Match1.Validation}
                            {assign var='statut1' value=$Match1.Statut}
                            {assign var='periode1' value=$Match1.Periode}

                            <tr class='{$Match1.past}'>
                                {if $Match1.Numero_ordre}
                                    <td class="text-center">{$Match1.Numero_ordre}</td>
                                    <td class="text-center">
                                        <span class="badge">{$Match1.Heure_match}</span>
                                    </td>
                                    <td class="text-center"><span class="badge">{$Match1.Terrain}</span></td>
                                    <td>{$Match1.Phase|default:'&nbsp;'}</td>
                                    <td class="text-center" data-filter="{$Match1.EquipeA|default:'&nbsp;'}">
                                        <a class="btn btn-xs btn-default" {if $Match1.Id_equipeA > 0}
                                                href="frame_team.php?Team={$Match1.Id_equipeA}&Compet={$Match1.Code_competition}&Css={$Css}&navGroup={$navGroup}"
                                            title="{#Palmares#}" {/if}>
                                            {$Match1.EquipeA|default:'&nbsp;'}
                                        </a>
                                    </td>
                                    <td class="text-center"><span title="{$Match1.Id}">
                                            {if $validation1 == 'O' && $Match1.ScoreA != '?' && $Match1.ScoreA != '' && $Match1.ScoreB != '?' && $Match1.ScoreB != ''}
                                                <a class="btn btn-success btn-xs" href="PdfMatchMulti.php?listMatch={$Match1.Id}"
                                                    target="_blank" title="{#END#} - {#Feuille_marque#}">
                                                    {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                                    {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                                </a>
                                            {elseif $statut1 == 'ON' && $validation1 != 'O'}
                                                <button type="button" class="btn btn-warning btn-xs scoreProvisoire"
                                                    title="{#scoreProvisoire#}">
                                                    {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                                    {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                                </button>
                                            {elseif $statut1 == 'END' && $validation1 != 'O'}
                                                <button type="button" class="btn btn-warning btn-xs scoreProvisoire"
                                                    title="{#scoreProvisoire#}">
                                                    {$Match1.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} -
                                                    {$Match1.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                                </button>
                                            {else}
                                                <button type="button" class="btn btn-default btn-xs" title="{#ATT#}">
                                                    {#ATT#}
                                                </button>
                                            {/if}
                                        </span>
                                    </td>
                                    <td class="text-center" data-filter="{$Match1.EquipeB|default:'&nbsp;'}">
                                        <a class="btn btn-xs btn-default" {if $Match1.Id_equipeB > 0}
                                                href="frame_team.php?Team={$Match1.Id_equipeB}&Compet={$Match1.Code_competition}&Css={$Css}&navGroup={$navGroup}"
                                            title="{#Palmares#}" {/if}>
                                            {$Match1.EquipeB|default:'&nbsp;'}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-xs btn-default arbitre">{$Match1.Arbitre_principal|default:'&nbsp;'}</a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-xs btn-default arbitre">{$Match1.Arbitre_secondaire|default:'&nbsp;'}</a>
                                    </td>
                                {else}
                                    <td colspan="6" class="pause">{#Pause#}</td>
                                {/if}

                            </tr>
                        {/if}
                        {sectionelse}
                        <tr>
                            <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                        </tr>
                    {/section}
                </tbody>
            </table>
        </article>
    </div>
</div>
{sectionelse}
<div class="container-fluid" id="containor">
    <article class="table-responsive col-md-12 padTopBottom">
        {#Aucun_match#}
    </article>
</div>
{/section}