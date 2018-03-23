<div class="container">
    <article class="col-md-6 padTopBottom">        
        <div class="form-horizontal">
            <label class="col-sm-2">{#Chercher#}:</label>
            <input class="col-sm-6" type="text" id="rechercheEquipe" placeholder="{#Nom_de_l_equipe#}">
            <input class="col-sm-2" type="hidden" id="equipeId" value="{$Equipe}">
            <h2 class="col-sm-12 text-center" id="nomEquipe">{$nomEquipe}</h2>
            <div class="form-group">
                <div class="col-sm-12 text-center" id="nomClub">
                    <a class="btn btn-xs btn-default" href='kpclubs.php?clubId={$Code_club}' title='{#Club#}'>
                        {$Club}
                    </a>
                    <div id="fb-root"></div>
                    <div class="fb-like" data-href="https://www.kayak-polo.info/kpequipes.php?Equipe={$Equipe}" data-layout="button" data-action="recommend" data-show-faces="false" data-share="true"></div>
                </div>
            </div>
        </div>
        {if $eColors}
            <div class="col-xs-10 col-xs-offset-1" id="equipeColors">
                <a href="{$eColors}" target="_blank"><img class="img-responsive img-thumbnail" src="{$eColors}" alt="{$nomEquipe}"></a>
            </div>
        {elseif $eLogo}
            <div class="col-sm-6 col-sm-offset-3 col-xs-8 col-xs-offset-2" id="equipeColors">
                <a href="kpclubs.php?clubId={$Code_club}" title='{#Club#}'><img class="img-responsive img-thumbnail" src="{$eLogo}" alt="{$nomEquipe}"></a>
            </div>
        {/if}
    </article>
    
    <article class="col-md-6 padTopBottom" id="equipePalmares">        
        {if $eTeam}
            <div class="col-sm-12" id="equipeTeam">
                <a href="{$eTeam}" target="_blank"><img class="img-responsive img-thumbnail" src="{$eTeam}" alt="{$nomEquipe}" title="{$nomEquipe}"></a>
                <span class="pull-right badge">{$eSeason}</span>
            </div>
        {/if}
            <h3 class="col-sm-12">{#Palmares#}:</h3>
            {section  name=i loop=$arraySaisons}
                {assign var='Saison' value=$arraySaisons[i].Saison}
                <table class='table table-striped table-hover table-condensed' id='tableMatchs'>
                    <caption><h3>{$Saison}</h3></caption>
                    <tbody>        
                        {section  name=j loop=$arrayPalmares[$Saison]}
                            {if $arrayPalmares[$Saison][j].Code_tour == 10}
                                <tr>
                                    <td>
                                        <a class="btn btn-xs btn-default" href='kpclassements.php?Compet={$arrayPalmares[$Saison][j].Code}&Group={$arrayPalmares[$Saison][j].Code_ref}&Saison={$arrayPalmares[$Saison][j].Saison}' title='{#Classement#}'>
                                            {$arrayPalmares[$Saison][j].Competitions}
                                        </a>
                                    </td>
                                    <td>
                                        {$arrayPalmares[$Saison][j].Classt}
                                        {if $arrayPalmares[$Saison][j].Classt > 0 && $arrayPalmares[$Saison][j].Classt <= 3}
                                            <img class="pull-right" width="20" src="img/medal{$arrayPalmares[$Saison][j].Classt}.gif" alt="{$arrayPalmares[$Saison][j].Classt}" title="{$arrayPalmares[$Saison][j].Classt}" />
                                        {/if}

                                    </td>
                                </tr>
                            {else}
                                <tr>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-default" href='kpclassements.php?Compet={$arrayPalmares[$Saison][j].Code}&Group={$arrayPalmares[$Saison][j].Code_ref}&Saison={$arrayPalmares[$Saison][j].Saison}' title='{#Classement#}'>
                                            <i>{$arrayPalmares[$Saison][j].Competitions}</i>
                                        </a>
                                        <i>{$arrayPalmares[$Saison][j].Classt}</i>
                                    </td>
                                    <td>
                                        
                                    </td>
                                </tr>
                            {/if}
                        {/section}
                    </tbody>
                </table>
            {sectionelse}
                <em class="text-right">{#Pas_de_classement_equipe#}.</em>
            {/section}
    </article>
</div>
