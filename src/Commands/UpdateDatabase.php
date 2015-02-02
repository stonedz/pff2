<?php
/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 12:29 PM
 */

namespace pff\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDatabase extends Command {

    protected function configure() {
        $this
            ->setName('db:updateDb')
            ->setDescription('Backups and then updates the db using doctrine orm:schema-tool:update --force');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        exec('vendor/bin/doctrine',$res);
        foreach ($res as $r) echo $r."\n";
    }
}