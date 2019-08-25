{if $navGroup}
    <div class="container-fluid visible-lg categorie mb5">
        <div class="col-md-12">
            <a class="btn {if $page == 'Matchs'}btn-primary{else}btn-default actif{/if}"
                href="frame_matchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Matchs#}
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
                    {if $arrayNavGroup}
                        <a class="btn {if '*' == $codeCompet}btn-primary{else}btn-default actif{/if}" 
                           href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                            {#Tous#}
                        </a>
                    {/if}
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

    <div class="container-fluid article hidden-lg">
        <ul class="nav nav-pills">
            <li role="presentation" class="dropdown">
                <a id="drop5" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <b>{$smarty.config.$page}</b> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="drop5">
                    <li class="{if $page == 'Matchs'}active{/if}"><a id="btnkpmatch" href="frame_matchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">{#Matchs#}</a></li>
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
                <a id="drop5" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
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
                <ul class="dropdown-menu" aria-labelledby="drop5">
                    {if $arrayNavGroup && $page == 'Matchs'}
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