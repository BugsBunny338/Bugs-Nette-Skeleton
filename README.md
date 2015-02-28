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
- TODO
- force IE edge
- HTML 5 valid
- IE only CSS
- IE8- only CSS

## Concept
- Different types of Presenters
  - Homepage
    - whole page editable in each language using tinyMCE
    - table 'pages' with columns 'presenter', 'lang' and 'contents'
  - Users
    - management of users
    - table 'users'
  - Events (example)
    - displaying records from db for each language
    - common privileges for all resources
    - table 'events' with column 'lang'
  - Files (example)
    - displaying records from db
    - individual privileges for each resource
    - tables 'files' and 'files_users'
  - Langs (example)
    - template is in corresponding language folder
    - no dynamic data from db, only possible use of Translator

- Multilingual support (Translator)
- ACL
- User management
- …

## Before You Begin
- edit .htaccess (set RewriteBase)
- edit RouterFactory (supported languages)
- edit Authorizator
- edit BasePresenter
- use UserMangaer.php to create new users
- edit User.php

## TODO
- fix MyString::truncate
- custom form renderer compatible with Foundation
- fix set_locale (date formats etc.)
- 
