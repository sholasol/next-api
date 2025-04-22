<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $products = Product::where('user_id', $userId)->get();

        return response()->json([
            'products' =>$products,
            'message' => 'Message fetched'
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'banner_image' => 'nullable|string',
            'price' => 'required|numeric',
        ]);


        if($validator->fails())
        {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            $userId = Auth::user()->id;
            //upload product image
            if($request->hasFile('banner_image')){
                $imgName = time(). '.'.$request->banner_image->extension(); 
               $imgPath = $request->banner_image->storeAs('products', $imgName);
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'banner_image' => $imgPath,
                'price' => $request->price,
                'user_id' => $userId
            ]);
            
            return response()->json([
                'product' =>$product,
                'message' => 'Product created successfully'
            ], 200);
        }catch(\Exception $e)
        {
            return response()->json([
                'message' => 'Oops! Something went wrong',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
           'product' =>$product,
           'message' => 'Product exists' 
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'banner_image' => 'nullable|string',
            'price' => 'required|numeric',
        ]);


        if($validator->fails())
        {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            $userId = Auth::user()->id;
            if($request->hasFile('banner_image')){
                 //Delete existing banner_image if there exist banner image
                if($product->banner_image){
                    $oldPath = public_path('asset'.$product->banner_image);

                    if(file_exists($oldPath)){
                        unlink($oldPath);
                    }
                }

                //process new image
                $imgName = time(). '.'.$request->banner_image->extension(); 
                $imgPath = $request->banner_image->storeAs('products', $imgName);
            }

            $udpProduct = $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'banner_image' => $imgPath,
                'price' => $request->price,
                'user_id' => $userId
            ]);
            
            return response()->json([
                'product' =>$udpProduct,
                'message' => 'Product updated successfully'
            ], 200);
        }catch(\Exception $e)
        {
            return response()->json([
                'message' => 'Oops! Something went wrong',
                'errors' => $e->getMessage()
            ], 422);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Message deleted successfully'
        ], 200);
    }
}
