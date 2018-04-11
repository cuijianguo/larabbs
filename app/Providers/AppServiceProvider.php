<?php

namespace App\Providers;

use App\Models\Link;
use App\Models\Reply;
use App\Models\Topic;
use App\Models\User;
use App\Observers\LinkObserver;
use App\Observers\ReplyObserver;
use App\Observers\TopicObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot ()
    {
        User::observe ( UserObserver::class );
        Topic::observe ( TopicObserver::class );
        Reply::observe ( ReplyObserver::class );
        Link::observe ( LinkObserver::class );

        \Carbon\Carbon::setLocale ( 'zh' );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register ()
    {
        if ( app ()->isLocal () ) {
            $this->app->register ( \VIACreative\SudoSu\ServiceProvider::class );
        }
    }
}
