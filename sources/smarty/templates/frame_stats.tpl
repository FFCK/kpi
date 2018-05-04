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
