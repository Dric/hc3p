<?php
/**
* Classe contactform
* @package Contact Form
*  
* Gère un formulaire de contact
*/

/**
* Formulaire de contact
* @package Contact Form
*/
Class contactform{
	/** @var array $dests tableau regroupant la liste des destinataires */
	public $dests = array();
	
	/** @var string $copyto Destinataire en copie */
	public $copyto = null;
	
	/** @var bool $antispam Activer ou non l'antispam basique */
	public $antispam = true;
	
	/**
	* Formulaire de contact
	* @var array $dest Liste des destinataires (libellé du destinataire => Adresse email)
	* 
	*/
	function __construct($dests, $copyto = null){
		$this->dests = $dests;
		$this->copyto = $copyto;
	}
	
	/**
	* Envoi du mail
	* @param string $dest Destinataire
	* @param string $exp_name Nom de l'expéditeur
	* @param string $exp_email Adresse email de l'expéditeur
	* @param string $message Corps de l'email
	* @param string $antispam_check Si renseigné, nous avons affaire à un spam !
	* @return array (bool Succès ou non de l\'opération|string Message qui accompagne le résultat) 
	* 
*/
	public function send_mail($dest, $exp_name, $exp_email, $message, $antispam_check = null){
		$OK = false;
		$ret_mess = null;
		if (!empty($antispam_check)){
			die('NO SPAM ALLOWED (how surprising isn\'t it ?)');
		}
		if (empty($dest) or empty($exp_name) or empty($exp_email) or empty($message)){
			$OK = false;
			$ret_mess = 'Au moins un des champs requis est vide !';
		}else{
			$dest = $this->dests[htmlentities($dest)];
			$exp_name = htmlentities($exp_name);
			if (!$this->validEmail($exp_email)){
				$OK = false;
				$ret_mess = 'L\'adresse email n\'est pas valide';
			}else{
				$message = htmlentities($message);
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=utf-8";
				$headers[] = "From: ".$exp_name." <".$exp_email.">";
				if (!empty($this->copyto)){
					$headers[] = "Bcc: <".$this->copyto.">";
				}
				$headers[] = "Reply-To: ".$exp_name." <".$exp_email.">";
				$headers[] = "Subject: {Mail provenant du site de Haut Comme 3 Pommes}";
				$headers[] = "X-Mailer: PHP/".phpversion();
				$ret = mail($dest, 'Mail provenant du site de Haut Comme 3 Pommes', $message, implode("\r\n", $headers));
				$OK = $ret;
				if (!$ret){
					$ret_mess = 'L\'email n\'a pas pu être envoyé !';
				} else{
					$ret_mess = 'Email envoyé !';
				}
			}
		}
		return array($OK, $ret_mess);
	}
	
	/**
	* Validate an email address.
	* 
	* @param string $email Adresse email à vérifier
	* @return bool
	*/
	private function validEmail($email){
	  if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
	    return false;
	  }else{
	    return true;
	  }
	}

	/**
	* Affichage du formulaire de contact
	* 
	*/
	public function display(){
		?>
		<form action="" method="POST">
			<div class="form-group">
		    <label for="dest">Contacter <span title="Obligatoire" class="required tooltip-bottom">*</span></label>
	      <select name="dest" id="dest" class="form-control">
					<?php if (count($this->dests) > 1){ ?>
					<option></option>
					<?php
					}
					foreach ($this->dests as $label=>$email){
						echo '<option value="'.$label.'">'.$label.'</option>';
					}
					?>
				</select>
			</div>
	    <div class="form-group">
	    	<label for="email">Votre addresse Email <span title="Obligatoire" class="required tooltip-bottom">*</span></label>
	    	<input type="text" name="email" id="email" placeholder="Email" class="form-control">
	    </div>
	    <div class="form-group">
	    	<label for="name">Votre Nom/Pseudo <span title="Obligatoire" class="required tooltip-bottom">*</span></label>
	    	<input type="text" name="name" id="name" placeholder="Nom/Pseudo" class="form-control">
	    </div>
			<?php if ($this->antispam){ ?>
			<div class="hide form-group">
				<label for="subject">Sujet</label>
	    	<input type="text" name="subject" id="subject" placeholder="Sujet" class="form-control">
			</div>
			<?php } ?>
	    <div class="form-group">
	    	<label for="message">Votre Message <span title="Obligatoire" class="required tooltip-bottom">*</span></label>
	    	<textarea name="message" id="message" placeholder="Message" rows="6" class="form-control"></textarea>
			</div>
			<div class="help span4 small">
				<a class="help-summary" title="Afficher les détails" data-toggle="collapse" data-target="#contact-help-details"><i class="glyphicon glyphicon-question-sign"></i> Utilisation du formulaire de contact</a>
				<div id="contact-help-details" class="collapse">
					Il y a quelques règles évidentes et usuelles à respecter pour vous servir de ce formulaire de contact :
					<ul>
						<li>Vérifiez que votre français est compréhensible et ne contrevient pas trop aux normes orthographiques et grammaticales en vigueur.</li>
						<li>Si vous souhaitez utiliser ce formulaire pour envoyer du spam, abstenez-vous. Vous perdez votre temps et le nôtre.</li>
						<li>Les astérisques signalent que le champ est obligatoire pour que votre message soit envoyé.</li>
						<li>Nous essaierons de vous répondre dans les plus brefs délais, mais n'hésitez pas à relancer si vous ne recevez pas de réponses au bout d'une semaine.</li>
					</ul>
				</div>
			</div>
			<div class="span1 text-right"><button type="submit" class="btn btn-lg" id="contact_send" name="contact_send">Envoyer</button></div>
			
		</form>
		<?php
	}
}
?>