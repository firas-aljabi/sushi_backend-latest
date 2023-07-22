<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = ProductResource::collection(Product::get());
        return $this->apiResponse($products,'success',200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|min:3|max:2500',
            'image' => 'nullable|file|image|mimes:jpeg,jpg,png',
            'category_id' => 'integer|exists:categories,id',
            'position' => 'nullable',
            'ingredients' => 'nullable|string',
            'notes' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->ingredients = $request->ingredients;
        $product->notes = $request->notes;
        $product->category_id = $request->category_id;
        if($request->position){
            $product->position = $request->position;
            // Shift existing products below the new position
            Product::where('position', '>=',  $request->position)->increment('position');
        }
     
        
        if($request->hasFile('image')){
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $request->image->move(public_path('/images/product'),$filename);
            $product->image = $filename;
        }
        $product->save();
        

        if($product){
            return $this->apiResponse(new ProductResource($product),'The product Save',201);
        }else{
            return $this->apiResponse(null,'The product Not Save',400);
        }

        
    }

    /**
     * Display the specified resource.
     */
    public function show($id){

        $product = Product::find($id);
        
        if($product){
            return $this->apiResponse(new ProductResource($product),'ok',200);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }
        

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
        $product = Product::find($id);
        if ($product->status == '1') {
            $product->status = 0;
        } else {
            $product->status = 1;
        }
        $product->save();

        return response()->json(['success' => 'Status change successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request ,$id){

        $validator = Validator::make($request->all(), [
            'name' => 'max:255',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|min:3|max:2500',
            'position' => 'nullable',
            'image' => 'nullable|file||image|mimes:jpeg,jpg,png',
            'category_id' => 'integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null,$validator->errors(),400);
        }


        $product=Product::find($id);
        
        if($product){
            if($product->name == $request->name && $product->description == $request->description && $product->price == $request->price && $product->ingredients == $request->ingredients && $product->category_id == $request->category_id){
                return $this->apiResponse(null,'Enter Data to update',404);
            }
            
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->ingredients = $request->ingredients;
            $product->notes = $request->notes;
            $product->category_id = $request->category_id;
            if($request->position){
                $product->position = $request->position;
                // Shift existing products below the new position
                Product::where('position', '>=',  $request->position)->increment('position');
            }
            
            
            if($request->hasFile('image')){
                File::delete(public_path('/images/product/'.$product->image));
                $image = $request->file('image');
                $filename = $image->getClientOriginalName();
                $request->image->move(public_path('/images/product'),$filename);
                $product->image = $filename;
            }
            $product->save();
            
            return $this->apiResponse(new ProductResource($product),'The product update',201);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id){

        $product=Product::find($id);

        if($product){

            $product->delete($id);
            File::delete(public_path('/images/product/'.$product->image));

            return $this->apiResponse(null,'The product deleted',200);
        }else{
            return $this->apiResponse(null,'The product Not Found',404);
        }

    }
}
