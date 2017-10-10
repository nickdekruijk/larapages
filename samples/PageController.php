<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Requests;
use App\Page;
use App;

class PageController extends Controller
{
    // This array will store all pages store with parent as key
    public $tree = [];

    // $nav will contain the html for the navigation menu (nested <ul><li>)
    public $nav = '';

    // $current will contain the current page if found
    public $current = false;

    private $translate = false;
    private $baseUrl = null;
    private $segments = null;

    private function parseSegments()
    {
        $this->segments = Request::segments();
        # Check if first segment matches a language
        if (isset(Page::$languages)) {
            foreach (Page::$languages as $locale => $suffix) {
                if (isset($this->segments[0]) && $this->segments[0] == $locale) {
                    # Found it, remove segment and set locale
                    $this->baseUrl = $this->segments[0];
                    array_shift($this->segments);
                    App::setLocale($locale);
                }
            }
            $this->translate = true;
        }
        if (App::getLocale() == 'nl') {
            setlocale(LC_TIME, 'nl_NL');
        }
    }

    public function segments()
    {
        if (!$this->baseUrl) {
            $this->parseSegments();
        }
        return $this->segments;
    }
    
    public function baseUrl()
    {
        if (!$this->baseUrl) {
            $this->parseSegments();
        }
        return $this->baseUrl;        
    }

    // The walk() function is used by the route() method to parse the pages tree array
    private function walk($parent = 0, $depth = 0, $segments = false, $url = '/', $hidden = false, $activeParent = true)
    {
        // The id might not exist if it's the domain root for example
        if (!isset($segments[$depth])) {
            $segments[$depth] = '';
        }

        if (!$hidden) $this->nav .= '<ul class="nav' . $depth . '">';
        foreach($this->tree[$parent] as $n => $page) {
            $hide = $page->hidden || $hidden;
            // Set current if it's the one but only if $activeParent is true to prevent page with same slug from different parent
            if (((empty($segments[$depth]) && $depth==0 && $n==0) || $segments[$depth] == $page->slug) && $activeParent) {
                $this->current = $page;
                $active = true;
            } else {
                $active = false;
            }

            if (!$hide) $this->nav .= '<li' . ($active?' class="active"':''). '>';
            if (!$hide) $this->nav .= '<a href="' . url($url . ($this->translate?$page->trans('slug'):$page->slug)) .'">' . ($this->translate?$page->trans('title'):$page->title) . ($active?' (*)':'') . '</a>';
            if (isset($this->tree[$page->id])) {
                $this->walk($page->id, $depth+1, $segments, $url . ($this->translate?$page->trans('slug'):$page->slug) . '/', $hide, $active);
            }
            if (!$hide) $this->nav .= '</li>';
        }
        if (!$hidden) $this->nav .= '</ul>';
    }

    /**
     * Controller method for Route creation
     * In routes.php / web.php use:
     * Route::get('{any}', 'PageController@route')->where('any', '(.*)');
     */
    public function route($any = null, Request $request)
    {
        // Get all active pages, sorted, and store them in the tree array
        foreach(Page::where('active', 1)->orderBy('sort')->get() as $page) {
            $this->tree[$page->parent?:0][$page->id] = $page;
        }

        // Start walking the pages tree
        $this->walk(0, 0, $this->segments(), $this->baseUrl().'/');

//         echo $this->nav;
//         dd($this->tree,$this->nav,Request::segments(),$this->current);

        // If current isn't set raise a 404
        if (!$this->current) {
            abort(404);
        }
        
        if (!$this->current->view) {
            !$this->current->view = 'page';
        }
		return view($this->current->view, ['page'=>$this->current,'nav'=>$this->nav]);
    }
}
