<?php
declare(strict_types=1);

use App\Exceptions\Entity\EntityException;
use App\Services\EntityHydrateService;
use Composite\Entity\AbstractEntity;
use DI\DependencyException;
use DI\NotFoundException;
use PHPUnit\Framework\TestCase;

class EntityHydrateTest extends TestCase
{

    protected EntityHydrateService $hydrate;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        global $app;
        parent::__construct($name, $data, $dataName);

        $this->hydrate = $app->getContainer()->get(EntityHydrateService::class);
    }

    /**
     * @throws EntityException
     */
    public function testMissingField(): void
    {
        $this->expectException(EntityException::class);
        $this->expectErrorMessage("Property 'is_valid' is required but missing");

        $this->hydrate->fromArray([], MissingFieldTestSubjectEntity::class);
    }

    /**
     * @throws EntityException
     * @throws \Composite\Entity\Exceptions\EntityException
     */
    public function testValidProperty(): void
    {
        $randomNumber = mt_rand(0, 100);

        $this->assertEquals(
            ['isValid' => true, 'randomNumber' => $randomNumber],
            $this->hydrate->fromArray(
                ['is_valid' => true, 'random_number' => $randomNumber],
                ValidPropertyTestSubjectEntity::class
            )->toArray()
        );
    }

    /**
     * @throws EntityException|\Composite\Entity\Exceptions\EntityException
     */
    public function testOptionalProperty(): void
    {
        $this->assertEquals(
            ['isValid' => null],
            $this->hydrate->fromArray([], OptionalPropertyTestSubjectEntity::class)->toArray()
        );
    }

    /**
     * @throws EntityException
     */
    public function testInvalidPropertyUnionType(): void
    {
        $this->expectException(EntityException::class);
        $this->expectExceptionMessage('Invalid type, expected one of following types: string, int. Got bool instead.');

        $this->hydrate->fromArray(['name' => true], InvalidPropertyUnionTypeTestSubjectEntity::class);
    }

    /**
     * @throws EntityException
     */
    public function testInvalidBuiltinPropertyType(): void
    {
        $this->expectException(EntityException::class);
        $this->expectExceptionMessage('Invalid type, expected bool. Got string instead.');

        $this->hydrate->fromArray(
            ['is_valid' => 'invalid'],
            InvalidBuiltinPropertyTypeTestSubjectEntity::class
        );
    }

    /**
     * @throws EntityException
     */
    public function testValidDatePropertyType(): void
    {
        /** @var ValidDatePropertyTypeTestSubjectEntity $entity */
        $entity = $this->hydrate->fromArray(
            ['date_time' => '1/21/2023 3:37 AM'],
            ValidDatePropertyTypeTestSubjectEntity::class
        );

        $dateTimeObject = $entity->dateTime;

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $dateTimeObject,
            'Property did not resolve to correct type'
        );
    }

    /**
     * @throws EntityException
     */
    public function testDefaultDatePropertyType(): void
    {
        /** @var DefaultDatePropertyTypeTestSubjectEntity $entity */
        $entity = $this->hydrate->fromArray(
            [],
            DefaultDatePropertyTypeTestSubjectEntity::class
        );

        $dateTimeObject = $entity->dateTime;

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $dateTimeObject,
            'Property did not read promoted parameter\'s value'
        );
    }

    /**
     * @throws EntityException
     */
    public function testValidEnumTypeProperty(): void
    {
        /** @var EnumPropertyTypeTestSubjectEntity $entity */
        $entity = $this->hydrate->fromArray(
            ['enum' => 'FOO'],
            EnumPropertyTypeTestSubjectEntity::class
        );

        $this->assertEquals(TestEnum::FOO, $entity->enum, 'enum property did not evaluate properly');
    }

    /**
     * @throws EntityException
     */
    public function testInvalidEnumTypeProperty(): void
    {
        $this->expectException(EntityException::class);
        $this->expectErrorMessage('Undefined constant TestEnum::INVALID_ENUM_VALUE');

        /** @var EnumPropertyTypeTestSubjectEntity $entity */
        $this->hydrate->fromArray(
            ['enum' => 'INVALID_ENUM_VALUE'],
            EnumPropertyTypeTestSubjectEntity::class
        );
    }

}

class MissingFieldTestSubjectEntity extends AbstractEntity
{
    public function __construct(public bool $isValid)
    {

    }
}

class ValidPropertyTestSubjectEntity extends AbstractEntity
{
    public int $randomNumber;

    public function __construct(public bool $isValid)
    {

    }
}

class OptionalPropertyTestSubjectEntity extends AbstractEntity
{
    public function __construct(public ?bool $isValid)
    {
    }
}

class InvalidPropertyUnionTypeTestSubjectEntity extends AbstractEntity
{
    public function __construct(public string|int $name)
    {
    }
}

class InvalidBuiltinPropertyTypeTestSubjectEntity extends AbstractEntity
{
    public function __construct(public bool $isValid)
    {
    }
}

class ValidDatePropertyTypeTestSubjectEntity extends AbstractEntity
{
    public function __construct(public DateTimeImmutable $dateTime)
    {
    }
}

class DefaultDatePropertyTypeTestSubjectEntity extends AbstractEntity
{
    public function __construct(public DateTimeImmutable $dateTime = new DateTimeImmutable())
    {
    }
}

class EnumPropertyTypeTestSubjectEntity extends AbstractEntity
{
    public function __construct(public TestEnum $enum)
    {
    }
}

enum TestEnum
{
    case FOO;
}