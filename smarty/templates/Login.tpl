<div class="container">
    <div class="col-md-6 col-md-offset-3">
        <h2 class="form-signin-heading text-center"><img src="../img/KPI.png" alt="KPI" height="60"> {#Connexion#}</h2>
    </div>
    <div class="col-md-4 col-md-offset-4">
        <form class="form-signin2" method="POST" action="Login.php" name="formLogin" id="formLogin" enctype="multipart/form-data">
            <label for="User">{#Identifiant#}</label>
            <input type="tel" name="User" id="idUser" class="form-control" placeholder="{#Identifiant#}" required autofocus>
            <div id="connect">
                <label for="password">{#Mot_de_passe#}</label>
                <input type="password" name="Pwd" id="idPwd" class="form-control" placeholder="{#Mot_de_passe#}" required>
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
            <p class="text-center"><a href="mailto:laurent@poloweb.org?subject=Demande d'identifiant administrateur kayak-polo.info&body=Nom:%0D%0APrénom:%0D%0AN°Licence:%0D%0AFonctions fédérales:%0D%0AUn petit mot ?">Demander un identifiant</a></p>
            <br>
            <p class="text-center" id="perdu"><a href="" onClick="return false">{#j_ai_perdu_mon_mdp#}</a></p>
            <br>
            <br>
        </form>
    </div>
    <div class="col-md-6 col-md-offset-3">
        <p>Vous devez vous identifier pour accéder à cette page (<a href="../">{#Retour#}</a>)</p>
        <p>L'accès aux panneau d'administration est réservé aux membres de la commission kayak-polo de la FFCK
            et aux responsables de compétition ou de club. Merci de votre compréhension.</p>
    </div>
</div> <!-- /container -->

{*		<div class="main">
                
<div class="blocformlogin">		
    
<div class='blocRight'>
<table width=100%>
<tr>
<th class='titreForm' colspan=2>
<label>Identification</label>
</th>
</tr>
{if $bProd}
<tr>
<td>
<label for="User">Identifiant</label>
<input type="tel" name="User" id="idUser" size="15" class='court newInput'/>
<div id="connect">
<br>
<br>
<label for="Pwd">Mot de Passe</label>
<input type="password" name="Pwd" id="idPwd" size="15" class='court newInput'/>
</div>
<div id="renv">
<br>
<br>
<label for="Mel">Email</label>
<input type="text" name="Mel" id="idMel" size="15" class='court newInput'/>
</div>
</td>
</tr>
<tr>
<td>
<br>
<br>
<input type="submit" name="login" id="login" value="Connexion">
<input type="submit" name="Renvoyer" id="Renvoyer" value="Renvoyer">
<input type="submit" name="Annuler" id="Annuler" value="Annuler" onClick="return false">
<input type="hidden" name="Mode" id="Mode" value="Connexion">
</td>
</tr>
<tr>
<td>
<br>
<br>
<p><a href="mailto:laurent@poloweb.org?subject=Demande d'identifiant administrateur kayak-polo.info&body=Nom:%0D%0APrénom:%0D%0AN°Licence:%0D%0AFonctions fédérales:%0D%0AUn petit mot ?">Demander un identifiant</a></p>
<br>
<p id="perdu"><a href="" onClick="return false">J'ai perdu mon mot de passe...</a></p>
</td>
</tr>
{else}
<tr>
<td>
<label for="User">Identifiant local</label>
<input type="text" name="User" id="idUser" size="15" class='court'/>
<div id="connect">
<br>
<label for="Pwd">Mot de passe local</label>
<input type="password" name="Pwd" id="idPwd" size="15" class='court'/>
</div>
</td>
</tr>
<tr>
<td>
<input type="submit" name="login" id="login" value="Connexion">
<input type="hidden" name="Mode" id="Mode" value="Connexion">
</td>
</tr>
{/if}
</table>
<br>
<br>
<br>
</div>
<p>Vous devez vous identifier pour accéder à cette page. (<a href="javascript:history.back()">retour</a>)</p>
<p>L'accès aux panneau d'administration est réservé aux membres de la commission kayak-polo de la FFCK<br>
et aux responsables de compétition ou de club. Merci de votre compréhension.</p>

</form>			
</div>
</div>	  	   
*}