<div class="container-fluid" id="containor">
    <article class="table-responsive col-md-12 padTopBottom">
        <table class='tableau table table-striped table-condensed table-responsive table-hover display compact' {if is_array($arrayMatchs[0])}id='tableMatchs_{$lang}'{/if}>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{#Date#}</th>
                    <th>{#Cat#}</th>
                    {if $arrayCompetition[0].Code_typeclt == 'CP'}
                        <th>{#Poules#}</th>
                    {else}
                        <th>{#Lieu#}</th>
                    {/if}
                    <th>{#Terr#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                    {if $arbitres > 0}
                        <th class="arb1">{#Arbitre_1#}</th>	
                        <th class="arb2">{#Arbitre_2#}</th>
                    {/if}
                </tr>
            </thead>
            <tbody>
                {section name=i loop=$arrayMatchs}
                    {assign var='validation' value=$arrayMatchs[i].Validation}
                    {assign var='statut' value=$arrayMatchs[i].Statut}
                    {assign var='periode' value=$arrayMatchs[i].Periode}
                    <tr class='{$arrayMatchs[i].StdOrSelected} {$arrayMatchs[i].past}'>
                            <td>{$arrayMatchs[i].Numero_ordre}</td>
                            <td data-order="{$arrayMatchs[i].Date_EN} {$arrayMatchs[i].Heure_match}" data-filter="{$arrayMatchs[i].Date_match}">
                                {$arrayMatchs[i].Date_match}
                                <span class="pull-right badge">{$arrayMatchs[i].Heure_match}</span>
                            </td>
                            <td>{$arrayMatchs[i].Code_competition}</td>
                            {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                <td>{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
                            {else}
                                <td>{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
                            {/if}
                            <td>{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
                            <td class="text-center" data-filter="{$arrayMatchs[i].EquipeA|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}" title="{#Palmares#}">
                                    {$arrayMatchs[i].EquipeA|default:'&nbsp;'}
                                </a>
                            </td>
                            <td class="text-center">
                                {if $arrayMatchs[i].logoA != ''}
                                    <img class="img2 pull-left" width="30" src="{$arrayMatchs[i].logoA}" alt="{$arrayMatchs[i].clubA}" />
                                {/if}
                                {if $arrayMatchs[i].logoB != ''}
                                    <img class="img2 pull-right" width="30" src="{$arrayMatchs[i].logoB}" alt="{$arrayMatchs[i].clubB}" />
                                {/if}
                                {if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
                                    <button type="button" class="btn btn-success btn-sm" title="{#END#}">
                                           {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                    </button>
                                {elseif $statut == 'ON' && $validation != 'O'}
                                    <button type="button" class="btn btn-warning btn-sm" title="{#scoreProvisoire#}">
                                           {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                    </button>
                                    {*<span class="scoreProvisoire btn btn-xs btn-warning" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info" title="{$smarty.config.$periode}">{$smarty.config.$periode}</span>*}
                                {elseif $statut == 'END' && $validation != 'O'}
                                    <button type="button" class="btn btn-warning btn-sm" title="{#scoreProvisoire#}">
                                           {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                    </button>
                                    {*<span class="scoreProvisoire btn btn-xs btn-warning" role="presentation" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info" title="{#scoreProvisoire#}">{#scoreProvisoire#}</span>*}
                                {else}
                                    <button type="button" class="btn btn-default btn-sm" title="{#ATT#}">
                                           {#ATT#}
                                    </button>
                                {/if}
                            </td>
                            <td class="text-center" data-filter="{$arrayMatchs[i].EquipeB|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}" title="{#Palmares#}">
                                    {$arrayMatchs[i].EquipeB|default:'&nbsp;'}
                                </a>
                            </td>
                            {if $arbitres > 0}
                                <td class="arb1">{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
                                <td class="arb2">{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</td>
                            {/if}
                    </tr>
                {sectionelse}
                    <tr>
                        <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                    </tr>
                {/section}
            </tbody>
            <tfoot class="hidden-xs hidden-sm">
                <tr>
                    <th>#</th>
                    <th>{#Cat#}</th>
                    <th>{#Date#}</th>
                    {if $PhaseLibelle == 1}
                        <th>{#Poules#}</th>
                    {else}
                        <th>{#Lieu#}</th>
                    {/if}
                    <th>{#Terr#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_A#}</th>
                    <th class="cliquableScore">{#Score#}</th>
                    <th class="cliquableNomEquipe">{#Equipe_B#}</th>
                    {if $arbitres > 0}
                        <th class="arb1">{#Arbitre_1#}</th>	
                        <th class="arb2">{#Arbitre_2#}</th>
                    {/if}
                </tr>
            </tfoot>
        </table>
        
	</article>
</div>
<script>
    {if $arrayCompetition[0].Code_typeclt == 'CP'}
        table_ordre = [[ 2, 'asc' ], [ 4, 'asc' ]];
    {else}
        table_ordre = [[ 0, 'asc' ]];
    {/if}
</script>