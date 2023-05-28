<?php

namespace App\DataFixtures;

use App\Factory\FormationFactory;
use App\Factory\UserFactory;
use Doctrine\Persistence\ObjectManager;

/**
 * Classe permettant de générer un jeu de données minimal,
 * afin de tester et utiliser le logiciel.
 */
class AppFixtures extends \Doctrine\Bundle\FixturesBundle\Fixture implements \Doctrine\Bundle\FixturesBundle\FixtureGroupInterface
{
    /**
     * Permet de spécifier à doctrine quel groupe de fixtures à exécuter.
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

    /**
     * Crée des données dans toutes les tables, et les persiste en base de données.
     */
    public function load(ObjectManager $manager)
    {
        // Crée deux formations
        FormationFactory::createMany(2);

        // Crée 2 formateurs
        UserFactory::createMany(2, function () {
            return [
                'roles' => ['ROLE_FORMATEUR'],
                'formations' => FormationFactory::randomSet(1),
            ];
        });

        // Crée 4 élèves
        UserFactory::createMany(4, function () {
            return [
                'roles' => ['ROLE_ELEVE'],
                'formations' => FormationFactory::randomSet(1),
            ];
        });

        // Envoie en base les données crées
        $manager->flush();
    }
}
