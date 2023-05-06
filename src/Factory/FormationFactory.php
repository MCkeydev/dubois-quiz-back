<?php

namespace App\Factory;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Formation>
 *
 * @method        Formation|Proxy                     create(array|callable $attributes = [])
 * @method static Formation|Proxy                     createOne(array $attributes = [])
 * @method static Formation|Proxy                     find(object|array|mixed $criteria)
 * @method static Formation|Proxy                     findOrCreate(array $attributes)
 * @method static Formation|Proxy                     first(string $sortedField = 'id')
 * @method static Formation|Proxy                     last(string $sortedField = 'id')
 * @method static Formation|Proxy                     random(array $attributes = [])
 * @method static Formation|Proxy                     randomOrCreate(array $attributes = [])
 * @method static FormationRepository|RepositoryProxy repository()
 * @method static Formation[]|Proxy[]                 all()
 * @method static Formation[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Formation[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Formation[]|Proxy[]                 findBy(array $attributes)
 * @method static Formation[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Formation[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class FormationFactory extends ModelFactory
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
            'name' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Formation $formation): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Formation::class;
    }
}
