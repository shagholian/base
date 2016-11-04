<?php
namespace packages\base;
use \packages\base\frontend\theme;
use \packages\base\frontend\location;
use \packages\base\frontend\source;
use \packages\base\view\error;
class view{
	protected $title = array();
	protected $description;
	protected $file;
	protected $source;
	protected $css = array();
	protected $js = array();
	protected $data = array();
	protected $errors = array();
	public function setTitle($title){
		if(is_array($title)){
			$this->title = $title;
			return true;
		}elseif(is_string($title)){
			return $this->setTitle(array($title));
		}
		return false;
	}
	public function getTitle($spliter = ' | '){
		return $spliter ? implode($spliter, $this->title) : $title;
	}
	public function setDescription($description){
		$this->description = $description;
	}
	public function getDescription(){
		return $this->description;
	}
	public function addCSS($code, $name = ''){
		$this->css[] = array(
			'name' => $name,
			'type' => 'inline',
			'code' => $code
		);
	}
	public function addCSSFile($file,$name =''){
		if($name == ''){
			$name = $file;
		}
		$this->css[] = array(
			'name' => $name,
			'type' => 'file',
			'file' => $file
		);
	}
	public function removeCSS($name){
		foreach($this->css as $key=> $css){
			if($css['name'] == $name){
				unset($this->css[$key]);
				return;
			}
		}
	}
	protected function loadCSS(){
		foreach($this->css as $css){
			if($css['type'] == 'file'){
				echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"{$css['file']}\" />\n");
			}
		}
		foreach($this->css as $css){
			if($css['type'] == 'inline'){
				echo("<style>\n{$css['code']}\n</style>\n");
			}
		}
	}
	public function addJS($code, $name = ''){
		$this->js[] = array(
			'name' => $name,
			'type' => 'inline',
			'code' => $code
		);
	}
	public function addJSFile($file,$name =''){
		if($name == ''){
			$name = $file;
		}
		$this->js[] = array(
			'name' => $name,
			'type' => 'file',
			'file' => $file
		);
	}
	public function removeJS($name){
		foreach($this->js as $key=> $js){
			if($js['name'] == $name){
				unset($this->js[$key]);
				return;
			}
		}
	}
	protected function loadJS(){
		foreach($this->js as $js){
			if($js['type'] == 'file'){
				echo("<script src=\"{$js['file']}\"></script>\n");
			}
		}
		foreach($this->js as $js){
			if($js['type'] == 'inline'){
				echo("<script>\n{$js['code']}\n</script>\n");
			}
		}
	}
	public function setSource(source $source){
		$this->source = $source;
		theme::setPrimarySource($this->source);
		$sources = theme::byName($this->source->getName());
		foreach($sources as $source){
			$assets = $source->getAssets();
			foreach($assets as $asset){
				if($asset['type'] == 'css'){
					if(isset($asset['file'])){
						$this->addCSSFile($source->url($asset['file']), isset($asset['name']) ? $asset['name'] : '');
					}elseif(isset($asset['inline'])){
						$this->addCSS($asset['inline'], isset($asset['name']) ? $asset['name'] : '');
					}
				}elseif($asset['type'] == 'js'){
					if(isset($asset['file'])){
						$this->addJSFile($source->url($asset['file']), isset($asset['name']) ? $asset['name'] : '');
					}elseif(isset($asset['inline'])){
						$this->addJS($asset['inline'], isset($asset['name']) ? $asset['name'] : '');
					}
				}
			}
		}

	}
	public function setFile($file){
		$this->file = $file;
	}
	static public function byName($viewName){
		$location = theme::locate($viewName);
		if($location instanceof location){
			$sources = theme::byName($location->source->getName());
			foreach($sources as $source){
				$source->register_translates(translator::getCodeLang());
			}
			$view = new $location->view();
			$view->setSource($location->source);
			if($location->file){
				$view->setFile($location->file);
			}
			return $view;
		}
		return false;
	}
	public function setData($data, $key = null){
		if($key){
			$this->data[$key] = $data;
		}else{
			$this->data = $data;
		}
	}
	public function getData($key = null){
		if($key){
			return(isset($this->data[$key]) ? $this->data[$key] : false);
		}else{
			return $this->data;
		}
	}
	public function output(){
		if($this->file){
			theme::loadViews();
			if(method_exists($this, '__beforeLoad')){
				$this->__beforeLoad();
			}
			$path = $this->source->getPath()."/".$this->file;
			require_once($path);
		}
	}
	public function addError(error $error){
		$this->errors[] = $error;
	}
	public function getError(){
		return($this->errors ? $this->errors[0] : null);
	}
	public function getErrors(){
		return $this->errors;
	}
}
