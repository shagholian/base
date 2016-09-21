<?php
namespace packages\base;
use \packages\base\http;
use \packages\base\options;
use \packages\base\IO;
function url($page = '',$parameters = array(), $absolute = false){
	$page = IO\removeLastSlash($page);
	$url = '';
	if($absolute){
		$hostname = http::$request['hostname'];
		$www = options::get('packages.base.routing.www');
		if($www == 'nowww'){
			if(substr($hostname, 0, 4) == 'www.'){
				$hostname = substr($hostname, 4);
			}
		}elseif($www == 'withwww'){
			if(substr($hostname, 0, 4) != 'www.'){
				$hostname = 'www.'.$hostname;
			}
		}
		$url .= http::$request['scheme'].'://'.$hostname;
	}

	$changelang = options::get('packages.base.translator.changelang');
	$type = options::get('packages.base.translator.changelang.type');
	if($changelang == 'uri'){
		$lang = '';
		if(isset($parameters['lang'])){
			$lang = $parameters['lang'];
			unset($parameters['lang']);
		}else{
			if($type == 'short'){
				$lang = translator::getShortCodeLang();
			}elseif($type == 'complete'){
				$lang = translator::getCodeLang();
			}
		}
		if(!$page){
			if(strlen($lang) == 2){
				if($lang != translator::getDefaultShortLang()){
					$url .= '/'.$lang;
				}
			}elseif($lang and $lang != translator::getDefaultLang()){
				$url .= '/'.$lang;
			}
		}elseif($lang){
			$url .= '/'.$lang;
		}
	}elseif($changelang == 'parameter'){
		if(!isset($parameters['lang'])){
			if($type == 'short'){
				$parameters['lang'] = translator::getShortCodeLang();
			}elseif($type == 'complete'){
				$parameters['lang'] = translator::getCodeLang();
			}
		}
	}
	if($page){
		$url .= '/'.$page;
	}
	if(!$url){
		$url .= '/';
	}
	if(is_array($parameters) and $parameters){
		$url .= '?'.http_build_query($parameters);
	}
	return $url;
}
