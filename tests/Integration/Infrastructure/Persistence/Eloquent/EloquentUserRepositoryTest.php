<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence\Eloquent;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Models\User as EloquentUser;
use App\Domain\Entity\User\User as DomainUser;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\HashedPassword;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Ramsey\Uuid\UuidInterface;

class EloquentUserRepositoryTest extends TestCase
{
    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserRepository();
    }

    public function testFindByIdReturnsUserWhenExists(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('test@example.com');
        
        EloquentUser::create([
            'id' => $userId,
            'email' => $email,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->findById($userId);

        $this->assertInstanceOf(DomainUser::class, $result);
        $this->assertEquals($userId, $result->getId());
        $this->assertEquals($email->value, $result->getEmail()->value);
    }

    public function testFindByIdReturnsNullWhenNotExists(): void
    {
        $userId = Uuid::uuid4();

        $result = $this->repository->findById($userId);

        $this->assertNull($result);
    }

    public function testFindByEmailReturnsUserWhenExists(): void
    {
        $email = new Email('test123@example.com');
        $userId = Uuid::uuid4();
        
        EloquentUser::create([
            'id' => $userId,
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->findByEmail($email);

        $this->assertInstanceOf(DomainUser::class, $result);
        $this->assertEquals($email->value, $result->getEmail()->value);
    }

    public function testFindByEmailReturnsNullWhenNotExists(): void
    {
        $email = new Email('nonexistent@example.com');

        $result = $this->repository->findByEmail($email);

        $this->assertNull($result);
    }

    public function testFindByPhoneReturnsUserWhenExists(): void
    {
        $phone = new Phone('+1234567890');
        $userId = Uuid::uuid4();
        
        EloquentUser::create([
            'id' => $userId,
            'phone' => $phone,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->findByPhone($phone);

        $this->assertInstanceOf(DomainUser::class, $result);
        $this->assertEquals($phone->value, $result->getPhone()->value);
    }

    public function testFindByTelegramIdReturnsUserWhenExists(): void
    {
        $telegramId = new TelegramId('123456789');
        $userId = Uuid::uuid4();
        
        EloquentUser::create([
            'id' => $userId,
            'telegram_id' => $telegramId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->repository->findByTelegramId($telegramId);

        $this->assertInstanceOf(DomainUser::class, $result);
        $this->assertEquals($telegramId->value, $result->getTelegramId()->value);
    }

    public function testSaveCreatesNewUser(): void
    {
        $email = new Email('new@example.com');
        $password = new HashedPassword('password');
        
        $domainUser = $this->createDomainUser($email, 'Foo', 'Bar', $password);

        $this->repository->save($domainUser);

        $this->assertDatabaseHas('users', [
            'id' => (string) $domainUser->getId(),
            'email' => $email->value,
        ]);
    }

    public function testSaveUdatesExistingUser(): void
    {
        $userId = Uuid::uuid4();
        $originalEmail = new Email('original@example.com');
        $newEmail = new Email('updated@example.com');
        
        // Создаем пользователя в БД
        EloquentUser::create([
            'id' => $userId,
            'email' => $originalEmail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создаем доменную модель с обновленными данными
        $domainUser = $this->createDomainUser($newEmail, 'Updated', 'User', new HashedPassword('newpassword'), $userId);

        $this->repository->save($domainUser);

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => $newEmail->value,
        ]);
        
        $this->assertDatabaseMissing('users', [
            'id' => $userId,
            'email' => $originalEmail,
        ]);
    }

    public function testDeleteRemovesUser(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('delete@example.com');
        
        EloquentUser::create([
            'id' => $userId,
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $domainUser = $this->createDomainUser($email, 'Delete', 'User', new HashedPassword('password'), $userId);

        $this->repository->delete($domainUser);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }

    /**
     * Вспомогательный метод для создания доменной модели пользователя
     */
    private function createDomainUser(
        Email $email,
        string $firstName,
        string $lastName,
        HashedPassword $password,
        ?UuidInterface $id = null,
    ): DomainUser {
        $user = DomainUser::registerNew(
            $email,
            null,
            null,
            $password,
            $firstName,
            $lastName
        );

        if ($id !== null) {
            return DomainUser::fromPersistence(
                $id,
                $user->getEmail(),
                $user->getPhone(),
                $user->getTelegramId(),
                $user->getPassword(),
                $user->isEmailVerified(),
                $user->isPhoneVerified(),
                $user->isTelegramVerified(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getCreatedAt(),
                $user->getUpdatedAt(),
            );
        }

        return $user;
    }
}
