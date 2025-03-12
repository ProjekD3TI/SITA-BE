<?php

namespace App\Http\Controllers\Dosens\Kalab\Pelaporan;

use App\Models\Pelaporan\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function showAllCategory()
    {
        $categories = Category::all(); 
        return response()->json($categories);
    }

    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required',
            'nama' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $path = $image->storeAs('public/category', $filename);
    
            $category = Category::create([
                'nama' => $request->nama,
                'image_path' => $filename
            ]);
    
            return response()->json(['message' => 'Category berhasil ditambahkan']);
        }
    
        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function showDetailCategory($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json($category);
        } else {
            return response()->json(['message' => 'Category tidak ditemukan'], 404);
        }
    }

    public function editCategory(Request $request, $id)
    {
        
            $category = Category::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'nama' => $request->nama,
            ];

            if ($request->hasFile('image_path')) {
                if ($category->image_path) {
                    Storage::delete('public/category/' . $category->image_path);
                }
        
                $image = $request->file('image_path');
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/category', $filename);
                
                $data['image_path'] = $filename;
            }
        
            $category->update($data);
        
            return response()->json(['message' => 'Category berhasil diupdate'], 200);
        }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        if (!$category) {
            return response()->json(['message' => 'category tidak ditemukan'], 404);
        }

        if ($category->image_path) {
            Storage::delete('public/category/' . $category->image_path);
        }
    
        $category->delete();
    
        return response()->json(['message' => 'category berhasil dihapus']);
    }
}
