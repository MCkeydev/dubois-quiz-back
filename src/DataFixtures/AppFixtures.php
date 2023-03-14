<?php

namespace App\DataFixtures;

use App\Factory\EvaluationFactory;
use App\Factory\FormationFactory;
use App\Factory\QuizFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * Loads all the fixtures, and persists them to the database.
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // Creates 5 Formation entity
        FormationFactory::createMany(5);

        // Creates 20 Users
        UserFactory::createMany(20, function() {
            return [
                // Populates the ManyToMany relationship.
                'formations' => FormationFactory::randomSet(random_int(1, 3)),
            ];
        });

        // Creates Quiz Entities
        QuizFactory::createMany(12, function () {
            return [
                'author' => UserFactory::createOne([
                    'roles' => ["ROLE_FORMATEUR"]
                ])
            ];
        });

        // Creates Evaluation Entities, and populates its ManyToMany relationships
        EvaluationFactory::createMany(12, function () {
            return [
                'quiz' => QuizFactory::random(),
                'formations' => FormationFactory::randomSet(random_int(1, 3)),
            ];
        });

        $manager->flush();
    }
}
