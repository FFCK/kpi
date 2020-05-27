<div class="container">

    <div class="col-md-6 col-md-offset-3">
        <h2 class="form-signin-heading text-center"><img src="../img/CNAKPI_small.png" alt="KPI" height="60"> {#Feuille_marque#}</h2>
    </div>
    <div class="col-md-4 col-md-offset-4">
        <form class="form-signin2" method="POST" action="Login.php" name="formLogin" id="formLogin" enctype="multipart/form-data">

            <label for="User">{#Identifiant_match#}</label>
            <input type="tel" name="idFeuille" id="idFeuille" class="form-control" placeholder="{#Identifiant_match#}" required autofocus>

            <br>
            <input class="btn btn-lg btn-primary btn-block" type="button" name="chargeFeuille" id="chargeFeuille" value="{#Charger#}">
            <input type="hidden" id="target" value="{$target}">
            {if $profile < 9}
                <br>
                <input class="btn btn-lg btn-default btn-block" type="button" name="retour" id="retour" value="{#Retour#}">
            {/if}
        </form>
        <br>
        <br>
        <br>
        <br>
        <div class="text-center"><a href="GestionJournee.php">{#Liste#}</a></div>
    </div>
</div> <!-- /container -->
