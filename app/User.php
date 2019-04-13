<?php

namespace App;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table    = 'shop_users';
    protected $fillable = [
        'name', 'email', 'password', 'address1', 'address2', 'phone',
    ];
    protected $appends = [
        'order_total',
        'order_amount',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany('App\Models\ShopOrder', 'user_id', 'id');
    }

    public function ordersAmount()
    {
        $amount = 0;
        foreach ($this->orders as $order) {
            $amount += $order->total;
        }
        return $amount;
    }

    public function likes()
    {
        return $this->hasMany('App\Models\ShopProductLike', 'users_id', 'id');
    }

    public function getOrderTotalAttribute()
    {
        return $this->orders->count();
    }
    public function getOrderAmountAttribute()
    {
        return $this->ordersAmount();
    }

    //Send email reset password
    public function sendPasswordResetNotification($token)
    {
        if (\Helper::configs()['forgot_password']) {
            $content = (new EmailTemplate)->where('group', 'forgot_password')->where('status', 1)->first()->text;

            $dataFind = [
                '/\{\{\$text1\}\}/',
                '/\{\{\$text2\}\}/',
                '/\{\{\$text3\}\}/',
                '/\{\{\$reset_link\}\}/',
                '/\{\{\$reset_button\}\}/',
            ];
            $dataReplace = [
                trans('email.reset_password.text1'),
                trans('email.reset_password.text2', ['site_admin' => config('mail.from.name')]),
                trans('email.reset_password.text3', ['reset_button' => trans('email.reset_password.reset_button')]),
                route('password.reset', ['token' => $token]),
                trans('email.reset_password.reset_button'),
            ];
            $content = preg_replace($dataFind, $dataReplace, $content);
            $data    = [
                'title'   => trans('email.reset_password.title'),
                'content' => $content,
            ];

            $config = [
                'to'      => $this->getEmailForPasswordReset(),
                'subject' => trans('email.reset_password.reset_button'),
            ];

            \Helper::sendMail('mail.resetPassword', $data, $config, []);

        } else {
            return false;
        }

    }

}
