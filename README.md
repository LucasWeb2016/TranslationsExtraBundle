# Lucasweb\TranslationsExtraBundle

[![Symfony](https://img.shields.io/badge/Symfony-4-green.svg)](https://symfony.com/) [![License](https://img.shields.io/badge/License-MIT-orange.svg)](https://github.com/LucasWeb2016/TranslationsExtraBundle/blob/master/LICENSE) [![Stable](https://img.shields.io/badge/Stable-1.0.2-blue.svg)](https://github.com/LucasWeb2016/TranslationsExtraBundle/releases/tag/1.0) [![Stable](https://img.shields.io/badge/Unstable-devmaster-red.svg)](https://github.com/LucasWeb2016/TranslationsExtraBundle)

[![knpbundles.com](http://knpbundles.com/LucasWeb2016/TranslationsExtraBundle/badge-short)](http://knpbundles.com/LucasWeb2016/TranslationsExtraBundle)

**Bundle with extra developer tools for Symfony Framework**

This Bundle adds extra commands to Symfony for easy managing translations. With Yandex Translate API support for automatic translations. 

 - Add a new translation message
 - Edit a translation message
 - Search in translation files
 - Remove a translation message
 - Get info from a translation message
 - Check translation files
 - Sync translation files 
 - Create translation files
 - Import translation files
 - Automatic translate with Yandex Translate API 
 
## Why create this Bundle?
 Honestly, I hate having to change tab when I need to create a new message while I'm working on a Twig template or a controller. 
  
  The first idea was to create a command that would create new messages in a simple way, but I needed other functions, so I added them.
  
  I am currently improving the bundle and expanding functionalities every day.
 
 If you found a bug, please [create an issue](https://github.com/LucasWeb2016/TranslationsExtraBundle/issues) !!
  
  I hope you find it useful!
 
# Requeriments
 - Symfony >3.4 (Not tested in previous versions)
 - PHP >5.6.3
 - [Yandex/Translate-api](https://github.com/yandex-php/translate-api) 1.5.x

# Documentation
## Installation
    composer require lucasweb/translations

## Configuration
This Bundle only need this config to start working:

    translations_extra:
	    default_format: 'xml' 
	    default_locale: 'es'
	    other_locales: ['en,'fr']
	    domains: ['messages','validators']
	    main_folder: '%kernel.project_dir%/translations/'
	    yandex_api_key: 'trnsl.1.1.4e434e....'

- **default_format (string, required)** : Format to be used by default in new files created by this bundle. Valid values are 'xml', 'yaml' and 'php'
- **default_locale (string, required)** : Default locale in your symfony project. (Ex. 'en' or 'fr')
- **other_locales (array, required)** : Array with other locales used in your project (Ex.  ['en','fr']. This can be an empty array if you want to use this Bundle only for your default locale. This configuration is required, but can be an empty array if no other locales.
- **domains (array, required)** : Array with domains that will be accesible by this bundle. "messages" and "validators" are default domains in Symfony.
- **main_folder (string, required)** : Main folder where translation files are stored. 
- **yandex_api_key (string, optional)** : Yandex Translate API key from [here](http://api.yandex.com/key/form.xml?service=trnsl) . If configured, Yandex Translation will be activated.

# How to use this bundle

## Add a new translation message
This command adds a new message to all the locales of a domain if file exists.
     
     php bin/console trans:add domainname

**Example**

    php bin/console trans:add messages
    
    TRANS:ADD => INFO : Starting Add Process ...
    TRANS:ADD => QUESTION : Please, enter unique ID for the new message : label.name
    TRANS:ADD => INFO : The ID you have entered is valid!
    TRANS:ADD => QUESTION : Please, enter value for ID="label.name" in file "messages.es.xliff" : Nombre
    TRANS:ADD => QUESTION : Please, enter value for ID="label.name" in file "messages.en.xliff" (Yandex Translation: Name ) : Name
    TRANS:ADD => QUESTION : Please, enter value for ID="label.name" in file "messages.fr.xliff" (Yandex Translation: Nom ) : Nom
    TRANS:ADD => SUCCESS : Translation message created!

## Search 
Search for a string in all translation files of a domain.
     
     php bin/console trans:search "searchterm" domainname

**Example**

    php bin/console trans:search "Nombre" messages
    
    TRANS:SEARCH => INFO : Starting Search Process ...
    
    +--------+-------------------+------------------+-------------------+
    | Locale | File              | ID               | Value             |
    +--------+-------------------+------------------+-------------------+
    | es     | messages.es.xliff | label.name       | Nombre            |
    | es     | messages.es.xliff | profile.username | Nombre de usuario |
    +--------+-------------------+------------------+-------------------+


## Edit a translation message
This command edits an ID in all the locales of a domain if file exists.


     php bin/console trans:edit ID domainname

**Example**

    php bin/console trans:edit label.name messages
    
    TRANS:EDIT => INFO : Starting Edit Process ...
    TRANS:EDIT => INFO : ID="label.name" found in file "messages.es.xliff".
    TRANS:EDIT => QUESTION : New value for ID=label.name in default file messages.es.xliff (Current="Nombre") : Nombre completo
    TRANS:EDIT => INFO : ID="label.name" found in file "messages.en.xliff".
    TRANS:EDIT => QUESTION : New value for ID=label.name in file messages.en.xliff (Current="Name") (Yandex Translation: Full name ) : Full name
    TRANS:EDIT => INFO : ID="label.name" found in file "messages.fr.xliff".
    TRANS:EDIT => QUESTION : New value for ID=label.name in file messages.fr.xliff (Current="Nom") (Yandex Translation: Nom complet ) : Nom complet
    TRANS:EDIT => SUCCESS : Translation message edited and saved!


## Remove a translation message
This commands removes an ID from all the locales of a domain if file exists, even if it's being used in the project.

     php bin/console trans:edit ID domainname

**Example**

    php bin/console trans:remove label.name messages
    
    TRANS:REMOVE => INFO : Starting Remove Process ...
    TRANS:REMOVE => INFO : ID="label.name" found in default file "messages.es.xliff".
    TRANS:REMOVE => WARNING : Translation ID="label.name" will be deleted from default file "messages.es.xliff", even if it is being used in the project. Continue? (y/n) : y
    TRANS:REMOVE => SUCCESS : Translation message ID="label.name" removed from default file "messages.es.xliff"!
    TRANS:REMOVE => INFO : ID="label.name" found in file "messages.en.xliff".
    TRANS:REMOVE => WARNING : Translation ID="label.name" will be deleted from file "messages.en.xliff", even if it is being used in the project. Continue? (y/n) : y
    TRANS:REMOVE => SUCCESS : Translation message ID="label.name" removed from file "messages.en.xliff"!
    TRANS:REMOVE => INFO : ID="label.name" found in file "messages.fr.xliff".
    TRANS:REMOVE => WARNING : Translation ID="label.name" will be deleted from file "messages.fr.xliff", even if it is being used in the project. Continue? (y/n) : y
    TRANS:REMOVE => SUCCESS : Translation message ID="label.name" removed from file "messages.fr.xliff"!

## Get info from a translation message
This command shows all locale translations of a ID

     php bin/console trans:info ID domainname

**Example**

    php bin/console trans:info label.name messages
    
    TRANS:INFO => INFO : Starting Info Process ...
    
    +--------+-------------------+------------+--------+
    | Locale | File              | ID         | Value  |
    +--------+-------------------+------------+--------+
    | es     | messages.es.xliff | label.name | Nombre |
    +--------+-------------------+------------+--------+
    | en     | messages.en.yml   | label.name | Name   |
    +--------+-------------------+------------+--------+
    | fr     | messages.fr.xliff | Not found! |        |
    +--------+-------------------+------------+--------+
    
    TRANS:INFO => SUCCESS : Translation message info shown!


## Check translation files 

Checks locale files supposed to exist for a domain, and report problems.
     
     php bin/console trans:check domainname

**Example**

    php bin/console trans:remove label.name messages
    
    TRANS:CHECK => INFO : Starting Check Process ...
    
    +--------+-------------------+--------+----------+--------------------------------------------------------------------------------------------+
    | Locale | File              | Format | Messages | Status                                                                                     |
    +--------+-------------------+--------+----------+--------------------------------------------------------------------------------------------+
    | es     | messages.es.xliff | xml    | 9        | Ok!                                                                                        |
    +--------+-------------------+--------+----------+--------------------------------------------------------------------------------------------+
    | en     | messages.en.xliff | xml    | 8        | Different quantity of messages than default locale, Run "trans:sync messages" to solve it! |
    +--------+-------------------+--------+----------+--------------------------------------------------------------------------------------------+
    | fr     | messages.fr.???   | ???    | 0        | File not found, Run "trans:create messages" to solve it!                                   |
    +--------+-------------------+--------+----------+--------------------------------------------------------------------------------------------+
    
    TRANS:CHECK => SUCCESS : Check process finished!


## Sync translations files
This command checks that all translation files of a domain have the same number of messages, and in case of finding some missing translation, gives the option to create a new message in the file where it is missing, or to delete the message from the file where it was found .

     php bin/console trans:sync domainname

**Example**

    php bin/console trans:sync messages
    
    TRANS:SYNC => INFO : Starting Sync Process ...
    TRANS:SYNC => INFO : Comparing messages.es.xliff -> messages.en.xliff
    TRANS:SYNC => INFO : ID="menu.login" with value "Inicio de sesión" is in default file messages.es.xliff but not in messages.en.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION : Value for messages.en.xliff (Yandex Translation: Login ) : Login
    TRANS:SYNC => INFO : ID="menu.login"" created in messages.en.xliff!
    TRANS:SYNC => INFO : Comparing messages.en.xliff -> messages.es.xliff
    TRANS:SYNC => INFO : ID="profile.username" with target "Username" is in messages.en.xliff but not in default file messages.es.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION: Value for default file messages.es.xliff (Yandex Translation: Nombre de usuario ) : Nombre de usuario
    TRANS:SYNC => INFO : ID=profile.username created in default file messages.es.xliff
    TRANS:SYNC => SUCCESS : Process finished!
    TRANS:SYNC => INFO : Comparing messages.es.xliff -> messages.fr.xliff
    TRANS:SYNC => INFO : ID="menu.home" with value "Inicio" is in default file messages.es.xliff but not in messages.fr.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION : Value for messages.fr.xliff (Yandex Translation: Démarrer ) :
    TRANS:SYNC => INFO : ID="menu.home"" created in messages.fr.xliff!
    TRANS:SYNC => INFO : ID="profile.username" with value "Nombre de usuario" is in default file messages.es.xliff but not in messages.fr.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION : Value for messages.fr.xliff (Yandex Translation: Nom d'utilisateur ) : Nom d'utilisateur
    TRANS:SYNC => INFO : ID="profile.username"" created in messages.fr.xliff!
    TRANS:SYNC => INFO : Comparing messages.fr.xliff -> messages.es.xliff
    TRANS:SYNC => SUCCESS : Process finished!

    
## Create translation files
This command checks the existence of all the files that must exist according to the locales and domains configured, and allows you to create both empty files and clones of the default locale.


     php bin/console trans:create domainname

**Example**

    php bin/console trans:create messages
    
    TRANS:CREATE => INFO : Starting Create Process ...
    TRANS:CREATE => INFO : Default File "messages.es.xliff" exists!
    TRANS:CREATE => QUESTION : File for domain "messages" and locale "en" not found!
      [0] Skip
      [1] Create new empty file
      [2] Create a clon of default file
      [3] Create a clon of default file and translate it with Yandex Translate API
     > 3
    TRANS:CREATE => SUCCESS : File "messages.en.xliff" with Yandex Translation from default file create!
    TRANS:CREATE => INFO : File "messages.fr.xliff" exists!

    
## Import translations files
In Symfony you can override translation messages from bundles in main folder. This commands checks a path or a composer installed package for translation files for locales in a domain, and imports them to main folder, where you can easily modify them. 

     php bin/console trans:import bundle/package domainname 

**Example**
In this example, the command imports Vich Uploader translation files to our main folder according to locales and default format configured, and in the default format . Not needed files will not be imported. 

    php bin/console trans:import Vich\Uploader-Bundle VichUploaderBundle
    
    TRANS:IMPORT => INFO : Starting Import Process ...
    TRANS:IMPORT => INFO : Folder "Vich\Uploader-Bundle" found !!.
    TRANS:IMPORT => INFO : File "VichUploaderBundle.es.yml" located in folder "C:\xampp\htdocs\symfny4\src/../vendor/vich\uploader-bundle/Resources/Translations"!
    TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : y
    TRANS:IMPORT => SUCCESS : File "VichUploaderBundle.es.xliff" created with imported data.
    TRANS:IMPORT => INFO : File "VichUploaderBundle.en.yml" located in folder "C:\xampp\htdocs\symfny4\src/../vendor/vich\uploader-bundle/Resources/Translations"!
    TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : y
    TRANS:IMPORT => SUCCESS : File "VichUploaderBundle.en.xliff" created with imported data.
    TRANS:IMPORT => INFO : File "VichUploaderBundle.fr.yml" located in folder "C:\xampp\htdocs\symfny4\src/../vendor/vich\uploader-bundle/Resources/Translations"!
    TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : y
    TRANS:IMPORT => SUCCESS : File "VichUploaderBundle.fr.xliff" created with imported data.
    TRANS:IMPORT => INFO : Process finished!
    TRANS:IMPORT => IMPORTANT : Remember to add "VichUploaderBundle" domain to configuration or this files will be ignored by this Bundle.

    
You can also import files from any folder. The following command would do the same as the previous example:
   
    php bin/console trans:import C:\xampp\htdocs\symfny4\vendor\vich\uploader-bundle\Resources\Translations VichUploaderBundle
# License
This bundle is under the MIT license. See the complete license in the bundle.

# Reporting an issue or a feature request
Issues and feature requests are tracked in the Github issue tracker.

