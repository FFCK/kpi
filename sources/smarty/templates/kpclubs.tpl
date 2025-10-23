<div class="container">
    <article class="col-md-6 padTopBottom">        
        <div class="form-horizontal">
            <label class="col-sm-2">{#Chercher#}:</label>
            <input class="col-sm-6" type="text" id="rechercheClub" placeholder="{#Nom_ou_numero_de_club#}">
            <input type="hidden" id="clubId" value="{$clubId}">
            <a class="btn btn-primary pull-right" href="kplogos.php">{#Tous_les_clubs#}...</a>
            <div class="row">
                <h3 class="col-xs-12" id="clubLibelle">Club:</h3>
            </div>
            <div class="row">
                <span class="col-xs-1"><img id="localise" class="img-responsive" style="cursor: pointer;" src="img/mapmarker.png" title="{#Localiser#}"></span>
                <h3 class="col-xs-4 col-xs-offset-3" id="clubLogo"></h3>
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

            //création des marqueurs
            {/literal}{$mapParam|default:''}{literal}

    </script>
{/literal}
