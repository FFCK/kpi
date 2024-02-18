{* Logo competition *}
{if $visuels.bandeau or $visuels.logo or $recordCompetition.Web}
    <div class="container logo_lien visible-lg visible-md">
        <div class="padTopBottom table-responsive col-md-12">
            <div class="text-center">
                {if $recordCompetition.Web}
                    <a class="text-primary titre" href='{$recordCompetition.Web}' target='_blank'>
                {/if}
                {if $visuels.bandeau}
                    <img class="img2" id='logo' src='{$visuels.bandeau}' alt="logo"><br>
                {else if $visuels.logo}
                    <img class="img2" id='logo' src='{$visuels.logo}' alt="logo"><br>
                {/if}
                {if $recordCompetition.Web}
                    <i>{$recordCompetition.Web}</i></a>
                {/if}
            </div>
        </div>
    </div>
{/if}

{* Competition/Evénement, saison, partage *}
<div class="container-fluid titre" id="navTitle">
    <div>
        <h2 class="my-2">
            <span class="btn btn-dark float-end">{$Saison}</span>&nbsp;
            <a class="btn btn-light float-start" title="{#Partager#}" id="share_btn">  
                <img src="img/share.png" width="16">
            </a>
            <span class="text-light">
                {if $event > 0}
                    {$eventTitle}
                {elseif '*' == $codeCompet}
                    {$arrayNavGroup[0].Soustitre|default:$arrayNavGroup[0].Libelle}
                {elseif $recordCompetition.Titre_actif != 'O' && $recordCompetition.Soustitre2 != ''}
                    {$recordCompetition.Soustitre}
                {else}
                    {$recordCompetition.Libelle}
                {/if}
            </span>
        </h2>
    </div>
</div>
{*<a class="btn" title="{#Partager#}" data-link="https://www.kayak-polo.info/kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}" id="share_btn"><img src="img/share.png" width="16"></a>*}

{* Sous-navigation *}
<div class="container-fluid">
    <nav class="navbar navbar-expand-xl bg-light" id="navGroup">
        <div class="container-fluid">
            {* <a class="navbar-brand" href="#"></a> *}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navGroupContent" aria-controls="navGroupContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navGroupContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Matchs'}active{/if}" id="btnkpmatch"
                            href='kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Matchs#}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Terrains'}active{/if}"
                            href='kpterrains.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Terrains#}</a>
                    </li>
                    {if $recordCompetition.Code_typeclt == 'CHPT'}
                        <li class="nav-item">
                            <a class="nav-link {if $page == 'Infos'}active{/if}"
                                href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CHPT&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a>
                        </li>
                    {else}
                        <li class="nav-item">
                            <a class="nav-link {if $page == 'Infos'}active{/if}"
                                href='kpdetails.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&typ=CP&Round={$Round}&Css={$Css}&navGroup=1'>{#Infos#}</a>
                        </li>
                    {/if}
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Deroulement'}active{/if}"
                            href='kpchart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Deroulement#}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Phases'}active{/if}"
                            href='kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Phases#}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Classement'}active{/if}"
                            href='kpclassement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Classement#}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $page == 'Stats'}active{/if}"
                            href='kpstats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1'>{#Stats#}</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    {if $page == 'Matchs'}
                        {if $next}
                            <li class="nav-item">
                                <a class="nav-link active" 
                                    href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                                    {#Prochains_matchs#}
                                </a>
                            </li>
                        {else}
                            <li class="nav-item">
                                <a class="nav-link" 
                                href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                                    {#Prochains_matchs#}
                                </a>
                            </li>
                        {/if}
                    {/if}

                    {if $page == 'Terrains'}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {#Date#}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                                        {#Tous#}
                                    </a>
                                </li>
                                {section name=i loop=$arrayJours}
                                    <li {if $arrayJours[i] == $filtreJour}class="active"{/if}>
                                        <a class="dropdown-item" href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour={$arrayJours[i]}&Css={$Css}&navGroup=1">
                                            {if $lang == 'fr'}{$arrayJours[i]|date_format:"%d/%m/%Y"}{else}{$arrayJours[i]}{/if}
                                        </a>
                                    </li>
                                {sectionelse}
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item">{#Aucune#}</a></li>
                                {/section}
                            </ul>
                        </li>
                    {/if}

                    {if ($page == 'Matchs' || $page == 'Terrains') && $arrayNavGroup}
                        <li class="nav-item">
                            <a class="nav-link {if '*' == $codeCompet}active{/if}" 
                                href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                                {#Tous#}
                            </a>
                        </li>
                    {/if}

                    {section name=i loop=$arrayNavGroup}
                        {if $arrayNavGroup[i].Code == $codeCompet}
                            <li class="nav-item">
                                <a class="nav-link active">{$arrayNavGroup[i].Soustitre2}</a>
                            </li>
                        {else}
                            <li class="nav-item">
                                <a class="nav-link" 
                                href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$arrayNavGroup[i].Code_ref}&Compet={$arrayNavGroup[i].Code}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                                    {$arrayNavGroup[i].Soustitre2}
                                </a>
                            </li>
                        {/if}
                    {sectionelse}
                        <li class="nav-item">
                            <a class="btn btn-primary">
                                {$recordCompetition.Soustitre2}
                            </a>
                        </li>
                    {/section}
                </ul>
            </div>
        </div>
    </nav>
</div>

<div class="container-fluid article hidden-lg">
    <ul class="nav nav-pills">
        <li role="presentation" class="dropdown">
            <a id="drop5" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="fa fa-bars"></span>
                <b>{$smarty.config.$page}</b>
                <span class="caret"></span>
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
                    <span class="fa fa-calendar"></span>
                    <b>{#Date#}</b>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="drop3">
                    <li {if '' == $filtreJour}class="active"{/if}>
                        <a href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet}&J={$idSelJournee}&Round={$Round}&filtreJour=&Css={$Css}&navGroup=1">
                        {#Tous#}</a>
                    </li>
                    <li role="separator" class="divider"></li>
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