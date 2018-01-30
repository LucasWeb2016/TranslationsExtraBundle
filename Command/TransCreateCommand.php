<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class TransCreateCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:create')
            ->setDescription('Create all non-existent translation files of a domain.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/symfony-TranslationExtraBundle')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:CREATE => INFO : Starting Create Process ...');
        $helper = $this->getHelper('question');
        $filesystem = new Filesystem();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));
        $file_extensions = $common->getFileExtensions();

        //Previous checks
        if (!in_array($input->getArgument('domain'), $config['domains'])) {
            $output->writeln('TRANS:CREATE => ERROR : "' . $input->getArgument('domain') . '" is not one of the configured domains.');
            die();
        } else if (!$filesystem->exists($config['main_folder'])) {
            $output->writeln('TRANS:CREATE => ERROR : Main folder not found. Bad configuration?');
            die();
        } else if (count($config['other_locales']) == 0) {
            $output->writeln('TRANS:CREATE => WARNING : No locales configured except default. Process will only work with default locale translations.');
        }

        if (!$filesystem->exists($domainfiles['path'])) {
            $a = 0;
            while ($a <= 0) {
                $question = new Question('TRANS:CREATE => QUESTION : File "' . $domainfiles['default'] . '" not found. Create new empty file(1) or skip(2)', 1);
                $replytarget = $helper->ask($input, $output, $question);
                if ($replytarget == 1 || $replytarget == 2) {
                    $a = $replytarget;
                }
            }

            if ($a == 1) {
                $path = $this->getContainer()->getParameter('translationsextra.main_folder') . '/' . $input->getArgument('domain') . '.' . $domainfiles['locale'] . '.' . $file_extensions[$config['default_format']][0];
                $common->putArrayInFile($path, $config['default_format'], []);
                $output->writeln('TRANS:CREATE => SUCCESS : Default File "' . $domainfiles['default'] . '" created!');
            }
        } else {
            $output->writeln('TRANS:CREATE => INFO : Default File "' . $domainfiles['default'] . '" exists!');
        }
        if (isset($domainfiles['others'])) {
            foreach ($domainfiles['others'] as $domfile) {
                if (!$filesystem->exists($domfile['path'])) {
                    $a = -1;
                    while ($a <= -1) {
                        $question = null;
                        if ($domainfiles['path'] != '') {
                            if ($config['yandex_api_key'] != '') {
                                $question = new ChoiceQuestion(
                                    'TRANS:CREATE => QUESTION : File for domain "' . $input->getArgument('domain') . '" and locale "' . $domfile['locale'] . '" not found!',
                                    array('Skip', 'Create new empty file', 'Create a clon of default file', 'Create a clon of default file and translate it with Yandex Translate API'),
                                    0
                                );
                            } else {
                                $question = new ChoiceQuestion(
                                    'TRANS:CREATE => QUESTION : File for domain "' . $input->getArgument('domain') . '" and locale "' . $domfile['locale'] . '" not found!',
                                    array('Skip', 'Create new empty file', 'Create a clon of default file'),
                                    0
                                );
                            }
                        } else {
                            $question = new ChoiceQuestion(
                                'TRANS:CREATE => QUESTION : File for domain "' . $input->getArgument('domain') . '" and locale "' . $domfile['locale'] . '" not found!',
                                array('Skip', 'Create new empty file'),
                                0
                            );
                        }
                        $question->setErrorMessage('Option %s is invalid.');
                        $answers=array('Skip', 'Create new empty file', 'Create a clon of default file', 'Create a clon of default file and translate it with Yandex Translate API');
                        $replytarget = $helper->ask($input, $output, $question);
                        $answer=array_search($replytarget,$answers);
                        if ($answer >= 0 && $answer <= 3) {
                            $a = $answer;
                        }
                    }
                    $path = $this->getContainer()->getParameter('translationsextra.main_folder') . '/' . $input->getArgument('domain') . '.' . $domfile['locale'] . '.' . $file_extensions[$config['default_format']][0];
                    if ($a == 1) {
                        $common->putArrayInFile($path, $config['default_format'], []);
                        $output->writeln('TRANS:CREATE => SUCCESS : Empty file "' . $domfile['filename'] . '" created!');

                    } else if ($a == 2) {
                        $clonedata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
                        if (!is_array($clonedata)) {
                            $output->writeln('TRANS:CREATE => ERROR : File "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?.');
                        } else {
                            $common->putArrayInFile($path, $config['default_format'], $clonedata);
                            $output->writeln('TRANS:CREATE => SUCCESS : File "' . $domfile['filename'] . '" cloned from default file created!');
                        }

                    } else if ($a == 3) {
                        $clonedata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
                        if (!is_array($clonedata)) {
                            $output->writeln('TRANS:CREATE => ERROR : File "' . $domainfiles['default'] . '" cant´t be opened. Incorrect format?.');
                        } else {
                            foreach ($clonedata as $key => $value) {
                                $clonedata[$key] = $common->YandexTrans($value, $domainfiles['locale'], $domfile['locale'], $config, $output);
                            }
                            $common->putArrayInFile($path, $config['default_format'], $clonedata);
                            $output->writeln('TRANS:CREATE => SUCCESS : File "' . $domfile['filename'] . '" with Yandex Translation from default file create!');
                        }

                    }
                } else {
                    $output->writeln('TRANS:CREATE => INFO : File "' . $domfile['filename'] . '" exists!');
                }

            }
        }
    }

}


