<?php
	define('ENTRIES_DIRECTORY', 'contents/entries');

	function getEntries($type = Null, $limit = Null) {
		$entries = array();

		$dir = opendir(ENTRIES_DIRECTORY);

		$count = 0;
		while($entryFile = readdir($dir)) {

			if($entryFile{0} == '.') //Dont show hidden files
				continue;

			$entryName = str_replace('.php', Null, $entryFile);
			$entry = getEntry($entryName);

			if($type && $entry->type != $type) {
				continue;
			}

			$entries[$entry->date] = $entry;
			$count++;
		}

		krsort($entries);

		if($limit)
			$entries = array_slice($entries, 0, $limit, True);

		return $entries;
	}

	function getEntry($id) {
		$dom = New DOMDocument;
		$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'.file_get_contents(ENTRIES_DIRECTORY.'/'.$id.'.php'));

		$xpath = New DOMXPath($dom);

		
		$entry = New stdClass;
		$entry->id = $id;

		foreach($xpath->query('//*[@class]') as $element) {
			$attributeName = $element->attributes->getNamedItem('class')->value;

			if($element->childNodes->length > 1) {
				$childs = Array();

				foreach($element->childNodes as $childNode)
					if(strlen(trim($childNode->textContent)))
						$childs[] = trim($childNode->textContent);

				$entry->$attributeName = $childs;
			} else
				if(strlen($element->textContent))
					$entry->$attributeName = trim($element->textContent);
		}

		return $entry;
	}

	function getNextSession() {
		$last = current(getEntries('Session', 1));
		$now = time();

		$timestamp = strtotime($last->date);
		if($timestamp >= $now)
			return $last;
	}

	function toPersian($num) {
		return str_replace(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0), array('۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '۰'), $num);
	}

	function filenameToId($fileName) {
		return str_replace(array('contents/', '.php'), Null, $fileName);
	}
?>