{if $navGroup}
    <div class="container-fluid visible-lg categorie mb5">
        <div class="col-md-12">
            <a class="btn btn-primary" onclick="history.back()">
                    < {#Retour#}
            </a>
        </div>
    </div>
{/if}
{if $Equipe > 0}
    <div class="container-fluid flex">
        <article class="col-md-6 padTopBottom">
            <div class="form-horizontal">
                <h2 class="col-sm-12 text-center" id="nomEquipe">{$nomEquipe}</h2>
                <div class="form-group">
                    <div class="col-sm-12 text-center" id="nomClub">
                        <a class="btn btn-xs btn-default" {if !$arrayCompo}href='kpclubs.php?clubId={$Code_club}' title='{#Club#}'{/if}>
                            {$Club}
                        </a>
                    </div>
                </div>
            </div>
            {if $eColors}
                <div class="col-xs-10 col-xs-offset-1" id="equipeColors">
                    <a href="{$eColors}?v={$NUM_VERSION}" target="_blank">
                        <img class="img-responsive img-thumbnail" src="{$eColors}?v={$NUM_VERSION}" alt="{$nomEquipe}">
                    </a>
                    <span class="pull-right badge">{$eSeason}</span>
                </div>
            {elseif $eLogo}
                <div class="col-sm-6 col-sm-offset-3 col-xs-8 col-xs-offset-2" id="equipeColors">
                    <a {if !$arrayCompo}href="kpclubs.php?clubId={$Code_club}" title='{#Club#}'{/if}>
                        <img class="img-responsive img-thumbnail" src="{$eLogo}?v={$NUM_VERSION}" alt="{$nomEquipe}">
                    </a>
                </div>
            {/if}
        </article>

        <article class="col-md-6 padTopBottom" id="equipePalmares"> 
            {if $codeCompet != ''}
                <div class="page-header">
                    <h4 class="text-info">{$recordCompetition.Soustitre}<br>{$recordCompetition.Soustitre2}</h4>
                </div>
                <table class='table table-condensed' id='tableStats'>
                    <thead>
                        <tr class='header'>
                                <th class="text-center">#</th>
                                <th>{#Nom#}</th>
                                <th class="text-center">{#Buts#}</th>
                                <th class="bg-success text-center">{#C_V#}</th>
                                <th class="bg-warning text-center">{#C_J#}</th>
                                <th class="bg-danger text-center">{#C_R#}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {section name=i loop=$arrayCompo}
                            <tr class='{cycle values="impair,pair"}'>
                                <td class="text-center">
                                    {if $arrayCompo[i].Capitaine == 'E'}
                                        <span class="label label-default" title="Staff">S</span>
                                    {else}
                                        {$arrayCompo[i].Numero}
                                    {/if}
                                </td>
                                <td>
                                    {$arrayCompo[i].Nom|upper} {$arrayCompo[i].Prenom|upper}
                                    {if $arrayCompo[i].Capitaine == 'C'}
                                        <span class="label label-default" title="{#Capitaine#}">C</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if $arrayCompo[i].buts > 0}
                                        <span class="badgeg">{$arrayCompo[i].buts}</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if $arrayCompo[i].verts > 0}
                                        <span class="label label-success">{$arrayCompo[i].verts}</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if $arrayCompo[i].jaunes > 0}
                                        <span class="label label-warning">{$arrayCompo[i].jaunes}</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if $arrayCompo[i].rouges > 0}
                                        <span class="label label-danger">{$arrayCompo[i].rouges}</span>
                                    {/if}
                                </td>
                            </tr>
                        {sectionelse}
                            <tr>
                                <td colspan="6" class="text-center">{#Information_non_disponible#}</td>
                            </tr>
                        {/section}
                    </tbody>
                </table>
            {/if}

            {if $eTeam}
                <div class="col-sm-12" id="equipeTeam">
                    <a href="{$eTeam}?v={$NUM_VERSION}" target="_blank">
                        <img class="img-responsive img-thumbnail" src="{$eTeam}?v={$NUM_VERSION}" alt="{$nomEquipe}" title="{$nomEquipe}">
                    </a>
                    <span class="pull-right badge">{$eSeason2}</span>
                </div>
            {/if}

        </article>
    </div>
{/if}