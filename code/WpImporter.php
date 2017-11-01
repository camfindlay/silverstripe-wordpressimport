<?php
require('WpParser.php');

/*
 * Decorates a Blog page type, specified in _config
 */

class WpImporter extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        $html_str = '<iframe name="WpImport" src="WpImporter_Controller/index/' . $this->owner->ID . '" width="500"> </iframe>';
        $fields->addFieldToTab('Root.Import', LiteralField::create("ImportIframe", $html_str));
    }
}

class WpImporter_Controller extends Controller
{
    private static $allowed_actions = array(
        'index',
        'UploadForm',
        'doUpload'
    );

    public function init()
    {
        parent::init();

        // Do security check in case this controller is called by unauthorised user using direct url
        if (!Permission::check("ADMIN")) {
            Security::permissionFailure();
        }

        // Check for requirements
        if (!class_exists('Blog')) {
            user_error('Please install the blog module before importing from Wordpress', E_USER_ERROR);
        }
    }

    public function index($request)
    {
        return $this->renderWith('WpImporter');
    }

    protected function getBlogID()
    {
        if (isset($_REQUEST['BlogID'])) {
            return $_REQUEST['BlogID'];
        }

        return $this->request->param('ID');
    }

    /*
     * Outputs an file upload form
     */

    public function UploadForm()
    {
        return Form::create($this, "UploadForm",
                        FieldList::create(
                            FileField::create("XMLFile", 'Wordpress XML file'),
                            HiddenField::create("BlogID", '', $this->getBlogID())
                        ),
                        FieldList::create(
                            FormAction::create('doUpload', 'Import Wordpress XML file')
                        )
        );
    }

    protected function getOrCreateComment($wordpressID)
    {
        if ($wordpressID && $comment = Comment::get()->filter(array('WordpressID' => $wordpressID))->first()) {
            return $comment;
        }

        return Comment::create();
    }

    protected function importComments($post, $entry)
    {
        if (!class_exists('Comment')) {
            return;
        }

        $comments = $post['Comments'];
        foreach ($comments as $comment) {
            $page_comment = $this->getOrCreateComment($comment['WordpressID']);
            $page_comment->update($comment);
            $page_comment->ParentID = $entry->ID;
            $page_comment->write();
        }
    }

    protected function getOrCreatePost($wordpressID)
    {
        if ($wordpressID && $post = BlogPost::get()->filter(array('WordpressID' => $wordpressID))->first()) {
            return $post;
        }

        return BlogPost::create();
    }

    protected function importPost($post)
    {
        // create a blog entry
        $entry = $this->getOrCreatePost($post['WordpressID']);

        $entry->ParentID = $this->getBlogID();

        // $posts array and $entry have the same key/field names
        // so we can use update here.
        $entry->update($post);

        // write this post to the database before modifying the list of authors
        // because of the onBeforeWrite logic in BlogPost->onBeforeWrite
        // this will use the current logged in user as the author of this post
        // but we will override that below
        $entry->write();

        //Assign the author as a Member object
        //TODO deal with XML that doesn't define authors
        if($author = Member::get()->filter(array('WordpressAuthorLogin' => $post['AuthorLogin']))->first()) {
            //We need to remove the default admin author assigned when the BlogPost object gets created with no data
            //TODO this is not acting as expected default admin still be added.
            $entry->Authors()->RemoveAll();
            $entry->Authors()->add($author);
        }

        //Create and attach tags
        foreach($post['Tags'] as $tag){

            //check if it already exists
            if(!$blogtag = BlogTag::get()->filter(array('Title' => $tag))->first()){
                $blogtag = BlogTag::create();
                $blogtag->Title = $tag;
                $blogtag->BlogID = $entry->ParentID;
                $blogtag->write();
            }
            $entry->Tags()->add($blogtag);

        }

        //Create and attach categories
        foreach($post['Categories'] as $category){

            //check if it already exists
            if(!$blogcategory = BlogCategory::get()->filter(array('Title' => $category))->first()){
                $blogcategory = BlogCategory::create();
                $blogcategory->Title = $category;
                $blogcategory->BlogID = $entry->ParentID;
                $blogcategory->write();
            }
            $entry->Categories()->add($blogcategory);

        }

        $entry->write();
        if ($post['IsPublished']) {
            $entry->publish("Stage", "Live");
        }

        $this->importComments($post, $entry);

        return $entry;
    }

    protected function importAuthor($author)
    {

        //check if a user already exists with this email address if they do get the object and add the wp username
        if(!$member = Member::get()->filter(array('Email' => $author['Email']))->first())
        {

           $member = Member::create();

           $member->FirstName = $author['FirstName'];
           $member->Email = $author['Email'];
        }

        $member->WordpressAuthorLogin = $author['Login'];
        $member->write();

    }

    public function doUpload($data, $form)
    {

        // Checks if a file is uploaded
        if (!is_uploaded_file($_FILES['XMLFile']['tmp_name'])) {
            return;
        }

        echo '<p>Processing...<br/></p>';
        flush();
        $file = $_FILES['XMLFile'];
        // check file type. only xml file is allowed
        if ($file['type'] != 'text/xml') {
            echo 'Please select Wordpress XML file';
            die;
        }

        $wp = new WpParser($file['tmp_name']);

        //Parse authors
        $authors = $wp->parseauthors();
        foreach($authors as $author){
            $this->importAuthor($author);
        }

        // Parse posts
        $posts = $wp->parseposts();

        foreach ($posts as $post) {

            $this->importPost($post);
        }

        // delete the temporaray uploaded file
        unlink($file['tmp_name']);

        // print sucess message
        echo 'Complete!<br/>';
        echo 'Please refresh the admin page to see the new blog entries.';
    }
}
