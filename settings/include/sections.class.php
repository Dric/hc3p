<?php
/**
* Gestion des sections
* 
* @package Settings
* @subpackage Sections
*/

/**
* Classe de gestion des sections
* 
* @package Settings
* @subpackage Sections
*/
Class SectionManager{
	
	/**
	* Choix possibles pour le fond des sections
	* @var array 
	* 
	*/
	protected $backgroundOptions = array(
		'color'			=> 'Utiliser une couleur',
		'image'			=> 'Utiliser une image',
		'pattern'		=> 'Utiliser un motif',
		'inherited'	=> 'Utiliser la couleur de fond globale'
		);
	
	/**
	* Couleur du fond par défaut
	* @var string
	* 
	*/
	private $_defaultBackgroundColor = '#ffffbd';
	
	/**
	* Couleur du texte par défaut
	* @var string
	* 
	*/
	private $_defaultTextColor = '#3c1f1f';
	
	/**
	* Tableau contenant la liste des sections
	* @var array
	* 
	*/
	private $_sections = array();
	
	/**
	* Chemin des data
	* @var string
	* 
	*/
	private $_dataPath = '';
	
	
	/**
	* Construction de la classe
	* 
	* @return void
	*/
	public function __construct(){
		$this->_dataPath = '.'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.DATA_PATH;
		$this->_sections = $this->getSectionsList();
		require_once('.'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'cache.class.php');
	}
	
	/**
	* Destruction de la classe
	* 
	* @return void
	*/
	public function __destruct(){
		//On supprime le cache systématiquement
		$cache = new cache();
		$cache->del();
	}
	
	/**
	* Affiche des règles de style css
	* 
	* @return void
	*/
	public function cssStyle(){
		?>
		<style>
			#sectionBackgroundImageDisplay, #sectionBackgroundColorDisplay {
				display: none;
			}
			.slider, .minicolors-theme-default.minicolors{
				display: block;
			}
		</style>
		<?php
	}
	
	/**
	* Traite les envois de requêtes
	* 
	* Retourne true si une requête a été traitée
	* @return bool
	*/
	public function requestActions(){
		if (isset($_REQUEST['action'])){
			switch(htmlspecialchars($_REQUEST['action'])){
				case 'getSectionEditSettingsForm':
					if (isset($_REQUEST['order'])){
						$order = (int)$_REQUEST['order'];
						$this->formEditSectionSettings('add', null, false, $order);
					}elseif (isset($_REQUEST['editGlobalStyle'])){
						$global = new Section('global');
						$this->formEditSectionSettings('global', $global, false);
					}
					return true;
					break;
				case 'getSectionEditContentForm':
					$section = new Section(htmlspecialchars($_REQUEST['dirname']), $this->_dataPath);
					$this->formEditSectionContent($section);
					return true;
				case 'getSectionEditTabs':
					$section = new Section(htmlspecialchars($_REQUEST['dirname']), $this->_dataPath);
					$this->editSectionTabs($section);
					return true;
				case 'getSection':
					$section = new Section(htmlspecialchars($_REQUEST['dirname']), $this->_dataPath);
					$this->_displaySection($section, true);
					return true;
				case 'saveSection':
					$section = $this->_postDataSection();
					$ret = $this->_saveSection($section);
					if ($ret['ok']){
						//$this->_displaySection($section, true);
						echo 'done';
					}else{
						?><div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><?php echo $ret['message']; ?></div><?php
					}
					return true;
				case 'delSection':
					$section = new Section(htmlspecialchars($_REQUEST['dirname']), $this->_dataPath);
					$ret = $this->_delSection($section);
					if ($ret['ok']){
						echo 'done';
					}else{
						?><div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><?php echo $ret['message']; ?></div><?php
					}
					return true;
				case 'insertSubSection':
					$section = $this->_postDataSection();
					$section = $this->_insertSubSection($section, htmlspecialchars($_REQUEST['after']));
					$ret = $this->_saveSection($section);
					if ($ret['ok']){
						$this->formEditSectionContent($section, true);
					}else{
						?><div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><?php echo $ret['message']; ?></div><?php
					}
					return true;
				case 'removeSubSection':
					$section = $this->_postDataSection();
					$section = $this->_removeSubSection($section, htmlspecialchars($_REQUEST['sub']));
					$ret = $this->_saveSection($section);
					if ($ret['ok']){
						$this->formEditSectionContent($section, true);
					}else{
						?><div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><?php echo $ret['message']; ?></div><?php
					}
					return true;
			}
		}
		return false;
	}
	
	/**
	* Affichage de la gestion des sections
	* 
	* @return void
	*/
	public function display(){
		$this->cssStyle();
		?>
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="jumbotron">
				  <h1>Sections</h1>
				  <p>Modifiez la présentation et le contenu du site.</p>
				  <p><button id="globalStyleEditButton" class="btn btn-default btn-lg" role="button">Modifier le style global du site</button></p>
				</div>
				<div class="row">
					<div class="col-md-10 col-md-offset-1">
						<div id="formGlobalStyleEdit"></div>
						<?php
						if (empty($this->_sections)){
							?>
							<div class="alert alert-danger">Il n'y a pas de sections définies !</div>
							<?php
							$this->formEditSectionSettings('add', null, false, 1); 
						}else{
							foreach ($this->_sections as $section){
								$this->_displaySection($section);
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	* Affiche l'édition complète d'une section
	* @param Section $section Objet de section
	* 
	* @return void
	*/
	public function editSectionTabs(Section $section){
		?>
		<h2><?php echo $section->order.' - '.$section->name; ?></h2>
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#formEditSectionContent" data-toggle="tab">Texte</a></li>
		  <li><a href="#formEditSectionSettings" data-toggle="tab">Présentation</a></li>
		</ul>
		<!-- Tab panes -->
		<div class="tab-content">
		  <div class="tab-pane row well active" id="formEditSectionContent"><?php $this->formEditSectionContent($section, true); ?></div>
		  <div class="tab-pane row well" id="formEditSectionSettings"><?php $this->formEditSectionSettings('edit', $section, true); ?></div>
		</div>
		<button class="btn btn-default" id="cancelEditSectionTabs" data-dirname="<?php echo $section->dirname; ?>" data-name="<?php echo $section->name; ?>">Annuler</button> <button id="editSectionTabsSave" class="btn btn-large btn-primary">Enregistrer</button>
		<?php
	}
	
	/**
	* Affiche le formulaire de modification du contenu d'une section
	* @param Section $section objet de section
	* 
	* @return
	*/
	public function formEditSectionContent(Section $section, $noWrap = false){
		?>
		<?php if (!$noWrap) { ?><div id="formEditSectionContent" class="row"><?php } ?>
			<h3>Modifier le contenu de la section</h3>
			<form role="form" id="editSectionContent" <?php if (!$noWrap) { ?>class="well"<?php } ?>>
				<?php
				$totalSubsRow = count($section->subSections);
				if ($totalSubsRow > 0){
					foreach ($section->subSections as $i => $subSections){
						?><div class="row"><?php
						$nbSubs = count($subSections);
						$grid = 12 / $nbSubs;
						foreach ($subSections as $x => $subSection){
							?>
							<div class="col-md-<?php echo $grid; ?>">
								<div class="form-group">
									<h3>Sous-section-<?php echo $i.'-'.$x; ?> <button class="btn btn-default removeSubSection" data-href="<?php echo $i.'-'.$x; ?>">Supprimer</button> <?php if (count($subSections) < 4){ ?><button class="btn btn-default insertSubSection" data-after="<?php echo $i.'-'.$x; ?>">Ajouter une sous-section à la suite</button><?php } ?></h3>
									<textarea id="subSectionContent-<?php echo $i; ?>-<?php echo $x; ?>" name="subSectionContent-<?php echo $i; ?>-<?php echo $x; ?>" class="form-control subSectionContentTextarea" rows="6"><?php echo $subSection; ?></textarea>
								</div>
							</div>
							<?php
						}
						?>
						</div>
						<button class="btn btn-default insertSubSection" data-after="<?php echo $i; ?>"><?php echo ($i < $totalSubsRow) ? 'Insérer' : 'Ajouter'; ?> une sous-section</button>
						<?php
					}
				}else{
					?><button class="btn btn-default insertSubSection" data-after="0">Ajouter une sous-section</button><?php
				}
				?>
				<?php if (!$noWrap) { ?>
				<div class="form-group">
					<button class="btn btn-default" id="cancelEditSectionContent" data-name="<?php echo $section->name; ?>">Annuler</button> <button class="btn btn-primary" id="saveEditSectionContent">Enregistrer</button>
				</div>
				<?php } ?>
			</form>
			<!-- Modal -->
			<input type="hidden" id="sectionEditorInsertImageUrl" value="">
			<div class="modal fade" id="sectionEditorLoadImageModal" tabindex="-1" role="dialog" aria-labelledby="Load Images" aria-hidden="true" data-src="include/filemanager/dialog.php?type=1&field_id=sectionEditorInsertImageUrl">
			  <div class="modal-dialog modal-lg">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h4 class="modal-title" id="myModalLabel">Sélectionner une image</h4>
			      </div>
			      <div class="modal-body">
			        <iframe frameborder="0"></iframe>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			      </div>
			    </div>
			  </div>
			</div>
		<?php if (!$noWrap) { ?></div><?php } ?>
		<?php
	}
	
	/**
	* Affichage du formulaire d'ajout/édition des paramètres d'une section
	* @param string $mode peut prendre deux valeurs : 'add' ou 'edit' (défaut)
	* @param array $formData Données à injecter dans le formulaire
	* 
	* @return void
	*/
	public function formEditSectionSettings($mode = 'edit', Section $section = null, $noWrap = false, $setOrder = 0){
		?>
		<?php if (!$noWrap) { ?><div id="formEditSectionSettings" class="row"><?php } ?>
			<h2>
			<?php
				switch ($mode){
					case 'edit':
						echo 'Modifier les paramètres de la section';
						break;
					case 'add':
						echo 'Ajouter une section';
						break;
					case 'global':
						echo 'Style global';
						break;
				}
			?>
			</h2>
			<form role="form" id="editSectionSettings">
				<fieldset class="col-md-4">
				  <?php if ($mode != 'global'){ ?>
				  <div class="form-group">
				    <label for="sectionName">Nom de la section</label>
				    <input type="text" class="form-control" name="sectionName" id="sectionName" placeholder="Nom de la section" value="<?php echo (!empty($section->name)) ? $section->name : ''; ?>">
				    <span class="help-block">Ce nom sera utilisé pour créer le menu et ne sera pas visible sur le site. Veuillez à ne pas mettre un nom trop long.</span>
				  </div>
				  <?php }else{ ?>
				  <input type="hidden" name="sectionName" id="sectionName" placeholder="Nom de la section" value="global">
				  <?php } ?>
				  <div class="checkbox">
				    <label>
				      <input id="sectionCreateMenuItem" name="sectionCreateMenuItem" type="checkbox" <?php echo (!empty($section->createMenuItem)) ? 'checked' : ''; ?>> <?php echo ($mode == 'global') ? 'Créer des items de menus pour les sections par défaut' : 'Créer un item de menu pour cette section'; ?>
				      <span class="help-block"><?php echo ($mode == 'global') ? 'Vos sections apparaîtront' : 'Votre section apparaîtra'; ?> dans le menu de navigation en haut de la page.</span>
				    </label>
				  </div>
				  <div class="checkbox">
				    <label>
				      <input id="sectionDisabled" name="sectionDisabled" type="checkbox" <?php echo (!empty($section->disabled)) ? 'checked' : ''; ?>> <?php echo ($mode == 'global') ? 'Désactiver les sections par défaut' : 'Désactiver cette section'; ?>
				      <span class="help-block">Une section désactivée ne sera pas affichée sur le site.</span>
				    </label>
				  </div>
			  </fieldset>
			  <fieldset class="col-md-4">
			  	<legend>Type de fond</legend>
			  	<?php
			  	if ($mode != 'global'){
				  	foreach ($this->backgroundOptions as $backgroundOption => $backgroundLabel){
							?>
							<div class="radio">
							  <label>
							    <input type="radio" name="sectionBackground" id="sectionBackgroundUse<?php echo ucfirst($backgroundOption); ?>" value="<?php echo $backgroundOption; ?>" <?php echo (!empty($section->background) and $section->background == $backgroundOption) ? 'checked' : ''; ?>>
							    <?php echo $backgroundLabel; ?>
							  </label>
							</div>
							<?php
						}
						$hasImage = false;
						if (!empty($section->background) and ($section->background == 'image' or $section->background == 'pattern') and !empty($section->backgroundData)){
							$sectionBackgroundImage = str_replace('data/images', 'cache/thumbs', $section->backgroundData);
							$hasImage = true;
							?>
							<style>
								#sectionBackgroundImageDisplay {
									display: block;
								}
							</style>
						<?php
						}
					}else{
						$hasImage = false;
						?>
						<style>
							#sectionBackgroundColorDisplay {
								display: block;
							}
						</style>
						<?php
					}
			  	?>
			  	<div id="sectionBackgroundSelectedImage"><img class="img-rounded" alt="Image" src="<?php echo ($hasImage) ? $sectionBackgroundImage : '../images/blank.png'; ?>" /></div>
			  	<div class="form-group>" id="sectionBackgroundImageDisplay">
				    <label for="sectionBackgroundImage">Image ou motif à charger</label>
				    <div class="input-group">
				      <input type="text" id="sectionBackgroundImage" name="sectionBackgroundImage" class="form-control" value="<?php echo ($hasImage) ? $section->backgroundData : ''; ?>">
				      <span class="input-group-btn">
				        <a href="#" data-src="include/filemanager/dialog.php?type=1&field_id=sectionBackgroundImage" class="btn btn-default" id="selectSectionBackgroundImage" data-toggle="modal" data-target="#sectionLoadImageModal">...</a>
				      </span>
				    </div>
				    <p class="help-block">Cliquez sur le bouton pour charger une image.</p>
				  </div>
				  <!-- Modal -->
					<div class="modal fade" id="sectionLoadImageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					  <div class="modal-dialog modal-lg">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					        <h4 class="modal-title" id="myModalLabel">Sélectionner une image</h4>
					      </div>
					      <div class="modal-body">
					        <iframe frameborder="0"></iframe>
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
					      </div>
					    </div>
					  </div>
					</div>
				  <div class="form-group" id="sectionBackgroundColorDisplay">
				    <label for="sectionBackgroundColor">Couleur<?php echo ($mode == 'global') ? ' du fond par défaut' : ''; ?></label>
				    <input type="text" id="sectionBackgroundColor" name="sectionBackgroundColor" class="form-control colorPickerEnabled" value="<?php echo (!empty($section->backgroundColor)) ? $section->backgroundColor : $this->_defaultBackgroundColor; ?>">
				  </div>
				</fieldset>
				<fieldset class="col-md-4">
					<legend>Format du texte</legend>
					<div class="form-group">
				    <label for="sectionTextSize">Taille du texte<?php echo ($mode == 'global') ? ' par défaut' : ''; ?></label>
				    <input type="text" class="form-control sliderEnabled" id="sectionTextSize" name="sectionTextSize" data-slider-min="0" data-slider-max="300" data-slider-step="10" data-slider-value="<?php echo (!empty($section->textSize)) ? $section->textSize : '100'; ?>" value="<?php echo (!empty($section->textSize)) ? $section->textSize : '100'; ?>">
				    <span class="help-block">La taille du texte est en pourcentage de la taille <?php echo ($mode == 'global') ? 'de la taille normale' : 'du texte globale'; ?>.</span>
				  </div>
				  <?php if ($mode != 'global'){ ?>
				  <div class="checkbox">
				    <label>
				      <input id="sectionTextColorInherited" name="sectionTextColorInherited" type="checkbox" <?php echo ((!empty($section->textColor) and $section->textColor == 'inherited') or empty($section->textColor)) ? 'checked' : ''; ?>> Couleur de texte globale
				    </label>
				  </div>
				  <?php
					  if ((!empty($section->textColor) and $section->textColor == 'inherited') or empty($section->textColor)){
							?>
							<style>
								#sectionTextColorDisplay {
									display: none;
								}
							</style>
							<?php
						}
					}
				  ?>
				  <div class="form-group" id="sectionTextColorDisplay">
				    <label for="sectionTextColor">Couleur<?php echo ($mode == 'global') ? ' du texte par défaut' : ''; ?></label>
				    <input type="text" id="sectionTextColor" name="sectionTextColor" data-globalColor="<?php echo $this->_defaultTextColor; ?>" class="form-control colorPickerEnabled" value="<?php echo (!empty($section->textColor)) ? $section->textColor : $this->_defaultTextColor; ?>">
				  </div>
				  <?php if ($mode == 'edit') { ?>
				  <input type="hidden" id="sectionDateCreated" name="sectionDateCreated" value="<?php echo $section->dateCreated; ?>">
				  <input type="hidden" id="sectionDirName"  name="sectionDirName" value="<?php echo $section->dirname; ?>">
				  <input type="hidden" id="sectionOrder"  name="sectionOrder" value="<?php echo $section->order; ?>">
				  <?php 
				  }elseif($setOrder > 0) { 
				  ?>
				  <input type="hidden" id="sectionOrder"  name="sectionOrder" value="<?php echo $setOrder; ?>">
				  <?php } ?>
				  <?php if (!$noWrap) { ?><button id="cancelEditSectionSettings" class="btn btn-default">Annuler</button> <button type="submit" id="saveEditSectionSettings" class="btn btn-primary "><?php echo ($mode == 'edit' or $mode == 'global') ? 'Modifier' : 'Ajouter'; ?></button><?php } ?>
				</fieldset> 
			</form>
		<?php if (!$noWrap) { ?></div><?php } ?>
		<?php	
	}
	
	/**
	* Affiche le contenu d'une section
	* @param Section $section Objet de section
	* @param bool $sectionOnly Pour afficher seulement le contenu de la section
	* 
	* @return void
	*/
	private function _displaySection(Section $section, $sectionOnly = false){
		?>
		<div class="panel" id="section-<?php echo $section->name; ?>" data-order="<?php echo $section->order; ?>">
			<h1><span class="sectionTitleOrder"><?php echo $section->order.' - '.$section->name; ?></span> <button id="editSection-<?php echo $section->name; ?>" class="btn btn-default editSectionTabs" data-name="<?php echo $section->name; ?>" data-dirname="<?php echo $section->dirname; ?>">Modifier</button> <button id="editSection-<?php echo $section->name; ?>" class="btn btn-default delSession" data-toggle="del-section-confirmation" data-href="<?php echo $section->dirname; ?>" data-name="<?php echo $section->name; ?>" data-dirname="<?php echo $section->dirname; ?>">Supprimer</button></h1>
			<p>
				<span class="label <?php echo ($section->disabled)?'label-danger':'label-success'; ?>">Statut : <?php echo ($section->disabled)?'Désactivé':'Activé'; ?></span>
				<span class="label label-info">Date de création : <?php echo date('d/m/Y H:i', $section->dateCreated); ?></span>
				<?php if ($section->dateModified > 0){ ?>
				<span class="label label-info">Dernière modification : <?php echo date('d/m/Y H:i', $section->dateModified); ?></span>
				<?php } ?>
				<span class="label <?php echo ($section->createMenuItem)?'label-info':'label-default'; ?>">Menu : <?php echo ($section->createMenuItem)?'Présent':'Absent'; ?></span>
			</p>
			<?php
			if (!empty($section->subSections)){
				foreach ($section->subSections as $ID => $subSections){
					$nbSub = count($subSections);
					?><div class="row"><?php
					$grid = 12 / $nbSub;
					foreach ($subSections as $subID => $subSection){
						?><div class="col-md-<?php echo $grid; ?>"><h3>Sous-section-<?php echo $ID; ?><?php echo ($subID > 0) ? '-'.$subID : ''; ?></h3><div class="well"><?php echo \Michelf\MarkdownExtra::defaultTransform($subSection); ?></div></div><?php
					}
					?></div><?php
				}
			}else{
				?><div class="alert alert-warning">Cette section n'a pas de contenu !</div><?php
			}
			?>
		</div>
		<?php 
		if (!$sectionOnly){ 
			?>
			<button class="insertNewSectionAfter btn btn-default" data-after="<?php echo $section->name; ?>" data-before="<?php echo ($section->order + 1); ?>">Insérer une nouvelle section ici</button>
			<div id="insertNewSectionDivAfterSection-<?php echo $section->name; ?>" class="insertDiv"></div>
			<?php
		}
	}
	
	/**
	* Supprime une sous-section
	* @param Section $section
	* @param string $subSection Sous-section à supprimer
	* 
	* @return Section
	*/
	private function _removeSubSection(Section $section, $subSection){
		$tab = explode('-', $subSection);
		if (!isset($tab[1]) or (int)$tab[1] === 0){
			$ID = (int)$tab[0];
			unset ($section->subSections[$ID]);
			if (isset($section->subSections[$ID+1])){
				$reIndexedSubSections = array();
				foreach ($section->subSections as $index => $data){
					if ($index >= $ID+1){
						$reIndexedSubSections[$index-1] = $data;
					}else{
						$reIndexedSubSections[$index] = $data;
					}
				}
				$section->subSections = $reIndexedSubSections;
			}
			ksort ($section->subSections);
		}else{
			$ID = (int)$tab[0];
			$subID = (int)$tab[1];
			unset ($section->subSections[$ID][$subID]);
			if (isset($section->subSections[$ID][$subID+1])){
				$reIndexedSubSections = array();
				foreach ($section->subSections[$ID] as $index => $data){
					if ($index >= $subID+1){
						$reIndexedSubSections[$index-1] = $data;
					}else{
						$reIndexedSubSections[$index] = $data;
					}
				}
				$section->subSections[$ID] = $reIndexedSubSections;
				ksort ($section->subSections[$ID]);
			}elseif(count($section->subSections[$ID]) === 0){
				unset ($section->subSections[$ID]);
				ksort ($section->subSections);
			}
		}
		return $section;
	}
	
	/**
	* Insère une sous-section
	* @param Section $section Objet de section
	* @param string $after Insérer après cette sous-section
	* 
	* @return Section
	*/
	private function _insertSubSection(Section $section, $after){
		$tab = explode('-', $after);
		if (!isset($tab[1])){
			$ID = (int)$tab[0];
			if (isset($section->subSections[$ID + 1])){
				$reIndexedSubSections = array();
				foreach ($section->subSections as $index => $data){
					if ($index >= $ID + 1){
						$reIndexedSubSections[$index+1] = $data;
					}else{
						$reIndexedSubSections[$index] = $data;
					}
				}
				$section->subSections = $reIndexedSubSections;
				$section->subSections[$ID + 1][0] = '';
			}else{
				$section->subSections[$ID + 1][0] = '';
			}
			ksort ($section->subSections);
		} else {
			$ID = (int)$tab[0];
			$subID = (int)$tab[1];
			if (isset($section->subSections[$ID][$subID + 1])){
				$reIndexedSubSections = array();
				foreach ($section->subSections[$ID] as $index => $data){
					if ($index >= $subID + 1){
						$reIndexedSubSections[$index + 1] = $data;
					}else{
						$reIndexedSubSections[$index] = $data;
					}
				}
				$section->subSections[$ID] = $reIndexedSubSections;
				$section->subSections[$ID][$subID + 1] = '';
			}else{
				if ($tab[1] === 0){
					$section->subSections[$ID][1] = $section->subSections[$ID][0];
					unset($section->subSections[$ID][0]);
					$tab[1] = 1;
				}
				$section->subSections[$ID][$subID + 1] = '';
			}
			ksort ($section->subSections[$ID]);
		}
		return $section;
	}
	
	/**
	* Insère ou enlève une section en changeant l'ordre
	* @param Section $section objet de section à insérer/enlever
	* 
	* @return bool
	*/
	private function _setPosition(Section $section, $mode = 'insert'){
		$OK = true;
		$path = $this->_dataPath;
		$dirs = scandir($path);
		$dirNames = array();
		//On récupère la liste des sections
		foreach ($dirs as $dir){
			if (is_dir($path.DIRECTORY_SEPARATOR.$dir) and $dir != '.' and $dir != '..' and is_numeric(substr($dir, 0, 2))){
				list($sectionOrder, $sectionName) = explode('-', $dir);
				$sectionOrder = (int)$sectionOrder;
				$dirNames[$sectionOrder] = $sectionName;
			}
		}
		// Si le numéro d'ordre existe et que la section est différente de celle analysée, alors il faut changer l'ordre
		if ($mode == 'insert' and isset($dirNames[$section->order]) and $dirNames[$sectionOrder] != $this->sanitizeName($section->name)){
			foreach ($dirNames as $sectionOrder => $sectionName){
				if ($sectionOrder >= $section->order){
					$sectionNewDirName = sprintf("%02s", $sectionOrder + 1).'-'.$sectionName;
					$ret = rename($path.DIRECTORY_SEPARATOR.sprintf("%02s", $sectionOrder).'-'.$sectionName, $path.DIRECTORY_SEPARATOR.$sectionNewDirName);
					if (!$ret){
						$OK = false;
					}
				}
			}
		}elseif($mode == 'remove'){
			foreach ($dirNames as $sectionOrder => $sectionName){
				if ($sectionOrder > $section->order){
					$sectionNewDirName = sprintf("%02s", $sectionOrder - 1).'-'.$sectionName;
					$ret = rename($path.DIRECTORY_SEPARATOR.sprintf("%02s", $sectionOrder).'-'.$sectionName, $path.DIRECTORY_SEPARATOR.$sectionNewDirName);
					if (!$ret){
						$OK = false;
					}
				}
			}
		}
		return $OK;
	}
	
	/**
	* Supprime une section
	* @param Section $section Objet de section à supprimer
	* 
	* @return array(bool, string)
	*/
	private function _delSection(Section $section){
		$mess = '';
		$OK = true;
		$path = $this->_dataPath.DIRECTORY_SEPARATOR.$section->dirname;
		if (file_exists($path)){
			$dirs = scandir($path);
			foreach ($dirs as $item) {
		    if ($item == '.' || $item == '..') continue;
		    $ret = unlink($path.DIRECTORY_SEPARATOR.$item);
		    if (!$ret){
					$mess .= '<li>Impossible de supprimer le fichier <code>'.$item.'</code></li>';
					$OK = false;
				}
			}
			$ret = rmdir($path);
			if ($ret){
				if (!$this->_setPosition($section, 'remove')){
					$OK = false;
					$mess .= '<li>Impossible de changer l\'ordre des sections !</li>';
				}
			}else{
				$OK = false;
				$mess .= '<li>Impossible de supprimer la section !</li>';
			}
			
		}
		return array('ok' => $OK, 'message' => $mess);
	}
	/**
	* Sauvegarde une section
	* @param Section objet de section
	* 
	* @return array(bool, string) Résultat et message
	*/
	private function _saveSection(Section $section){
		$mess = '';
		$OK = true;
		if ($section->dirname == 'global'){
			$dirname = 'global';
		}else{
			$dirname = sprintf("%02s", $section->order).'-'.$this->sanitizeName($section->name);
		}
		$path = $this->_dataPath.DIRECTORY_SEPARATOR.$dirname;
		if ($dirname != $section->dirname and file_exists($this->_dataPath.DIRECTORY_SEPARATOR.$section->dirname)){
			$dirs = scandir($this->_dataPath.DIRECTORY_SEPARATOR.$section->dirname);
			foreach ($dirs as $item) {
		    if ($item == '.' || $item == '..') continue;
		    unlink($this->_dataPath.DIRECTORY_SEPARATOR.$section->dirname.DIRECTORY_SEPARATOR.$item);
			}
			rmdir($this->_dataPath.DIRECTORY_SEPARATOR.$section->dirname);
		}
		if (!file_exists($path)) {
			//Comme c'est une petite nouvelle, on l'insère dans l'ordre des sections
			$ret = $this->_setPosition($section, 'insert');
			mkdir($path, 0777, true);
			if (!$ret){
				$OK = false;
				$mess .= '<li>Impossible de changer l\'ordre des sections</li>'; 
			}
		}
		if ($OK){
			$ini['name']						= $section->name;
			$ini['dateCreated']			= $section->dateCreated;
			if ($section->dateModified > 0){
				$ini['dateModified']	= $section->dateModified;
			}
			$ini['createMenuItem']	=	$section->createMenuItem;
			$ini['disabled']				= $section->disabled;
			$ini['background']			= $section->background;
			$ini['backgroundData']	= $section->backgroundData;
			$ini['textColor']				= $section->textColor;
			$ini['textSize']				= $section->textSize;
		
			$OK = $this->_writeIniFile($ini, $path.DIRECTORY_SEPARATOR.'section.ini');
		}
		if ($OK and !empty($section->subSections)){
			$secMD = '';
			foreach ($section->subSections as $ID => $subSection){
				if (count($subSection) === 1){
					$secMD .= '/*Section-'.$ID.'*/'.PHP_EOL;
					$secMD .= trim($subSection[0]).PHP_EOL;
					$secMD .= '/*section-end*/'.PHP_EOL;
				}elseif(count($subSection) > 1){
					foreach ($subSection as $subID => $data){
						$secMD .= '/*Section-'.$ID.'-'.$subID.'*/'.PHP_EOL;
						$secMD .= trim($data).PHP_EOL;
						$secMD .= '/*section-end*/'.PHP_EOL;
					}
				}
			}
			// Que le résultat soit 0ko écrits ou false, c'est une erreur !
			$OK = (bool)file_put_contents($path.DIRECTORY_SEPARATOR.'section.md', $secMD);
		}
		return array('ok' => $OK, 'message' => $mess);
	}
	
	/**
	* Récupère les données d'un formulaire de section
	* 
	* @return Section object
	*/
	private function _postDataSection(){
		
		if (isset($_REQUEST['sectionDirName'])){
			$dirname = htmlspecialchars($_REQUEST['sectionDirName']);
		}elseif($_REQUEST['sectionName'] == 'global'){
			$dirname = 'global';
		}else{
			$dirname = $_REQUEST['sectionOrder'].'-'.htmlspecialchars($_REQUEST['sectionName']);
		}
		$tmp = new Section($dirname, $this->_dataPath, true);
		$tmp->dirname 				= $dirname;
		$tmp->name						= htmlspecialchars($_REQUEST['sectionName']);
		$tmp->order						= (isset($_REQUEST['sectionOrder'])) ? $_REQUEST['sectionOrder'] : 0;
		if (!isset($_REQUEST['sectionDateCreated'])){
			$tmp->dateCreated		= time();
		}else{
			$tmp->dateCreated		= (int)$_REQUEST['sectionDateCreated'];
			$tmp->dateModified	= time();
		}
		$tmp->createMenuItem	= (isset($_REQUEST['sectionCreateMenuItem'])) ? true : false;
		if (isset($_REQUEST['sectionBackground'])){
			$tmp->background		= (array_key_exists($_REQUEST['sectionBackground'], $this->backgroundOptions)) ? $_REQUEST['sectionBackground'] : 'inherited';
		}else{
			$tmp->background		= 'inherited';
		}
		$tmp->textColor				= htmlspecialchars($_REQUEST['sectionTextColor']);
		$tmp->textSize				= (int)$_REQUEST['sectionTextSize'];
		$tmp->disabled				= (isset($_REQUEST['sectionDisabled'])) ? true : false;
		$tmp->backgroundData	= ($tmp->background == 'image' or $tmp->background == 'pattern') ? htmlspecialchars($_REQUEST['sectionBackgroundImage']) : htmlspecialchars($_REQUEST['sectionBackgroundColor']);
		
		foreach ($_REQUEST as $index => $data){
			if (strpos($index, 'subSectionContent') !== false){
				list($dummy, $ID, $subID) = explode('-', $index);
				$tmp->subSections[$ID][$subID] = strip_tags($data, '<a><abbr><acronym><address><article><aside><b><bdo><big><blockquote><br><caption><cite><code><col><colgroup><dd><del><details><dfn><div><dl><dt><em><figcaption><figure><font><h1><h2><h3><h4><h5><h6><hgroup><hr><i><img><ins><li><map><mark><menu><meter><ol><p><pre><q><rp><rt><ruby><s><samp><section><small><span><strong><style><sub><summary><sup><table><tbody><td><tfoot><th><thead><time><tr><tt><u><ul><var><wbr>');
			}
		}
		return $tmp;
	}
	
	/**
	* Retourne la liste des sections sous forme de tableau d'objets
	* 
	* @return array
	*/
	public function getSectionsList(){
		$sectionsList = array();
		$path = $this->_dataPath;
		$dirs = scandir($path);
		foreach ($dirs as $i => $dir){
			if (is_dir($path.DIRECTORY_SEPARATOR.$dir) and $dir != '.' and $dir != '..' and is_numeric(substr($dir, 0, 2))){
				$section = new Section($dir, $this->_dataPath);
				$sectionsList[$section->order] = $section;
			}
		}
		return $sectionsList;
	}
	
	/**
	* retourne un nom sans caractères accentués
	* @param string $name Nom à transformer
	* 
	* @return string
	*/
	static function sanitizeName($name){
		return iconv('UTF-8','ASCII//TRANSLIT//IGNORE', $name); 
	}
	
	/**
	* Ecrit un tableau dans un fichier INI
	* @param array $assoc_arr Tableau des valeurs
	* @param string $path Nom complet du fichier
	* @param bool $has_sections Activer ou non les sections INI
	* @from <http://stackoverflow.com/a/1268642/1749967>
	* 
	* @return bool
	*/
	private function _writeIniFile($assoc_arr, $path, $has_sections=FALSE) { 
    $content = ""; 
    if ($has_sections) { 
      foreach ($assoc_arr as $key=>$elem) { 
        $content .= "[".$key."]".PHP_EOL; 
        foreach ($elem as $key2=>$elem2) { 
          if (is_array($elem2)){ 
            for($i=0;$i<count($elem2);$i++) { 
              $content .= $key2."[] = \"".$elem2[$i]."\"".PHP_EOL; 
            } 
          } 
          else if($elem2=="") $content .= $key2." = ".PHP_EOL; 
          else $content .= $key2." = \"".$elem2."\"".PHP_EOL; 
        } 
      } 
    } else { 
      foreach ($assoc_arr as $key=>$elem) { 
        if(is_array($elem)) { 
          for($i=0;$i<count($elem);$i++) { 
            $content .= $key.'[] = "'.$elem[$i].'"'.PHP_EOL; 
          } 
        } 
        else if($elem === true) 	$content .= $key.' = true'.PHP_EOL;
        else if($elem === false) 	$content .= $key.' = false'.PHP_EOL;
        else if(is_int($elem)) 		$content .= $key.' = '.$elem.PHP_EOL;
        else if($elem=='')				$content .= $key.' = '.PHP_EOL;
        else $content .= $key.' = "'.$elem.'"'.PHP_EOL; 
      } 
    } 

    // Que le résultat soit 0ko écrits ou false, c'est une erreur !
		return (bool)file_put_contents($path, $content);
	}
}

