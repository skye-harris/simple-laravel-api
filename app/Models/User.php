<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email',
        'email_verified_at',
        'email_verification_token'
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
        ];
    }

    public function posts(): HasMany {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany {
        return $this->hasMany(Comment::class);
    }

    public function delete()
    {
        // Before deleting our User, lets delete our Posts
        $this->posts()->get()->map(static function (Post $post) {
            $post->delete();
        });

        // Lets orphan any comments left behind on other users Posts, else any responses. Alternatively these could also be deleted.
        $this->comments()->get()->map(static function (Comment $comment) {
            $comment->update([
                'user_id' => null
            ]);
        });

        return parent::delete();
    }
}
