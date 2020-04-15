{if $navGroup}
    <div class="container-fluid visible-lg categorie mb5">
        <div class="col-md-12">
            <a class="btn {if $page == 'Matchs'}btn-primary{else}btn-default actif{/if}"
                href="frame_matchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Matchs#}
            </a>
            <a class="btn {if $page == 'Terrains'}btn-primary{else}btn-default actif{/if}"
                href="frame_terrains.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Terrains#}
            </a>
            <a class="btn {if $page == 'Categories'}btn-primary{else}btn-default actif{/if}"
                href="frame_categories.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Categories#}
            </a>
            {if $recordCompetition.Code_typeclt == 'CHPT'}
                <a class="btn {if $page == 'Infos'}btn-primary{else}btn-default{/if}" 
                   href='frame_details.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&typ=CHPT&Round={$Round}&Css={$Css}&navGroup=1&J={$idSelJournee}'>
                    {#Infos#}
                </a>
            {else}
                <a class="btn {if $page == 'Infos'}btn-primary{else}btn-default{/if}" 
                   href='frame_details.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&typ=CP&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>
                    {#Infos#}
                </a>
            {/if}
            <a class="btn {if $page == 'Deroulement'}btn-primary{else}btn-default actif{/if}"
                href="frame_chart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Deroulement#}
            </a>
            <a class="btn {if $page == 'Phases'}btn-primary{else}btn-default actif{/if}"
                href="frame_phases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Phases#}
            </a>
            <a class="btn {if $page == 'Classement'}btn-primary{else}btn-default actif{/if}"
                href="frame_classement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Classement#}
            </a>
            <a class="btn {if $page == 'Stats'}btn-primary{else}btn-default actif{/if}"
                href="frame_stats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Stats#}
            </a>
            <div class="pull-right">
                {if $page == 'Matchs'}
                    {if $next}
                        <a class="btn btn-primary actif" 
                           href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                            {#Prochains_matchs#}
                        </a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                            {#Prochains_matchs#}
                        </a>
                    {/if}
                {/if}
                
                {if $page == 'Terrains'}
                    <span class="dropdown">
                        <a id="drop4" class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="fa fa-calendar"></span>
                            <b>{#Date#}</b>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="drop4">
                            <li {if '' == $filtreJour}class="active"{/if}>
                                <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                                {#Tous#}</a>
                            </li>
                            {section name=i loop=$arrayJours}
                                <li {if $arrayJours[i] == $filtreJour}class="active"{/if}>
                                    <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour={$arrayJours[i]}&Css={$Css}&navGroup=1">
                                    {if $lang == 'fr'}{$arrayJours[i]|date_format:"%d/%m/%Y"}{else}{$arrayJours[i]}{/if}</a>
                                </li>
                            {sectionelse}
                                <a class="btn btn-primary">
                                    {#Aucune#}
                                </a>
                            {/section}
                        </ul>
                    </span>
                {/if}
                
                {if ($page == 'Matchs' || $page == 'Terrains') && $arrayNavGroup}
                    <a class="btn {if '*' == $codeCompet}btn-primary{else}btn-default actif{/if}" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Tous#}
                    </a>
                {/if}
            
                {section name=i loop=$arrayNavGroup}
                    {if $arrayNavGroup[i].Code == $codeCompet}
                        <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$arrayNavGroup[i].Code_ref}&Compet={$arrayNavGroup[i].Code}&Round={$Round}&Css={$Css}&navGroup=1">
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

    <div class="container-fluid article hidden-lg navgroup">
        <ul class="nav nav-pills">
            <li role="presentation" class="dropdown">
                <a id="drop1" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-bars"></span>
                    <b>{$smarty.config.$page}</b>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="drop1">
                    <li class="{if $page == 'Matchs'}active{/if}"><a id="btnkpmatch" href="frame_matchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Matchs#}</a></li>
                    <li class="{if $page == 'Terrains'}active{/if}"><a href="frame_terrains.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Terrains#}</a></li>
                    {if $recordCompetition.Code_typeclt == 'CHPT'}
                        <li class="{if $page == 'Infos'}active{/if}"><a href='frame_details.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&typ=CHPT&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a></li>
                    {else}
                        <li class="{if $page == 'Infos'}active{/if}"><a href='frame_details.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&typ=CP&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a></li>
                    {/if}
                    <li class="{if $page == 'Deroulement'}active{/if}"><a href="frame_chart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Deroulement#}</a></li>
                    <li class="{if $page == 'Phases'}active{/if}"><a href="frame_phases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Phases#}</a></li>
                    <li class="{if $page == 'Classement'}active{/if}"><a href="frame_classement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Classement#}</a></li>
                    <li class="{if $page == 'Stats'}active{/if}"><a href="frame_stats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Stats#}</a></li>
                </ul>
            </li>
            <li role="presentation" class="dropdown pull-right">
                <a id="drop2" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    {section name=i loop=$arrayNavGroup}
                        {if $arrayNavGroup[i].Code == $codeCompet}
                            <b>{$arrayNavGroup[i].Soustitre2}</b>
                        {/if}
                    {/section}
                    {if '*' == $codeCompet}
                       <b> {#Toutes_divisions#}</b>
                    {/if}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="drop2">
                    {if ($page == 'Matchs' || $page == 'Terrains') && $arrayNavGroup}
                        <li {if '*' == $codeCompet}class="active"{/if}>
                            <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                            {#Toutes_divisions#}</a>
                        </li>
                        <li role="separator" class="divider"></li>
                    {/if}
                    {section name=i loop=$arrayNavGroup}
                        <li {if $arrayNavGroup[i].Code == $codeCompet}class="active"{/if}>
                            <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$arrayNavGroup[i].Code_ref}&Compet={$arrayNavGroup[i].Code}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                            {$arrayNavGroup[i].Soustitre2}</a>
                        </li>
                    {sectionelse}
                        <a class="btn btn-primary">
                            {$recordCompetition.Soustitre2}
                        </a>
                    {/section}
                </ul>
            </li>
            
            {if $page == 'Matchs'}
                {if $next}
                    <li role="presentation" class="active pull-right">
                        <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                            <b>{#Prochains_matchs#}</b>
                        </a>
                    </li>
                {else}
                    <li role="presentation" class="pull-right">
                        <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                            <b>{#Prochains_matchs#}</b>
                        </a>
                    </li>
                {/if}
            {/if}
            
            {if $page == 'Terrains'}
                <li role="presentation" class="dropdown pull-right">
                    <a id="drop3" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="fa fa-calendar"></span>
                        <b>{#Date#}</b>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="drop3">
                        <li {if '' == $filtreJour}class="active"{/if}>
                            <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                            {#Tous#}</a>
                        </li>
                        <li role="separator" class="divider"></li>
                        {section name=i loop=$arrayJours}
                            <li {if $arrayJours[i] == $filtreJour}class="active"{/if}>
                                <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour={$arrayJours[i]}&Css={$Css}&navGroup=1">
                                {if $lang == 'fr'}{$arrayJours[i]|date_format:"%d/%m/%Y"}{else}{$arrayJours[i]}{/if}</a>
                            </li>
                        {sectionelse}
                            <a class="btn btn-primary">
                                {#Aucune#}
                            </a>
                        {/section}
                    </ul>
                </li>
            {/if}
        </ul>
    </div>

{else}
    <div class="container-fluid titre" id="navTitle">
        <h2 class="col-md-12">
            {$recordCompetition.Soustitre2}
        </h2>
    </div>
{/if}
{*<br>*}