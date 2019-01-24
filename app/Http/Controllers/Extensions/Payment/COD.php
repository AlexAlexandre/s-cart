<?php
#app\Http\Controller\Extension\Payment\Paypal.php
namespace App\Http\Controllers\Extensions\Payment;

use App\Models\Config;

class COD extends \App\Http\Controllers\Controller
{
    protected $configKey  = 'COD';
    protected $configCode = 'Payment';
    public $title;
    const ALLOW  = 1;
    const DENIED = 0;

    public function __construct()
    {
        $this->title = trans('Extensions/' . $this->configCode . '/' . $this->configKey . '.title');
    }

    public function getData()
    {
        return $this->processData();
    }

    public function processData()
    {
        $arrPayment = [
            'code'       => $this->configKey,
            'title'      => $this->title,
            'fee'        => 0, //Fee when use this payment method
            'permission' => self::ALLOW,
        ];
        return $arrPayment;
    }

    public function install()
    {
        $return = ['error' => 0, 'msg' => ''];
        $check  = Config::where('key', $this->configKey)->first();
        if ($check) {
            $return = ['error' => 1, 'msg' => 'Module exist'];
        } else {
            $process = Config::insert(
                [
                    'code'   => $this->configCode,
                    'key'    => $this->configKey,
                    'sort'   => 0, // Sort extensions in group
                    'value'  => 1, //1- Enable extension; 0 - Disable
                    'detail' => 'Extensions/' . $this->configCode . '/' . $this->configKey . '.title',
                ]
            );
            if (!$process) {
                $return = ['error' => 1, 'msg' => 'Error when install'];
            }
        }
        return $return;
    }

    public function uninstall()
    {
        $return  = ['error' => 0, 'msg' => ''];
        $process = (new Config)->where('key', $this->configKey)->delete();
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error when uninstall'];
        }
        return $return;
    }
    public function enable()
    {
        $return  = ['error' => 0, 'msg' => ''];
        $process = (new Config)->where('key', $this->configKey)->update(['value' => 1]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error enable'];
        }
        return $return;
    }
    public function disable()
    {
        $return  = ['error' => 0, 'msg' => ''];
        $process = (new Config)->where('key', $this->configKey)->update(['value' => 0]);
        if (!$process) {
            $return = ['error' => 1, 'msg' => 'Error disable'];
        }
        return $return;
    }

    public function config()
    {
//
    }

    public function process($data)
    {
//
    }

}
