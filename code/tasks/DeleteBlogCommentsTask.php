<?php


/**
 * Provides support for the comment module when running the delete blog task.
 *
 * @package bloggerimport
 * @subpackage wordpress
 *
 * @author Michael Strong <michael@daykinandstorey.co.uk>
**/
class DeleteBlogCommentsTask extends Extension {


	/**
	 * Hooks into the run process of {@link DeleteBlogPostsTask}
	**/
	public function updateDeleteBlogPosts() {
		$this->owner->count["Comment"] = 0;

		if(count($this->owner->blogPostIds)) {
			$classes = ClassInfo::subClassesFor("BlogPost");
			$comments = Comment::get()->filter(array(
				"BaseClass" => $classes,
				"ParentID" => $this->owner->blogPostIds
			));

			if($comments->count() > 0) {
				foreach($comments as $comment) {
					$comment->delete();
				}
			}
		}
	}

}