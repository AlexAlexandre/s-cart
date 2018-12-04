<?php
#app/Models/ShopCurrency.php
/**
 * @author Naruto <lanhktc@gmail.com>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCurrency extends Model
{
    public $table                   = 'shop_currency';
    public $timestamps              = false;
    protected static $code          = '';
    protected static $name          = '';
    protected static $symbol        = '';
    protected static $exchange_rate = 1;
    protected static $precision     = 0;
    protected static $symbol_first  = 0;
    protected static $thousands     = ',';
    protected static $decimal       = '.';

/**
 * [setCode description]
 * @param [type] $code [description]
 */

    public static function setCode($code)
    {
        self::$code    = $code;
        $checkCurrency = self::where('code', $code)->first();
        if ($checkCurrency) {
            self::$name          = $checkCurrency->name;
            self::$symbol        = $checkCurrency->symbol;
            self::$exchange_rate = $checkCurrency->exchange_rate;
            self::$precision     = $checkCurrency->precision;
            self::$symbol_first  = $checkCurrency->symbol_first;
            self::$thousands     = $checkCurrency->thousands;
            self::$decimal       = ($checkCurrency->thousands == '.') ? ',' : '.';
        }
    }

/**
 * [getCurrency description]
 * @return [type] [description]
 */
    public static function getCurrency()
    {
        return [
            'code'          => self::$code,
            'name'          => self::$name,
            'symbol'        => self::$symbol,
            'exchange_rate' => self::$exchange_rate,
            'precision'     => self::$precision,
            'symbol_first'  => self::$symbol_first,
            'thousands'     => self::$thousands,
            'decimal'       => self::$decimal,
        ];
    }

/**
 * [getCode description]
 * @return [type] [description]
 */
    public static function getCode()
    {
        return self::$code;
    }
/**
 * [getRate description]
 * @return [type] [description]
 */
    public static function getRate()
    {
        return self::$exchange_rate;
    }

/**
 * [getValue description]
 * @param  float  $money [description]
 * @param  [type] $rate  [description]
 * @return [type]        [description]
 */
    public static function getValue(float $money, $rate = null)
    {
        if ($rate) {
            return $money * $rate;
        } else {
            return $money * self::$exchange_rate;
        }

    }

/**
 * [format description]
 * @param  float  $money [description]
 * @return [type]        [description]
 */
    public static function format(float $money)
    {
        return number_format($money, self::$precision, self::$decimal, self::$thousands);
    }

/**
 * [render description]
 * @param  float   $money                [description]
 * @param  [type]  $currency             [description]
 * @param  [type]  $rate                 [description]
 * @param  boolean $space_between_symbol [description]
 * @param  boolean $include_symbol       [description]
 * @return [type]                        [description]
 */
    public static function render(float $money, $currency = null, $rate = null, $space_between_symbol = false, $include_symbol = true)
    {
        $dataCurrency = self::getCurrency();

        if ($currency) {
            $checkCurrency = self::where('code', $currency)->first();
            if ($checkCurrency) {
                $dataCurrency = $checkCurrency;
            }
        }
        $value = self::getValue($money, $rate);
        if ($dataCurrency['symbol_first']) {
            if ($money < 0) {
                return '-' . (($include_symbol) ? $dataCurrency['symbol'] : '') . (($space_between_symbol) ? ' ' : '') . self::format(abs($value));
            } else {
                return (($include_symbol) ? $dataCurrency['symbol'] : '') . (($space_between_symbol) ? ' ' : '') . self::format($value);
            }

        } else {
            return self::format($value) . (($space_between_symbol) ? ' ' : '') . (($include_symbol) ? $dataCurrency['symbol'] : '');
        }
    }

    public function scopeSort($query)
    {
        return $query->orderBy('sort', 'desc')->orderBy('id', 'desc');
    }

    public static function getAll()
    {
        return self::where('status', 1)->sort()->get();
    }

}
