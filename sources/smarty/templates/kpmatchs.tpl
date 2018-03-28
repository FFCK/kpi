<div class="container titre"> 
    <h1 class="col-xs-12">{#Matchs#}
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </h1>
</div>

<div class="container" id="selector">
    <article class="col-md-12 padTopBottom">
        <form method="POST" action="kpmatchs.php#containor" name="formJournee" id="formJournee" enctype="multipart/form-data">
            <input type='hidden' name='Cmd' Value=''/>
            <input type='hidden' name='ParamCmd' Value=''/>
            <input type='hidden' name='idEquipeA' Value=''/>
            <input type='hidden' name='idEquipeB' Value=''/>
            <input type='hidden' name='Pub' Value=''/>
            <input type='hidden' name='Verrou' Value=''/>
            
            <div class='col-md-1 col-sm-4 col-xs-3 selects'>
                <label for="Saison">{#Saison#}</label>
                <select name="Saison" onChange="submit()">
                    {section name=i loop=$arraySaison} 
                        <Option Value="{$arraySaison[i].Code}" {if $arraySaison[i].Code eq $Saison}selected{/if}>{$arraySaison[i].Code}</Option>
                    {/section}
                </select>
            </div>
            <div class='col-md-4 col-sm-8 col-xs-8 selects'>
                <label for="Group">{#Competition#}</label>
                <select name="Group" onChange="submit();">
                    {section name=i loop=$arrayCompetitionGroupe}
                        {assign var='options' value=$arrayCompetitionGroupe[i].options}
                        {assign var='label' value=$arrayCompetitionGroupe[i].label}
                        <optgroup label="{$smarty.config.$label|default:$label}">
                            {section name=j loop=$options}
                                {assign var='optionLabel' value=$options[j].Groupe}
                                <Option Value="{$options[j].Groupe}" {$options[j].selected}>{$smarty.config.$optionLabel|default:$options[j].Libelle}</Option>
                            {/section}
                        </optgroup>
                    {/section}
                </select>
            </div>
            <a class="visible-xs-block pull-right" href="" id="selects_toggle">
                <img class="img-responsive" src="img/glyphicon-triangle-bottom.png" width="16">
            </a>
            {if $arrayCompetition[0].Code_typeclt == 'CHPT'}
                <div class='col-md-3 col-sm-6 col-xs-12 selects'>
                    <label for="J">{#Journee#}</label>
                    <select name="J" onChange="submit();">
                        <Option Value="*" Selected>{#Toutes#}</Option>
                        {section name=i loop=$arrayListJournees}
                                <Option Value="{$arrayListJournees[i].Id}" {if $idSelJournee == $arrayListJournees[i].Id}Selected{/if}>{if $lang == 'en'}{$arrayListJournees[i].Date_debut_en}{else}{$arrayListJournees[i].Date_debut}{/if} - {$arrayListJournees[i].Lieu}</Option>
                        {/section}
                    </select>
                </div>
            {elseif $nbCompet > 1}
                <div class='col-md-3 col-sm-6 col-xs-12 selects'>
                    <label for="Compet">{#Categorie#}Cat.</label>
                    <select name="Compet" onChange="submit();">
                        <Option Value="*" Selected>{#Toutes#}</Option>
                        {section name=i loop=$arrayCompetition}
                                <Option Value="{$arrayCompetition[i].Code}" {if $idSelCompet == $arrayCompetition[i].Code}Selected{/if}>{$arrayCompetition[i].Soustitre2|default:$arrayCompetition[i].Libelle}</Option>
                        {/section}
                    </select>
                </div>
            {else}
                <div class='col-md-3 col-sm-6 col-xs-12 selects'></div>
            {/if}
            <div class='col-md-4 col-sm-6 col-xs-12 text-right selects'>
                <div class="row">
                    <div id="fb-root"></div>
                    <div class="fb-like" data-href="https://www.kayak-polo.info/kpmatchs.php?Group={$codeCompetGroup}&Saison={$sessionSaison}" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
                </div>
                <div class="row">
                    {if $arrayCompetition[0].Code_typeclt == 'CHPT' && $arrayListJournees|count > 0}
                        {if $idSelJournee == '*'}{assign var='selJournee' value=$arrayListJournees[0].Id}{else}{assign var='selJournee' value=$idSelJournee}{/if}
                        <a class="btn btn-default" href='kpdetails.php?Compet={$codeCompetGroup}&Group={$codeCompetGroup}&Saison={$Saison}&Journee={$selJournee}&typ=CHPT'>{#Infos#}</a>
                    {elseif $nbCompet > 1}
                        {if $idSelCompet == '*'}{assign var='selCompet' value=$arrayCompetition[0].Code}{else}{assign var='selCompet' value=$idSelCompet}{/if}
                        <a class="btn btn-default" href='kpdetails.php?Compet={$selCompet}&Group={$codeCompetGroup}&Saison={$Saison}&typ=CP'>{#Infos#}</a>
                    {/if}
                    <a class="pdfLink btn btn-default" href="PdfListeMatchs{if $lang=='en'}EN{/if}.php?S={$Saison}&Group={$codeCompetGroup}&Compet={$idSelCompet}&Journee={$idSelJournee}" Target="_blank"><img width="20" src="img/pdf.gif" alt="{#Matchs#} (pdf)" title="{#Matchs#} (pdf)" /></a>
                    <a class="btn btn-default" href='kpclassements.php?Compet={$idSelCompet}&Group={$codeCompetGroup}&Saison={$Saison}&Journee={$idSelJournee}'>{#Classements#}...</a>
                    <a class="btn btn-default" title="{#Partager#}" data-link="https://www.kayak-polo.info/kpmatchs.php?Group={$codeCompetGroup}&Compet={$idSelCompet}&Saison={$Saison}&Journee={$idSelJournee}&lang={$lang}" id="share_btn"><img src="img/share.png" width="16"></a>
                </div>
            </div>
        </form>
    </article>
</div>
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
                            <td class="hidden-xs">{$arrayMatchs[i].Code_competition}</td>
                            {if $arrayCompetition[0].Code_typeclt == 'CP'}
                                <td class="hidden-xs">{$arrayMatchs[i].Phase|default:'&nbsp;'}</td>
                            {else}
                                <td class="hidden-xs">{$arrayMatchs[i].Lieu|default:'&nbsp;'}</td>
                            {/if}
                            <td class="hidden-xs">{$arrayMatchs[i].Terrain|default:'&nbsp;'}</td>
                            <td class="text-center hidden-xs" data-filter="{$arrayMatchs[i].EquipeA|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}" title="{#Palmares#}">
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
                                    <a class="btn btn-xs btn-default" href="PdfMatchMulti.php?listMatch={$arrayMatchs[i].Id}" Target="_blank" title="{#Feuille_marque#}">
                                    {$arrayMatchs[i].ScoreA|replace:'?':'&nbsp;'|default:'&nbsp;'} - {$arrayMatchs[i].ScoreB|replace:'?':'&nbsp;'|default:'&nbsp;'}
                                    </a>
                                    <br />
                                    <span class="statutMatch label label-success" title="{#END#}">{#END#}</span>
                                {elseif $statut == 'ON' && $validation != 'O'}
                                    <span class="scoreProvisoire btn btn-xs btn-warning" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info" title="{$smarty.config.$periode}">{$smarty.config.$periode}</span>
                                {elseif $statut == 'END' && $validation != 'O'}
                                    <span class="scoreProvisoire btn btn-xs btn-warning" role="presentation" title="{#scoreProvisoire#}">{$arrayMatchs[i].ScoreDetailA} - {$arrayMatchs[i].ScoreDetailB}</span>
                                    <br />
                                    <span class="statutMatchOn label label-info" title="{#scoreProvisoire#}">{#scoreProvisoire#}</span>
                                {else}
                                    <br />
                                    <span class="statutMatchATT label label-default" title="{#ATT#}">{#ATT#}</span>
                                {/if}
                            </td>
                            <td class="text-center hidden-xs" data-filter="{$arrayMatchs[i].EquipeB|default:'&nbsp;'}">
                                <a class="btn btn-xs btn-default" href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}" title="{#Palmares#}">
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
                                        <span type="button" class="col-xs-5 text-right" href="kpequipes.php?Equipe={$arrayMatchs[i].NumA}" title="{#Palmares#}">
                                            <b class="">{$arrayMatchs[i].EquipeA|default:'&nbsp;'}</b>
                                        </span>
                                        
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
                                        
                                        <span type="button" class="col-xs-5 text-left" href="kpequipes.php?Equipe={$arrayMatchs[i].NumB}" title="{#Palmares#}">
                                            <b class="">{$arrayMatchs[i].EquipeB|default:'&nbsp;'}</b>
                                        </span>
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
	</article>
</div>
<script>
    {if $arrayCompetition[0].Code_typeclt == 'CP'}
        table_ordre = [[ 1, 'asc' ], [ 4, 'asc' ]];
    {else}
        table_ordre = [[ 0, 'asc' ]];
    {/if}
</script>