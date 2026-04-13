<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PERFIL_ADMIN = 'admin';
    public const PERFIL_GESTOR = 'gestor';
    public const PERFIL_OPERACIONAL = 'operacional';
    public const PERFIS = [
        self::PERFIL_ADMIN,
        self::PERFIL_GESTOR,
        self::PERFIL_OPERACIONAL,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'perfil',
        'cooperativa_id',
        'ativo',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    public function cooperativa(): BelongsTo
    {
        return $this->belongsTo(Cooperativa::class);
    }

    public function cooperativas(): BelongsToMany
    {
        return $this->belongsToMany(Cooperativa::class, 'cooperativa_user')
            ->withTimestamps();
    }

    public function papeis(): BelongsToMany
    {
        return $this->belongsToMany(Papel::class, 'user_papel');
    }

    public function isAdmin(): bool
    {
        return $this->perfil === self::PERFIL_ADMIN;
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class, 'responsavel_id');
    }

    public function andamentosCaso(): HasMany
    {
        return $this->hasMany(AndamentoCaso::class, 'usuario_id');
    }

    /**
     * @return array<int>
     */
    public function cooperativasIds(): array
    {
        $ids = [];

        if ($this->cooperativa_id !== null) {
            $ids[] = (int) $this->cooperativa_id;
        }

        $idsRelacionamento = $this->relationLoaded('cooperativas')
            ? $this->cooperativas
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all()
            : $this->cooperativas()
                ->pluck('cooperativas.id')
                ->map(fn ($id): int => (int) $id)
                ->all();

        $ids = [...$ids, ...$idsRelacionamento];

        return collect($ids)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function cooperativaPrincipalId(): ?int
    {
        $ids = $this->cooperativasIds();

        return $ids[0] ?? null;
    }

    public function pertenceCooperativa(?int $cooperativaId): bool
    {
        if ($cooperativaId === null || $cooperativaId <= 0) {
            return false;
        }

        return in_array((int) $cooperativaId, $this->cooperativasIds(), true);
    }
}
