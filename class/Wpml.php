<?php
namespace App;

/**	
 * Custom WPML plugin config
 */
class Wpml {

	//Custom language selector
	static function languageSelector(): string
	{
		$languages = icl_get_languages('skip_missing=0');
		$content = '';
	
		foreach($languages as $key => $value):
			if($value['active']):
				$current = $value;
				break;
			endif;
		endforeach;
	
		if (!empty($languages)) :
			$content = '<span>' . $current['language_code'] . '</span>';
			foreach ($languages as $key => $value) :
				if ($value['code'] != $current['code']) :
					$content .= '<a href="'.$value['url'].'">'.$value['language_code'].'</a>';
				endif;
			endforeach;
		endif;

		return $content;
	}
}
