<?php
/**
* Classe cache
* @package Cache
*  
* Gère le cache
*/

/**
* Classe cache
* @package Cache
*/
Class cache{
	
	/** @var string Répertoire utilisé pour le cache */
	public $cache_dir = 'cache/';
	
	
	public $cache_file = '';
	
	/**
	* initialise le cache d'une page
	* 
	*/
	public function init(){
		ob_start();	
		$cache_file = urlencode(basename($_SERVER['REQUEST_URI'], '.php'));
		if ($cache_file == 'html'){
			$cache_file = 'index';
		}	
		$this->cache_file = $cache_file;
	}
	
	/**
	* Vérifie qu'une page est en cache
	* @param string $cache_file nom de la page
	* 
	* @return bool
	*/
	public function check($cache_file){
		$cache_file = $this->cache_dir.$cache_file.'.html';
		if (file_exists($cache_file)){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	* Récupère le cacche d'une page
	* 
	* @return false si erreur
	*/
	public function get(){
		$cache_file = $this->cache_file;
		if ($this->check($cache_file)){
			readfile($this->cache_dir.$cache_file.'.html');
		}else{
			return false;
		}
	}
	
	/**
	* Met une page en cache puis affiche le cache
	* @param string $cache_file facultatif nom de la page (prend la page demandée si vide)
	* 
	*/
	public function to_file($cache_file = null){
		if (empty($cache_file)){
			$cache_file = $this->cache_file;
		}
		$cache = $this->cache_dir.$cache_file.'.html';
		echo '<!-- cached file '.$cache.' on '.time().' -->';
		$cachecontent = ob_get_contents();
    ob_end_clean();
		file_put_contents($cache,$cachecontent);
		$ret = $this->get();
		if ($ret === false){
			die('Impossible de récupérer le cache !');
		}
	}
	
	/**
	* Efface le cache
	* @param string $cache_file Facultatif Nom de la page (efface tout le cache si vide)
	* 
	*/
	public function del($cache_file = null){
		if (!empty($cache_file)){
			if(is_file($cache_file))
		    unlink($cache_file); // delete file
		}else{
			$files = glob($this->cache_dir); // get all file names
			foreach($files as $file){ // iterate files
			  if(is_file($file))
			    unlink($file); // delete file
			}
		}
	}
}
?>