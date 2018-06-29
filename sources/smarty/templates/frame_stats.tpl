{if $navGroup}
    <div class="container-fluid categorie">
        <div class="col-md-12">
            <a class="btn btn-default actif"
                href="kpmatchs.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round=*&Css={$Css}&navGroup=1">
                {#Matchs#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_phases.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Round=*&Css={$Css}&navGroup=1">
                        {#Classement_par_phase#}
            </a>
            <a class="btn btn-default actif" 
                href="frame_classement.php?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$codeCompet}&Css={$Css}&navGroup=1">
                        {#Classement#}
            </a>
            <a class="btn btn-primary">{#Stats#}</a>
            <div class="pull-right">
                {section name=i loop=$arrayNavGroup}
                    {if $arrayNavGroup[i].Code == $codeCompet}
                        <a class="btn btn-primary">{$arrayNavGroup[i].Soustitre2}</a>
                    {else}
                        <a class="btn btn-default actif" 
                           href="?lang={$lang}&Saison={$Saison}&Group={$group}&Compet={$arrayNavGroup[i].Code}&Round={$Round}&Css={$Css}&navGroup=1">
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
<div class="container">
    <article class="padTopBottom table-responsive col-md-8 col-md-offset-2 tableClassement">
        <h4>{#Meilleurs_buteurs#}</h4>
        <table class='table' id='tableStats'>
            <thead>
                <tr class='header'>
                        <th></th>
                        <th>{#Nom#}</th>
                        <th>{#Prenom#}</th>
                        <th>{#Sexe#}</th>
                        <th>{#Num#}</th>
{*                        <th>Cat</th>*}
                        <th>{#Equipe#}</th>
                        <th>{#Buts#}</th>
                </tr>
            </thead>
            <tbody>
                {section name=i loop=$arrayButeurs}
                    <tr class='{cycle values="impair,pair"}'>
                        <td class="centre">{$smarty.section.i.iteration}</td>
                        <td>{$arrayButeurs[i].Nom}</td>
                        <td>{$arrayButeurs[i].Prenom}</td>
                        <td class="centre">{$arrayButeurs[i].Sexe}</td>
                        <td class="centre">{$arrayButeurs[i].Numero}</td>
                        <td class="centre">{$arrayButeurs[i].Equipe}</td>
                        <td class="centre">{$arrayButeurs[i].Buts}</td>
                    </tr>
                {/section}
            </tbody>
        </table>
    </article>
</div>
{if $voie}
    <script type="text/javascript" src="js/voie.js?v={$NUM_VERSION}" ></script>
    <script type="text/javascript">SetVoie({$voie});</script>
{/if}