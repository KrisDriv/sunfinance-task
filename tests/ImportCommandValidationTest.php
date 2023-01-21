<?php
declare(strict_types=1);

use App\Commands\ImportJsonCommand;
use Composite\Entity\AbstractEntity;
use PHPUnit\Framework\TestCase;

class ImportCommandValidationTest extends TestCase
{

    private function createCommandSubject(): ImportJsonCommand
    {
        return new class extends ImportJsonCommand {
        };
    }

    /**
     * @throws ReflectionException
     */
    public function testMissingField(): void
    {
        $command = $this->createCommandSubject();

        $this->expectException(Exception::class);
        $this->expectErrorMessage("Property 'is_valid' is required but missing");

        $command->transformRowDataAccordingToEntityMetadata([], MissingFieldTestSubjectEntity::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testValidProperty(): void
    {
        $command = $this->createCommandSubject();

        $this->assertEquals(
            ['isValid' => true],
            $command->transformRowDataAccordingToEntityMetadata(['is_valid' => true], ValidPropertyTestSubjectEntity::class)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testOptionalProperty(): void
    {
        $command = $this->createCommandSubject();

        $this->assertEquals(
            ['isValid' => null],
            $command->transformRowDataAccordingToEntityMetadata([], OptionalPropertyTestSubjectEntity::class)
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidPropertyUnionType(): void
    {
        $command = $this->createCommandSubject();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid type, expected one of following types: string, int. Got bool instead.');

        $command->transformRowDataAccordingToEntityMetadata(['name' => true], InvalidPropertyUnionTypeTestSubjectEntity::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidBuiltinPropertyType(): void
    {
        $command = $this->createCommandSubject();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid type, expected bool. Got string instead.');

        $command->transformRowDataAccordingToEntityMetadata(
            ['is_valid' => 'invalid'],
            InvalidBuiltinPropertyTypeTestSubjectEntity::class
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testValidDatePropertyType(): void
    {
        $command = $this->createCommandSubject();

        $data = $command->transformRowDataAccordingToEntityMetadata(
            ['date_time' => '1/21/2023 3:37 AM'],
            ValidDatePropertyTypeTestSubjectEntity::class
        );
        $dateTimeObject = $data['dateTime'];

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $dateTimeObject,
            'Property did not resolve to correct type'
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testDefaultDatePropertyType(): void
    {
        $command = $this->createCommandSubject();

        $data = $command->transformRowDataAccordingToEntityMetadata(
            [],
            DefaultDatePropertyTypeTestSubjectEntity::class
        );
        $dateTimeObject = $data['dateTime'];

        $this->assertInstanceOf(
            DateTimeImmutable::class,
            $dateTimeObject,
            'Property did not read promoted parameter\'s value'
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