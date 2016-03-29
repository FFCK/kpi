<div class="container titre">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">{#Details#} {#journee#}</h1>
    </div>
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
</div>

<div class="container">
    {if $type == 'CHPT'}
        <article class="col-md-6 padTopBottom">        
            <div class="form-horizontal">
                <h2 class="col-sm-12" id="competition">{$journee[0].Libelle_compet}</h2>
                <h3 class="col-sm-12 text-info" id="journee">{$journee[0].Nom}</h3>
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
                    <label class="col-sm-4">Responsable Compétition</label>
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
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="form-horizontal">
                <h4>Accès direct</h4>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpmatchs.php?J={$journee[0].Id_journee}" role="button">Matchs de la journée</a>
                </div>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpmatchs.php?Compet={$journee[0].Code_competition}&J=*" role="button">Tous les matchs</a>
                </div>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpclassement.php?Compet={$journee[0].Code_competition}" role="button">Classement</a>
                </div>
                <br>
                <br>
                <hr>
                <h4>Autres journées de la compétition</h4>
                {section name=i loop=$arrayListJournees}
                    <p>
                        <a class="btn {if $arrayListJournees[i].Id_journee == $journee[0].Id_journee}bg-blue{else}btn-info{/if}" href="kpdetails.php?Compet={$journee[0].Code_competition}&Group={$codeCompetGroup}&J={$arrayListJournees[i].Id_journee}" role="button">
                            {$arrayListJournees[i].Date_debut|date_format:'%d/%m/%Y'} - {$arrayListJournees[i].Date_fin|date_format:'%d/%m/%Y'} à {$arrayListJournees[i].Lieu} ({$arrayListJournees[i].Departement})
                        </a>
                    </p>
                {sectionelse}
                    <p class="text-info">Aucune autre journée</p>
                {/section}
            </div>
    </article>
    {else}
        <article class="col-md-6 padTopBottom">
            <div class="form-horizontal">
                <h2 class="col-sm-12" id="competition">{$journee[0].Libelle_compet}</h2>
                <h3 class="col-sm-12 text-info" id="journee">{$journee[0].Nom}</h3>
                <div class="form-group">
                    <label class="col-sm-4">Date</label>
                    <div class="col-sm-8" id="date_debut">{$journee[0].Date_debut|date_format:'%d/%m/%Y'} - {$journee[0].Date_fin|date_format:'%d/%m/%Y'}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">Lieu</label>
                    <div class="col-sm-8" id="lieu">{$journee[0].Lieu} ({$journee[0].Departement})</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">Club organisateur</label>
                    <div class="col-sm-8" id="organisateur">{$journee[0].Organisateur}</div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-4">Responsable Compétition</label>
                    <div class="col-sm-8" id="rc">{$journee[0].Responsable_insc}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">Responsable R1</label>
                    <div class="col-sm-8" id="r1">{$journee[0].Responsable_R1}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">Délégué fédéral</label>
                    <div class="col-sm-8" id="delegue">{$journee[0].Delegue}</div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4">Chef arbitres</label>
                    <div class="col-sm-8" id="chefarbitre">{$journee[0].ChefArbitre}</div>
                </div>
            </div>
        </article>
        <article class="col-md-6 padTopBottom">        
            <div class="form-horizontal">
                <h4>Accès direct</h4>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpmatchs.php?Compet={$journee[0].Code_competition}&J=*" role="button">Tous les matchs</a>
                </div>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpclassement.php?Compet={$journee[0].Code_competition}" role="button">Classement</a>
                </div>
                <div class="col-sm-4 text-center">
                    <a class="btn bg-blue" href="kpclassements.php?Group={$journee[0].Code_ref}" role="button">Tous les classements</a>
                </div>
                <br>
                <br>
                <hr>
                <h4>Autres phases ou catégories de la compétition</h4>
                {section name=i loop=$arrayListJournees}
                    <p>
                        <a class="btn {if $arrayListJournees[i].Code_competition == $journee[0].Code_competition}bg-blues{else}btn-info{/if}" href="kpdetails.php?Compet={$arrayListJournees[i].Code_competition}" role="button">
                            {$arrayListJournees[i].Nom}
                        </a>
                    </p>
                {sectionelse}
                    <p class="text-info">Aucune autre compétition</p>
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
