<?php

namespace App\Factory;

use App\Entity\Evaluation;
use App\Repository\EvaluationRepository;
use Doctrine\DBAL\Types\DateImmutableType;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Evaluation>
 *
 * @method        Evaluation|Proxy create(array|callable $attributes = [])
 * @method static Evaluation|Proxy createOne(array $attributes = [])
 * @method static Evaluation|Proxy find(object|array|mixed $criteria)
 * @method static Evaluation|Proxy findOrCreate(array $attributes)
 * @method static Evaluation|Proxy first(string $sortedField = 'id')
 * @method static Evaluation|Proxy last(string $sortedField = 'id')
 * @method static Evaluation|Proxy random(array $attributes = [])
 * @method static Evaluation|Proxy randomOrCreate(array $attributes = [])
 * @method static EvaluationRepository|RepositoryProxy repository()
 * @method static Evaluation[]|Proxy[] all()
 * @method static Evaluation[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Evaluation[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Evaluation[]|Proxy[] findBy(array $attributes)
 * @method static Evaluation[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Evaluation[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class EvaluationFactory extends ModelFactory
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
            'startsAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'endsAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'quiz' => QuizFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
             ->afterInstantiate(function(Evaluation $evaluation): void {
                 $startDate = new \DateTimeImmutable('now');
                 $endDate = $startDate->add(\DateInterval::createFromDateString( strval(random_int(1, 7)) . 'day'));

                 $evaluation->setStartsAt($startDate);
                 $evaluation->setEndsAt($endDate);
             });
    }

    protected static function getClass(): string
    {
        return Evaluation::class;
    }
}
