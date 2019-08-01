{if $navGroup}
    <div class="container-fluid categorie mb5">
        <div class="col-md-12">
            <a class="btn {if $page == 'matchs'}btn-primary{else}btn-default actif{/if}"
                href="kpmatchs.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                {#Matchs#}
            </a>
            <a class="btn {if $page == 'chart'}btn-primary{else}btn-default actif{/if}"
                href="frame_chart.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                {#Deroulement#}
            </a>
            <a class="btn {if $page == 'phases'}btn-primary{else}btn-default actif{/if}"
                href="frame_phases.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Phases#}
            </a>
            <a class="btn {if $page == 'classement'}btn-primary{else}btn-default actif{/if}"
                href="frame_classement.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Classement#}
            </a>
            <a class="btn {if $page == 'stats'}btn-primary{else}btn-default actif{/if}"
                href="frame_stats.php?lang={$lang}&event={$event}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round={$Round}&Css={$Css}&navGroup=1">
                    {#Stats#}
            </a>
            <div class="pull-right">
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
{else}
    <div class="container categorie">
        <h2 class="col-md-12">
            {$recordCompetition.Soustitre2}
        </h2>
    </div>
{/if}
{*<br>*}