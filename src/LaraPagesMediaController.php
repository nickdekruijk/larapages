<?php

namespace NickDeKruijk\LaraPages;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Input;
use Carbon\Carbon;

class LaraPagesMediaController extends Controller
{
    # The media admin requires authentication
    public function __construct()
    {
        $this->middleware('larapages');
    }
    
    # Returns the html for a row
    public function treeviewRow($folder)
    {
        $treeviewRow='<div>';
        $treeviewRow.='<span></span><span>'.$folder.'</span>';
        $treeviewRow.='<a class="delete icondelete"></a>';
        $treeviewRow.='</div>';
        return $treeviewRow;
    }

    # Walk thru the media folder tree and return the treeview html
    public function treeview($root,$ids='')
    {
        # Fetch all files
        $dir=opendir($root);
        $folders=[];
        while($file=readdir($dir)) {
            # Don't show folders starting with .
            if ($file[0]=='.') continue;
            # Hide c and s folders (from imageresize)
            if ($file=='c' || $file=='s') continue;
            # Check if it's actualy a folder
            if (is_dir($root.'/'.$file)) $folders[]=$file;
        }
        natcasesort($folders);

        # Return if no rows found to prevent empty <ul></ul> but only if not root
        if (!count($folders) && $ids) return;
        
        $nav='';
        if (!$ids) {
            $nav='<ul>';
            $nav.='<li data-id="0">'.$this->treeviewRow('Media');
        }

        # Create the html list
        $nav.='<ul>';
        $i=0;
        foreach($folders as $folder) {
            $i++;
            $nav.='<li data-id="'.$ids.$i.'" data-folder="'.htmlspecialchars($folder).'">';
            $nav.=$this->treeviewRow($folder);
            # Check if this row has children and add them
            $nav.=$this->treeview($root.'/'.$folder,$ids.$i.'-');
            $nav.='</li>';
        }

        # Finalize the html list and return it
        if (!$ids) 
            $nav.='</li><ul>';
        $nav.='</ul>';
        return $nav;
    }
    
    # Return the navigation from PagesAdminController
    public function nav()
    {
        $PagesAdmin=new LaraPagesController;
        $PagesAdmin->modelId='media';
        return $PagesAdmin->nav();
   }

    # Display the listview and editview for Media management
    public function index(Request $request, $mini=false)
    {
        $this->controllerUrl=$request->segment(2);
        $data=$this->treeview(public_path(config('larapages.media.folder')));
		return view('laraPages::media',['data'=>$data, 'media'=>$this, 'mini'=>$mini, 'admin'=>$this]);
    }
    
    # Display a minified version (for popup)
    public function mini(Request $request)
    {
        return $this->index($request,true);
    }
        
    # Returns the maximum files size we can upload
    public function maxUploadSize() {
        $max=ini_get('upload_max_filesize')>(int)ini_get('post_max_size')?(int)ini_get('post_max_size'):(int)ini_get('upload_max_filesize');
        if (config('larapages.media.maxUploadSize') && config('larapages.media.maxUploadSize')<$max) 
            $max=config('larapages.media.maxUploadSize');
        return $max;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        # Which extensions are allowed
        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'zip', 'pdf', 'doc', 'docx', 'csv', 'xls', 'xlsx', 'pages', 'numbers', 'psd', 'mp4', 'mp3', 'mpg', 'm4a', 'ogg'];
        
        $upl=$request->file('upl');

        # Check if upload file exists
        if (!$upl)
            die('{"status":"error, File may be too big?"}');

        # Check if it had an error
        if ($upl->getError())
            die('{"status":"error '.$upl->getError().': '.str_replace('"','\\"',$upl->getErrorMessage()).'"}');
        
        # Check if filesize is allowed
        if ($upl->getClientSize()>$this->maxUploadSize()*1024*1024)
            die('{"status":"File too big"}');

        # Check if extension is allowed
        if (!in_array(strtolower($upl->getClientOriginalExtension()), $allowed))
            die('{"status":"Extension not allowed"}');
        
