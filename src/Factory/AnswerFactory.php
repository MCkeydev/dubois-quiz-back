<?php

namespace App\Factory;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Answer>
 *
 * @method        Answer|Proxy                     create(array|callable $attributes = [])
 * @method static Answer|Proxy                     createOne(array $attributes = [])
 * @method static Answer|Proxy                     find(object|array|mixed $criteria)
 * @method static Answer|Proxy                     findOrCreate(array $attributes)
 * @method static Answer|Proxy                     first(string $sortedField = 'id')
 * @method static Answer|Proxy                     last(string $sortedField = 'id')
 * @method static Answer|Proxy                     random(array $attributes = [])
 * @method static Answer|Proxy                     randomOrCreate(array $attributes = [])
 * @method static AnswerRepository|RepositoryProxy repository()
 * @method static Answer[]|Proxy[]                 all()
 * @method static Answer[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Answer[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Answer[]|Proxy[]                 findBy(array $attributes)
 * @method static Answer[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Answer[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class AnswerFactory extends ModelFactory
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
            'question' => QuestionFactory::new(),
            'title' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Answer $answer): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Answer::class;
    }
}
