# Bugs’ Nette Skeleton
- author:		Bugs Bunny
- date:			2015/02/16

## Libraries (see bower.js and composer.js)
- Nette			~2.2.0
  - http://nette.org
- Foundation	~5.4
  - http://foundation.zurb.com/
- tinyMCE		~4.0
	- http://www.tinymce.com/

## Features
- force IE edge
- HTML 5 valid
- IE only CSS
- IE8- only CSS
- ...

## Concept
- Different types of Presenters
  - Homepage
    - bootstrap components example
  - Editable
    - whole page editable in each language using tinyMCE
    - table 'pages' with columns 'presenter', 'lang' and 'contents'
  - Users
    - management of users
    - table 'users'
  - Events (example)
    - displaying records from db for each language
    - common privileges for all resources
    - table 'events' with column 'lang'
  - News (example)
    - like Events, but with picture
    - table 'news'
  - Files (example)
    - displaying records from db
    - individual privileges for each resource
    - tables 'files' and 'files_users'
  - Forum (example)
    - simple forum with posts and comments
    - table 'forum'
  - Photogallery
    - basic photogallery with albums and photos
    - tables 'albums' and 'photos'
  - Langs (example)
    - template is in corresponding language folder
    - no dynamic data from db, only possible use of Translator

- Multilingual support (Translator)
- ACL
- User management
- ...

## Before You Begin
- edit /db.sql (rename database name)
- edit /app/config/config.neon and config.local.neon (change database name)
- edit /.htaccess (set RewriteBase if needed)
- edit /app/router/RouterFactory.php (add/remove supported languages)
- edit /app/model/Authorizator.php (see *** EDIT HERE *** comments)
- edit /app/presenters/BugsBasePresenter.php (see *** EDIT HERE *** comments)
- edit /app/presenters/UsersPresenter.php (see *** EDIT HERE *** comments)
- use /app/model/UserManager.php to create new admin users (see *** USE THIS *** comment)
-

## TODO
- fix MyString::truncate
- custom better form renderer compatible with Foundation
- fix set_locale (date formats etc.)
- change alert() calls for something async
- ...
