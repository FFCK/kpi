{if $visuels.bandeau or $visuels.logo or $recordCompetition.Web}
    <div class="container logo_lien visible-lg visible-md">
        <div class="padTopBottom table-responsive col-md-12">
            <div class="text-center">
                {if $recordCompetition.Web}
                    <a class="text-primary titre" href='{$recordCompetition.Web}' target='_blank'>
                {/if}
                {if $visuels.bandeau}
                    <img id='logo' src='{$visuels.bandeau}' alt="logo"><br>
                {else if $visuels.logo}
                    <img id='logo' src='{$visuels.logo}' alt="logo"><br>
                {/if}
                {if $recordCompetition.Web}
                    <i>{$recordCompetition.Web}</i></a>
                {/if}
            </div>
        </div>
    </div>
{/if}

<div class="container-fluid titre" id="navTitle">
    <div class="col-md-12">
        <h2 class="col-md-12">
            <span class="label label-primary pull-right">{$Saison}</span>&nbsp;
            <a class="btn btn-default pull-left" title="{#Partager#}" id="share_btn">  
                <img src="img/share.png" width="16">
            </a>
            {if $event > 0}
                <span>{$eventTitle}</span>
            {elseif '*' == $codeCompet}
                {$arrayNavGroup[0].Soustitre|default:$arrayNavGroup[0].Libelle}
            {elseif $recordCompetition.Titre_actif != 'O' && $recordCompetition.Soustitre2 != ''}
                <span>{$recordCompetition.Soustitre}</span>
            {else}
                <span>{$recordCompetition.Libelle}</span>
            {/if}
        </h2>
    </div>
</div>
{*<a class="btn btn-default" title="{#Partager#}" data-link="https://www.kayak-polo.info/kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}" id="share_btn"><img src="img/share.png" width="16"></a>*}

<div class="container-fluid visible-lg" id="navGroup">
    <nav class="col-md-12 navbar navbar-custom">
        <div class='pull-left'>
            <a class="btn {if $page == 'Matchs'}btn-primary{else}btn-default{/if} btn-navigation" id="btnkpmatch" href='kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Matchs#}</a>
            <a class="btn {if $page == 'Terrains'}btn-primary{else}btn-default{/if} btn-navigation" href='kpterrains.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Terrains#}</a>
            {if $recordCompetition.Code_typeclt == 'CHPT'}
                <a class="btn {if $page == 'Infos'}btn-primary{else}btn-default{/if}" href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CHPT&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a>
            {else}
                <a class="btn {if $page == 'Infos'}btn-primary{else}btn-default{/if}" href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CP&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a>
            {/if}
            <a class="btn {if $page == 'Deroulement'}btn-primary{else}btn-default{/if} btn-navigation" href='kpchart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Deroulement#}</a>
            <a class="btn {if $page == 'Phases'}btn-primary{else}btn-default{/if} btn-navigation" href='kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Phases#}</a>
            <a class="btn {if $page == 'Classement'}btn-primary{else}btn-default{/if} btn-navigation" href='kpclassement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Classement#}</a>
            <a class="btn {if $page == 'Stats'}btn-primary{else}btn-default{/if} btn-navigation" href='kpstats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Stats#}</a>
        </div>

        <div class="pull-right">
            {if $page == 'Matchs'}
                {if $next}
                    <a class="btn btn-primary actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                        {#Prochains_matchs#}
                    </a>
                {else}
                    <a class="btn btn-default actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                        {#Prochains_matchs#}
                    </a>
                {/if}
            {/if}

            {if $page == 'Terrains'}
                <span class="dropdown">
                    <a id="drop4" class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <b>{#Date#}</b> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="drop4">
                        <li {if '' == $filtreJour}class="active"{/if}>
                            <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                            {#Tous#}</a>
                        </li>
                        {section name=i loop=$arrayJours}
                            <li {if $arrayJours[i] == $filtreJour}class="active"{/if}>
                                <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour={$arrayJours[i]}&Css={$Css}&navGroup=1">
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
                   href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Tous#}
                </a>
            {/if}
            
            {section name=i loop=$arrayNavGroup}
                {if $arrayNavGroup[i].Code == $codeCompet}
                    <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                {else}
                    <a class="btn btn-default actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$arrayNavGroup[i].Code_ref}&Compet={$arrayNavGroup[i].Code}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                        {$arrayNavGroup[i].Soustitre2}
                    </a>
                {/if}
            {sectionelse}
                <a class="btn btn-primary">
                    {$recordCompetition.Soustitre2}
                </a>
            {/section}
        </div>
    </nav>
</div>

<div class="container-fluid article hidden-lg">
    <ul class="nav nav-pills">
        <li role="presentation" class="dropdown">
            <a id="drop5" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <b>{$smarty.config.$page}</b> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="drop5">
                <li class="{if $page == 'Matchs'}active{/if}"><a id="btnkpmatch" href='kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Matchs#}</a></li>
                <li class="{if $page == 'Terrains'}active{/if}"><a href='kpterrains.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Terrains#}</a></li>
                {if $recordCompetition.Code_typeclt == 'CHPT'}
                    <li class="{if $page == 'Infos'}active{/if}"><a href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CHPT&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a></li>
                {else}
                    <li class="{if $page == 'Infos'}active{/if}"><a href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CP&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a></li>
                {/if}
                <li class="{if $page == 'Deroulement'}active{/if}"><a href='kpchart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Deroulement#}</a></li>
                <li class="{if $page == 'Phases'}active{/if}"><a href='kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Phases#}</a></li>
                <li class="{if $page == 'Classement'}active{/if}"><a href='kpclassement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Classement#}</a></li>
                <li class="{if $page == 'Stats'}active{/if}"><a href='kpstats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Stats#}</a></li>
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
                    <b>{#Toutes_divisions#}</b>
                {/if}
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="drop2">
                {if ($page == 'Matchs' || $page == 'Terrains') && $arrayNavGroup}
                    <li {if '*' == $codeCompet}class="active"{/if}>
                        <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
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
                    <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                        <b>{#Prochains_matchs#}</b>
                    </a>
                </li>
            {else}
                <li role="presentation" class="pull-right">
                    <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                        <b>{#Prochains_matchs#}</b>
                    </a>
                </li>
            {/if}
        {/if}
        
        {if $page == 'Terrains'}
            <li role="presentation" class="dropdown pull-right">
                <a id="drop3" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <b>{#Date#}</b> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="drop3">
                    <li {if '' == $filtreJour}class="active"{/if}>
                        <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                        {#Tous#}</a>
                    </li>
                    {section name=i loop=$arrayJours}
                        <li {if $arrayJours[i] == $filtreJour}class="active"{/if}>
                            <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour={$arrayJours[i]}&Css={$Css}&navGroup=1">
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