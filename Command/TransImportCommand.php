<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransImportCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:import')
            ->setDescription('Import translation catalogues from Bundles files to main folder. This commands search for translation files in a folder, and import them to main directory, where this bundle can manage them.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('folder', InputArgument::REQUIRED, 'Folder that stores Bundle translations files.(Ej. c:/xampp/htdocs/symfonyproject/vendor/')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:IMPORT => INFO : Starting Import Process ...');
        $filesystem = new Filesystem();
        $helper = $this->getHelper('question');

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $file_extensions = $common->getFileExtensions();
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));
        //var_dump();die();

        //Previous checks
        if (!$filesystem->exists($this->getContainer()->get('kernel')->getRootDir() . '/../vendor/' . strtolower($input->getArgument('folder')) . '/Resources/Translations')) {

            if (!$filesystem->exists($input->getArgument('folder'))) {
                $output->writeln('TRANS:IMPORT => ERROR : Folder or Bundle not not found !!. ');
                die();
            } else {
                $importfolder = $input->getArgument('folder');
            }

        } else {
            $importfolder = $this->getContainer()->get('kernel')->getRootDir() . '/../vendor/' . strtolower($input->getArgument('folder')) . '/Resources/Translations';
        }

        $output->writeln('TRANS:IMPORT => INFO : Folder "' . $input->getArgument('folder') . '" found !!. ');

        $filesfound = $common->GetImportFiles($importfolder, $input->getArgument('domain'), $config);

        if (count($filesfound) == 0) {
            $output->writeln('TRANS:IMPORT => ERROR : No files has been located in folder "' . $importfolder . '" for domain "' . $input->getArgument('domain') . '" !');
        } else {
            foreach ($filesfound as $file) {
                $output->writeln('TRANS:IMPORT => INFO : File "' . $file['filename'] . '" located in folder "' . $importfolder . '"!');
                $question = new ConfirmationQuestion('TRANS:IMPORT => QUESTION : Import this translation file content (y/n) : ', false);
                if ($helper->ask($input, $output, $question)) {
                    // checks if a file for this locale and domain exists in default folder.
                    $found['path'] = '';
                    foreach ($file_extensions as $key => $value) {
                        foreach ($value as $v) {
                            if ($filesystem->exists($config['main_folder'] . '/' . $input->getArgument('domain') . '.' . $file['locale'] . '.' . $v)) {
                                $found['path'] = $config['main_folder'] . '/' . $input->getArgument('domain') . '.' . $file['locale'] . '.' . $v;
                                $found['format']= $key;
                                break;
                            }
                        }
                        if ($found != '') {
                            break;
                        }
                    }
                    $replytarget = 0;
                    if ($found['path'] != '') {
                        $a = 0;
                        while ($a <= 0) {
                            $question = new Question('TRANS:IMPORT => QUESTION : There is already a translation file for locale "' . $file['locale'] . '" and domain "' . $input->getArgument('domain') . '". Do not import (1), Overwrite content(2) ', 1);
                            $replytarget = $helper->ask($input, $output, $question);
                            if ($replytarget == 1 || $replytarget == 2) {
                                $a = 1;
                            }
                        }

                        if ($replytarget == 2) {
                            $dataoriginal = $common->getArrayFromFile($file['path'], $file['format']);
                            $common->putArrayInFile($found['path'], $found['format'], $dataoriginal);
                            $output->writeln('TRANS:IMPORT => SUCCESS : Imported !.');

                        } else {
                            $output->writeln('TRANS:IMPORT => Import of file "' . $input->getArgument('domain') . '.' . $file['locale'] . '.' . $file_extensions[$config['default_format']][0] . '" cancelled.');

                        }
                    } else {
                        $dataoriginal = $common->getArrayFromFile($file['path'], $file['format']);
                        $path = $config['main_folder'] . '/' . $input->getArgument('domain') . '.' . $file['locale'] . '.' . $file_extensions[$config['default_format']][0];
                        $common->putArrayInFile($path, $config['default_format'], $dataoriginal);
                        $output->writeln('TRANS:IMPORT => SUCCESS : File "' . $input->getArgument('domain') . '.' . $file['locale'] . '.' . $file_extensions[$config['default_format']][0] . '" created with imported data.');

                    }


                } else {
                    $output->writeln('TRANS:IMPORT => INFO : File "' . $file['filename'] . '" import cancelled.');
                }
            }
        }

        $output->writeln('TRANS:IMPORT => INFO : Process finished!');
        $output->writeln('TRANS:IMPORT => IMPORTANT : Remember to add "' . $input->getArgument('domain') . '" domain to configuration or this files will be ignored by this Bundle.');

    }

}