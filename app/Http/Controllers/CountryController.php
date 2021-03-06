<?php

namespace App\Http\Controllers;

use App\Country;
use App\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countries = Country::orderBy('name', 'asc')->get();
        return view('admin.country.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.country.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'countryName' => ['required', 'min:2'],
            'countrySlug' => ['required', 'unique:countries,slug'],
            'countryType' => 'required|integer',
            'areaName' => ['required_if:countryType,1', 'array'],
            'areaName.*' => ['required_if:countryType,1', 'string', 'min:2'],
            'areaSlug' => ['required_if:countryType,1', 'array'],
            'areaSlug.*' => ['required_if:countryType,1', 'string'],
        ]);

        //create new country data
        $country = Country::addNewCountry($request);

        //add area to country data
        if($country->type == 1) {
            foreach($request->areaName as $keyName => $areaName) {
            $area = new Area;
            $area->name = $areaName;
            foreach($request->areaSlug as $keySlug => $areaSlug) {
                if($keyName == $keySlug) {
                    $slugVar = $areaSlug;
                }
            }
            $area->slug = $slugVar;
            $area->country_id = $country->id;
            $area->save();
        }
        }

        return redirect()->route('country.index')->with('flashmessage', 'New country was successfully added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        return view('admin.country.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        $this->validate($request, [
            'countryName' => ['required', 'min:2'],
            'countrySlug' => ['required', Rule::unique('countries','slug')->ignore($country->id)],
            'countryType' => 'required|integer',
            'areaName' => ['required_if:countryType,1', 'array'],
            'areaName.*' => ['required_if:countryType,1', 'string', 'min:2'],
            'areaSlug' => ['required_if:countryType,1', 'array'],
            'areaSlug.*' => ['required_if:countryType,1', 'string'],
        ]);

        //update country data
        $country->name = $request->countryName;
        $country->slug = $request->countrySlug;
        $country->type = $request->countryType;
        $country->save();

        foreach($country->areas as $ar) {
            $mark = false;
            foreach($request->areaName as $key => $name) {
                if($key == $ar->id) {
                    $mark = true;
                    break;
                }
            }
            if($mark == false) {
                $ar->delete();
            }
        }
        
        if($country->type  == 1) { 
            //add area to country data
            foreach($request->areaName as $keyName => $areaName) {
                if(!empty($country->areas->find($keyName))) {
                    $country->areas->find($keyName)->name = $areaName;

                    foreach($request->areaSlug as $keySlug => $areaSlug) {
                        if($keyName == $keySlug) {
                            $slugVar = $areaSlug;
                        }
                    }
                    $country->areas->find($keyName)->slug = $slugVar;
                    $country->areas->find($keyName)->country_id = $country->id;
                    $country->areas->find($keyName)->save();
                }
                else {

                    $area = new Area;
                    $area->name = $areaName;

                    foreach($request->areaSlug as $keySlug => $areaSlug) {
                        if($keyName == $keySlug) {
                            $slugVar = $areaSlug;
                        }
                    }
                    $area->slug = $slugVar;
                    $area->country_id = $country->id;
                    $area->save();
                }
            }
        }

        $countries = Country::orderBy('name', 'asc')->get();
        return redirect()->route('country.index')->with('flashmessage', 'Country was successfully updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('country.index')->with('flashmessage', 'Country was deleted!');
    }
}
