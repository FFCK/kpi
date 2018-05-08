<div class="container stats">
    <div class="col-md-12">
        <h2 class="col-md-12">{$recordCompetition.Soustitre2}</h2>
    </div>
</div>

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