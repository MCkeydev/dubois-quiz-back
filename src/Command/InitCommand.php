<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Classe de commande, qui permet de générer une base de données complète pour le test de l'application.
 */
#[AsCommand(
    name: 'app:init',
    description: "Permet de générer les données nécessaire à l'utilisation de l'application",
)]
class InitCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Permet de lancer la commande chargeant le jeu de données de test en base.
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('doctrine:fixtures:load');

        // Nous ne souhaitons exécuter que les fixtures du groupe "test"
        $arguments = new ArrayInput([
            '--group' => ['test'],
        ]);

        return $command->run($arguments, $output);
    }
}
