<?php

namespace App\DataFixtures;

use App\Factory\EvaluationFactory;
use App\Factory\FormationFactory;
use App\Factory\QuizFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Classe permettant de générer un jeu de données complet.
 * Son utilisation n'est prévue qu'à des fins de développement et de test.
 */
class DevFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * Permet de spécifier à doctrine quel groupe de fixtures à exécuter.
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['dev'];
    }

    /**
     * Crée des données dans toutes les tables, et les persiste en base de données.
     */
    public function load(ObjectManager $manager): void
    {
        // Creates 5 Formation entity
        FormationFactory::createMany(5);

        // Creates 20 Users
        UserFactory::createMany(20, function () {
            return [
                // Populates the ManyToMany relationship.
                'formations' => FormationFactory::randomSet(random_int(1, 3)),
            ];
        });

        // Creates Quiz Entities
        QuizFactory::createMany(12, function () {
            return [
                'author' => UserFactory::createOne([
                    'roles' => ['ROLE_FORMATEUR'],
                ]),
            ];
        });

        // Creates Evaluation Entities, and populates its ManyToMany relationships
        EvaluationFactory::createMany(12, function () {
            return [
                'quiz' => QuizFactory::random(),
                'formation' => FormationFactory::random(),
            ];
        });

        $manager->flush();
    }
}
