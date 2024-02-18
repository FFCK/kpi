{include file='kpnavselect.tpl'}

{if $navGroup}
    {include file='kpnavgroup.tpl'}
{else}
    <div class="container-fluid categorie mb5">
        <div class="col-md-12">
            <a class="btn btn-primary">{#Matchs#}</a>
            <a class="btn btn-default actif"
                href="frame_chart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1">
                {#Deroulement#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_phases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Phases#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_classement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Classement#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_stats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Stats#}
            </a>
            <div class="pull-right">
                {if $next}
                    <a class="btn btn-primary actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                        {#Prochains_matchs#}
                    </a>
                {else}
                    <a class="btn btn-default actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                        {#Prochains_matchs#}
                    </a>
                {/if}
                {if $arrayNavGroup}
                    <a class="btn {if '*' == $codeCompet}btn-primary{else}btn-default actif{/if}" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet=*&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Tous#}
                    </a>
                {/if}
                {section name=i loop=$arrayNavGroup}
                    {if $arrayNavGroup[i].Code == $codeCompet}
                        <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$arrayNavGroup[i].Code}&Round={$Round}&Css={$Css}&navGroup=1">
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
{/if}

<div class="container-fluid" id="containor">
    <article class="table-responsive col-md-12 padTopBottom">
        <table class='tableau table table-striped table-condensed table-hover display compact' {if is_array($arrayMatchs[0])}id='tableMatchs_{$lang}'{/if}>
            <thead>
                <tr>
                    <th class="hidden-xs">#</th>
                    <th class="hidden-xs">{#Date#}</th>
                    <th class="hidden-xs">{#Cat#}</th>
                    {if $arrayCompetition[0].Code_typeclt == 'CP'}
                        <th class="hidden-xs">{#Poules#}</th>
                    {else}
                        <th class="hidden-xs">{#Lieu#}</th>
                    {/if}
                    <th class="hidden-xs">{#Terr#}</th>
                    <th class="cliquableNomEquipe hidden-xs">{#Equipe_A#}</th>
                    <th class="cliquableScore hidden-xs">{#Score#}</th>
                    <th class="cliquableNomEquipe hidden-xs">{#Equipe_B#}</th>
                    <th class="arb1 hidden-xs">{#Arbitre_1#}</th>	
                    <th class="arb2 hidden-xs">{#Arbitre_2#}</th>
                    <th class="visible-xs-block">{#Matchs#}</th>
                </tr>
            </thead>
            <tfoot class="hidden-xs">
                <tr>
                    <th class="hidden-xs">#</th>
                    <th class="hidden-xs">{#Date#}</th>
                    <th class="hidden-xs">{#Cat#}</th>
                    {if $PhaseLibelle == 1}
                        <th class="hidden-xs">{#Poules#}</th>
                    {else}
                        <th class="hidden-xs">{#Lieu#}</th>
                    {/if}
                    <th class="hidden-xs">{#Terr#}</th>
                    <th class="cliquableNomEquipe hidden-xs">{#Equipe_A#}</th>
                    <th class="cliquableScore hidden-xs">{#Score#}</th>
                    <th class="cliquableNomEquipe hidden-xs">{#Equipe_B#}</th>
                    <th class="arb1 hidden-xs">{#Arbitre_1#}</th>	
                    <th class="arb2 hidden-xs">{#Arbitre_2#}</th>	
                </tr>
            </tfoot>
            <tbody>
                {section name=i loop=$arrayMatchs}
                    {assign var='validation' value=$arrayMatchs[i].Validation}
                    {assign var='statut' value=$arrayMatchs[i].Statut}
                    {assign var='periode' value=$arrayMatchs[i].Periode}
                    <tr class='{$arrayMatchs[i].StdOrSelected} {$arrayMatchs[i].past}'>
                            <td class="hidden-xs">{$arrayMatchs[i].Numero_ordre}</td>
                            <td class="hidden-xs" data-order="{$arrayMatchs[i].Date_EN} {$arrayMatchs[i].Heure_match}" data-filter="{if $lang == 'en'}{$arrayMatchs[i].Date_EN}{else}{$arrayMatchs[i].Date_match}{/if}">
                                {if $lang == 'en'}{$arrayMatchs[i].Date_EN}{else}{$arrayMatchs[i].Date_match}{/if}<br /><span class="pull-right badge">{$arrayMatchs[i].Heure_match}</span>
                            </td>
                            <td class="hidden-xs">{$arrayMatchs[i].Categorie}</td>
                            {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                <td class="hidden-xs">{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
                            {else}
                                <td class="hidden-xs">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
                            {/if}
                            <td class="hidden-xs">{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
                            <td class="text-center hidden-xs" data-filter="{$arrayMatchs[i].EquipeA|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default"{if $arrayMatchs[i].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                    {$arrayMatchs[i].EquipeA|default:'&nbsp;'}
                                </a>
                            </td>
                            <td class="text-center hidden-xs">
                                {if $arrayMatchs[i].logoA != ''}
                                    <img class="img2 pull-left hidden-sm hidden-xs" width="30" src="{$arrayMatchs[i].logoA}" alt="{$arrayMatchs[i].clubA}" />
                                {/if}
                                {if $arrayMatchs[i].logoB != ''}
                                    <img class="img2 pull-right hidden-sm hidden-xs" width="30" src="{$arrayMatchs[i].logoB}" alt="{$arrayMatchs[i].clubB}" />
                                {/if}
                                
                                {if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
                                    <span class="btn btn-xs btn-default ">{$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}</span>
                                    <br />
                                    <a class="statutMatch label label-success report" href="PdfMatchMulti.php?listMatch={$arrayMatchs[i].Id}" target="_blank">
                                        {#Feuille_marque#}
                                    </a>
                                {elseif $statut == 'ON' && $validation != 'O'}
                                    <span class="scoreProvisoire btn btn-xs btn-warning">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info">{$smarty.config.$periode}</span>
                                {elseif $statut == 'END' && $validation != 'O'}
                                    <span class="scoreProvisoire btn btn-xs btn-warning" role="presentation">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info">{#scoreProvisoire#}</span>
                                {else}
                                    <br />
                                    <span class="statutMatchATT label label-default">{#ATT#}</span>
                                {/if}
                                
                            </td>
                            <td class="text-center hidden-xs" data-filter="{$arrayMatchs[i].EquipeB|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default"{if $arrayMatchs[i].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                    {$arrayMatchs[i].EquipeB|default:'&nbsp;'}
                                </a>
                            </td>
                            <td class="arb1 hidden-xs"><small>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:'(':'<br>('}{else}&nbsp;{/if}</small></td>
                            <td class="arb2 hidden-xs"><small>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:'(':'<br>('}{else}&nbsp;{/if}</small></td>
                            
                            
                            <td class="text-center visible-xs-block" 
                                data-order="{$arrayMatchs[i].Date_EN} {$arrayMatchs[i].Heure_match} {$arrayMatchs[i].Terrain|default:'&nbsp;'}"
                                data-filter="{$arrayMatchs[i].EquipeA} 
                                            {$arrayMatchs[i].EquipeB} 
                                            {if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal}{/if} 
                                            {if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire}{/if}">
                                <div class="col-xs-6">
                                    <span class="pull-left badge" title="{if $lang == 'en'}{$arrayMatchs[i].Date_EN}{else}{$arrayMatchs[i].Date_match}{/if}">
                                        {if $lang == 'en'}{$arrayMatchs[i].Date_EN|substr:-5}{else}{$arrayMatchs[i].Date_match|truncate:5:''}{/if}
                                        {$arrayMatchs[i].Heure_match} - {#Terr#} {$arrayMatchs[i].Terrain|default:'&nbsp;'}
                                    </span>
                                </div>
                                <div class="col-xs-6">
                                    {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                        <small><em><span class="pull-right">{$arrayMatchs[i].Phase|default:'&nbsp;'}</em></span></small>
                                    {else}
                                        <small><em><span class="pull-right">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</em></span></small>
                                    {/if}
                                </div>
                                <div class="col-xs-12">
                                    <div class="btn-group btn-block" role="group">
                                        <a class="col-xs-5 text-right"{if $arrayMatchs[i].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                            <b class="">{$arrayMatchs[i].EquipeA|default:'&nbsp;'}</b>
                                        </a>
                                        
                                        {if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
                                            <span type="button" class="col-xs-2 label label-success" href="PdfMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank" title="{#Feuille_marque#}">
                                                {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                            </span>
                                        {elseif $statut == 'ON' && $validation != 'O'}
                                            <span type="button" class="col-xs-2 scoreProvisoire label label-warning" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                        {elseif $statut == 'END' && $validation != 'O'}
                                            <span type="button" class="col-xs-2 scoreProvisoire label label-info" role="presentation" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                        {else}
                                            <span type="button" class="col-xs-2 statutMatchATT label label-default" title="{#ATT#}">{#ATT#}</span>
                                        {/if}
                                        
                                        <a class="col-xs-5 text-left"{if $arrayMatchs[i].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                            <b class="">{$arrayMatchs[i].EquipeB|default:'&nbsp;'}</b>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-xs-6 text-left">
                                    <small><em>{if $arrayMatchs[i].Arbitre_principal != '-1'}{$arrayMatchs[i].Arbitre_principal|replace:' (':'<br>('}{else}&nbsp;{/if}</em></small>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <small><em>{if $arrayMatchs[i].Arbitre_secondaire != '-1'}{$arrayMatchs[i].Arbitre_secondaire|replace:' (':'<br>('}{else}&nbsp;{/if}</em></small>
                                </div>
                            </td>
                    </tr>
                {sectionelse}
                    <tr>
                        <td colspan=13 class="text-center hidden-xs"><i>{#Aucun_match#}</i></td>
                        <td align="center" class="visible-xs-block"><i>{#Aucun_match#}</i></td>
                    </tr>
                {/section}
            </tbody>
        </table>
        <br>
        <br>
        <br>
	</article>
</div>
<script>
    {if $arrayCompetition[0].Code_typeclt == 'CP'}
        table_ordre = [[ 1, 'asc' ], [ 4, 'asc' ]];
    {else}
        table_ordre = [[ 0, 'asc' ]];
    {/if}
</script>