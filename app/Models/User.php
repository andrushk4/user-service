<?php

declare(strict_types=1);

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\HashedPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /** @var string */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var array<string, string> */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'telegram_id',
        'password',
        'is_email_verified',
        'is_phone_verified',
        'is_telegram_verified',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_email_verified' => 'boolean',
        'is_phone_verified' => 'boolean',
        'is_telegram_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string) Uuid::uuid4();
        });
    }

    // Мутаторы и аксессоры для Value Objects
    public function getEmailAttribute(?string $value): ?Email
    {
        return $value ? new Email($value) : null;
    }

    public function setEmailAttribute(?Email $value): void
    {
        $this->attributes['email'] = $value?->value;
    }

    public function getPhoneAttribute(?string $value): ?Phone
    {
        return $value ? new Phone($value) : null;
    }

    public function setPhoneAttribute(?Phone $value): void
    {
        $this->attributes['phone'] = $value?->value;
    }

    public function getTelegramIdAttribute(?string $value): ?TelegramId
    {
        return $value ? new TelegramId($value) : null;
    }

    public function setTelegramIdAttribute(?TelegramId $value): void
    {
        $this->attributes['telegram_id'] = $value?->value;
    }

    public function getPasswordAttribute(?string $value): ?HashedPassword
    {
        return $value ? new HashedPassword($value) : null;
    }

    public function setPasswordAttribute(?HashedPassword $value): void
    {
        $this->attributes['password'] = $value?->value;
    }

    public function getIdAttribute(?string $value): UuidInterface
    {
        return Uuid::fromString($value);
    }

    public function setIdAttribute(UuidInterface $value): void
    {
        $this->attributes['id'] = (string) $value;
    }
}
