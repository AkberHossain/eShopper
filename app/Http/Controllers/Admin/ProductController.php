<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Product_Category;
use App\Models\Tag;
use App\Models\Product_Tag;

use Session;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{

    public function productDetails($id)
    {
        $product = Product::findOrFail($id);

        $product_categories = (array)$product->allCategories;

        //dd($product_categories);

        $categories = Category::all();

        $compact = compact('product' , 'categories' , 'product_categories');

        return view('admin-panel.products.product_details' , $compact);
    }


    public function showProducts()
    {

        $products = Product::all();

        return view('admin-panel.products.all_products' , compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin-panel.products.product_create_form' , compact('categories'));
    }

    public function store(Request $request)
    {

        ///Validation

        ///create product
        if($request->hasFile('cover_image'))
            $path = $request->file('cover_image')->store('product_image');

        $product = Product::create([

            'name' => $request->name , 
            'sell_price' => $request->sell_price , 
            'buy_price' => $request->buy_price , 
            'short_description' => $request->short_desc , 
            'stock' => $request->stock , 
            'user_id' => Session::get('user_id') , 
            'long_description' => $request->details_desc , 
            'cover_image' => $path ,

        ]);

        ///create product-categories table value

        foreach($request->categories as $category)
        {
            Product_Category::create(
                [
                    'product_id' => $product->id ,
                    'category_id' => $category
                ]
            );
        }

        //craete product_tags table

        $tags = explode("," , $request->tags[0] );

        foreach($tags as $tag)
        {

            $found_tag = Tag::where('name' , $tag)->first();

            if($found_tag)
            {
                Product_Tag::create(
                    [
                        'product_id' => $product->id ,
                        'tag_id' => $found_tag['id'] ,
                    ]
                );
            }
            else
            {   
                $new_tag = Tag::create([
                    'name' => $tag ,
                ]);

                Product_Tag::create(
                    [
                        'product_id' => $product->id ,
                        'tag_id' => $new_tag->id ,
                    ]
                );
            }
        }


        return redirect()->route('admin.product');
    }
}