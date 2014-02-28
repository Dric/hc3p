<?php
require_once('settings'.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'sections.class.php');

/**
* Classe de gestion des sections en frontend
* @package Frontend
* @subpackage Sections
*/
Class SectionsFrontend{
	
	/**
	* Liste des sections
	* @var array
	* 
	*/
	private $_sectionsList = array();
	
	/**
	* Liste des items de menu
	* @var array
	* 
	*/
	private $_menuItems = array();
	
	/**
	* Construction de la classe
	* 
	* @return void
	*/
	public function __construct(){
		$this->_sectionsList = $this->_getSectionsList();
		$this->_buildMenu();
	}
	
	/**
	* Affichage des sections
	* 
	* @return void
	*/
	public function display(){
		foreach ($this->_sectionsList as $section){
			if (!$section->disabled){
				$this->_displaySection($section);
			}
		}
	}

	public function globalStyle(){
		$global = new Section('global');
		?>
		<style>
			body{
				color: <?php echo $global->textColor; ?>;
				text-size: <?php echo $global->textSize; ?>%;
				background-color: <?php echo $global->backgroundData; ?>;
			}
		</style>
		<?php
	}
	/**
	* Construit le menu
	* 
	* @return bool
	*/
	private function _buildMenu(){
		foreach ($this->_sectionsList as $section){
			if ($section->createMenuItem and !$section->disabled){
				$this->_menuItems[] = array(
					'itemTitle' => str_replace('_', ' ', $section->name),
					'linkTo'		=> $section->dirname
				);
			}
		}
		return true;
	}
	
	public function getMenu(){
		$first = true;
		foreach ($this->_menuItems as $item){
			?><li <?php ($first) ? 'class="active"' : '';?>><a href="#s<?php echo $item['linkTo']; ?>"><?php echo $item['itemTitle']; ?></a></li><?php
			$first = false;
		}
	}
	
	/**
	* Retourne la liste des sections sous forme de tableau d'objets
	* 
	* @return array
	*/
	private function _getSectionsList(){
		$sectionsList = array();
		$path = DATA_PATH;
		$dirs = scandir($path);
		foreach ($dirs as $i => $dir){
			if (is_dir($path.DIRECTORY_SEPARATOR.$dir) and $dir != '.' and $dir != '..' and is_numeric(substr($dir, 0, 2))){
				$section = new Section($dir);
				$sectionsList[$section->order] = $section;
			}
		}
		ksort ($sectionsList);
		return $sectionsList;
	}
	
	/**
	* Affiche le contenu d'une section
	* @param Section $section Objet de section
	* 
	* @return void
	*/
	private function _displaySection(Section $section){
		?>
		<section id="s<?php echo $section->dirname; ?>" class="slide <?php echo ($section->background == 'image') ? 'backstretch" data-backstretch-images="'.$section->backgroundData : ''; ?>">
			<style>
				#s<?php echo $section->dirname; ?> {
					color: <?php echo $section->textColor; ?>;
					font-size: <?php echo $section->textSize; ?>%;
					<?php
					switch ($section->background){
						case 'pattern':
							?>background-image: url(<?php echo $section->backgroundData; ?>);<?php
						case 'inherited':
						case 'color':
							?>background-color: <?php echo $section->backgroundData; ?>;<?php
							break;
					}
					?>
				}
			</style>
			<div class="row">
			<?php
			if (!empty($section->subSections)){
				foreach ($section->subSections as $ID => $subSections){
					$nbSub = count($subSections);
					?><div class="row"><?php
					$grid = 12 / $nbSub;
					foreach ($subSections as $subID => $subSection){
						?><div class="col-md-<?php echo $grid; ?>"><?php echo \Michelf\MarkdownExtra::defaultTransform($subSection); ?></div><?php
					}
					?></div><?php
				}
			}
			?>
			</div>
		</section>
		<?php 
	}
}
?>