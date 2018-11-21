<?php

namespace App\Admin\Controllers;

use App\Scart\Promocodes\Models\Promocode;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ShopPromotionController extends GeneralController
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public $arrType = [
        '0' => 'Cash',
        '1' => 'Point',
        '2' => '%',
    ];
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header(trans('language.admin.promotion_manager'));
            $content->description(' ');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header(trans('language.admin.promotion_manager'));
            $content->description(' ');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header(trans('language.admin.promotion_manager'));
            $content->description(' ');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Promocode::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->code(trans('language.promotion.code'));
            $grid->reward(trans('language.promotion.value'))->display(function ($reward) {
                return number_format($reward);
            });
            $arrType = $this->arrType;
            $grid->type(trans('language.promotion.type'))->display(function ($type) use ($arrType) {
                if ($type == 0) {
                    return "<span class='label label-success'>$arrType[$type]</span>";
                } elseif ($type == 1) {
                    return "<span class='label label-warning'>$arrType[$type]</span>";
                } elseif ($type == 2) {
                    return "<span class='label label-info'>$arrType[$type]</span>";
                }

            });
            $grid->data(trans('language.promotion.description'));
            $grid->number_uses(trans('language.promotion.maximum'));
            $grid->used(trans('language.promotion.used'));
            $grid->users(trans('language.promotion.history'))->expand(function () {
                $dataPromo = Promocode::find($this->id);
                $html      = '<br>';
                foreach ($dataPromo->users as $key => $value) {
                    $html .= '<span style="padding-left:20px;"><i class="fa fa-angle-double-right"></i> ' . trans('language.promotion.customer') . ' ID' . $value->pivot->user_id . trans('language.promotion.used_at') . $value->pivot->used_at . '.  ' . trans('language.promotion.content') . ': ' . $value->pivot->log . '</span><br>';
                }
                return $html . "<br>";
            }, trans('language.promotion.history'));
            $grid->status()->switch();
            $grid->expires_at(trans('language.promotion.expire'));
            $grid->disableExport();
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Promocode::class, function (Form $form) {
            $form->text('code', trans('language.promotion.code'))->rules(function ($form) {
                return 'required|regex:/(^([0-9A-Za-z\-]+)$)/|unique:promocodes,code,' . $form->model()->id . ',id';
            }, ['unique' => trans('language.promotion.exist'), 'regex' => trans('language.promotion.validate')])->placeholder(trans('language.promotion.example') . ' SAVEOFF2018,SAVE50,...')->help(trans('language.promotion.validate'));

            $form->number('reward', trans('language.promotion.value'))->rules('numeric|min:0');
            $form->select('type', trans('language.promotion.type'))->options($this->arrType)->help(trans('language.promotion.note'));
            $form->text('data', trans('language.promotion.description'));
            $form->number('number_uses', trans('language.promotion.maximum'))->default(1)->rules('numeric|min:0');
            $form->datetime('expires_at', trans('language.promotion.expire'));
            $form->switch('status', trans('language.admin.status'));
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('');
            $content->description('');
            $content->body(Admin::show(Promocode::findOrFail($id), function (Show $show) {
                $show->id('ID');
            }));
        });
    }
}
