<div class="container">
    <div class="col-md-6 col-md-offset-3">
        <h2 class="form-signin-heading text-center"><img src="../img/CNAKPI_small.png" alt="KPI" height="60"></h2>
    </div>
    <div class="col-md-4 col-md-offset-4">
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
            <input class="btn btn-lg btn-primary btn-block" type="button" name="login" id="login" value="{#Connexion#}">
            <input class="btn btn-lg btn-primary btn-block" type="button" name="Renvoyer" id="Renvoyer" value="{#Renvoyer#}">
            <input class="btn btn-lg btn-primary btn-block" type="button" name="Annuler" id="Annuler" value="{#Annuler#}" onClick="return false">

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
    <div class="col-md-6 col-md-offset-3 text-center">
        <p>{#Vous_devez_vous_identifier#} (<a href="../">{#Retour#}</a>)</p>
    </div>
</div> <!-- /container -->
