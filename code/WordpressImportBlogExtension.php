<?php

/**
 * Decorates a BlogHolder page type, specified in _config.php
 *
 * @package blogimport
 * @subpackage wordpress
 *
 * @author Michael Strong <micmania@hotmail.co.uk>
**/
class WordpressImportBlogExtension extends DataExtension {

	function updateCMSFields(FieldList $fields) {
		$fields->addFieldsToTab("Root.Import", WordpressImportField::create("WordpressFile", "WordPress File (XML)"));
		return $fields;
	}

}
