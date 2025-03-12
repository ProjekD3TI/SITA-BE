<?php

namespace App\Http\Controllers\Dosens\Kalab\Pelaporan;

use App\Models\Pelaporan\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class SliderController extends Controller
{
    public function showAllSlider()
    {
        $sliders = Slider::all(); 
        return response()->json($sliders);
    }

    public function createSlider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required',
            'text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $uuid = Str::uuid()->toString();
            $extension = $image->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            $path = $image->storeAs('public/slider', $filename);
    
            $slider = Slider::create([
                'text' => $request->text,
                'image_path' => $filename
            ]);
    
            return response()->json(['message' => 'slider berhasil ditambahkan']);
        }
    
        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function showDetailSlider($id)
    {
        $slider = Slider::find($id);
        if ($slider) {
            return response()->json($slider);
        } else {
            return response()->json(['message' => 'slider tidak ditemukan'], 404);
        }
    }

    public function editSlider(Request $request, $id)
    {
        
            $slider = Slider::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'text' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $data = [
                'text' => $request->text,
            ];

            if ($request->hasFile('image_path')) {
                if ($slider->image_path) {
                    Storage::delete('public/slider/' . $slider->image_path);
                }
        
                $image = $request->file('image_path');
                $uuid = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $filename = $uuid . '.' . $extension;
                $image->storeAs('public/slider', $filename);
                
                $data['image_path'] = $filename;
            }
        
            $slider->update($data);
        
            return response()->json(['message' => 'slider berhasil diupdate'], 200);
        }

    public function deleteSlider($id)
    {
        $slider = Slider::findOrFail($id);

        if (!$slider) {
            return response()->json(['message' => 'slider tidak ditemukan'], 404);
        }

        if ($slider->image_path) {
            Storage::delete('public/slider/' . $slider->image_path);
        }
    
        $slider->delete();
    
        return response()->json(['message' => 'slider berhasil dihapus']);
    }
}