        # If file exists add a number until file is available
        $postfix=false;
        $filename=$upl->getClientOriginalName();
        while (file_exists(public_path(config('larapages.media.folder')).Input::all()['folder'].'/'.$filename)) {
            if (!$postfix) $postfix=2; else $postfix++;
            $filename=pathinfo($upl->getClientOriginalName(), PATHINFO_FILENAME).'_'.$postfix.'.'.$upl->getClientOriginalExtension();
        }
        $request->file('upl')->move(public_path(config('larapages.media.folder')).Input::all()['folder'],$filename);
        die('{"status":"success","folder":"'.Input::all()['folder'].'"}');
    }
    
    public function urlencodeFolder($string,$alt=false) {
        if (!$alt) $string=htmlspecialchars($string);
        $string=rawurldecode($string);
        if (!$alt) $string=str_replace("'","\\'",$string);
        return $string;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($folder)
    {
        $root=public_path(config('larapages.media.folder')).Input::all()['folder'];
        $dir=opendir($root);
        $files=[];
        while($file=readdir($dir)) {
            if ($file[0]!='.' && $file!='imageresize.php' && !is_dir($root.'/'.$file)) {
                $files[]=$file;
            }
        }
        natcasesort($files);
        $return='';
        Carbon::setLocale('en');
        
        $preview=['jpg','png','gif','jpeg'];
        
        # Check if Safari version is 9 or higher so we can preview PDF thumbnails
        $ua=$_SERVER['HTTP_USER_AGENT'];
        $safari=strpos($ua, 'Safari') && !strpos($ua, 'Chrome');
        $p=strpos($ua, 'Version/');
        $safariVersion=substr($ua, $p+8, strpos($ua, '.', $p)-$p-8);
        if ($safariVersion>=9) $preview[]='pdf';
        
        foreach($files as $file) {
            $return.='<li>';
            $return.='<a target="_blank" href="/'.$this->urlencodeFolder(config('larapages.media.folder'),1).$this->urlencodeFolder(Input::all()['folder'],1).'/'.$this->urlencodeFolder($file,1).'">';
            $extension=strtolower(substr($file,strrpos($file,'.')+1));
            if (in_array($extension, $preview))
                $return.='<div style="background-image:url(\'/'.$this->urlencodeFolder(config('larapages.media.folder')).$this->urlencodeFolder(Input::all()['folder']).'/'.$this->urlencodeFolder($file).'\')">';
            else
                $return.='<div>'.$extension;
            $return.='</div></a>';
            $size=filesize($root.'/'.$file);
            $time=filemtime($root.'/'.$file);
            $time=Carbon::createFromTimeStamp($time)->formatLocalized('%a %d %B %Y').' '.Carbon::createFromTimeStamp($time)->toTimeString(); #->diffForHumans();
            $return.='<span class="fileselect"><input type="checkbox" value="x"></span>';
            $return.='<span class="title">'.$file.'</span>';
            $return.='<span class="size">'.$size.'</span>';
            $return.='<span class="time">'.$time.'</span>';
            $return.='<span class="actions"><a class="delete icondelete"></a></span>';
            $return.='</li>';
        }
        return $return;
    }
    
    public function newfolder() {
        $root=public_path(config('larapages.media.folder')).Input::all()['folder'];
        if (is_dir($root)) die('{"error":"Folder already exists"}');
        if (file_exists($root)) die('{"error":"File already exists"}');
        mkdir($root);
        echo json_encode([
            'success'=>$this->treeview(public_path(config('larapages.media.folder'))),
        ]);
    }

    /**
     * Rename the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rename(Request $request)
    {
        $file=public_path(config('larapages.media.folder')).Input::all()['folder'].'/'.Input::all()['file'];
        $newname=public_path(config('larapages.media.folder')).Input::all()['folder'].'/'.Input::all()['title'];
        if (!file_exists($file)) die('File not found '.$file);
        rename($file, $newname);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $file=public_path(config('larapages.media.folder')).Input::all()['folder'].'/'.Input::all()['file'];
        if (!file_exists($file)) die('File not found '.$file);
        unlink($file);
    }

    /**
     * Remove the specified folder from storage including all content.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyFolder()
    {
        $file=public_path(config('larapages.media.folder')).Input::all()['folder'];
        if (!file_exists($file)) die('File not found '.$file);
        if (!is_dir($file)) die('Not a folder '.$file);
        $h=opendir($file);
        while($f=readdir($h)) {
            if ($f!='.' && $f!='..') die('Folder is not empty');
        }
        closedir($h);
        rmdir($file);
    }
}
