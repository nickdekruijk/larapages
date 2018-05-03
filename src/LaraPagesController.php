<?php

namespace NickDeKruijk\LaraPages;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;
use DB;

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
        
        # Show all models
        foreach(config('larapages.models') as $model=>$title) {
            $i++;
            if (is_array($title)) {
                # Model is actually a submenu
                $this->nav.='<li class="'.($i==1?'start':'').($i==count(config('larapages.models'))?' end':'').($model==$this->modelId?' active':'').'"><a>'.$model.'</a>';
                $this->nav.='<ul class="nav1">';
                foreach($title as $model2=>$title2)
                    $this->nav.='<li class="'.($model2==$this->modelId?'active':'').'"><a href="'.url(config('larapages.adminpath').'/model/'.$model2).'">'.$title2.'</a></li>';
                $this->nav.='</ul>';
                $this->nav.='</li>';
            } else
                $this->nav.='<li class="'.($i==1?'start':'').($i==count(config('larapages.models'))?' end':'').($model==$this->modelId?' active':'').'"><a href="'.url(config('larapages.adminpath').'/model/'.$model).'">'.$title.'</a></li>';
        }
        
        # Show Media button if needed
        if (config('larapages.media'))
            $this->nav.='<li class="start end'.($this->modelId=='media'?' active':'').'"><a href="'.url(config('larapages.adminpath').'/media/').'">'.config('larapages.media.nicename', 'Media').'</a></li>';
           
        # Show Report button if needed
        if (config('larapages.reports')) {
            $this->nav.='<li class="start end logout'.($this->modelId=='reports'?' active':'').'"><a>'.config('larapages.reports.nicename', 'Reports').'</a>';
            $this->nav.='<ul class="nav1">';
            foreach(config('larapages.reports.queries') as $name=>$query)
                $this->nav.='<li class="'.($name==$this->modelId?'active':'').'"><a href="'.url(config('larapages.adminpath').'/reports/'.str_slug($name)).'">'.$name.'</a></li>';
            $this->nav.='</ul>';
            $this->nav.='</li>';
        }
        
        # Show logout button
        $this->nav.='<li class="right end logout"><a href="'.url(config('larapages.adminpath').'/login/').'">Logout</a></li>';
        
        # Show dashboard button with users name
        $this->nav.='<li class="right start'.(!$this->modelId?' active':'').'"><a href="'.url(config('larapages.adminpath')).'">'.LaraPagesAuth::user()->name.'</a></li>';
        
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
		    if ($request->username==$user['username'] && password_verify($request->password, $user['password'])) {
				session(['larapages_user' => $user]);
				return redirect('/'.config('larapages.adminpath'));
			}
	    }
	    if (config('larapages.userModel.model')) {
    	    $model=config('larapages.userModel.model');
    	    $users=new $model;
    	    foreach($users->all() as $user)
    		    if ($request->username==$user[config('larapages.userModel.username', 'email')] && password_verify($request->password, $user[config('larapages.userModel.password', 'password')])) {
    				session(['larapages_user' => [
        				'username' => $request->username,
        				'name' => $user[config('larapages.userModel.name', 'name')],
    				]]);
    				return redirect('/'.config('larapages.adminpath'));
    			}
	    }
	    
		return back()->with(['username'=>$request->username, 'error'=>'Invalid username and/or password']);
    }
    
    # Create CSV file for the report
    public function reportCSV($reportSlug) {
        function csvrow($row, $header=false)
        {
            $csvrow='';
            foreach($row as $field=>$value)
                $csvrow.=($csvrow?';':'').'"'.($header?$field:$value).'"';
            return $csvrow.chr(10);
        }

        $data=$this->report($reportSlug, true);

        $csv='';
        foreach($data as $rowId=>$row) {
            if ($rowId==0)
                $csv.=csvrow($row, true);
            $csv.=csvrow($row);
        }

        return response($csv)->header('Content-type','text/csv')->header('Content-disposition','attachment;filename='.$reportSlug.'.csv');
    }
    
    public function report($reportSlug, $array=false) {
        $report=false;
        foreach(config('larapages.reports.queries') as $name=>$query)
            if ($reportSlug==str_slug($name)) {
                $report=$name;
                break;
            }
        if (!$report) abort(404);
        if (env('DB_CONNECTION')=='mysql') $set = DB::select('SET SESSION group_concat_max_len = 1024000');
        $data = DB::select($query);
        if ($array)
            return $data;
        else
		    return view('laraPages::report',['admin'=>$this, 'report'=>$report, 'data'=>$data, 'nav'=>$this->nav()]);
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
            $treeviewRow.='<span>'.$this->formatValue($index, $row[$index]).'</span>';
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
    
    # Format the values according to type/casts
    private function formatValue($field, $value)
    {
        # Label to show (either renamed or field name made nice)
        $label = isset($this->model->pagesAdmin['rename'][$field]) ? $this->model->pagesAdmin['rename'][$field] : ucfirst(str_replace('_', ' ', $field));
        
        # If boolean show either label or nothing
        if (isset($this->model->getCasts()[$field]) && $this->model->getCasts()[$field]=='boolean') {
            $value = $value?$label:'';
        }
        
        # If date only show year month and date (no more time) but only if it doesn't have a specific type setting
        if (isset($this->model->getCasts()[$field]) && $this->model->getCasts()[$field]=='date' && $value && !isset($this->model->pagesAdmin['type'][$field])) {
            $value = $value->format('Y-m-d');
        }
        
        # Check if field has a specific type setting
        if ($value && isset($this->model->pagesAdmin['type'][$field])) {
            $type = explode(',', $this->model->pagesAdmin['type'][$field]);
            
            if ($type[0] == 'radio') {
                # Show the radio button label instead of value if any labels present
                foreach (explode('|', $type[1]) as $option) {
//                     dd($option);
                    @list($option, $label) = explode('=>', $option, 2);
                    if ($option == $value && $label) $value = $label;
                }
            }
        }
        return $value;
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
            foreach (explode(',',$this->model->pagesAdmin['index']) as $index) {
                # Check if index field has a dot and fetch sub value
                $sub=explode('.',$index);
                if (isset($sub[1])) 
                    $value=$row[$sub[0]][$sub[1]];
                else
                    $value=$row[$index];
                $nav.='<td>'.$this->formatValue($index, $value).'</td>';
            }
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
                if ((isset($model->pagesAdmin['type'][$field]) && $model->pagesAdmin['type'][$field]=='boolean') || (isset($model->getCasts()[$field]) && $model->getCasts()[$field]=='boolean'))
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
        # Update the pivot table(s) when there is a belongToMany
        $this->updatePivot($row, $request);
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

        $many=false;
        # First check if there is a belongsToMany relation set in the first place
        if (isset($this->model->pagesAdmin['belongsToMany'])) {
            # Initialize the $many array
            $many=[];
            # Walk thru each belongsToMany item
            foreach($this->model->pagesAdmin['belongsToMany'] as $field=>$options) {
                # Fetch the options
                list($remoteModel, $method) = explode(',', $options);
                # Walk thru the related items and add the ids to the $many array
                foreach($row->$method()->get() as $relatedItem)
                    $many['many_'.str_slug($field).'_'.$relatedItem->id]=$relatedItem->id;
            }
        }
        # We want the original values and not the auto defaults from the accessors
        if (isset($model->pagesAdmin['accessors']) && !$model->pagesAdmin['accessors'])
            $row=$row->getOriginal();
        else
            $row=$row->toArray();
        
        # Only return the fillable fields
        foreach($row as $field=>$value)
            if (!$model->isFillable($field))
                unset($row[$field]);
            elseif ($this->isPassword($field))
                $row[$field]='********';
            elseif (isset($model->pagesAdmin['type'][$field]) && $model->pagesAdmin['type'][$field]=='date' && $row[$field]) # Remove time from date fields
                $row[$field]=substr($value,0,10);
        
        # If there is a $many array merge it with the row
        if (is_array($many)) 
            $row=array_merge($row,$many);
            
        # Return the row data
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
    
    # Update the pivot table(s) when there is a belongToMany
    public function updatePivot($row, $request)
    {
        # First check if there is a belongsToMany relation set in the first place
        if (isset($this->model->pagesAdmin['belongsToMany'])) {
            # Walk thru each belongsToMany item
            foreach($this->model->pagesAdmin['belongsToMany'] as $field=>$options) {
                # Fetch the options
                list($remoteModel, $method) = explode(',', $options);
                # Get the values from the checkboxes
                $values=$request->input('many_'.str_slug($field));
                # If none checked we still need an empty array to sync
                if (empty($values)) $values=[];
                # Sync the pivot table
                $row->$method()->sync($values);
            }
        }
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
        # Update the pivot table(s) when there is a belongToMany
        $this->updatePivot($row, $request);
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
        $table = $model->getTable();
        foreach(explode(',',$ids) as $i => $id) {
            DB::table($table)->where('id', $id)->update(['sort' => $i]);
        }
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
     * WARNING: DEPRECIATED!!! WILL BE REMOVED IN 1.0 RELEASE!
     * Should be done in your Page model, See Page.php in samples
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
                $nav.=' <li'.($ids[$depth]==$page->url?' class="active"':'').'>';
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
     * WARNING: DEPRECIATED!!! WILL BE REMOVED IN 1.0 RELEASE!
     * Should be done in your Page model, See Page.php in samples
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
     * WARNING: DEPRECIATED!!! WILL BE REMOVED IN 1.0 RELEASE!
     * Should be done in your Page model, See Page.php in samples
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
