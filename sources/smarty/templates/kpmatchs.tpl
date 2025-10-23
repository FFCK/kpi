<div class="container saison">
    <article class="col-md-12 padTopBottom">
        <form method="POST" action="kpmatchs.php#containor" name="formJournee" id="formJournee" enctype="multipart/form-data">
            <input type='hidden' name='Cmd' Value=''/>
            <input type='hidden' name='ParamCmd' Value=''/>
            <input type='hidden' name='idEquipeA' Value=''/>
            <input type='hidden' name='idEquipeB' Value=''/>
            <input type='hidden' name='Pub' Value=''/>
            <input type='hidden' name='Verrou' Value=''/>
            
            <div class='col-md-1 col-sm-2 col-xs-2 hidden-xs selects'>
                <label for="Saison">{#Saison#}</label>
                <select name="Saison" onChange="submit()" id="Saison">
                    {section name=i loop=$arraySaison} 
                        <option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $Saison}selected{/if}>{$arraySaison[i].Code}</option>
                    {/section}
                </select>
            </div>
            <div class='col-md-2 col-sm-4 col-xs-4 hidden-xs selects'>
                <label for="event">{#Evenement#}</label>
                <select name="event" onChange="submit();" id="event">
                    <option value="0" {if $event == 0}selected{/if}>--- {#Aucun#} ---</option>
                    {section name=i loop=$arrayEvents}
                        <option value="{$arrayEvents[i].Id}" {if $event == $arrayEvents[i].Id}selected{/if}>{$arrayEvents[i].Libelle}</option>
                    {/section}
                </select>
            </div>
            {if $event <= 0}
                <div class='col-md-4 col-sm-6 col-xs-5 hidden-xs selects'>
                    <label for="Group">{#Competition#}</label>
                    <select name="Group" onChange="submit();" id="Group">
                        {section name=i loop=$arrayCompetitionGroupe}
                            {assign var='options' value=$arrayCompetitionGroupe[i].options}
                            {assign var='label' value=$arrayCompetitionGroupe[i].label}
                            <optgroup label="{$smarty.config.$label|default:$label}">
                                {section name=j loop=$options}
                                    {assign var='optionLabel' value=$options[j].Groupe}
                                    <option Value="{$options[j].Groupe}" {$options[j].selected}>{$smarty.config.$optionLabel|default:$options[j].Libelle}</option>
                                {/section}
                            </optgroup>
                        {/section}
                    </select>
                </div>
            {/if}   
            <div class="visible-xs col-xs-11 selects bold" id="subtitle"><label></label></div>    
            <a class="visible-xs-block col-xs-1 pull-right" href="" id="selects_toggle">
                <img class="img-responsive" src="img/glyphicon-triangle-bottom.png" width="16">
            </a>
            {if $event <= 0}
                {if $arrayCompetition[0].Code_typeclt == 'CHPT'}
                    <div class='col-md-3 col-sm-6 col-xs-7 hidden-xs selects'>
                        <label for="J">{#Journee#}</label>
                        <select name="J" onChange="submit();" id="J">
                            <option Value="*" Selected>{#Toutes#}</option>
                            {section name=i loop=$arrayListJournees}
                                    <option Value="{$arrayListJournees[i].Id}" {if $idSelJournee == $arrayListJournees[i].Id}Selected{/if}>
                                        {if $lang == 'en'}{$arrayListJournees[i].Date_debut_en}
                                        {else}{$arrayListJournees[i].Date_debut}
                                        {/if} - {$arrayListJournees[i].Lieu}
                                    </option>
                            {/section}
                        </select>
                    </div>
                {elseif $nbCompet > 1}
                    <div class='col-md-3 col-sm-6 col-xs-7 hidden-xs selects'>
                        <label for="Compet">{#Categorie#}</label>
                        <select name="Compet" onChange="submit();" id="Compet">
                            <option Value="*" Selected>{#Toutes#}</option>
                            {section name=i loop=$arrayCompetition}
                                    <option Value="{$arrayCompetition[i].Code}" {if $codeCompet == $arrayCompetition[i].Code}Selected{/if}>
                                        {$arrayCompetition[i].Soustitre2|default:$arrayCompetition[i].Libelle}
                                    </option>
                            {/section}
                        </select>
                    </div>
                {else}
                    <div class='col-md-3 col-sm-6 col-xs-7 hidden-xs selects'></div>
                {/if}
            {/if}
            <div class='col-md-2 col-sm-6 col-xs-5 hidden-xs text-right selects'>
                <div id="fb-root"></div>
                <div class="fb-like" data-href="https://www.kayak-polo.info/kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}" 
                     data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
                <br>
                <a class="pdfLink btn btn-default" href="PdfListeMatchs{if $lang=='en'}EN{/if}.php?S={$Saison}&idEvenement={$event}&Group={$codeCompetGroup}&Compet={$codeCompet}&Journee={$idSelJournee}" Target="_blank"><img width="20" src="img/pdf.gif" alt="{#Matchs#} (pdf)" title="{#Matchs#} (pdf)" /></a>
            </div>
        </form>
    </article>
</div>
                
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
                    {assign var='periode' value=$arrayMatchs[i].Periode|default:''}
                    <tr class='{$arrayMatchs[i].StdOrSelected|default:''} {$arrayMatchs[i].past|default:''}'>
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
                                <div class="col-xs-5">
                                    <span class="pull-left badge" title="{if $lang == 'en'}{$arrayMatchs[i].Date_EN}{else}{$arrayMatchs[i].Date_match}{/if}">
                                        {if $lang == 'en'}{$arrayMatchs[i].Date_EN|substr:-5}{else}{$arrayMatchs[i].Date_match|truncate:5:''}{/if}
                                        {$arrayMatchs[i].Heure_match} - {#Terr#} {$arrayMatchs[i].Terrain|default:'&nbsp;'}
                                    </span>
                                </div>
                                <div class="col-xs-2">
                                    <small><em>#{$arrayMatchs[i].Numero_ordre}</em></small>
                                </div>
                                <div class="col-xs-5">
                                    {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                        <small><em><span class="pull-right">{$arrayMatchs[i].Phase|default:'&nbsp;'}</span></em></small>
                                    {else}
                                        <small><em><span class="pull-right">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</span></em></small>
                                    {/if}
                                </div>
                                <div class="col-xs-12">
                                    <div class="btn-group btn-block" role="group">
                                        <a class="col-xs-5 text-right"{if $arrayMatchs[i].NumA > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                            <b class="btn btn-xs btn-default">{$arrayMatchs[i].EquipeA|default:'&nbsp;'}</b>
                                        </a>
                                        
                                        {if $validation == 'O' && $arrayMatchs[i].ScoreA != '?' && $arrayMatchs[i].ScoreA != '' && $arrayMatchs[i].ScoreB != '?' && $arrayMatchs[i].ScoreB != ''}
                                            <a type="button" class="col-xs-2 label label-success" href="PdfMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank" title="{#Feuille_marque#}">
                                                {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                            </a>
                                        {elseif $statut == 'ON' && $validation != 'O'}
                                            <span type="button" class="col-xs-2 scoreProvisoire label label-warning" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                        {elseif $statut == 'END' && $validation != 'O'}
                                            <span type="button" class="col-xs-2 scoreProvisoire label label-info" role="presentation" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                        {else}
                                            <span type="button" class="col-xs-2 statutMatchATT label label-default" title="{#ATT#}">{#ATT#}</span>
                                        {/if}
                                        
                                        <a class="col-xs-5 text-left"{if $arrayMatchs[i].NumB > 0} href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}&Compet={$arrayMatchs[i].Code_competition}&Css={$Css}" title="{#Palmares#}"{/if}>
                                            <b class="btn btn-xs btn-default">{$arrayMatchs[i].EquipeB|default:'&nbsp;'}</b>
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