<?php

namespace Lucasweb\TranslationsExtraBundle\Command;

use Lucasweb\TranslationsExtraBundle\Utils\CommonUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TransSyncCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('trans:sync')
            ->setDescription('Sync all translation files of a domain.')
            ->setHelp('Documentation available at https://github.com/LucasWeb2016/TranslationsExtraBundle')
            ->addArgument('domain', InputArgument::REQUIRED, 'Translation domain name (Ej."messages"). Required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TRANS:SYNC => INFO : Starting Sync Process ...');
        $helper = $this->getHelper('question');
        QuestionHelper::disableStty();

        // Get Configurations and Files to process
        $common = new CommonUtils();
        $config = $common->getConfig($this->getContainer());
        $domainfiles = $common->getFiles($this->getContainer(), $input->getArgument('domain'));

        //Process
        if ($domainfiles['path'] == '') {

            $output->writeln('TRANS:SYNC => ERROR : Default locale file "' . $domainfiles['default'] . '" is required and not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it.');
            die();
        }
        foreach ($domainfiles['others'] as $other) {

            if ($other['path'] == '') {

                $output->writeln('TRANS:SYNC => WARNING : File "' . $other['filename'] . '" not found. Run "trans:create ' . $input->getArgument('domain') . '" command to solve it. This file will not be synced.');

            } else {
                $defaultdata = $common->getArrayFromFile($domainfiles['path'], $domainfiles['format']);
                if (!is_array($defaultdata)) {
                    $output->writeln('TRANS:SYNC => ERROR : Default locale file "' . $domainfiles['default'] . '"cant´t be opened. Incorrect format?.');
                    die();
                }
                $otherdata = $common->getArrayFromFile($other['path'], $other['format']);
                if (!is_array($otherdata)) {
                    $output->writeln('TRANS:SYNC => ERROR : File "' . $other['filename'] . '"cant´t be opened. Incorrect format?.');
                    die();
                }
                $output->writeln('TRANS:SYNC => INFO : Comparing ' . $domainfiles['default'] . ' -> ' . $other['filename']);
                $diff = array_diff_key($defaultdata, $otherdata);

                foreach ($diff as $key => $value) {

                    $a = 0;
                    while ($a <= 0) {
                        $question = new Question('TRANS:SYNC => INFO : ID="' . $key . '" with value "' . $value . '" is in default file ' . $domainfiles['default'] . ' but not in ' . $other['filename'] . '. Create(1) or Delete(2) : ', '1');
                        $reply = $helper->ask($input, $output, $question);
                        if ($reply >= 1 || $reply <= 2) {
                            $a = $reply;
                        }
                    }

                    if ($a == 1) {
                        $a = 0;
                        $reply = 0;
                        while ($a <= 0) {
                            $reply = 0;
                            if ($config['yandex_api_key'] != '') {
                                $trans = $common->YandexTrans($defaultdata[$key], $domainfiles['locale'], $other['locale'], $config, $output);
                                if ($trans == '') {
                                    $yandex = '';
                                    $yandexraw = '';
                                } else {
                                    $yandex = ' (Press enter for Yandex Translation: ' . $trans . ' )';
                                    $yandexraw = $trans;
                                }
                            } else {
                                $yandex = '';
                                $yandexraw = '';
                            }
                            $question = new Question('TRANS:SYNC => QUESTION : Value for ' . $other['filename'] . $yandex . ' : ', $yandexraw);
                            $reply = $helper->ask($input, $output, $question);
                            if (is_string($reply)) {
                                $a = 1;
                            }
                        }
                        $reply=$common->Sanitize($reply);
                        $otherdata[$key] = $reply;
                        $common->putArrayInFile($other['path'], $other['format'], $otherdata);
                        $output->writeln('TRANS:SYNC => INFO : ID="' . $key . '"" created in ' . $other['filename'] . '!');
                    } else {
                        unset($defaultdata[$key]);
                        $common->putArrayInFile($other['path'], $other['format'], $defaultdata);
                        $output->writeln('TRANS:SYNC => INFO : ID="' . $key . '" deleted from ' . $domainfiles['default']);
                    }
                }

                $diff2 = array_diff_key($otherdata, $defaultdata);
                $output->writeln('TRANS:SYNC => INFO : Comparing ' . $other['filename'] . ' -> ' . $domainfiles['default']);

                foreach ($diff2 as $key => $value) {

                    $a = 0;
                    while ($a <= 0) {
                        $reply = 0;
                        $question = new Question('TRANS:SYNC => INFO : ID="' . $key . '" with target "' . $value . '" is in ' . $other['filename'] . ' but not in default file ' . $domainfiles['default'] . '. Create(1) or Delete(2) : ', '1');
                        $reply = $helper->ask($input, $output, $question);
                        if ($reply == 1 || $reply == 2) {
                            $a = $reply;
                        }
                    }

                    if ($a == 1) {

                        $a = 0;
                        $reply = 0;
                        while ($a <= 0) {
                            $reply = 0;
                            if ($config['yandex_api_key'] != '') {
                                $trans = $common->YandexTrans($otherdata[$key], $other['locale'], $domainfiles['locale'], $config, $output);
                                if ($trans == '') {
                                    $yandex = '';
                                    $yandexraw = '';
                                } else {
                                    $yandex = ' (Press enter for Yandex Translation: ' . $trans . ' )';
                                    $yandexraw = $trans;
                                }
                            } else {
                                $yandex = '';
                                $yandexraw = '';
                            }
                            $question = new Question('TRANS:SYNC => QUESTION: Value for default file ' . $domainfiles['default'] . $yandex . ' : ', $yandexraw);
                            $reply = $helper->ask($input, $output, $question);
                            if (is_string($reply)) {
                                $a = 1;
                            }
                        }
                        $reply=$common->Sanitize($reply);
                        $defaultdata[$key] = $reply;
                        $common->putArrayInFile($domainfiles['path'], $domainfiles['format'], $defaultdata);
                        $output->writeln('TRANS:SYNC => INFO : ID=' . $key . ' created in default file ' . $domainfiles['default']);

                    } else {
                        unset($otherdata[$key]);
                        $common->putArrayInFile($other['path'], $other['format'], $otherdata);
                        $output->writeln('TRANS:SYNC => INFO : ID=' . $key . ' deleted from ' . $other['filename']);
                    }
                }
            }
            $output->writeln('TRANS:SYNC => SUCCESS : Process finished!');
        }
    }
}