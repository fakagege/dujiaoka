namespace App\Providers;

use App\Services\Cards;
use App\Services\Coupons;
use App\Services\Email;
use App\Services\Shop;
use App\Services\OrderProcess;
use App\Services\Orders;
use App\Services\Payment;
use App\Services\Validator;
use App\Services\CacheManager;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Shop::class);
        $this->app->singleton(Payment::class);
        $this->app->singleton(Cards::class);
        $this->app->singleton(Orders::class);
        $this->app->singleton(Coupons::class);
        $this->app->singleton(OrderProcess::class);
        $this->app->singleton(Email::class);
        $this->app->singleton(Validator::class);
        $this->app->singleton(CacheManager::class);
        $this->app->singleton('Jenssegers\Agent', function () {
            return $this->app->make(Agent::class);
        });
        $this->app->singleton('App\\Services\\ConfigService', function ($app) {
            return new \App\Services\ConfigService();
        });
        $this->app->singleton('App\\Services\\ThemeService');
    }

    public function boot()
    {
        // 检测是否为安装页面
        if (!request()->is('install') && !request()->is('install/*')) {
            // 只有非安装页面才执行数据库相关操作
            $this->app->booted(function () {
                try {
                    $currency = shop_cfg('currency', 'cny');
                    $symbols = [
                        'cny' => '¥',
                        'usd' => '$',
                    ];
                    $symbol = $symbols[$currency] ?? '¥';

                    // 设置所有语言文件的货币符号
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_CN');
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_TW');
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'en');
                } catch (\Exception $e) {
                    // 如果数据库不可用，使用默认货币符号
                    $symbol = '¥';
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_CN');
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_TW');
                    app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'en');
                }
            });
        } else {
            // 安装页面使用默认货币符号
            $symbol = '¥';
            app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_CN');
            app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'zh_TW');
            app('translator')->addLines(['dujiaoka.money_symbol' => $symbol], 'en');
        }
    }
}