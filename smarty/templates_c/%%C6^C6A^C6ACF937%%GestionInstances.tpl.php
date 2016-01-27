<?php /* Smarty version 2.6.18, created on 2015-04-20 17:14:34
         compiled from GestionInstances.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'GestionInstances.tpl', 18, false),)), $this); ?>
	&nbsp;(<a href="GestionCalendrier.php">Retour</a>)
	
	<div class="main">
		<form method="POST" action="FeuilleInstances.php" name="formInstances" id="formInstances" enctype="multipart/form-data">
			<input type='hidden' name='Cmd' Value=''/>
			<input type='hidden' name='ParamCmd' Value=''/>

			<div class='titrePage'>Instances de la journée</div>
			<div class='blocTop centre'>
				<table width="100%">
                                    <tr>
					<td>
                                            <h3 align="center"><?php echo $this->_tpl_vars['arrayJournee']['Nom']; ?>
<h3>
                                            <input type="hidden" name="NomJournee" value="<?php echo $this->_tpl_vars['arrayJournee']['Nom']; ?>
" >           
                                            <h4 align="center"><?php echo $this->_tpl_vars['arrayJournee']['Lieu']; ?>
 (<?php echo $this->_tpl_vars['arrayJournee']['Departement']; ?>
)
                                            <input type="hidden" name="Lieu" value="<?php echo $this->_tpl_vars['arrayJournee']['Lieu']; ?>
" >
                                            <input type="hidden" name="Departement" value="<?php echo $this->_tpl_vars['arrayJournee']['Departement']; ?>
" >
					    <br /><?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournee']['Date_debut'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournee']['Date_fin'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
</h4>
                                            <input type="hidden" name="Date_debut" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournee']['Date_debut'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
" >
                                            <input type="hidden" name="Date_fin" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['arrayJournee']['Date_fin'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d/%m/%Y") : smarty_modifier_date_format($_tmp, "%d/%m/%Y")); ?>
" >
                                        </td>
					</tr>
				</table>
			</div>
			<div class='blocMiddle centre'>
                            Responsable de compétition : <?php echo $this->_tpl_vars['arrayJournee']['Responsable_insc']; ?>

                            <br />
                            <br />
                            <table class="tableau tableau60">
                                <thead>
                                    <tr>
                                        <th colspan="2">Comité de compétition</th>
                                    </tr>
                                </thead>
                                <tr>
                                    <th>Responsable de l'organisation (R1)</th>
                                    <td>
                                        <br />
                                        <span class="editOfficiel"><?php echo $this->_tpl_vars['arrayJournee']['Responsable_R1']; ?>
</span>
                                        <br />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Délégué de la Commission Nationale d'Activité</th>
                                    <td>
                                        <br />
                                        <span class="editOfficiel"><?php echo $this->_tpl_vars['arrayJournee']['Delegue']; ?>
</span>
                                        <br />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Chef des arbitres</th>
                                    <td>
                                        <br />
                                        <span class="editOfficiel"><?php echo $this->_tpl_vars['arrayJournee']['ChefArbitre']; ?>
</span>
                                        <br />
                                    </td>
                                </tr>
                                <thead>
                                <tr>
                                    <th colspan="2">Jury d'appel</th>
                                </tr>
                                </thead>
                                <tr>
                                    <th>Délégué C.N.A (président du Jury)</th>
                                    <td>
                                        <br />
                                        <span class="editOfficiel"><?php echo $this->_tpl_vars['arrayJournee']['Delegue']; ?>
</span>
                                        <br />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Responsable de l'organisation (R1)</th>
                                    <td>
                                        <br />
                                        <span class="editOfficiel"><?php echo $this->_tpl_vars['arrayJournee']['Responsable_R1']; ?>
</span>
                                        <br />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Représentant des compétiteurs</th>
                                    <td>
                                        <br />
                                        <input type="text" name="Representant" id="Representant" size="60" placeholder="Nom prénom ou numéro de licence" class="ac_input" />
                                        <br />
                                    </td>
                                </tr>
                            </table>
			</div>
			<div class='blocBottom centre'>
                            <a href="FeuilleInstances.php?idJournee=<?php echo $this->_tpl_vars['arrayJournee']['Id']; ?>
" title="pdf" target="_blank"><img src="../img/pdf.gif" alt="pdf"></a>
                        </div>
		</form>			
				
	</div>	  	   