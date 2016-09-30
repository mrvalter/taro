<?php

function replaceEngToRusChars($string)
{	
	$replacementArrEng = [
		'o','a','e','x','c','p','O',
		'A','T','M','B','C','X','H',		
		'P','E',
	];
	$replacementArrRus = [
		'о','а','е','х','с','р','О',
		'А','Т','М','В','С','Х','Н',
		'Р','Е',
	];
	
	foreach($replacementArrEng as $i=>$engSimb){
		$string = preg_replace('/['.$engSimb.']/u', $replacementArrRus[$i], $string);
	}
	return $string;
	
}

function translit($st)
{
	 // Сначала заменяем "односимвольные" фонемы.
    $st = mb_str_replace(
		['а','б','в','г','д','е','ё','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ь','ы','э'],
		['a','b','v','g','d','e','e','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h',"'",'i','e'], $st);
    
	$st = mb_str_replace(
		['А','Б','В','Г','Д','Е','Ё','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ь','Ы','Э'],
		['A','B','V','G','D','E','E','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H', "'", 'I','E'], $st);
    
    $st= mb_str_replace(		
			['ё','ж','ц','ч','ш','щ','ю','я','Ё','Ж','Ц','Ч','Ш','Щ','Ю','Я'],
			['yo','zh','tc','ch','sh','sh','yu','ya','YO','ZH','TC','CH','SH','SH','YU','YA'],
		$st
	);
    
    return $st;
}


if (!function_exists('mb_str_replace')) {
	function mb_str_replace($search, $replace, $subject, &$count = 0) {
		if (!is_array($subject)) {
			// Normalize $search and $replace so they are both arrays of the same length
			$searches = is_array($search) ? array_values($search) : array($search);
			$replacements = is_array($replace) ? array_values($replace) : array($replace);
			$replacements = array_pad($replacements, count($searches), '');
			foreach ($searches as $key => $search) {
				$parts = mb_split(preg_quote($search), $subject);
				$count += count($parts) - 1;
				$subject = implode($replacements[$key], $parts);
			}
		} else {
			// Call mb_str_replace for each subject in array, recursively
			foreach ($subject as $key => $value) {
				$subject[$key] = mb_str_replace($search, $replace, $value, $count);
			}
		}
		return $subject;
	}
}

function generatePassword($len=8)
{
	## Create Unicode password 			
	$simbols = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmlnpqrstuvwxyz";
	$pwdtxt = '';
	for($i=0; $i<$len; $i++){
		$pwdtxt .= substr($simbols, rand(0, strlen($simbols)-1), 1);
	}
	
	return $pwdtxt;
}

function getMonthesRus()
{
	return [
		'Январь',
		'Февраль',
		'Март',
		'Апрель',
		'Май',
		'Июнь',
		'Июль',
		'Август',
		'Сентябрь',
		'Октябрь',
		'Ноябрь',
		'Декабрь'		
	];
}

function vd ($text)
{
    printf("<pre>%s</pre>", print_r($text, true));
}

