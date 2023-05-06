<?php

namespace App\Factory;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Quiz>
 *
 * @method        Quiz|Proxy                     create(array|callable $attributes = [])
 * @method static Quiz|Proxy                     createOne(array $attributes = [])
 * @method static Quiz|Proxy                     find(object|array|mixed $criteria)
 * @method static Quiz|Proxy                     findOrCreate(array $attributes)
 * @method static Quiz|Proxy                     first(string $sortedField = 'id')
 * @method static Quiz|Proxy                     last(string $sortedField = 'id')
 * @method static Quiz|Proxy                     random(array $attributes = [])
 * @method static Quiz|Proxy                     randomOrCreate(array $attributes = [])
 * @method static QuizRepository|RepositoryProxy repository()
 * @method static Quiz[]|Proxy[]                 all()
 * @method static Quiz[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Quiz[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Quiz[]|Proxy[]                 findBy(array $attributes)
 * @method static Quiz[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Quiz[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class QuizFactory extends ModelFactory
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
            'author' => UserFactory::new(),
            'title' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Quiz $quiz): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Quiz::class;
    }
}
