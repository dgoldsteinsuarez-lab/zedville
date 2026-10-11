<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Npo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin CRUD for the NPO (non-profit organisation) directory students
 * donate to. Extracted out of AdminEducationController (planning.md
 * Phase 3 #4) - fully self-contained.
 */
class NpoController extends Controller
{
    public function npos(Request $request)
    {
        $query = Npo::query();
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $npos = $query->latest()->get();
        return view('admin.npos.index', compact('npos'));
    }

    public function add_npo(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required'
        ]);

        $npo = new Npo();
        $npo->name = $request->name;
        $npo->category = $request->category;
        $npo->slug = Str::slug($request->name);
        $npo->content = $request->content;
        $npo->bank_name = $request->bank_name;
        $npo->account_number = $request->account_number;
        $npo->status = $request->status ?? 1;
        $npo->sort_order = $request->sort_order ?? 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/npos'), $filename);
            $npo->image = $filename;
        }

        $npo->save();

        return redirect()->back()->with('success', 'NPO Added Successfully');
    }

    public function edit_npo($id)
    {
        $npo = Npo::findOrFail($id);

        $npos = Npo::latest()->get();

        return view('admin.npos.edit', compact('npo', 'npos'));
    }

    public function update_npo(Request $request, $id)
    {
        $npo = Npo::findOrFail($id);

        $npo->name = $request->name;
        $npo->category = $request->category;
        $npo->slug = Str::slug($request->name);
        $npo->content = $request->content;
        $npo->bank_name = $request->bank_name;
        $npo->account_number = $request->account_number;
        $npo->status = $request->status;
        $npo->sort_order = $request->sort_order;

        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/npos'), $filename);

            $npo->image = $filename;
        }

        $npo->save();

        return redirect()->back()->with('success', 'NPO Updated Successfully');
    }

    public function delete_npo(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        Npo::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'NPO Deleted');
    }
}
