<?php

namespace App\Factory;

use App\Entity\StudentAnswer;
use App\Repository\StudentAnswerRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<StudentAnswer>
 *
 * @method        StudentAnswer|Proxy                     create(array|callable $attributes = [])
 * @method static StudentAnswer|Proxy                     createOne(array $attributes = [])
 * @method static StudentAnswer|Proxy                     find(object|array|mixed $criteria)
 * @method static StudentAnswer|Proxy                     findOrCreate(array $attributes)
 * @method static StudentAnswer|Proxy                     first(string $sortedField = 'id')
 * @method static StudentAnswer|Proxy                     last(string $sortedField = 'id')
 * @method static StudentAnswer|Proxy                     random(array $attributes = [])
 * @method static StudentAnswer|Proxy                     randomOrCreate(array $attributes = [])
 * @method static StudentAnswerRepository|RepositoryProxy repository()
 * @method static StudentAnswer[]|Proxy[]                 all()
 * @method static StudentAnswer[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static StudentAnswer[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static StudentAnswer[]|Proxy[]                 findBy(array $attributes)
 * @method static StudentAnswer[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static StudentAnswer[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class StudentAnswerFactory extends ModelFactory
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
            'annotation' => self::faker()->text(255),
            'question' => QuestionFactory::new(),
            'score' => self::faker()->randomNumber(),
            'studentCopy' => StudentCopyFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(StudentAnswer $studentAnswer): void {})
        ;
    }

    protected static function getClass(): string
    {
        return StudentAnswer::class;
    }
}
