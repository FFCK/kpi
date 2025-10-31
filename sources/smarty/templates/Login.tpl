<div class="container">
    <div class="row">
        <div class="col-11 col-sm-10 col-md-6 mx-auto">
            <h2 class="form-signin-heading text-center"><img src="../img/CNAKPI_small.png" alt="KPI" height="60"></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-10 col-lg-4 mx-auto">
            <form class="form-signin2" method="POST" action="Login.php" name="formLogin" id="formLogin" enctype="multipart/form-data">
            <label for="User">{#Identifiant#}</label>
            <input type="tel" name="User" id="idUser" class="form-control" placeholder="{#Identifiant#}" required autofocus>
            <div id="connect">
                <label for="password">{#Mot_de_passe#}</label>
                <input type="password" name="Pwd" id="idPwd" class="form-control" placeholder="{#Mot_de_passe#}" required>
                <input type="hidden" name="tzOffset" id="tzOffset" value="">
            </div>
            <div id="renv">
                <label for="Mel">Email</label>
                <input type="email" name="Mel" id="Mel" class="form-control" placeholder="E-mail" required>
            </div>
            <br>
            <div class="d-grid gap-2">
                <input class="btn btn-lg btn-primary" type="button" name="login" id="login" value="{#Connexion#}">
                <input class="btn btn-lg btn-primary" type="button" name="Renvoyer" id="Renvoyer" value="{#Renvoyer#}">
                <input class="btn btn-lg btn-primary" type="button" name="Annuler" id="Annuler" value="{#Annuler#}" onClick="return false">
            </div>

            <input type="hidden" name="Mode" id="Mode" value="Connexion">        
            <br>
            <br>
            <div class="text-center">
                <a class="ui-button ui-widget ui-corner-all" href="{$target}&lang=fr"><img src="../img/Pays/FRA.png" height="25" align="bottom"></a>
                <a class="ui-button ui-widget ui-corner-all" href="{$target}&lang=en"><img src="../img/Pays/GBR.png" height="25" align="bottom"></a>
            </div>
            <br>
            <br>
            <p class="text-center"><a href="mailto:contact@kayak-polo.info?subject=Demande d'identifiant administrateur kayak-polo.info&body=Nom:%0D%0APrénom:%0D%0AN°Licence:%0D%0AFonctions fédérales:%0D%0AUn petit mot ?">{#Demander_identifiant#}</a></p>
            <br>
            <p class="text-center" id="perdu"><a href="" onClick="return false">{#j_ai_perdu_mon_mdp#}</a></p>
            <br>
            <br>
        </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-10 col-lg-6 mx-auto text-center">
            <p>{#Vous_devez_vous_identifier#} (<a href="../">{#Retour#}</a>)</p>
        </div>
    </div>
</div> <!-- /container -->
