<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">
            {#Informations_journee#}
            <a class="btn btn-default" title="{#Partager#}" id="share_btn">
                <img src="img/share.png" width="16">
            </a>
        </h1>
    </div>
    <div class="col-md-3">
        <div class="row">
            <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
        </div>
        <div class="row">
            
        </div>
    </div>
</div>

<div class="container" id="selector">
    {if $type == 'CHPT'}
        <article class="col-md-6 padTopBottom">        
            <div class="page-header">
                {if $journee[0].Titre_actif != 'O' && $journee[0].Soustitre2 != ''}
                    <h2 id="competition">{$journee[0].Soustitre}</h2>
                    <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
                {else}
                    <h2 id="competition">{$journee[0].Libelle}</h2>
                    <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
                {/if}
            </div>
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-4">Date</label>
                    <div class="col-sm-8" id="date_debut">{$journee[0].Date_debut|date_format:'%d/%m/%Y'} - {$journee[0].Date_fin|date_format:'%d/%m/%Y'}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Lieu#}</label>
                    <div class="col-sm-8" id="lieu">{$journee[0].Lieu} ({$journee[0].Departement})</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Organisateur#}</label>
                    <div class="col-sm-8" id="organisateur">{$journee[0].Organisateur}</div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-4">{#RC#}</label>
                    <div class="col-sm-8" id="rc">{$journee[0].Responsable_insc}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#R1#}</label>
                    <div class="col-sm-8" id="r1">{$journee[0].Responsable_R1}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Delegue#}</label>
                    <div class="col-sm-8" id="delegue">{$journee[0].Delegue}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Chef_arbitres#}</label>
                    <div class="col-sm-8" id="chefarbitre">{$journee[0].ChefArbitre}</div>
                </div>
                <hr>
                <h4>{#Acces_direct2#}</h4>
                <div class="col-sm-3 text-center">
                    <a class="btn bg-blue" href="kpmatchs.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}&J={$journee[0].Id_journee}" 
                       role="button">{#Matchs_de_la_journee#}</a>
                </div>
                <div class="col-sm-3 text-center">
                    <a class="btn bg-blue" href="kpmatchs.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}&J=*" 
                       role="button">{#Tous_les_matchs#}</a>
                </div>
                <div class="col-sm-3 text-center">
                    <a class="btn bg-blue" href="kpclassement.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}" 
                       role="button">{#Classement#}</a>
                </div>
                <div class="col-sm-3 text-center">
                    <a class="btn bg-blue" href="kpstats.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}" 
                       role="button">{#Stats#}</a>
                </div>
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="row">
                <h2 class="col-sm-12">{#Equipes_engagees#}</h2>
                <div class="row">
                    <div class="col-md-6">
                        <table class='table table-striped table-hover'>
                            {assign var='col' value=1}
                            {section name=i loop=$arrayEquipe}
                                {if $col == 1 && $smarty.section.i.iteration > $smarty.section.i.loop / 2}
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class='table table-striped table-hover'>
                                    {assign var='col' value=2}
                                {/if}
                                <tr>
                                    <td class="cliquableNomEquipe">
                                        {if $arrayEquipe[i].logo != ''}
                                            <img class="img2 pull-left" width="28" src="{$arrayEquipe[i].logo}" alt="{$arrayEquipe[i].club}" />
                                        {/if}
                                        <a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe[i].Numero}' title='{#Palmares#}'>{$arrayEquipe[i].Libelle}</a>
                                    </td>
                                </tr>
                            {sectionelse}
                                <div class="col-md-12 text-center">Information non disponible</div>
                            {/section}
                        </table>
                    </div>
                </div>
            </div>
            <div class="row form-horizontal">
                <hr>
                <h4 class="col-sm-12">{#Autres_journees#}</h4>
                {section name=i loop=$arrayListJournees}
                    <p class="col-sm-12">
                        <a class="btn {if $arrayListJournees[i].Id_journee == $journee[0].Id_journee}bg-blue{else}btn-info{/if}" 
                           href="kpdetails.php?Saison={$arrayListJournees[i].Code_saison}&Group={$arrayListJournees[i].Code_ref}&Compet={$arrayListJournees[i].Code_competition}&typ={$arrayListJournees[i].Code_typeclt}&J={$arrayListJournees[i].Id_journee}" role="button">
                            {$arrayListJournees[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayListJournees[i].Date_fin|date_format:'%d/%m/%Y'} à {$arrayListJournees[i].Lieu} ({$arrayListJournees[i].Departement})
                        </a>
                    </p>
                {sectionelse}
                    <p class="col-sm-12 text-info">Aucune autre journée</p>
                {/section}
            </div>
        </article>
    {else}
        <article class="col-md-6 padTopBottom">
            <div class="page-header">
                {if $journee[0].Titre_actif != 'O' && $journee[0].Soustitre2 != ''}
                    <h2 id="competition">{$journee[0].Soustitre}</h2>
                    <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
                {else}
                    <h2 id="competition">{$journee[0].Libelle}</h2>
                    <h3 class="text-info" id="journee">{$journee[0].Soustitre2}</h3>
                {/if}
            </div>
            <div class="form-horizontal">
                {if $logo}
                    <a href="{$logo}" target="_blank"><img class="img-responsive img-thumbnail img-rounded" src="{$logo}"></a>
                    <hr>
                {/if}
                <div class="form-group">
                    <label class="col-sm-4">Date</label>
                    <div class="col-sm-8" id="date_debut">{$journee[0].Date_debut|date_format:'%d/%m/%Y'} - {$journee[0].Date_fin|date_format:'%d/%m/%Y'}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Lieu#}</label>
                    <div class="col-sm-8" id="lieu">{$journee[0].Lieu} ({$journee[0].Departement})</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Club_organisateur#}</label>
                    <div class="col-sm-8" id="organisateur">{$journee[0].Organisateur}</div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-4">{#RC#}</label>
                    <div class="col-sm-8" id="rc">{$journee[0].Responsable_insc}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#R1#}</label>
                    <div class="col-sm-8" id="r1">{$journee[0].Responsable_R1}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Delegue#}</label>
                    <div class="col-sm-8" id="delegue">{$journee[0].Delegue}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">{#Chef_arbitres#}</label>
                    <div class="col-sm-8" id="chefarbitre">{$journee[0].ChefArbitre}</div>
                </div>
                <hr>
                <div class="form-group">
                    <h4>{#Acces_direct2#}</h4>
                    <div class="col-sm-3 text-center">
                        <a class="btn bg-blue" href="kpmatchs.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}&J=*" 
                           role="button">{#Tous_les_matchs#}</a>
                    </div>
                    <div class="col-sm-3 text-center">
                        <a class="btn bg-blue" href="kpclassement.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}" 
                           role="button">{#Classement#}</a>
                    </div>
                    <div class="col-sm-3 text-center">
                        <a class="btn bg-blue" href="kpclassements.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}" 
                           role="button">{#Tous_les_classements#}</a>
                    </div>
                    <div class="col-sm-3 text-center">
                        <a class="btn bg-blue" href="kpstats.php?Saison={$journee[0].Code_saison}&Group={$journee[0].Code_ref}&Compet={$journee[0].Code_competition}" 
                           role="button">{#Stats#}</a>
                    </div>
                </div>
                {if $schema}
                    <hr>
                    <h4>{#Schema#}</h4>
                    <a href="{$schema}" target="_blank"><img class="img-responsive img-thumbnail" src="{$schema}"></a>
                {/if}
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="row">
                <h2 class="col-sm-12">{#Equipes_engagees#}</h2>
                <div class="row">
                    {section name=i loop=$arrayPoule}
                        <div class="col-md-6 text-center text-info">
                            {if $arrayPoule[i] != '-'}
                                <h3>{#Poule#} {$arrayPoule[i]}</h3>
                            {/if}
                            {assign var='poule' value=$arrayPoule[i]}
                            <table class='table table-striped table-hover'>
                                {section  name=j loop=$arrayEquipe[$poule]}
                                    <tr>
                                        <td class="cliquableNomEquipe">
                                            {if $arrayEquipe[$poule][j].logo != ''}
                                                <img class="img2 pull-left" width="28" src="{$arrayEquipe[$poule][j].logo}" alt="{$arrayEquipe[$poule][j].club}" />
                                            {/if}
                                            <a class="btn btn-xs btn-default" href='kpequipes.php?Equipe={$arrayEquipe[$poule][j].Numero}' title='{#Palmares#}'>{$arrayEquipe[$poule][j].Libelle}</a>
                                        </td>
                                    </tr>
                                {/section}
                            </table>
                        </div>
                        {if $smarty.section.i.iteration % 2 == 0}
                            </div>
                            <div class="row">
                        {/if}
                    {sectionelse}
                        <div class="col-md-12 text-center">Information non disponible</div>
                    {/section}
                </div>
            </div>
            <div class="row form-horizontal">
                <hr>
                <h4 class="col-sm-12">{#Autres_categories#}</h4>
                {section name=i loop=$arrayListJournees}
                    <p class="col-sm-12">
                        <a class="btn {if $arrayListJournees[i].Code_competition == $journee[0].Code_competition}bg-blue{else}btn-info{/if}" 
                            href="kpdetails.php?Saison={$arrayListJournees[i].Code_saison}&Group={$arrayListJournees[i].Code_ref}&Compet={$arrayListJournees[i].Code_competition}&typ={$arrayListJournees[i].Code_typeclt}&J=*" role="button">
                            {if $arrayListJournees[i].Titre_actif != 'O' && $arrayListJournees[i].Soustitre2 != ''}
                                {$arrayListJournees[i].Soustitre} - {$arrayListJournees[i].Soustitre2}
                            {else}
                                {$arrayListJournees[i].Libelle} - {$arrayListJournees[i].Soustitre2}
                            {/if}
                        </a>
                    </p>
                {sectionelse}
                    <p class="col-sm-12 text-info">Aucune autre compétition</p>
                {/section}
            </div>
    </article>
    {/if}
</div>
<!--
<div class="container">
    <article class="col-md-6 padTopBottom">        
        <div id="carte" class="col-md-12 col-sm-12 col-xs-12" style="height: 400px"></div>
        <form onsubmit="codeAddress(); event.preventDefault();">
            <input type="text" size="50" name="address" id="address" placeholder="Adresse, Ville, Pays" />
            <input type="button" value="Localiser" onclick="codeAddress();" />
        </form>
    </article>
</div>
-->

{literal}
    <script type="text/javascript">
        function initialiser() {
            geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(46.85, 1.73);
            var options = {
                center: latlng,
                zoom: 5,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                panControl: true,
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE
                },
                scrollwheel: true,
                draggable: true
            };
            carte = new google.maps.Map(document.getElementById("carte"), options);
            var infoWindow = new google.maps.InfoWindow;

            //création des marqueurs
            {/literal}{$mapParam}{literal}
        }
    </script>
{/literal}
