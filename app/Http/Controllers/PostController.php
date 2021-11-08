<?php
 
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
 
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Photo;
use App\Models\User;
use DB;

class PostController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:post-list|post-create|post-edit|post-delete', ['only' => ['index','show', 'delete']]);
         $this->middleware('permission:post-create', ['only' => ['create','store']]);
         $this->middleware('permission:post-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:post-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $posts = Post::all();
        // dd($posts);
        return view('posts.index',compact('posts'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
 
    public function create()
    {
        return view('posts.create');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'waktu_acara' => 'required',
            'tipe' => 'required',
        ]);
 
        $post = Post::create([
    		'title' => $request->title,
            'content' => $request->content,
            'tipe' => $request->tipe,
    		'waktu_acara' => $request->waktu_acara,
        ]);
        
        if($request->hasfile('photos'))
            {
                foreach($request->file('photos') as $key => $file)
                {
                    $path = $file->store('public/images');
                    $name = $file->getClientOriginalName();
                    $ext = $file->getClientOriginalExtension();
                    $filename = time() . rand(0, 100) . "." . $ext;
                    $path1 = $file->move('images/', $filename);
                    
                    $insert[$key]['title'] = $name;
                    $insert[$key]['path'] = $path1;
                    $insert[$key]['post_id'] = $post->id;
                }
            }
        Photo::insert($insert);
                
        return redirect()->route('posts.index')
                        ->with('success','Post created successfully.');
    }

 
    public function show($id_po)
    {
        $post = Post::find($id_po);
        $photos = Photo::where('id',$id_po)->get();
        // dd($post->title);
        // dd($photos->post_id);
        // dd($photos->id);
        return view('posts.show',compact('post', 'photos'));
    }
 
    public function edit(Post $post)
    {
        // $post = Post::find($id);
        return view('posts.edit',compact('post'));
    }
 
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);
 
        $post->update($request->all());
 
        return redirect()->route('posts.index')
                        ->with('success','Post updated successfully');
    }
 
    public function destroy($post)
    {
        $user = Auth::user()->id;
        DB::table("post_user")->where('user_id', $user)->delete();
        
        return redirect()->route('posts.index')
                        ->with('success','Post deleted successfully');
    }

    public function daftarkegiatan(Request $request, $id)
    {
        // $user = User::find($id);
        // $user = Auth::user()->id;
        // $ay = User::find($user);  
        // $test = $ay->post()->get();
        //dd($test);
        $post = Post::find($id);
        // dd($post);
        $request->input('Roles');
        $user_id = Auth::user()->id;
        // $user = User::find($user_id); #yang ini
        // $user->amalyaumi()->attach($beer_id);
        // $amalibadah = AmalYaumi::pluck('name','name')->all();
        // $userRole = $user->roles->pluck('name','name')->all();
    
        return view('posts.daftarkegiatan',compact('post'));
    }

    public function userkegiatan($id)
    {
        
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        $user->post()->attach($id);
        return redirect()->route('posts.index')
                        ->with('success','User updated successfully');
    }

    public function diikuti()
    {

        $user = Auth::user()->id;
        $amaly = User::find($user);  
        $test = $amaly->post()->get();
        $amals = Post::get();
    
        return view('posts.diikuti',compact('test', 'amals'));
    }


    public function kegiatan_diikuti()
    {
        //
    }
}