<?php
#app/Admin/Controller/ShopProductController.php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ShopBrand;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopProductDescription;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ShopProductController extends Controller
{
    use ModelForm;
    public $arrType = ['0' => 'Mặc định', '1' => 'New'];

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $action = \Request::input('action');
            if ($action == 'report') {
                $content->header('Thống kê sản phẩm');
                $content->body($this->report());
            } else {
                $content->header('Quản lý sản phẩm');
                $content->description(' ');
                $content->body($this->grid());
            }

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

            $content->header('Chỉnh sửa sản phẩm');
            $content->description(' ');

            $content->body($this->form($id)->edit($id));
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

            $content->header('Tạo sản phẩm mới');
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
        return Admin::grid(ShopProduct::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('Tên sản phẩm')->sortable();
            $grid->category()->name('Danh mục');
            $grid->image('Hình ảnh')->image('', 50);
            $grid->cost('Giá cost')->display(function ($price) {
                return number_format($price);
            });
            $grid->price('Giá bán')->display(function ($price) {
                return number_format($price);
            });
            $arrType = $this->arrType;
            $grid->type('Loại sản phẩm')->display(function ($type) use ($arrType) {
                $style = ($type == 1) ? 'class="label label-success"' : (($type == 2) ? '  class="label label-danger"' : 'class="label label-default"');
                return '<span ' . $style . '>' . $arrType[$type] . '</span>';
            });
            $grid->status('Hiển thị sản phẩm')->switch();
            $grid->created_at('Ngày tạo');
            $grid->model()->orderBy('id', 'desc');
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
    protected function form($id = null)
    {

        return Admin::form(ShopProduct::class, function (Form $form) use ($id) {
            $languages = Language::where('status', 1)->get();
            $form->tab('Thông tin sản phẩm', function ($form) use ($languages) {

                $routeName        = \Route::currentRouteName();
                $action           = \Route::getCurrentRoute()->getActionMethod();
                $langDescriptions = array();
                $idCheck          = 0;
                if ($action === 'edit') {
                    $fullUrl  = url()->current();
                    $pathName = explode('.', $routeName)[0];
                    $idCheck  = empty(explode($pathName . '/', $fullUrl)[1]) ? 0 : (int) explode($pathName . '/', $fullUrl)[1];
                }

                foreach ($languages as $key => $language) {
                    if ($idCheck) {
                        $langDescriptions = ShopProductDescription::where('product_id', $idCheck)->where('lang_id', $language->id)->first();
                    }
                    $form->html('<b>' . $language->name . '</b> <img style="height:25px" src="/' . config('filesystems.disks.path_file') . '/' . $language->icon . '">');
                    $form->text($language->code . '[name]', 'Tên')->rules('required', ['required' => 'Bạn chưa nhập tên'])->default(!empty($langDescriptions->name) ? $langDescriptions->name : null);
                    $form->text($language->code . '[keyword]', 'Keyword')->default(!empty($langDescriptions->keyword) ? $langDescriptions->keyword : null);
                    $form->textarea($language->code . '[description]', 'Description')->rules('max:300', ['max' => 'Tối đa 300 kí tự'])->default(!empty($langDescriptions->description) ? $langDescriptions->description : null);
                    $form->ckeditor($language->code . '[content]', 'Nội dung')->default(!empty($langDescriptions->content) ? $langDescriptions->content : null);
                    $form->divide();
                }

                $arrBrand = ShopBrand::pluck('name', 'id')->all();
                $arrBrand = ['0' => '-- Chọn nhãn hiệu --'] + $arrBrand;
                $arrCate  = (new ShopCategory)->listCate();
                $form->select('category_id', 'Danh mục')->options($arrCate)->rules('required', [
                    'required' => 'Bạn chưa chọn danh mục']
                );
                $form->image('image', 'Hình ảnh')->uniqueName()->move('product');
                $form->currency('price', 'Giá bán')->symbol('VND')->options(['digits' => 0]);
                $form->currency('cost', 'Giá cost')->symbol('VND')->options(['digits' => 0]);
                $form->number('stock', 'Số lượng');
                $form->text('sku', 'Mã hàng')->rules(function ($form) {
                    return 'required|unique:shop_product,sku,' . $form->model()->id . ',id';
                }, ['required' => 'Bạn chưa nhập mã hàng', 'unique' => 'Mã hàng này đã có rồi'])->placeholder('Ví dụ: ABKOOT01,ABKOOT02,...')->help('Mã sản phẩm là duy nhất. Viết liền, không dấu');
                $form->select('brand_id', 'Nhãn hiệu')->options($arrBrand)->default('0')->rules('required', [
                    'required' => 'Bạn chưa chọn danh mục']
                );

                $form->switch('status', 'Hiển thị sản phẩm');
                $form->number('sort', 'Sắp xếp');
                $form->divide();
                $form->radio('type', 'Loại sản phẩm')->options($this->arrType)->default('0');
                $form->datetime('date_available', 'Ngày bán')->help('Ngày cho khách mua. Mặc định cho phép mua từ ngày đăng bán');

            })->tab('Hình ảnh phụ', function ($form) {
                $form->hasMany('images', 'Hình ảnh phụ', function (Form\NestedForm $form) {
                    $form->image('image', 'Hình ảnh nhỏ')->uniqueName()->move('product_slide');
                });

            });

            $arrData = array();
            $form->saving(function (Form $form) use ($languages, &$arrData) {
                foreach ($languages as $key => $language) {
                    $arrData[$language->code]            = $form->{$language->code};
                    $arrData[$language->code]['lang_id'] = $language->id;
                }
            });

//saved
            $form->saved(function (Form $form) use ($languages, &$arrData) {
                $id = $form->model()->id;
                foreach ($languages as $key => $language) {
                    $arrData[$language->code]['product_id'] = $id;
                }
                foreach ($arrData as $key => $value) {
                    $checkLangData = ShopProductDescription::where('lang_id', $value['lang_id'])->where('product_id', $value['product_id'])->delete();
                    ShopProductDescription::insert($value);
                }

                $product         = ShopProduct::find($id);
                $file_path_admin = config('filesystems.path_file.admin.root');
                try {
                    if (!file_exists($file_path_admin . '/thumb/' . $product->image)) {
                        \Image::make($file_path_admin . '/' . $product->image)->insert(public_path('watermark.png'), 'bottom-right', 10, 10)->save($file_path_admin . '/' . $product->image);
                        //thumbnail
                        $image_thumb = \Image::make($file_path_admin . '/' . $product->image);
                        $image_thumb->resize(250, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image_thumb->save($file_path_admin . '/thumb/' . $product->image);
                        //end thumb
                    }
                    if (($product->images)) {
                        foreach ($product->images as $key => $image) {
                            if (!file_exists($file_path_admin . '/thumb/' . $image->image)) {
                                \Image::make($file_path_admin . '/' . $image->image)->insert(public_path('watermark.png'), 'bottom-right', 10, 10)->save($file_path_admin . '/' . $image->image);
                                //thumbnail
                                $image_thumb = \Image::make($file_path_admin . '/' . $image->image);
                                $image_thumb->resize(250, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                                $image_thumb->save($file_path_admin . '/thumb/' . $image->image);
                                //end thumb
                            }
                        }
                    }

                    if (($product->types)) {
                        foreach ($product->types as $key => $image) {
                            if (!file_exists($file_path_admin . '/thumb/' . $image->opt_image)) {
                                \Image::make($file_path_admin . '/' . $image->opt_image)->insert(public_path('watermark.png'), 'bottom-right', 10, 10)->save($file_path_admin . '/' . $image->opt_image);
                                //thumbnail
                                $image_thumb = \Image::make($file_path_admin . '/' . $image->opt_image);
                                $image_thumb->resize(250, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                                $image_thumb->save($file_path_admin . '/thumb/' . $image->opt_image);
                                //end thumb
                            }
                        }
                    }

                } catch (\Exception $e) {
                    echo $e->getMessage();
                }

            });
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });
        });

    }

/**
 * Report product
 * @return [type] [description]
 */
    protected function report()
    {
        return Admin::grid(ShopProduct::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->sku('Mã hàng')->sortable();
            $grid->name('Tên sản phẩm')->sortable();
            $grid->category('Chuyên mục')->display(function ($cate) {
                return $cate['name'];
            });
            $grid->cost('Giá cost')->display(function ($price) {
                return number_format($price);
            })->sortable();
            $grid->price('Giá bán')->display(function ($price) {
                return number_format($price);
            })->sortable();
            $grid->stock('Tồn kho')->sortable();
            $grid->sold('Bán ra')->sortable();
            $arrType = $this->arrType;
            $grid->type('Loại sản phẩm')->display(function ($type) use ($arrType) {
                $style = ($type == 1) ? 'class="label label-success"' : (($type == 2) ? '  class="label label-danger"' : 'class="label label-default"');
                return '<span ' . $style . '>' . $arrType[$type] . '</span>';
            });
            $grid->status('Trạng thái')->display(function ($stt) {
                return ($stt) ? 'Actie' : 'Disabled';
            });
            $grid->model()->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableActions();
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('');
            $content->description('');
            $content->body(Admin::show(ShopProduct::findOrFail($id), function (Show $show) {
                $show->id('ID');
            }));
        });
    }
}
