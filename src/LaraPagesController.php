<?php

namespace NickDeKruijk\LaraPages;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;

class LaraPagesController extends Controller
{
    # The current model we are working with, will be set by fetchModel() method
    public $model=false;
    public $modelId=false;

    # The navigation html storing variable
    private $nav=false;
    
    # This function will build the $nav variable
    public function nav() 
    {
        if ($this->nav) return $this->nav;
        $this->nav='<ul class="nav0">';
        $i=0;
        foreach(config('larapages.models') as $model=>$title) {
            $i++;
            $this->nav.='<li class="'.($i==1?'start':'').($i==count(config('larapages.models'))?'end':'').($model==$this->modelId?' active':'').'"><a href="/'.config('larapages.adminpath').'/model/'.$model.'">'.$title.'</li>';
        }
        if (config('larapages.media'))
           $this->nav.='<li class="start end logout'.($this->modelId=='media'?' active':'').'"><a href="/'.config('larapages.adminpath').'/media/">'.config('larapages.media.nicename', 'Media').'</a></li>';
        $this->nav.='<li class="right end logout"><a href="/'.config('larapages.adminpath').'/login/">Logout</a></li>';
        $this->nav.='<li class="right start'.(!$this->modelId?' active':'').'"><a href="/'.config('larapages.adminpath').'/">'.LaraPagesAuth::user()->name.'</a></li>';
        $this->nav.='</ul>';
        return $this->nav;
    }
    
    public function login() {
	    session(['larapages_user' => false]);
		return view('laraPages::login');
    }

    public function loginValidate(Request $request) {
	    
	    session(['larapages_user' => false]);

	    foreach(config('larapages.users') as $username=>$user) {
		    if (!is_array($user)) 
		    	$user=['password' => $user];
		    if (!isset($user['username'])) $user['username']=$username;
		    if ($request->username==$user['username'] && password_verify($request->password,$user['password'])) {
				session(['larapages_user' => $user]);
				return redirect('/'.config('larapages.adminpath'));
			}
	    }
		return back()->with(['username'=>$request->username, 'error'=>'Invalid username and/or password']);
    }

    # The admin requires authentication
    public function __construct()
    {
        $this->middleware('larapages', ['except' => ['parse', 'login', 'loginValidate']]);
    }

    # Display the admin index.
    public function index()
    {
		return view('laraPages::dashboard',['admin'=>$this]);
    }
    
    # Returns the html for a row
    public function treeviewRow($row)
    {
        # Add class="inactive" to the div container if the model defined active field is false
        $treeviewRow='<div'.(isset($row->pagesAdmin['active']) && !$row[$row->pagesAdmin['active']]?' class="inactive"':'').'><span></span>';
        # Only add the columns from the index setting
        foreach (explode(',',$row->pagesAdmin['index']) as $index)
            $treeviewRow.='<span>'.$row[$index].'</span>';
        $treeviewRow.='</div>';
        return $treeviewRow;
    }

    # Walk thru the model tree and return the treeview html
    public function treeview($parent=null)
    {
        # Fetch all rows
        $rows = $this->model
                ->where($this->model->pagesAdmin['treeview'],$parent);
        if (!empty($this->model->pagesAdmin['orderBy'])) foreach(explode(',',$this->model->pagesAdmin['orderBy']) as $order) {
	        $order=explode(' ',$order);
	        if (empty($order[1]))
	        	$rows=$rows->orderBy($order[0]);
	        else
	        	$rows=$rows->orderBy($order[0], $order[1]);
        }
        $rows=$rows->get();

        # Return if no rows found to prevent empty <ul></ul>
        if (!count($rows)) return;

        # Create the html list
        $nav='<ul>';
        foreach($rows as $row) {
            $nav.='<li data-id="'.$row['id'].'">'.$this->treeviewRow($row);
            # Check if this row has children and add them
            $nav.=$this->treeview($row['id']);
            $nav.='</li>';
        }

        # Finalize the html list and return it
        $nav.='</ul>';
        return $nav;
    }

    # Read all data from the model and return html table
    public function table()
    {
        # Fetch all rows
        $rows = $this->model;
        if (!empty($this->model->pagesAdmin['orderBy'])) foreach(explode(',',$this->model->pagesAdmin['orderBy']) as $order) {
	        $order=explode(' ',$order);
	        if (empty($order[1]))
	        	$rows=$rows->orderBy($order[0]);
	        else
	        	$rows=$rows->orderBy($order[0], $order[1]);
        }
        $rows=$rows->get();

        # Return if no rows found to prevent empty <ul></ul>
        if (!count($rows)) return;

        # Create the html list
        $nav='<table>';
        foreach($rows as $row) {
            $nav.='<tr'.(isset($row->pagesAdmin['active']) && !$row[$row->pagesAdmin['active']]?' class="inactive"':'').' data-id="'.$row['id'].'">';
            foreach (explode(',',$this->model->pagesAdmin['index']) as $index)
                $nav.='<td>'.$row[$index].'</td>';
            $nav.='</tr>';
        }

        # Finalize the html list and return it
        $nav.='</table>';
        return $nav;
    }
    