/**
* Classe de section
* 
* @package Settings
* @subpackage Sections
*/
Class Section{
	
	/**
	* Nom de la section
	* @var string
	* 
	*/
	public $name = '';
	
	/**
	* Nom du répertoire de la section
	* @var string
	* 
	*/
	public $dirname = '';
	/**
	* Ordre d'affichage de la section dans la page
	* @var int
	* 
	*/
	public $order = 0;
	/**
	* Date de création au format Timestamp Unix
	* @var int
	* 
	*/
	public $dateCreated = 0;
	
	/**
	* Date de dernière modification au frmat Timestamp Unix
	* @var int
	* 
	*/
	public $dateModified = 0;
	
	/**
	* La section a son item de menu
	* @var bool
	* 
	*/
	public $createMenuItem = true;
	
	/**
	* Type de fond
	* @var string
	* 
	*/
	public $background = 'inherited';
	
	/**
	* Données du fond (couleur ou url de l'image)
	* @var string
	* 
	*/
	public $backgroundData = '';
	
	/**
	* Couleur du texte
	* @var string
	* 
	*/
	public $textColor = 'inherited';
	
	/**
	* Taille du texte (en pourcentage de la taille globale)
	* @var int
	* 
	*/
	public $textSize = 100;
	
	/**
	* Section désactivée et masquée sur le site
	* @var bool
	* 
	*/
	public $disabled = false;
	
	/**
	* Sous-sections
	* @var array
	* 
	*/
	public $subSections = array();
	
	/**
	* Chemin des data
	* @var string
	* 
	*/
	private $_dataPath = DATA_PATH;
	
	/**
	* Construction de la classe
	* @param string $dirname Nom du répertoire de la section
	* @param bool $tmp Si true, alors la classe ne récupère pas ses propriétés via le contenu d'un répertoire de section
	* 
	* @return void
	*/
	public function __construct($dirname, $dataPath = DATA_PATH, $tmp = false){
		$this->_dataPath = $dataPath;
		if (!$tmp){
			$ret = $this->_populate($dirname);
		}
	}
	
	/**
	* Récupère les propriétés de la section
	* @param string $dirname Nom du répertoire de la section
	* @return bool
	*/
	private function _populate($dirname){
		//Récupération des paramètres globaux
		if (!file_exists($this->_dataPath.DIRECTORY_SEPARATOR.'global'.DIRECTORY_SEPARATOR.'section.ini')){
			return false;
		}
		//On récupère les paramètres globaux du site
		$globals = parse_ini_file($this->_dataPath.DIRECTORY_SEPARATOR.'global'.DIRECTORY_SEPARATOR.'section.ini');
		if ($globals === false){
			return false;
		}

		$this->createMenuItem = (isset($globals['createMenuItem']))?(bool)$globals['createMenuItem']:$this->createMenuItem;
		$this->background			= (isset($globals['background']))?(string)$globals['background']:$this->background;
		$this->backgroundData	= (isset($globals['backgroundData']))?(string)$globals['backgroundData']:$this->backgroundData;
		$this->textColor			= (isset($globals['textColor']))?(string)$globals['textColor']:$this->textColor;
		$this->textSize				= (isset($globals['textSize']))?(int)$globals['textSize']:$this->textSize;
		$this->disabled				= (isset($globals['disabled']))?(bool)$globals['disabled']:$this->disabled;
		
		// Passons aux paramètres propres à la section
		$this->dirname = $dirname;
		
		if ($dirname != 'global'){
			//On récupère l'ordre d'affichage
			list($order, $name) = explode('-', $dirname, 2);
			$this->order = (int)$order;
			
			//On récupère les autres paramètres de la section
			$path = $this->_dataPath.DIRECTORY_SEPARATOR.$dirname;
			if (!file_exists($path.DIRECTORY_SEPARATOR.'section.ini')){
				return false;
			}
			//On récupère les infos de section
			$section = parse_ini_file($path.DIRECTORY_SEPARATOR.'section.ini');
			
			if ($section === false){
				return false;
			}
			$this->name						= (isset($section['name']))?(string)$section['name']:$this->name;
			$this->dateCreated 		= (isset($section['dateCreated']))?(int)$section['dateCreated']:$this->dateCreated;
			$this->dateModified 	= (isset($section['dateModified']))?(int)$section['dateModified']:$this->dateModified;
			$this->createMenuItem = (isset($section['createMenuItem']))?(bool)$section['createMenuItem']:$this->createMenuItem;
			$this->background			= (isset($section['background']))?(string)$section['background']:$this->background;
			$this->backgroundData	= (isset($section['backgroundData']) and $section['background'] != 'inherited')?(string)$section['backgroundData']:$this->backgroundData;
			$this->textColor			= (isset($section['textColor']) and $section['textColor'] != 'inherited')?(string)$section['textColor']:$this->textColor;
			$this->textSize				= (isset($section['textSize']))?(int)$section['textSize']:$this->textSize;
			$this->disabled				= (isset($section['disabled']))?(bool)$section['disabled']:$this->disabled;
			
			//On récupère le contenu des sous-sections
			if (file_exists($path.DIRECTORY_SEPARATOR.'section.md')){
				$content = file_get_contents($path.DIRECTORY_SEPARATOR.'section.md');
				if (!$content){
					return false;
				}
				//Une section commence par l'identifiant de section en commentaire et finit par 'section-end' en commentaire
				preg_match_all("/\/\*(.*)\*\/(.*)\/\*section\-end\*\//Us", $content, $subs);
				foreach ($subs[1] as $i => $sub){
					$tab = explode('-', $sub);
					if (isset($tab[1])){
						$id = (int)$tab[1];
					}else{
						return false;
					}
					$subId = 0;
					if (isset($tab[2])){
						$subId = (int)$tab[2];
					}
					$this->subSections[$id][$subId] = $subs[2][$i];
				}
			}
		}
		return true;
	}
}
?>