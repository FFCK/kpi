<!--<div class="container">
    <div class="col-md-9">
        <h1 class="col-md-11 col-xs-9">{#Clubs#}Clubs!</h1>
    </div>
    <div class="col-md-3">
        <span class="badge pull-right">{$smarty.config.Saison|default:'Saison'} {$Saison}</span>
    </div>
</div>
-->
<div class="container">
    <article class="col-md-6 padTopBottom">        
        <div class="form-horizontal">
            <label class="col-sm-2">{#Chercher#}:</label>
            <input class="col-sm-6" type="text" id="rechercheClub" placeholder="{#Nom_ou_numero_de_club#}">
            <input type="hidden" id="clubId" value="{$clubId}">
            <a class="btn btn-default pull-right" href="kplogos.php">Tous les clubs...</a>
            <div class="row">
                <h3 class="col-sm-12" id="clubLibelle">Club:</h3>
                <h3 class="col-sm-4 col-sm-offset-4" id="clubLogo"></h3>
            </div>
            <div class="form-group">
                <label class="col-sm-4">CD:</label>
                <div class="col-sm-8" id="comitedep"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">CR:</label>
                <div class="col-sm-8" id="comitereg"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">Web:</label>
                <div class="col-sm-8" id="www"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">email:</label>
                <div class="col-sm-8" id="email"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">{#Adresse#}:</label>
                <div class="col-sm-8" id="postal"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">{#Coordonnees#}:</label>
                <div class="col-sm-8" id="coord"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4">{#Equipes#}:</label>
                <div class="col-sm-8" id="listEquipes">
                </div>
            </div>
        </div>
    </article>
    
    <article class="col-md-6 padTopBottom">        
        <div id="carte" class="col-md-12 col-sm-12 col-xs-12" style="height: 400px"></div>
        <form onsubmit="codeAddress(); event.preventDefault();">
            <input type="text" size="50" name="address" id="address" placeholder="{#Adresse_Ville_Pays#}" />
            <input type="button" value="{#Localiser#}" onclick="codeAddress();" />
        </form>
    </article>
</div>


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