    # Converts the $modelId into a real \App\Model
    public function fetchModel($modelId)
    {
        # Convert the $modelId to true model name
        $model='App\\'.studly_case($modelId);
        
        # Initialize the model
        $this->model=new $model;
        $this->modelId=$modelId;
        
        # Does the model have a pagesAdmin variable? If not raise exception
        if (!isset($this->model->pagesAdmin))
            throw new \Exception($model.' does not have a pagesAdmin variable');
        
        # Seems to be fine, return the model
        return $this->model;
    }

    # Load the model and the view
    public function model($modelId)
    {
        # Get the real model
        $this->model=$this->fetchModel($modelId);
        $this->modelId=$modelId;
        
        # Get the table or treeview depending on the treeview setting
        if (isset($this->model->pagesAdmin['treeview']) && $this->model->pagesAdmin['treeview'])
            $data=$this->treeview();
        else
            $data=$this->table();
            
        # Return the model view
		return view('laraPages::model',['admin'=>$this, 'modelId'=>$modelId, 'model'=>$this->model, 'data'=>$data, 'nav'=>$this->nav()]);
    }

    # Check missing fields and convert empty values to null, also do some password magic
    public function checkInput($model,$input)
    {
        foreach ($model->getFillable() as $field) {
            if (!isset($input[$field]) || !$input[$field]) {
                # If it's boolean we should set to 0 and not null
                if (isset($model->pagesAdmin['type'][$field]) && $model->pagesAdmin['type'][$field]=='boolean')
                    $input[$field]=0;
                else
                    $input[$field]=null;
            }
            # Password magic: unset the field if password didn't change else hash it
            if ($this->isPassword($field))
                if ($input[$field]=='********')
                    unset($input[$field]);
                else
                    $input[$field]=bcrypt($input[$field]);
        }
        return $input;
    }

