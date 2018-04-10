<?php

namespace App\Providers;

use App\Models\Reply;
use App\Models\Topic;
use App\Observers\ReplyObserver;
use App\Observers\TopicObserver;
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
        Topic::observe ( TopicObserver::class );
        Reply::observe ( ReplyObserver::class );
        \Carbon\Carbon::setLocale ( 'zh' );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register ()
    {
        //
    }
}
