<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;

class Page extends Model
{
    use SoftDeletes;

    # Define what the PagesAdminController is allowed to to with the model and how
    public $pagesAdmin = [
        'nicename' => 'Website pages', # Title to show in header
        'index' => 'title,id', # Columns to show in listview
        'active' => 'active', # Boolean type column that determines if page is active or not
        'sortable' => 'sort', # Data can be sorted by dragging, store values in 'sort' column
        'orderBy' => 'sort', # Data is ordered by this column at for descending order you can use something like 'date DESC'
        'treeview' => 'parent', # Items can be shown in a treeview, 'parent' column determines parent/child relation
        'expanded' => 3, # When treeview is shown auto expand up to 3 levels
//         'preview' => '/preview/page/{id}', # Enable preview button, links to this url. Requires preview route.
        'validate' => [# Laravel validation rules
            'title' => 'required',
            'date' => 'date|nullable',
        ],
        'accessors' => false, # Disable accessors when editing model. Use this when accessors modify empty columns for example and you want to leave them blank when editing
        'type' => [# Column types, this determines the model editing view input types. If ommitted default text input is used
            'title' => '100',
            'view' => '100',
            'slug' => '100',
            'html_title' => '64',
            'description' => 'text',
            'pictures' => 'media,10',
            'background' => 'media',
            'body' => 'longtext',
        ],
        'rename' => [# Rename columns
            'pictures' => 'Picture',
        ],
        'tinymce' => [# List of columns that can contain html and should be edited with TinyMCE
            'body' => 'tinymce options',
        ],
    ];

    # Fillable columns, also used by PagesAdminController to build the form so the order matters
    protected $fillable = [
        'active',
        'hidden',
        'home',
        'title',
        'view',
        'head',
        'html_title',
        'slug',
        'description',
        'date',
        'pictures',
        'background',
        'body',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'hidden' => 'boolean',
        'home' => 'boolean',
        'date' => 'date',
        'deleted_at' => 'datetime',
    ];

    # This scope returns only the active pages and in the right order
    public function scopeActiveSorted($query)
    {
        $query->where('active', 1)->orderBy('sort');
    }

    # This scope return only the active pages that belong to a certain parent and in the right order
    public function scopeParent($query, $parent)
    {
        $query->where('parent', $parent)->activeSorted();
    }

    # If head is empty use the title
    public function getHeadAttribute($value)
    {
        if (!$value) {
            $value = $this->title;
        }
        return $value;
    }

    # If html_title is empty use the title
    public function getHtmlTitleAttribute($value)
    {
        if (!$value) {
            $value = $this->title;
        }
        return $value;
    }

    # If slug is empty create slug based on title
    public function getSlugAttribute($value)
    {
        # If slug = / then it's actually an empty route
        if ($value == '/') {
            return '';
        }

        # No value so create nicely formatted slug from title
        if (!$value) {
            $value = str_slug($this->title);
        }

        return $value;
    }

    # Determine fullUrl by include the parent slug(s)
    public function getFullUrlAttribute()
    {
        if ($this->parent > 0) {
            $parent = Page::findOrFail($this->parent);
            return $parent->fullUrl . '/' . $this->slug;
        } else {
            return $this->slug;
        }
    }

    # If picture is empty return first picture from pictures column
    public function getPictureAttribute($value)
    {
        if (!$value) {
            $value = trim(explode(chr(10), trim($this->pictures))[0]);
        }
        return $value;
    }

    # Return the children of the page (subpages)
    public function children()
    {
        return $this->hasMany('App\Page', 'parent');
    }

}