    # Store a newly created resource in storage.
    public function store($modelId, $parent=false, Request $request)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Validate the input if we need to
        $this->validate($request,$this->validationRules());
        # Update the row width checkInput for missing fields and NULL values
        $row=$model::create($this->checkInput($model, $request->input()));
        # If pagesAdmin uses sortable set sort field to the id, since id is also the highest value sort could ever be
        if (isset($model->pagesAdmin['sortable'])) {
            $col=$model->pagesAdmin['sortable'];
            $row->$col=$row->id;
            $row->save();
        }
        # Give new row html back with ajax based on treeview setting
        if (isset($model->pagesAdmin['treeview']) && $model->pagesAdmin['treeview']) {
            # If parent is set get the parent from that id
            if ($parent) {
                $parent=$model::findOrFail($parent)->parent;
                $row->parent=$parent;
                $row->save();
            }
            echo json_encode([
                'success'=>$this->treeviewRow($row),
                'id'=>$row->id,
                'parent'=>$parent>0?$parent:0,
            ]);
        } else {
            echo json_encode([
                'success'=>$this->table($row),
                'id'=>$row->id,
                'table'=>true,
            ]);
        }
    }
    
    # Check if the field is a password
    public function isPassword($field)
    {
        return isset($this->model->pagesAdmin['type'][$field]) && $this->model->pagesAdmin['type'][$field]=='password';
    }

    # Display the specified resource.
    public function show($modelId,$id)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Fetch the row
        $row=$model::findOrFail($id);
        # We want the original values and not the auto defaults from the accessors
        if (isset($model->pagesAdmin['accessors']) && !$model->pagesAdmin['accessors'])
            $row=$row->getOriginal();
        
        # Only return the fillable fields
        foreach($row as $field=>$value)
            if (!$model->isFillable($field))
                unset($row[$field]);
            elseif ($this->isPassword($field))
                $row[$field]='********';
        return $row;
    }
    
    # If needed this function will make some changes to the validation rules
    public function validationRules($replace=[])
    {
        # If no rules are set return empty array
        if (!isset($this->model->pagesAdmin['validate'])) 
            return [];

        # Fetch the rules
        $rules=$this->model->pagesAdmin['validate'];
        
        # Walk thru all rules
        foreach($rules as $ruleKey=>$rule) {
            # Replace #keys# if needed
            foreach($replace as $replaceKey=>$replaceValue)
                $rules[$ruleKey]=str_replace('#'.$replaceKey.'#',$replaceValue,$rule);
        }
        
        # Return the adjusted rules
        return $rules;
    }
    
    # Update the specified resource in storage.
    public function update($modelId, $id, Request $request)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Validate the input if we need to
        $this->validate($request,$this->validationRules(['id'=>$id]));
        # Fetch the row
        $row=$model::findOrFail($id);
        # Update the row width checkInput for missing fields and NULL values
        $row->fill($this->checkInput($model,$request->input()))->save();
        # Give new row html back with ajax based on treeview setting
        if (isset($model->pagesAdmin['treeview']) && $model->pagesAdmin['treeview'])
            echo json_encode(['success'=>$this->treeviewRow($row)]);
        else {
            echo json_encode(['success'=>$this->table(),'table'=>true]);
        }
    }

    # Change the parent of the item
    public function changeparent($modelId, $id, Request $request)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Fetch the row
        $row=$model::findOrFail($id);
        
        # Get the parent and oldparent from Input, make null if needed
        $parent=$request->input('parent');
        $oldparent=$request->input('oldparent');
        if ($oldparent<1) $oldparent=null;
        if ($parent<1) $parent=null;
        
        # Check if oldparent matches the actual id for safety
        if ($row->parent!=$oldparent) die('Invalid oldparent '.$oldparent);
        
        # Save the new parent
        $row->parent=$parent;
        $row->save();
    }

    # Sort the items 
    public function sort($modelId,$parent, Request $request)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Get the ids from input
        $ids=$request->input('ids');
        # Get the row for each id and update the sort
        if ($parent<1) $parent=null;
        $i=0;
        foreach(explode(',',$ids) as $id) {
            $i++;
            $row=$model::findOrFail($id);
            if ($row->parent!=$parent) die('Invalid parent');
            $row->sort=$i;
            $row->save();
        }
        #print_r($parent.'='.$ids);
    }

    # Remove the specified resource from storage.
    public function destroy($modelId,$id)
    {
        # Get the real model
        $model=$this->fetchModel($modelId);
        # Actually destroy it
        $model::destroy($id);
        echo json_encode(['success'=>'ok']);
    }

    # Initialize the currentPage.
    public $currentPage=false;
    
    /**
     * Walk thru the pages tree and return the navigation html and set currentPage is found
     *
     * @return (string)$nav
     */
    function walk($parent, $depth, $ids, $url='', $hidden=false) {
        
        # The id might not exist if it's the domain root for example
        if (!isset($ids[$depth])) $ids[$depth]='';
        
        # Fetch all pages
        $pages = \App\Page::parent($parent)->get();
        # Return if no pages found to prevent empty navigation <ul></ul>
        if (!count($pages)) return;

        # Create the navigation html
        $nav = '<ul class="nav'.$depth.'">';
        
        foreach($pages as $page) {
            # Set currentPage if it's this one but only if $depth equals the actual amount of ids
            if ($ids[$depth]==$page->url && $depth==count($ids)-1) $this->currentPage=$page;
            
            # Add page to navigation html and add active class when needed
            if (!$page->hidden) {
                $nav.='<li'.($ids[$depth]==$page->url?' class="active"':'').'>';
                $nav.='<a href="'.$url.$page->url.'">'.$page->title.'</a>';
            }
            # Check if this page has subpages and add them
            $nav.=$this->walk($page->id, $depth+1, $ids, $url.$page->url.'/', $page->hidden || $hidden);
            
            # Finalize navigation html
            if (!$page->hidden)
                $nav.='</li>';
        }
        
        # Finalize navigation html and return it
        $nav.='</ul>';
        if (!$hidden)
            return $nav;
    }

    /**
     * Raise a 404 error and load our custom 404 message with navigation
     *
     * @return \Illuminate\Http\Response
     */
    static public function raise404()
    {
        $nav=new LaraPagesController;
        $nav=$nav->walk(null,0,\Request::segments(),'/');
	    return Response::view(config('larapages.views.404','laraPages::main.404'), ['nav'=>$nav], 404);
    }

    /**
     * Parse the pages tree find the currentPage and return navigation html
     *
     * @return \Illuminate\Http\Response
     */
    public function parse($any, Request $request)
    {
        # Start walking the page tree
        $nav=$this->walk(null,0,$request->segments(),'/');
        
        # If currentPage isn't set raise a custom 404
        if (!$this->currentPage) 
        	return Response::view(config('larapages.views.404','laraPages::main.404'), ['nav'=>$nav], 404);
        
        # Return the page view
		return view(config('larapages.views.page','laraPages::main.page'),['page'=>$this->currentPage,'nav'=>$nav]);
    }

}
