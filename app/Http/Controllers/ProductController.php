<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $products = Product::where('user_id', $userId)
        ->orderBy('id', 'desc')
        ->get();

        //map over the products beacause of the image presentation to frontend
        // $products = Product::where('user_id', $userId)->get()->map(function($product) {
        //     $product->banner_image = $product->banner_image ? asset("storage/" . $product->banner_image) : null;

        //     return $product;
        // });

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
            'banner_image' => 'nullable|file|image',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation fails',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::user()->id;
            $imgPath = null;

            // Upload product image
            if ($request->hasFile('banner_image')) {
                $imgName = time() . '.' . $request->banner_image->extension();
                // Store in public/asset/products/
                $imgPath = $request->banner_image->storeAs('products', $imgName, 'public');
                // Save only the filename or relative path
                $imgPath = $imgName; // Store just the filename in the database
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'banner_image' => $imgPath,
                'price' => $request->price,
                'user_id' => $userId
            ]);

            return response()->json([
                'status' => true,
                'product' => $product,
                'message' => 'Product created successfully'
            ], 200);
        } catch (\Exception $e) {
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
    // public function update(Request $request, Product $product)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string',
    //         'description' => 'required|string',
    //         'banner_image' => 'nullable|file|image',
    //         'price' => 'required|numeric',
    //     ]);


    //     if($validator->fails())
    //     {
    //         return response()->json([
    //             'message' => 'Validation fails',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try{
    //         $userId = Auth::user()->id;

    //         // Handle image upload
    //     if ($request->hasFile('banner_image')) {
    //         // Delete existing banner_image if it exists
    //         if ($product->banner_image) {
    //             $oldPath = 'products/' . $product->banner_image;
    //             if (Storage::disk('public')->exists($oldPath)) {
    //                 Storage::disk('public')->delete($oldPath);
    //             }
    //         }

    //         // Process new image
    //         $imgName = time() . '.' . $request->banner_image->extension();
    //         $imgPath = $request->banner_image->storeAs('products', $imgName, 'public');
    //         $imgPath = $imgName; // Store only the filename
    //     }
            

    //         $udpProduct = $product->update([
    //             'name' => $request->name,
    //             'description' => $request->description,
    //             'banner_image' => $imgPath,
    //             'price' => $request->price,
    //             'user_id' => $userId
    //         ]);
            
    //         return response()->json([
    //             "status" => true,
    //             'product' =>$udpProduct,
    //             'message' => 'Product updated successfully'
    //         ], 200);
    //     }catch(\Exception $e)
    //     {
    //         return response()->json([
    //             'message' => 'Oops! Something went wrong',
    //             'errors' => $e->getMessage()
    //         ], 422);
    //     }

    // }

    public function update(Request $request, Product $product)
    {
        // Manually extract the data from the request
        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'banner_image' => $request->hasFile('banner_image') 
                ? $request->file('banner_image') 
                : null
        ];


        $validator = Validator::make($data, [
            'name' => 'required|string',
            'description' => 'required|string',
            'banner_image' => 'nullable|file|image',
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
            
            // Create update data array
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'user_id' => $userId
            ];
            
            // Handle image upload only if a new file is provided
            if ($request->hasFile('banner_image')) {
                // Delete existing banner_image if it exists
                if ($product->banner_image) {
                    $oldPath = 'products/' . $product->banner_image;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                // Process new image
                $imgName = time() . '.' . $request->banner_image->extension();
                $imgPath = $request->banner_image->storeAs('products', $imgName, 'public');
                $updateData['banner_image'] = $imgName; // Only update image if a new one is uploaded
            }
            
            $udpProduct = $product->update($updateData);
            
            return response()->json([
                "status" => true,
                'product' => $udpProduct,
                'message' => 'Product updated successfully'
            ], 200);
        } catch(\Exception $e) {
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
