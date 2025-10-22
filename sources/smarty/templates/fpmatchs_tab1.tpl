<div class="container titre">
    <div class="col-md-12">
        <h1 class="col-md-11 col-xs-9">{#Matchs#}</h1>
    </div>
</div>

<div class="container-fluid" id="containor">
    <article class="table-responsive col-md-12 padTopBottom">
        <table width="100%" class='tableau table table-bordered'>
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">{#Cat#}</th>
                    <th class="text-center">{#Date#}</th>
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
                </tr>
            </thead>
            <tbody>
                {section name=i loop=$arrayMatchs}
                    {assign var='validation' value=$arrayMatchs[i].Validation}
                    {assign var='statut' value=$arrayMatchs[i].Statut}
                    {assign var='periode' value=$arrayMatchs[i].Periode|default:''}
                    <tr class='{$arrayMatchs[i].StdOrSelected} {$arrayMatchs[i].past}'>
                            <td class="text-center">{$arrayMatchs[i].Numero_ordre}</td>
                            <td class="text-center">{$arrayMatchs[i].Code_competition}</td>
                            <td class="text-center">{$arrayMatchs[i].Date_match|truncate:5:""} {$arrayMatchs[i].Heure_match}</td>
                            {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                <td class="text-center">{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
                            {else}
                                <td class="text-center">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
                            {/if}
                            <td class="text-center">{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
                            <td class="text-center">
                                {if $arrayMatchs[i].logoA != ''}
                                    <img class="img2 pull-left" height="25" src="{$arrayMatchs[i].logoA}" alt="{$arrayMatchs[i].clubA}" />
                                {/if}
                                {$arrayMatchs[i].EquipeA|default:'&nbsp;'}
                            </td>
                            <td class="text-center">
                                {if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
                                    <span class="statutMatch label label-success">
                                        {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                    </span>
                                {elseif $statut == 'ON' && $validation != 'O'}
                                    <span class="statutMatchOn label label-info">
                                        {$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}
                                    </span>
                                {elseif $statut == 'END' && $validation != 'O'}
                                    <span class="statutMatchOn label label-info">
                                        {$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}
                                    </span>
                                {else}
                                    <span class="statutMatchATT label label-default" title="{#ATT#}">{#ATT#}</span>
                                {/if}
                            </td>
                            <td class="text-center">
                                {$arrayMatchs[i].EquipeB|default:'&nbsp;'}
                                {if $arrayMatchs[i].logoB != ''}
                                    <img class="img2 pull-right" height="25" src="{$arrayMatchs[i].logoB}" alt="{$arrayMatchs[i].clubB}" />
                                {/if}
                            </td>
                            <td class="text-center arb1">{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal}{else}&nbsp;{/if}</td>
                            <td class="text-center arb2">{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire}{else}&nbsp;{/if}</td>
                    </tr>
                {sectionelse}
                    <tr>
                        <td colspan=13 align=center><i>{#Aucun_match#}</i></td>
                    </tr>
                {/section}
            </tbody>
        </table>
	</article>
</div>