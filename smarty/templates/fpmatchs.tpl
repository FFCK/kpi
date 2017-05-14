{*        <pre>{$arrayDates|@var_dump}</pre>*}
<div id="header"><b>Kayak-polo.info</b></div>
{*<div id="footer">Footer</div>*}
<div id="fullpage">
    {section name=i loop=$arrayDates}
        <div class="section">
            <div class="container titre">
                <div class="col-md-12">
                    <h3 class="col-md-11 col-xs-9">{#Matchs#} - {$arrayDates[i].date|date_format:"%d/%m/%Y"}</h3>
                </div>
            </div>
            <div class="container-fluid" id="containor">
                <article class="table-responsive col-md-12 padTopBottom">
                    <table width="100%" class='tableau table table-bordered'>
                        <thead>
                            <tr>
                                <th class="text-center">{#Heure#}</th>
                                {section name=terr loop=$terrains}
                                    <th class="text-center">{#Terr#} {$smarty.section.terr.iteration}</th>
                                {/section}
        {*                            <th class="text-center">{#Heure#}</th>
                                    <th class="text-center">{#Heure#}</th>
                                    <th class="text-center">{#Cat#}</th>
                                    <th class="text-center"></th>
                                    {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                        <th class="text-center">{#Poules#}</th>
                                    {else}
                                        <th class="text-center">{#Lieu#}</th>
                                    {/if}
                                    <th class="text-center">{#Terr#}</th>
                                    <th class="cliquableNomEquipe text-center">{#Equipe_A#}</th>
                                    <th class="cliquableScore text-center">{#Score#}</th>
                                    <th class="cliquableNomEquipe text-center">{#Equipe_B#}</th>
                                    <th class="arb1 text-center">{#Arbitre_1#}</th>	
                                    <th class="arb2 text-center">{#Arbitre_2#}</th>
        *}                    </tr>
                        </thead>
                        <tbody>
                            {assign var='Date' value=$arrayDates[i].date}
        {*                    {assign var='tabHeure' value=$arrayHeure[$Date]}*}

        {*                    <pre>{$arrayHeures[$Date]|@var_dump}</pre>*}
                            {section name=j loop=$arrayHeures[$Date]}
                                {assign var='Heure' value=$arrayHeures[$Date][j].heure}
                                <tr>
                                    <td class="text-center"><b>{$Heure}</b></td>
                                    {section name=terr2 loop=$terrains}
                                        {assign var='Terrain' value=$smarty.section.terr2.iteration}
                                        {if $arrayMatchs[$Date][$Heure][$Terrain]}
                                            {assign var='Match' value=$arrayMatchs[$Date][$Heure][$Terrain]}
                                            {assign var='validation' value=$arrayMatchs[i].Validation}
                                            {assign var='statut' value=$arrayMatchs[i].Statut}
                                            {assign var='periode' value=$arrayMatchs[i].Periode}
                                            <td>
                                                <table width="100%" class='tableau2 table table-bordered'>
                                                    <tr>
                                                        <td class="text-center" rowspan="2">
                                                            {if $Match.logoA != ''}
                                                                <img class="img2 pull-left" src="{$Match.logoA}" alt="{$Match.clubA}" />
                                                            {/if}
                                                        </td>
                                                        <td class="text-center" colspan="4"><span class="idmatch pull-left badge">{$Match.Numero_ordre}</span> {$Match.Code_competition} - {$Match.Phase|default:'&nbsp;'}</td>
                                                        <td class="text-center" rowspan="2">
                                                            {if $Match.logoB != ''}
                                                                <img class="img2 pull-right" src="{$Match.logoB}" alt="{$Match.clubB}" />
                                                            {/if}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="btn btn-xs btn-primary">{$Match.EquipeA|default:'&nbsp;'}</span>
                                                        </td>
                                                        <td class="text-center" colspan="2">
                                                            {if $validation == 'O' && $Match.ScoreA != '?' && $Match.ScoreA != '' && $Match.ScoreB != '?' && $Match.ScoreB != ''}
                                                                <span class="statutMatch label label-success">
                                                                    {$Match.ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$Match.ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                                                </span>
                                                            {elseif $statut == 'ON' && $validation != 'O'}
                                                                <span class="statutMatchOn label label-info">
                                                                    {$Match.ScoreDetailA} - {$Match.ScoreDetailB}
                                                                </span>
                                                            {elseif $statut == 'END' && $validation != 'O'}
                                                                <span class="statutMatchOn label label-info">
                                                                    {$Match.ScoreDetailA} - {$Match.ScoreDetailB}
                                                                </span>
                                                            {else}
                                                                <span class="statutMatchATT label label-default" title="{#ATT#}">{#ATT#}</span>
                                                            {/if}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="btn btn-xs btn-primary">{$Match.EquipeB|default:'&nbsp;'}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="text-center arb1">
                                                            {if $Match.Arbitre_principal != '-1'}{$Match.Arbitre_principal}{else}&nbsp;{/if}
                                                        </td>
                                                        <td colspan="3" class="text-center arb2">
                                                            {if $Match.Arbitre_secondaire != '-1'}{$Match.Arbitre_secondaire}{else}&nbsp;{/if}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        {else}
                                            <td></td>
                                        {/if}
                                    {/section}
                            {/section}
                        </tbody>
                    </table>
                </article>
            </div>
        </div>
    {/section}
</div>