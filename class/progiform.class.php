<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';

class ProgiForm{

	public $maxfilesize = '';

	//public $wrapper = 0;
	//public $wrapper_class = '';

	public $error_fields = array();
	public $error_class = 'field-error';
	public $errors = array();

	public $db;
	public $form;

	/**
	 *	Constructor
	 *
	 *	@param 		DoliDB		$db			Database handler
	 * 	@param 		int			$socid	   	Id third party
	 * 	@param   	int			$userid    	Id user for filter
	 */
	public function __construct($db){
		$this->db = $db;
		$this->form = new Form($db);
		$this->maxfilesize = $this->fileUploadMaxSize();
	}

	/**
	 *	Return max file size
	 */
	private function fileUploadMaxSize(){

		global $conf;

		static $max_size = -1;
		if ($max_size < 0): $post_max_size = $this->parseSize(ini_get('post_max_size')); 
			if ($post_max_size > 0): $max_size = $post_max_size; endif; 
	  		$upload_max = $this->parseSize(ini_get('upload_max_filesize')); 
	  		if ($upload_max > 0 && $upload_max < $max_size):
	  			$max_size = $upload_max;
	  		endif;
	  	endif;
	  	if($conf->global->MAIN_UPLOAD_DOC < $max_size): return $conf->global->MAIN_UPLOAD_DOC;
	  	else: return $max_size; endif;
	}

