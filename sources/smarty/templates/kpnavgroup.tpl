{if $visuels.bandeau or $visuels.logo or $recordCompetition.Web}
    <div class="container logo_lien">
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

<div class="container-fluid" id="navGroup">
    <article class="padTopBottom{if $recordCompetition.Code_typeclt != 'CHPT'} table-responsive col-md-12{else} col-md-12{/if} tableClassement">
        <div class='pull-left'>
            <a class="btn {if $page == 'matchs'}btn-primary{else}btn-default{/if} btn-navigation" id="btnkpmatch" href='kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}'>{#Matchs#}</a>
            {if $recordCompetition.Code_typeclt == 'CHPT'}
                <a class="btn {if $page == 'details'}btn-primary{else}btn-default{/if}" href='kpdetails.php?lang={$lang}&event={$event}&Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&J={$idSelJournee}&typ=CHPT'>{#Infos#}</a>
            {else}
                <a class="btn {if $page == 'details'}btn-primary{else}btn-default{/if}" href='kpdetails.php?lang={$lang}&event={$event}&Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&J={$idSelJournee}&typ=CP'>{#Infos#}</a>
            {/if}
            <a class="btn {if $page == 'chart'}btn-primary{else}btn-default{/if} btn-navigation" href='kpchart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}'>{#Deroulement#}</a>
            <a class="btn {if $page == 'phases'}btn-primary{else}btn-default{/if} btn-navigation" href='kpphases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}'>{#Phases#}</a>
            <a class="btn {if $page == 'classement'}btn-primary{else}btn-default{/if} btn-navigation" href='kpclassement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}'>{#Classement#}</a>
            <a class="btn {if $page == 'stats'}btn-primary{else}btn-default{/if} btn-navigation" href='kpstats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&J={$idSelJournee}'>{#Stats#}</a>
        </div>
        
        <div class="pull-right">
            {if $page == 'matchs'}
                {if $next}
                    <a class="btn btn-primary actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=0">
                        {#Prochains_matchs#}
                    </a>
                {else}
                    <a class="btn btn-default actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet={$codeCompet2}&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1&next=next">
                        {#Prochains_matchs#}
                    </a>
                {/if}
                {if $arrayNavGroup}
                    <a class="btn {if '*' == $codeCompet}btn-primary{else}btn-default actif{/if}" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$codeCompetGroup}&Compet=*&J={$idSelJournee}&Round={$Round}&Css={$Css}&navGroup=1">
                        {#Tous#}
                    </a>
                {/if}
            {/if}
            
            {section name=i loop=$arrayNavGroup}
                {if $arrayNavGroup[i].Code == $codeCompet}
                    <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                {else}
                    <a class="btn btn-default actif" 
                       href="?lang={$lang}&event={$event}&Saison={$Saison}&Group={$arrayNavGroup[i].Code_ref}&Compet={$arrayNavGroup[i].Code}&J={$idSelJournee}">
                        {$arrayNavGroup[i].Soustitre2}
                    </a>
                {/if}
            {sectionelse}
                <a class="btn btn-primary">
                    {$recordCompetition.Soustitre2}
                </a>
            {/section}
        </div>
    </article>
</div>