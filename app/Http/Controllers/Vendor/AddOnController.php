<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\Translation;

class AddOnController extends Controller
{
    public function index()
    {
        $addons = AddOn::orderBy('name')->paginate(config('default_pagination'));
        return view('vendor-views.addon.index', compact('addons'));
    }

    public function store(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'max:200',
            'price' => 'required|numeric|between:0,999999999999.99',
            'img' => 'required|image|mimes:jpg,jpeg,png,svs,gif|max:2048'
        ],[
            'name.required' => translate('messages.Name is required!'),
            'img.image' => 'The file must be an image',
             'img.mimes' => 'The image must be a file of type: png,jpg,jpeg,svg or gif',
             'img.max' => 'The image may not be greater than 2MB'
            
        ]);
        $old = $request->img;
        echo $old;
        echo 'step1';
            // 'img.image' => 'The file must be an image',
            // 'img.mimes' => 'The image must be a file of type: png,jpg,jpeg,svg or gif',
            // 'img.max' => 'The image may not be greater than 8MB'

        // if($request->hasFile('img')){
           
            
        //     $imgPath = $request->file('img')->store('public/Addon');
            
            
        //     $imgPath = Str::replaceFirst('public/Addon/','',$imgPath);
            
        // }else {
           
        //     return redirect()->back()->with('error','Please Select Image for addon');
        // };

        
        
        

        $addon = new AddOn();
        $addon->name = $request->name[array_search('default', $request->lang)];
        $addon->img = Helpers::upload(dir:'Addon/', format:'png', image: $request->file('img'));
        //$addon->img = $imgPath;
        $addon->price = $request->price;
        $addon->restaurant_id = \App\CentralLogics\Helpers::get_restaurant_id();
        
        $addon->save();
        //dd($request);
        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\AddOn',
                        'translationable_id' => $addon->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $addon->name,
                    ));
                }
            }else{
                if ($request->name[$index] && $key != 'default') {
                    array_push($data, Array(
                        'translationable_type'  => 'App\Models\AddOn',
                        'translationable_id'    => $addon->id,
                        'locale'                => $key,
                        'key'                   => 'name',
                        'value'                 => $request->name[$index],
                    ));
                }
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }
        Toastr::success(translate('messages.addon_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $addon = AddOn::withoutGlobalScope('translate')->findOrFail($id);
        return view('vendor-views.addon.edit', compact('addon'));
    }

    public function update(Request $request, $id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $request->validate([
            'name' => 'required|max:191',
            'price' => 'required|numeric|between:0,999999999999.99',
        ], [
            'name.required' => translate('messages.Name is required!'),
        ]);

        $addon = AddOn::find($id);
        $addon->name = $request->name[array_search('default', $request->lang)];
        //dd($request);
        $addon->price = $request->price;
        if ($request->hasFile('img')) {
            $imgPath = $request->file('img')->store('public/Addon'); //Store the uploaded image
            $imgPath = Str::replaceFirst('public/Addon/','',$imgPath);
            $addon->img = $imgPath;
        }else{
            
        }
        
        $addon?->save();
        $default_lang = str_replace('_', '-', app()->getLocale());

        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->name[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\AddOn',
                            'translationable_id' => $addon->id,
                            'locale' => $key,
                            'key' => 'name'
                        ],
                        ['value' => $addon->name]
                    );
                }
            }else{

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        ['translationable_type'  => 'App\Models\AddOn',
                            'translationable_id'    => $addon->id,
                            'locale'                => $key,
                            'key'                   => 'name'],
                        ['value'                 => $request->name[$index]]
                    );
                }
            }
        }

        Toastr::success(translate('messages.addon_updated_successfully'));
        return redirect(route('vendor.addon.add-new'));
    }

    public function delete(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $addon = AddOn::find($request->id);
        $addon?->delete();
        Toastr::success(translate('messages.addon_deleted_successfully'));
        return back();
    }
}
