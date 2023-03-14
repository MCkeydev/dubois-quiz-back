<?php

namespace App\Factory;

use App\Entity\StudentCopy;
use App\Repository\StudentCopyRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<StudentCopy>
 *
 * @method        StudentCopy|Proxy create(array|callable $attributes = [])
 * @method static StudentCopy|Proxy createOne(array $attributes = [])
 * @method static StudentCopy|Proxy find(object|array|mixed $criteria)
 * @method static StudentCopy|Proxy findOrCreate(array $attributes)
 * @method static StudentCopy|Proxy first(string $sortedField = 'id')
 * @method static StudentCopy|Proxy last(string $sortedField = 'id')
 * @method static StudentCopy|Proxy random(array $attributes = [])
 * @method static StudentCopy|Proxy randomOrCreate(array $attributes = [])
 * @method static StudentCopyRepository|RepositoryProxy repository()
 * @method static StudentCopy[]|Proxy[] all()
 * @method static StudentCopy[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static StudentCopy[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static StudentCopy[]|Proxy[] findBy(array $attributes)
 * @method static StudentCopy[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static StudentCopy[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class StudentCopyFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'canShare' => self::faker()->boolean(),
            'evaluation' => EvaluationFactory::new(),
            'professor' => UserFactory::new(),
            'student' => UserFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(StudentCopy $studentCopy): void {})
        ;
    }

    protected static function getClass(): string
    {
        return StudentCopy::class;
    }
}
