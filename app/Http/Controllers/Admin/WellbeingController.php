<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WellbeingPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WellbeingController extends Controller
{
    /**
     * Wellbeing content is Super-Admin-only per the Admin Roles & Permissions
     * document — School Admin and Tutor have no access to this controller at all.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()?->isSuperAdmin(), 403, 'Only Super Admins can manage Wellbeing content.');
            return $next($request);
        });
    }

    public function index()
    {
        $posts = WellbeingPost::latest()->paginate(20);

        return view('admin.wellbeing.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.wellbeing.create');
    }

    public function store(Request $request)
    {

        $request->validate([

            'title' => 'required|max:255',

            'short_description' => 'required',

            'content' => 'nullable',

            'type' => 'required|in:article,video',

            'category' => 'required',

            'youtube_url' => 'nullable',

            'read_time' => 'required',

        ]);


        // Only one featured post
        if ($request->featured == 1) {

            WellbeingPost::where('featured',1)
                    ->update([
                        'featured'=>0
                    ]);

        }

        WellbeingPost::create([

            'title' => $request->title,

            'short_description' => $request->short_description,

            'content' => $request->content,

            'type' => $request->type,

            'category' => $request->category,

            'youtube_url' => $request->youtube_url,

            'read_time' => $request->read_time,

            'featured' => $request->featured ?? 0,

            'status' => $request->status ?? 1,

            'published_at' => now(),

            'created_by' => Auth::id()

        ]);

        return redirect()
                ->route('admin.wellbeing.index')
                ->with('success','Post Created Successfully');

    }

    public function edit($id)
    {

        $post = WellbeingPost::findOrFail($id);

        return view('admin.wellbeing.edit', compact('post'));

    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'title'=>'required|max:255',

            'short_description'=>'required',

            'content'=>'nullable',

            'type'=>'required|in:article,video',

            'category'=>'required',

            'youtube_url'=>'nullable',

            'read_time'=>'required',

        ]);

        $post = WellbeingPost::findOrFail($id);


        if ($request->featured == 1) {

            WellbeingPost::where('featured',1)
                ->where('id','!=',$post->id)
                ->update([
                    'featured'=>0
                ]);

        }

        $post->update([

            'title'=>$request->title,

            'short_description'=>$request->short_description,

            'content'=>$request->content,

            'type'=>$request->type,

            'category'=>$request->category,

            'youtube_url'=>$request->youtube_url,

            'read_time'=>$request->read_time,

            'featured'=>$request->featured ?? 0,

            'status'=>$request->status ?? 1,

        ]);

        return redirect()
            ->route('admin.wellbeing.index')
            ->with('success','Updated Successfully');

    }

    public function destroy($id)
    {

        $post = WellbeingPost::findOrFail($id);

        $post->delete();

        return back()->with('success','Deleted Successfully');

    }

}