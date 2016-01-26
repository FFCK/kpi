<?php /* Smarty version 2.6.18, created on 2015-07-01 18:01:19
         compiled from Login.tpl */ ?>
		<div class="main">
					
			<div class="blocformlogin">		
				<form method="POST" action="Login.php" name="formLogin" enctype="multipart/form-data">
					<div class='blocRight'>
						<table width=100%>
							<tr>
								<th class='titreForm' colspan=2>
									<label>Identification</label>
								</th>
							</tr>
							<?php if ($this->_tpl_vars['bProd']): ?>
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
							<?php else: ?>
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
							<?php endif; ?>
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