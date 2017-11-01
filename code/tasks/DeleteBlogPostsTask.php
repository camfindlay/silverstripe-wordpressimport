<?php

/**
 * Responsible for deleting blog posts, tags, categories and associated filed.
 *
 * @package blogimporter
 * @subpackage wordpress
 *
 * @author Michael Strong <micmania@hotmail.co.uk>
**/
class DeleteBlogPostsTask extends BuildTask {

	protected $title = 'Delete Blog Task';

	protected $description = 'Deletes all blog posts and attachments.';

	protected $blogPostIds = array();

	protected $imageIds = array();

	protected $count = array();

	function init() {
		parent::init();

		if (!Permission::check('ADMIN')) {
			return Security::permissionFailure($this);
		}
	}

	public function run($request) {

		// Setup counters for return data.
		$this->count["BlogCategory"] = 0;
		$this->count["BlogTag"] = 0;
		$this->count["BlogPost"] = 0;
		$this->count["File"] = 0;

		// List the blog post IDs
		$posts = Versioned::get_by_stage("BlogPost", "Stage");
		if($posts->count() > 0) {
			$this->blogPostIds = $posts->map("ID", "ID")->toArray();
			$this->imageIds = $posts->map("FeaturedImageID", "FeaturedImageID")->toArray();
		}

		// Remove Categories
		$categories = BlogCategory::get();
		foreach($categories as $category) {
			if($request->getVar("verbose")) {
				Debug::message($category->Title . " deleted.");
			}
			$this->count["BlogCategory"]++;
			$category->delete();
		}

		// Remove Tags
		$tags = BlogTag::get();
		foreach($tags as $tag) {
			if($request->getVar("verbose")) {
				Debug::message($tag->Title . " deleted.");
			}
			$this->count["BlogTag"]++;
			$tag->delete();
		}

		// Remove Posts
		if($posts->count() > 0) {
			foreach($posts as $post) {
				if($request->getVar("verbode")) {
					Debug::message($post->Title . ' deleted');
				}
				$post->deleteFromStage("Live");
				$post->delete();
				$this->count['BlogPost']++;
			}
		}

		$this->extend("updateDeleteBlogPosts");

		foreach($this->count as $class => $count) {
			Debug::message($count . ' ' . singleton($class)->i18n_plural_name() . ' deleted.');
		}
	}

}