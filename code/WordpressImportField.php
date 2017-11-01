<?php


/**
 * An upload fields which handles importing a wordpress xml file
 * into silverstripe-blogger.
 *
 * @package silverstripe
 * @subpackage bloggerwpimport
 *
 * @author Michael Strong <micmania@hotmail.co.uk>
**/
class WordpressImportField extends UploadField {
	
	private static $allowed_actions = array(
		"evaluate",
		"import",
	);

	/**
	 * Sets whether this is a dry run or the real thing.
	 *
	 * @var boolean
	**/
	protected $dryRun = false;



	/**
	 * Set a custom folder by default and set our buttons template.
	 *
	 * @param $name string - field name
	 * @param $title string - field title
	 * @param $items SS_List
	**/
	public function __construct($name, $title = null, SS_List $items = null) {
		$this->setFolderName("wordpress");
		$this->templateFileButtons = "WordpressImportField_FileButtons";
		parent::__construct($name, $title, $items);
	}



	/**
	 * Render our custom field.
	 *
	 * @param $properties array
	 *
	 * @return string
	**/
	public function Field($properties = array()) {
		Requirements::javascript("wordpressimport/js/WordpressImportField.js");
		$this->setConfig("urlEvaluate", $this->link("evaluate"));
		$this->setConfig("urlImport", $this->link("import"));
		$this->setOverwriteWarning(false);
		$validator = $this->getValidator();
		if($validator) {
			$validator->setAllowedExtensions(array("xml"));
		}
		return parent::Field($properties);
	}



	/**
	 * Replace the file actions with our custom actions.
	 *
	 * @param $file File
	 *
	 * @return FieldList
	**/
	public function getFileEditActions(File $file) {
		$fields = FieldList::create(
			FormAction::create("evaluate", "Evaluate"),
			FormAction::create("import", "Import")
		);
		return $fields;
	}



	/**
	 * Evaluates the file and returns what the user is about to import.
	 *
	 * @param $request
	 *
	 * @return SS_HTTPResponse
	**/
	public function evaluate(SS_HTTRequest $request) {
		$this->dryRun = true;
		return $this->import($request);
	}



	/**
	 * Creates an instance of the wordpress importer and initiates the import.
	 * If $this->dryRun is true, then nothing will be imported.
	 *
	 * @param $request
	 *
	 * @return SS_HTTPResponse
	**/
	public function import(SS_HTTPRequest $request) {
		$page_id = $this->getPageID();
		$file_id = $request->getVar("file");

		$response = new SS_HTTPResponse();
		if(Permission::check("CMS_ACCESS_CMSMain")) {
			$file = File::get()->byId($file_id);
			if(!$file) {
				return $this->httpError(404, "Not Found");
			}
			$parser = Injector::inst()->create("WordpressImporter");
			$parser->setFile($file->Filename);
			if($this->isDryRun()) {
				$created = $parser->dryRun();
			} else {
				$created = $parser->import();
			}

			// Prepare the response
			$response->addHeader("Content-Type", "application/json");
			$response->setBody(json_encode($created));
			return $response;
		} else {
			$response->httpError(403, "Forbidden");
		}
		return $response;
	}



	/**
	 * Get the current page id.
	 *
	 * @return int
	**/
	public function getPageID() {
		return (int) Session::get("CMSMain.currentPage");
	}



	/**
	 * Checks whether we're currently processing a dry run.
	 *
	 * @return boolean
	**/
	public function isDryRun() {
		return (boolean) $this->dryRun;
	}

}