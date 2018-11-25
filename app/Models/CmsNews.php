<?php
#app/Models/CmsNews.php
namespace App\Models;

use App\Models\CmsNewsDescription;
use App\Models\Language;
use Helper;
use Illuminate\Database\Eloquent\Model;

class CmsNews extends Model
{
    public $table      = 'cms_news';
    protected $appends = [
        'title',
        'keyword',
        'description',
        'content',
    ];

    public function local()
    {
        $lang = Language::pluck('id', 'code')->all();
        return CmsNewsDescription::where('cms_news_id', $this->id)
            ->where('lang_id', $lang[app()->getLocale()])
            ->first();
    }
    public function getItemsNews($limit = null, $opt = null)
    {
        $query = (new CmsNews)->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'desc');
        if (!(int) $limit) {
            return $query->get();
        } else
        if ($opt == 'paginate') {
            return $query->paginate((int) $limit);
        } else {
            return $query->limit($limit)->get();
        }

    }
/**
 * [getThumb description]
 * @return [type] [description]
 */
    public function getThumb()
    {
        if ($this->image) {
            $path_file = config('filesystems.disks.path_file', '');
            if (!file_exists($path_file . '/thumb/' . $this->image)) {
                return $this->getImage();
            } else {
                if (!file_exists($path_file . '/thumb/' . $this->image)) {
                } else {
                    return $path_file . '/thumb/' . $this->image;
                }
            }
        } else {
            return 'images/no-image.jpg';
        }

    }

/**
 * [getImage description]
 * @return [type] [description]
 */
    public function getImage()
    {
        if ($this->image) {
            $path_file = config('filesystems.disks.path_file', '');
            if (!file_exists($path_file . '/' . $this->image)) {
                return 'images/no-image.jpg';
            } else {
                return $path_file . '/' . $this->image;
            }
        } else {
            return 'images/no-image.jpg';
        }

    }
/**
 * [getUrl description]
 * @return [type] [description]
 */
    public function getUrl()
    {
        return url('blog/' . Helper::strToUrl($this->title) . '_' . $this->id . '.html');
    }

    //Fields language
    public function getTitle()
    {
        return empty($this->local()->title) ? '' : $this->local()->title;
    }
    public function getKeyword()
    {
        return empty($this->local()->keyword) ? '' : $this->local()->keyword;
    }
    public function getDescription()
    {
        return empty($this->local()->description) ? '' : $this->local()->description;
    }
    public function getContent()
    {
        return empty($this->local()->content) ? '' : $this->local()->content;
    }
//Attributes
    public function getTitleAttribute()
    {
        return $this->getTitle();
    }
    public function getKeywordAttribute()
    {
        return $this->getKeyword();

    }
    public function getDescriptionAttribute()
    {
        return $this->getDescription();

    }

    public function getContentAttribute()
    {
        return $this->getContent();

    }

}
