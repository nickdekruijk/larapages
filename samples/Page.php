<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;
    
    # Define what the PagesAdminController is allowed to to with the model and how
    public $pagesAdmin=[
        'nicename'=>'Website pages',                # Title to show in header
        'index'=>'title,id',                        # Columns to show in listview
        'active'=>'active',                         # Boolean type column that determines if page is active or not
        'sortable'=>'sort',                         # Data can be sorted by dragging, store values in 'sort' column
        'orderBy'=>'sort',                          # Data is ordered by this column
        'orderDesc'=>false,                         # Order descending, true or false. If false or doesn't exist ascending is used
        'treeview'=>'parent',                       # Items can be shown in a treeview, 'parent' column determines parent/child relation
        'expanded'=>3,                              # When treeview is shown auto expand up to 3 levels
        'validate'=>[                               # Laravel validation rules
            'title'=>'required',
            'published_at'=>'date',
            'date'=>'date',
        ],
        'accessors'=>false,                         # Disable accessors when editing model. Use this when accessors modify empty columns for example and you want to leave them blank when editing
        'type'=>[                                   # Column types, this determines the model editing view input types. If ommitted default text input is used
            'active'=>'boolean',
            'published_at'=>'datetime',
            'url'=>'100',
            'html_title'=>'64',
            'description'=>'text',
            'date'=>'date',
            'picture'=>'media,10',
            'body'=>'longtext',
        ],
        'tinymce'=>[                                # List of columns that can contain html and should be edited with TinyMCE
            'body'=>'tinymce options',
        ],
    ];
    
    # Fillable columns, also used by PagesAdminController to build the form so the order matters
    protected $fillable = [
        'active',
        'published_at',
        'title',
        'head',
        'html_title',
        'url',
        'description',
        'date',
        'picture',
        'caption',
        'body',
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','published_at'];
    
    # This scope returns only the active pages and in the right order
    public function scopeActiveSorted($query)
    {
        $query->where('active', 1)->orderBy('sort');
    }
    
    # This scope return only the active pages that belong to a certain parent and in the right order
    public function scopeParent($query,$parent)
    {
        $query->where('parent', $parent)->activeSorted();
    }
    
    # If head is empty use the title
    public function getHeadAttribute($value)
    {
        if (!$value) $value=$this->title;
        return $value;
    }
    
    # If html_title is empty use the title
    public function getHtmlTitleAttribute($value)
    {
        if (!$value) $value=$this->title;
        return $value;
    }
    
    # If url is empty create url based on title
    public function getUrlAttribute($value)
    {
        # If url = / then it's actually an empty route
        if ($value=='/') return '';
        
        # No value so create nicely formatted url from title
        if (!$value) $value=str_slug($this->title);

        return $value;
    }
}
