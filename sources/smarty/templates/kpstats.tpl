<div class="container titre">
    <div class="col-md-12">
        <h2 class="col-md-12">
            <span class="label label-primary pull-right">{$Saison}</span>
            {if $recordCompetition.Titre_actif != 'O' && $recordCompetition.Soustitre2 != ''}
                <span>{$recordCompetition.Soustitre}
                    <br />
                    {$recordCompetition.Soustitre2}
                </span>
            {else}
                <span>{$recordCompetition.Libelle}
                    <br />
                    {$recordCompetition.Soustitre2}
                </span>
            {/if}
        </h2>
    </div>
</div>

{if $bandeau or $logo or $recordCompetition.Web}
    <div class="container logo_lien">
        <article class="padTopBottom table-responsive col-md-6 col-md-offset-3">
            <div class="text-center">
                {if $bandeau}
                    <img class="img2" id='logo' src='{$bandeau}' alt="logo">
                {else if $logo}
                    <img class="img2" id='logo' src='{$logo}' alt="logo">
                {/if}
                {if $recordCompetition.Web}
                    <p><a class="text-primary" href='{$recordCompetition.Web}' target='_blank'><i>{$recordCompetition.Web}</i></a></p>
                {/if}
            </div>
        </article>
    </div>
{/if}
<div class="container" id="selector">
    <article class="padTopBottom table-responsive col-md-8 col-md-offset-2 tableClassement">
        <div class='pull-right'>
            {if $arrayCompetition[0].Code_typeclt == 'CHPT'}
                <a class="btn btn-default" href='kpdetails.php?Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&Journee={$idSelJournee}&typ=CHPT'>{#Infos#}</a>
            {else}
                <a class="btn btn-default" href='kpdetails.php?Compet={$codeCompet}&Group={$Code_ref}&Saison={$Saison}&typ=CP'>{#Infos#}</a>
            {/if}
            <a class="btn btn-default" title="{#Partager#}" data-link="https://www.kayak-polo.info/kpstats.php?Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}&lang={$lang}" id="share_btn"><img src="img/share.png" width="16"></a>
            <a class="btn btn-default" href='kpclassement.php?Saison={$Saison}&Group={$Code_ref}&Compet={$codeCompet}'>{#Deroulement#}...</a>
        </div>
        <h4>{#Meilleurs_buteurs#}</h4>
        <table class='tableau' id='tableStats'>
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
                        <td>{$smarty.section.i.iteration}</td>
                        <td>{$arrayButeurs[i].Nom}</td>
                        <td>{$arrayButeurs[i].Prenom}</td>
                        <td>{$arrayButeurs[i].Sexe}</td>
                        <td>{$arrayButeurs[i].Numero}</td>
                        <td>{$arrayButeurs[i].Equipe}</td>
                        <td>{$arrayButeurs[i].Buts}</td>
                    </tr>
                {/section}
{*                    <tr class='{cycle values="impair,pair"}'>
                        <td>1</td>
                        <td>VIGNET</td>
                        <td>ERIC</td>
                        <td>M</td>
                        <td>Women</td>
                        <td>PC Course</td>
                        <td>5</td>
                    </tr>
                    <tr class='{cycle values="impair,pair"}'>
                        <td>2</td>
                        <td>SAINTE-MARTINE</td>
                        <td>DENIS</td>
                        <td>M</td>
                        <td>U21 Men</td>
                        <td>PC Course</td>
                        <td>2</td>
                    </tr>
                    <tr class='{cycle values="impair,pair"}'>
                        <td>3</td>
                        <td>GARRIGUE</td>
                        <td>NATHAN</td>
                        <td>M</td>
                        <td>U21 Women</td>
                        <td>PC Course</td>
                        <td>2</td>
                    </tr>
                    <tr class='{cycle values="impair,pair"}'>
                        <td>4</td>
                        <td>GARRIGUE</td>
                        <td>LAURENT</td>
                        <td>M</td>
                        <td>DEV</td>
                        <td>PC Course</td>
                        <td>1</td>
                    </tr>
*}                    
            </tbody>
        </table>
    </article>
</div>
