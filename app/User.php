<?php

namespace App;

use App\Solr\Cores\LarangCore;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    use EntrustUserTrait, Notifiable;

    use CausesActivity, LogsActivity {
        LogsActivity::activity insteadof CausesActivity;
        CausesActivity::activity as log;
    }

    protected static $logAttributes = ['name', 'email'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     *
     */
    public static function boot() {
        // Initialize Container for Solr
        $larangCore = app(LarangCore::class);

        // Boot Parent Class
        parent::boot();

        // Event while Create/Update User record
        self::saved(function($user) use ($larangCore)
        {
            // Run indexer for update in solr
            $larangCore->indexer('user', $user->id);
        });
        self::deleted(function($user) use ($larangCore)
        {
            // Run indexer for update in solr
            $larangCore->deleteDocument('user-' . $user->id);
        });
    }
}
