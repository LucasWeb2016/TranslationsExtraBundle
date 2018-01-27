# Lucasweb\TranslationsExtraBundle

[![Symfony Tested](https://img.shields.io/badge/Tested-Symfony%20%3E3.4-green.svg)]() [![License](https://img.shields.io/badge/License-MIT-orange.svg)]()

[![knpbundles.com](http://knpbundles.com/LucasWeb2016/TranslationsExtraBundle/badge-short)](http://knpbundles.com/LucasWeb2016/TranslationsExtraBundle)

**Bundle with extra developer tools for Symfony Framework**

This Bundle adds extra commands to symfony for easy managing translations from translation files.

 - Add a new translation message
 - Edit a translation message
 - Remove a translation message
 - Get info from a translation message
 - Check translations files
 - Sync translations files 
 - Create translation files
 - Import translation files
 - Automatic translation with Yandex Translation API
 
## Why create this Bundle?
 Honestly, I hate having to change tab when I need to create a new message while I'm programming. 
 
 The first idea was to create a command that would create new messages in a simple way, but I needed other functions, so I added them.
 
 Some commands are still very simple and could give more information or functionalities, it is an initial version that still has much to improve.
# Requeriments
 - Symfony >3.4 (Not tested in previous versions)
 - PHP >5.6.3
 - [Yandex/Translate-api](https://github.com/yandex-php/translate-api) 1.5.x

# Documentation
## Installation
    composer require lucasweb/translations

## Configuration
This Bundle only needs a few fields in Yaml configuration files to start working:

    translations_extra:
	    format: 'xml' 
	    default_locale: 'es'
	    other_locales: []
	    domains: ['messages','validators']
	    main_folder: '%kernel.project_dir%/translations/'
	    yandex_api_key: 'trnsl.1.1.4e434e....'

- **default_format (string, required)** : Format to be used by default in new files created by this bundle. Valid values are 'xml', 'yaml' and 'php'
- **default_locale (string, required)** : Default locale in your symfony project. (Ex. 'en' or 'fr')
- **other_locale (array, required)** : Array with other locales used in your project (Ex.  ['en','fr']. This can be an empty array if you want to use this Bundle only for your default locale. This configuration is required, but can be an empty array if no other locales.
- **domains (array, required)** : Array with domains that will be accesible by this bundle. "messages" and "validator" are default domains in Symfony.
- **main_folder (string, required)** : Main folder where translations files are stored. 
- **yandex_api_key (string, optional)** : Yandex Translate API key from [here](http://api.yandex.com/key/form.xml?service=trnsl) 

# How to use this bundle

## Add a new translation message
This command adds a new message to all the locales of a domain.
     
     php bin/console trans:add domainname

**Example**

    php bin/console trans:add messages
    TRANS:ADD => INFO : Starting Add Process ...
    TRANS:ADD => QUESTION : Please, enter an uniq ID that will be used for identify this translation message in all locale files : label.name
    TRANS:ADD => INFO : The ID you have entered is OK!
    TRANS:ADD => QUESTION : Please, enter value for ID="label.name" in file "messages.es.xliff" : Nombre
    TRANS:ADD => QUESTION : Please, enter value for ID="label.name" in file "messages.en.xliff" : Name
    TRANS:ADD => SUCCESS : Translation message saved!

## Edit a translation message
This command edits a message of all the local files of a domain, if they exist.


     php bin/console trans:edit messageID domainname

**Example**

    php bin/console trans:edit label.name messages
    TRANS:EDIT => INFO : Starting Edit Process ...
    TRANS:EDIT => INFO : ID="label.name" found in file "messages.es.xliff".
    TRANS:EDIT => QUESTION : New value for ID=label.name in default file messages.es.xliff (Current="Nombre") : NOMBRE
    TRANS:EDIT => INFO : ID="label.name"found in file "messages.en.xliff".
    TRANS:EDIT => QUESTION : New value for ID=label.name in file messages.en.xliff (Current="Name") : NAME
    TRANS:EDIT => SUCCESS : Translation message edited and saved!

## Remove a translation message
This commands removes a messageID from all locale files of a domain, even if it's being used in the project.

     php bin/console trans:edit messageID domainname

**Example**

    php bin/console trans:remove label.name messages
    TRANS:REMOVE => INFO : Starting Remove Process ...
    TRANS:REMOVE => INFO : ID="label.name" found in default file "messages.es.xliff".
    TRANS:REMOVE => WARNING : Translation ID="label.name" will be deleted from default file "messages.es.xliff", even if it is being used in the project. Continue? (y/n) : y
    TRANS:REMOVE => SUCCESS : Translation message ID="label.name" removed from default file "messages.es.xliff"!
    TRANS:REMOVE => INFO : ID="label.name" found in file "messages.en.xliff".
    TRANS:REMOVE => WARNING : Translation ID="label.name" will be deleted from file "messages.en.xliff", even if it is being used in the project. Continue? (y/n) : y
    TRANS:REMOVE => SUCCESS : Translation message ID="label.name" removed from file "messages.en.xliff"!

## Get info from a translation message
This command shows all locale translations of a message ID

     php bin/console trans:info messageID domainname

**Example**

    php bin/console trans:remove label.name messages
    TRANS:INFO => INFO : Starting Info Process ...
    TRANS:INFO => INFO : Value of message ID="label.name" in file "messages.es.xliff" is "Nombre"
    TRANS:INFO => INFO : Value of message ID="label.name" in file "messages.en.xliff" is "Name"
    TRANS:INFO => SUCCESS : Translation message info shown!

## Check translations files 

Im still working on this area. For the moment, this commands does:
- Checks the existence of all the files that must exist according to the locales and domains configured.
- Checks for repeated translations with different message ID.
- Checks if all locales of a domain has same number of messages.

     
     php bin/console trans:check domainname

**Example**

    php bin/console trans:remove label.name messages
    TRANS:CHECK => INFO : Starting Check Process ...
    TRANS:CHECK => INFO : Default file messages.es.xliff has 4 translation messages.
    TRANS:CHECK => INFO : Checking "messages.es.xliff" for repeated translations with different ID
    TRANS:CHECK => INFO : File messages.en.xliff has 2 translation messages.
    TRANS:CHECK => WARNING : File messages.en.xliff has less translations than default. Run "trans:sync messages" command to solve it.
    TRANS:CHECK => INFO : Checking "messages.en.xliff" for repeated translations with different ID
    TRANS:CHECK => SUCCESS : Check files done!

## Sync translations files
This command checks that all the language files have the same number of messages, and in case of finding some missing translation, gives the option to create a new message in the file where it is missing, or to delete the message from the file where it was found .

     php bin/console trans:sync domainname

**Example**

    php bin/console trans:remove label.name messages
    TRANS:SYNC => INFO : Starting Sync Process ...
    TRANS:SYNC => INFO : Comparing messages.es.xliff -> messages.en.xliff
    TRANS:SYNC => INFO : ID="label.name" with value "Nombre" is in default file messages.es.xliff but not in messages.en.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION : Target for messages.en.xliff (Enter for default="Nombre"") : Name
    TRANS:SYNC => INFO : ID="label.name"" created in messages.en.xliff!
    TRANS:SYNC => INFO : ID="prueba.pruebas" with value "Pruebas" is in default file messages.es.xliff but not in messages.en.xliff. Create(1) or Delete(2) : 1
    TRANS:SYNC => QUESTION : Target for messages.en.xliff (Enter for default="Pruebas"") : Tests
    TRANS:SYNC => INFO : ID="prueba.pruebas"" created in messages.en.xliff!
    TRANS:SYNC => INFO : Comparing messages.en.xliff -> messages.es.xliff
    TRANS:SYNC => SUCCESS : Process finished!
    
## Create translations files
This command checks the existence of all the files that must exist according to the locales and domains configured, and allows you to create both empty files and clones of the default locale.


     php bin/console trans:create domainname

**Example**

    php bin/console trans:create messages
    TRANS:CREATE => INFO : Starting Create Process ...
    TRANS:CREATE => INFO : Default File "messages.es.xliff" exists!
    TRANS:CREATE => INFO : File "messages.en.xliff" exists!
    TRANS:CREATE => QUESTION : File "messages.fr.xliff" not found. Create new empty file(1), Create new and clone translations from default(2) or Skip(3)1
    TRANS:CREATE => SUCCESS : File "messages.fr.xliff" created!
    
    
## Import translations files
In Symfony you can override translation messages of a bundle in main folder. This commands checks a path or a composer installed package for translations files for locales ands domains configured, an imports them to main folder, where you can easily modify them. 

     php bin/console trans:import bundle/package domainname 

**Example**
In this example, the command imports Vich Uploader Translation files to our main folder according to locales and default format configured, and in the default format . Not needed files will not be imported. 

    php bin/console trans:import Vich\Uploader-Bundle VichUploaderBundle
    TRANS:IMPORT => INFO : Starting Import Process ...
    TRANS:IMPORT => INFO : Folder "C:\xampp\htdocs\symfny4\vendor\vich\uploader-bundle\Resources\translations" found !!.
    TRANS:IMPORT => INFO : File "VichUploaderBundle.es.yml" located in folder "C:\xampp\htdocs\symfny4\vendor\vich\uploader-bundle\Resources\translations"!
    TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : y
    TRANS:IMPORT => SUCCESS : File "VichUploaderBundle.es.xliff" created with imported data.
    TRANS:IMPORT => INFO : File "VichUploaderBundle.en.yml" located in folder "C:\xampp\htdocs\symfny4\vendor\vich\uploader-bundle\Resources\translations"!
    TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : y
    TRANS:IMPORT => SUCCESS : File "VichUploaderBundle.en.xliff" created with imported data.
    TRANS:IMPORT => INFO : Process finished!
    TRANS:IMPORT => IMPORTANT : Remember to add "VichUploaderBundle" domain to configuration or this files will be ignored by this Bundle.
    
You can also import files from any folder. The following command would do the same as the previous example:
   
    php bin/console trans:import C:\xampp\htdocs\symfny4\vendor\vich\uploader-bundle\Resources\Translations VichUploaderBundle
## Yandex Translate API Integration
If this feature is configured, this bundle can use this service for automatic translation in file clonation or suggestions when creating new messages, based on the value in default locale file. 

First, you need an API key from **[here](http://api.yandex.com/key/form.xml?service=trnsl)**. 
To activate this features, you need to add one more option to the bundle configuration:

     translations_extra:
         ...
         ...
         yandex_api_key: 'trnsl.1.1.4e434e....'
 
# TODO List
- Having a lot of problems when using latin characters in command line. Im Spanish and i need my Ñ !!!
- Implement debug:translation results.