	/**
	 *	Parse size
	 *
	 * 	@param 		int			$size	   	Size to parse
	 */
	private function parseSize($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);$size = preg_replace('/[^0-9\.]/', '', $size);
		if ($unit): return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		else: return round($size); endif;
	}

	/**********************************************************************************/

	/**
	 *	Ouvre un nouveau formulaire
	 *
	 *	@param 		string		$htmlname		Name and ID of form
	 * 	@param 		string		$action	   		Form action
	 * 	@param   	string		$method    		Form method
	 * 	@param   	bool		$accept_files   Multipart form
	 * 	@param   	string		$morecss   		Add classes to form
	 * 	@param   	string		$moreattr   	Add attributes to form
	 */
	public function form_open($htmlname,$action,$method = 'POST',$accept_files = 0,$morecss = '',$moreattr=''){

		$formopen = '<form action="'.$action.'" method="'.$method.'" name="'.$htmlname.'" id="'.$htmlname.'" class="'.$morecss.'" '.$moreattr;
		if($accept_files): $formopen.= ' enctype="multipart/form-data"'; endif;
		$formopen.= '>';

		return $formopen;
	}

	/**
	 *	Ferme un formulaire
	 */
	public function form_close(){
		return '</form>';
	}

	/**********************************************************************************/

	/**
	 *	Label
	 *
	 *	@param 		string		$label		Text of label
	 * 	@param 		string		$for		Input html name
	 * 	@param 		bool		$required	Add a * after label
	 * 	@param   	string		$morecss   	Add classes to label
	 */
	public function label($label,$for='',$required = 0,$morecss=''){

		global $langs;

		$html = '<label class="'.$morecss.'"';
		if(!empty($for)): $html.= ' for="'.$for.'"'; endif;
		$html.= '>'.$langs->transnoentities($label);
		if($required): $html.= ' <span class="required">*</span>'; endif;
		$html.= '</label>';

		return $html;
	}

	/**
	 *	Input type HIDDEN
	 *
	 *	@param 		string		$htmlname		Input name
	 * 	@param 		string		$value	   		Input value
	 */
	public function inputHidden($htmlname,$value){

		$html = '<input type="hidden" name="'.$htmlname.'" value="'.$value.'" >';
		return $html;
	}

	/**
	 *	Input type TEXT
	 *
	 *	@param 		string		$htmlname			Name and ID of input
	 * 	@param 		string		$selected_value		Value if GET/POST value is empty
	 * 	@param   	string		$morecss   			Add classes to input
	 * 	@param   	string		$moreattr   		Add attributes to input
	 */
	public function inputText($htmlname,$selected_value = '',$morecss = '',$moreattr = ''){

		$v = GETPOSTISSET($htmlname)?GETPOST($htmlname):$selected_value;

		if(in_array($htmlname, $this->error_fields)): $morecss = 'field-error '.$morecss; endif;

		$html = '<input type="text" class="'.$morecss.'" name="'.$htmlname.'" id="'.$htmlname.'" value="'.$v.'" '.$moreattr.'>';

		return $html;
	}

	/**
	 *	Input type NUMBER
	 *
	 *	@param 		string		$htmlname			Name and ID of input
	 *  @param 		string		$selected_value		Value if GET/POST value is empty
	 *	@param 		double		$min				Minimum value
	 *	@param 		double		$max				Maximum value
	 *	@param 		string		$step				Step of input (Any => step=1 but double value is possible)
	 *  @param   	string		$morecss   			Add classes to input
	 * 	@param   	string		$moreattr   		Add attributes to input
	 */
	public function inputNumber($htmlname,$selected_value = '',$min = '',$max = '',$step = 'any',$morecss = '',$moreattr=''){

		$v = GETPOSTISSET($htmlname)?GETPOST($htmlname):$selected_value;
		if(empty($v) && $min !== ''): $v = $min; endif;
		if(in_array($htmlname, $this->error_fields)): $morecss.= ' field-error'; endif;

		$html= '<input type="number" class="'.$morecss.'" name="'.$htmlname.'" id="'.$htmlname.'" step="'.$step.'" value="'.$v.'" '.$moreattr;
		if($min !== ''): $html.= ' min="'.$min.'"'; endif;
		if($max !== ''): $html.= ' max="'.$max.'"'; endif;
		$html.= '>';

		return $html;
	}

	
	/**
	 *	Input type CHECKBOX
	 *
	 *	@param 		string		$htmlname			Name and ID of input
	 *  @param 		bool		$is_checked			Default checked or not
	 *	@param 		string		$label				Label of checkbox
	 *	@param 		bool		$label_before		If label before checkbox
	 *  @param   	string		$morecss   			Add classes to input
	 * 	@param   	string		$moreattr   		Add attributes to input
	 */
	public function inputCheckbox($htmlname,$is_checked,$label = '',$label_before = 0,$morecss = '',$moreattr = ''){

		$checked = GETPOSTISSET($htmlname)?GETPOST($htmlname):$is_checked;

		$html = '';
		if($label && $label_before): $html.= '<label for="'.$htmlname.'">'.$label.'</label> '; endif;
		$html.= '<input type="checkbox" class="'.$morecss.'" name="'.$htmlname.'" id="'.$htmlname.'" '.$moreattr.' ';
		if($checked): $html.= 'checked'; endif;
		$html.= '>';
		if($label && !$label_before): $html.= ' <label for="'.$htmlname.'">'.$label.'</label>'; endif;

		return $html;
	}


	/**
	 *	Input type DATE
	 *
	 *	@param 		string		$htmlname			Name and ID of input
	 *  @param 		string		$selected_value		Value if GET/POST value is empty
	 *	@param 		date		$min				Minimum value
	 *	@param 		date		$max				Maximum value
	 *  @param   	string		$morecss   			Add classes to input
	 * 	@param   	string		$moreattr   		Add attributes to input
	 */
	public function inputDate($htmlname,$selected_value = '',$min = '',$max = '',$morecss = '',$moreattr = ''){

		$v = GETPOSTISSET($htmlname)?GETPOST($htmlname):$selected_value;

		if($min): $moreattr.= ' min="'.$min.'"'; endif;
		if($max): $moreattr.= ' max="'.$max.'"'; endif;
		if(in_array($htmlname, $this->error_fields)): $morecss.= ' field-error'; endif;		
		$html= '<input type="date" name="'.$htmlname.'" id="'.$htmlname.'" value="'.$v.'" class="'.$morecss.'" '.$moreattr.'>';

		return $html;
	}


	/**
	 *	Selects Time
	 *
	 *	@param 		string		$htmlname			Name and ID of input
	 *	@param 		string		$selected_h			Selected Hour
	 *	@param 		string		$selected_m			Selected Minutes
	 *  @param   	string		$morecss   			Add classes to input
	 */
	public function inputTime($htmlname,$selected_h = '',$selected_m = '',$morecss = '',$restricted_array = array()){

		$v_hour = GETPOSTISSET($htmlname.'_h')?GETPOST($htmlname.'_h'):$selected_h;
		$v_min = GETPOSTISSET($htmlname.'_m')?GETPOST($htmlname.'_m'):$selected_m;

		if(in_array($htmlname.'_h', $this->error_fields)): $morecss.= ' field-error'; endif;
		if(in_array($htmlname.'_m', $this->error_fields)): $morecss.= ' field-error'; endif;

		// HEURES
		$list_h = array();
		if(isset($restricted_array['hours'])):
			$min = isset($restricted_array['hours']['min'])?$restricted_array['hours']['min']:0;
			$max = isset($restricted_array['hours']['max'])?$restricted_array['hours']['max']:24;
			for ($i= $min; $i < $max + 1; $i++):
				if($i < 10) : $h = '0'.strval($i); else: $h = strval($i); endif;
				$list_h[$h] = $h;
			endfor;
		else:
			for ($i=0; $i < 24; $i++):
				if($i < 10) : $h = '0'.strval($i); else: $h = strval($i); endif;
				$list_h[$h] = $h;
			endfor;
		endif;

		// MINUTES
		$list_m = array();
		if(isset($restricted_array['minutes']) && $restricted_array['minutes'] == 'quarter'):
			$list_m = array('00'=>'00','15'=>'15','30'=>'30','45'=>'45');
		elseif(isset($restricted_array['minutes']) && $restricted_array['minutes'] == 'half'):
			$list_m = array('00'=>'00','30'=>'30');
		else:
			for ($i=0; $i < 60; $i++):
				if($i < 10) : $m = '0'.strval($i); else: $m = strval($i); endif;
				$list_m[$m] = $m;
			endfor;
		endif;

		$html = $this->form->selectarray($htmlname.'_h',$list_h,$v_hour,0,0,0,'',0,0,0,'','minwidth75 '.$morecss);
		$html.= $this->form->selectarray($htmlname.'_m',$list_m,$v_min,0,0,0,'',0,0,0,'','minwidth75 '.$morecss);
		
		return $html;
	}


	/**
	 *	TEXTAREA
	 *
	 *	@param 		string		$htmlname			Name and ID of textarea
	 * 	@param 		string		$selected_value		Value if GET/POST value is empty
	 * 	@param   	string		$morecss   			Add classes to textarea
	 * 	@param   	string		$moreattr   		Add attributes to textarea
	 */
	public function textarea($htmlname,$selected_value = '',$morecss = '',$moreattr=''){

		$v = GETPOSTISSET($htmlname)?GETPOST($htmlname):$selected_value;
		if(in_array($htmlname, $this->error_fields)): $morecss = 'field-error '.$morecss; endif;

		$html = '<textarea class="pgs-textarea '.$morecss.'" name="'.$htmlname.'" id="'.$htmlname.'" '.$moreattr.'>'.$v.'</textarea>';

		return $html;
	}


	/**
	 *	input type SUBMIT
	 *
	 *	@param 		string		$htmlname			Name and ID of submit
	 * 	@param 		string		$value				Submit value
	 * 	@param   	string		$cancel   			URL of cancel
	 * 	@param   	string		$morecss   			Add classes to submit
	 * 	@param   	string		$moreattr   		Add attributes to submit
	 */
	public function inputSubmit($htmlname = '',$value = '',$cancelurl = '',$cssclass = '',$moreattr = ''){

		global $langs;

		$html = '';
		if($cancelurl):
			$html.= '<a href="'.$cancelurl.'" class="dolpgs-btn btn-danger btn-sm" >'.$langs->trans('Cancel').'</a>';
		endif;
		$html.= '<input type="submit" name="'.$htmlname.'" class="dolpgs-btn btn-primary '.$cssclass.'" '.$moreattr.'';
		$html.= $value?' value="'.$value.'"':'';
		$html.= '>';

		return $html;
	}

	/**
	 *	Select Languages
	 *
	 *	@param 		string		$htmlname			Name and ID of select
	 * 	@param 		string		$selected_value		Value if GET/POST value is empty
	 */
	public function selectLanguage($htmlname,$selected_value = ''){

		$formadmin = new FormAdmin($this->db);
		return $formadmin->select_language($selected_value,$htmlname);
	}

	/**
	 *	Select2 for Array - Similar to $form->selectarray
	 *
	 *	@param 		string		$htmlname			Name and ID of select
	 *	@param 		array		$arrayvalues		Array of values
	 * 	@param 		string		$selected_value		Value if GET/POST value is empty
	 *  @param 		bool		$empty				Show empty value
	 *  @param   	string		$morecss   			Add classes to submit
	 *  @param   	string		$icon   			Icon class
	 *  @param   	string		$inline_label   	Inline label after select
	 *  @param   	bool		$value_as_key   	Use value as key
	 */
	public function select2($htmlname,$arrayvalues,$selected_value = '',$empty = false,$morecss = '',$icon = '',$inline_label = '',$value_as_key = 0,$moreattr = '',$translate = 0){

		$selected_value = GETPOSTISSET($htmlname)?GETPOST($htmlname):$selected_value;
		if(in_array($htmlname, $this->error_fields)): $morecss = 'field-error '.$morecss; endif;

		$html = '';

		if($icon):
			$html.= '<i class="'.$icon.'" style="color: #6c6aa8;margin-right:3px"></i>';
		endif;
		if($inline_label):
			$html.= '<label for="'.$htmlname.'" class="dolpgs-semibold">'.$inline_label.'</label>';
		endif;
		$html.= $this->form->selectarray($htmlname,$arrayvalues,$selected_value,$empty,0,$value_as_key,$moreattr,$translate,0,0,'',$morecss);

		return $html;
	}

	/**
	 *	Select projects
	 *
	 *	@param 		string		$htmlname			Name and ID of textarea
	 * 	@param 		string		$selected_value		Value if GET/POST value is empty
	 * 	@param 		bool		$empty				Show empty value
	 * 	@param 		bool		$discard_closed		Show closed projects
	 * 	@param   	string		$morecss   			Add classes to submit
	 */
	public function selectProjets($htmlname,$selected_value = '',$empty = false,$discard_closed = 1,$morecss = ''){

		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

		$fp = new FormProjets($this->db);
		return $fp->select_projects(-1,$selected_value,$htmlname,24,0,$empty,$discard_closed,0,0,0,'',1,0,$morecss);
	}


}

?>