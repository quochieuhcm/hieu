<?php
/*
|--------------------------------------------------------------------------
| Controller: ArticlesController
|--------------------------------------------------------------------------
| @author: Bis Deverloper
|--------------------------------------------------------------------------
*/
namespace Controllers\admin;
class ArticlesController extends \BaseController {
	protected $type ='tin-tuc';
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return \View::make('admin.articles.index')->with('data',\Article::orderBy('created_at','desc')->paginate(10));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$data = [
			'categories'	=>	\Categorie::where('type','=',$this->type)->get()->toArray()
		];
		return \View::make('admin.articles.create',$data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		/*
		|--------------------------------------------------------------------------
		| Defined Rules
		|--------------------------------------------------------------------------
		*/
		$rules = array(
			'title'  =>'required',
			'cate_id'=>'required'
		);
		/*
		|--------------------------------------------------------------------------
		| Defined Mes:
		|--------------------------------------------------------------------------
		*/
		$messages = array(
			'title.required'  => 'Chưa nhập tiêu đề bài viết !',
			'cate_id.required'=> 'Chưa chọn danh mục bài viết !'
		);
		/*
		|--------------------------------------------------------------------------
		| Validation :
		|--------------------------------------------------------------------------
		*/
		$valid = \Validator::make(\Input::all(), $rules, $messages);
		$img = TRUE;
		if ($valid->passes()){
			$dataInsert = array(
				'title' 	=> \Input::get('title'),
				'intro'  	=> \Input::get('intro'),
				'contents'  => \Input::get('contents'),
				'ext_info'  => json_encode(array('keywords'=>\Input::get('intro'),'description'=>\Input::get('intro'))),
				'public'    => \Input::get('public'),
				'alias'     => \Input::get('alias'),
				'cate_id'   => \Input::get('cate_id'),
				'created_by'=> 1,
				'updated_by'=> 1,
 			);
 			/*
 			* UPLOAD IMAGE THUMBNAIL
 			*/
 			if ($_FILES['images']['name'] != ""){

 				$path = 'public/upload/articles';
 				$images = \Input::file('images');
 				$filename = $images->getClientOriginalName();
 				$isUpload = $images->move($path,$filename);
 				if ($isUpload){
 					$dataInsert['images'] = $filename;
 					$img = TRUE;
 				}else{
 					$img = FALSE;
 				}
 			}
 			/*
 			* Insert article to Database
 			*/
 			if ($img == TRUE){
 				// Insert Articles
 				$articlesInsert = \Article::create($dataInsert);
 				$articles = \Article::find($articlesInsert->id);
 				// Kiem tra
 				if (\Str::length(\Input::get('tags'))){
 					$tag_array = explode(",",\Input::get("tags"));
 					if (count($tag_array) > 0) {
 						foreach ($tag_array as $tag_value)
 						{
 							$tag_value = trim(\Str::lower($tag_value));

 							$tag_alias = \Str::slug($tag_value,'-');

 							// Kiem if co thi khong them vao
 							$tagCheck = \Tag::where("alias",$tag_alias);
 							if($tagCheck->count() == 0)
 							{
 								$tagGet = \Tag::create(array(
 									"name" => $tag_value,
 									"alias" => $tag_alias,
 								));
 							}else
 							{
 								$tagGet = $tagCheck->first();
 							}
 							$articles->tags()->attach($tagGet->id);
 						}
 					}
 				}
 			}
 			//
 			return \Redirect::route('admin.articles.index')->with('success','Thêm mới thành công !');
		}else{
			return \Redirect::route('admin.articles.create')->withInput()->with("error",$valid->errors()->first());
		}
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$data = [
			'article'	=> \Article::with("tags")->find($id),
			'categories'=> \Categorie::where('type','=',$this->type)->get()->toArray()
		];
		return \View::make('admin.articles.edit',$data);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		/*
		|--------------------------------------------------------------------------
		| Defined Rules
		|--------------------------------------------------------------------------
		*/
		$rules = array(
			'title'		=>	'required',
			'cate_id'	=>	'required'
		);
		/*
		|--------------------------------------------------------------------------
		| Defined Mes:
		|--------------------------------------------------------------------------
		*/
		$messages = array(
			'title.required' 	=>  'Chưa nhập tiêu đề bài viết !',
			'cate_id.required'	=>	'Chưa chọn danh mục bài viết !'
		);
		/*
		|--------------------------------------------------------------------------
		| Validation :
		|--------------------------------------------------------------------------
		*/
		$valid = \Validator::make(\Input::all(), $rules, $messages);
		$img = TRUE;
		if ($valid->passes()){
			$dataInsert = array(
				'title' 	=> \Input::get('title'),
				'intro'  	=> \Input::get('intro'),
				'contents'  => \Input::get('contents'),
				'ext_info'  => json_encode(array('keywords'=>\Input::get('intro'),'description'=>\Input::get('intro'))),
				'public'    => \Input::get('public'),
				'alias'     => \Input::get('title'),
				'cate_id'   => \Input::get('cate_id'),
				'created_by'=> 1,
				'updated_by'=> 1,
 			);
 			if ($_FILES['images']['name'] != ""){

 				$path = 'public/upload/articles';
 				$images = Input::file('images');
 				$filename = $images->getClientOriginalName();
 				$isUpload = $images->move($path,$filename);
 				if ($isUpload){
 					$dataInsert['images'] = $filename;
 					$img = TRUE;
 				}else{
 					$img = FALSE;
 				}
 			}
 			if ($img == TRUE){
 				// Insert Articles
 				$articlesInsert = \Article::where('id', '=', $id)->update($dataInsert);
 				$articles = \Article::find($id);
 				// Kiem tra do dai tag
 				if (\Str::length(\Input::get('tags'))){
 					$tag_array = explode(",",\Input::get("tags"));
 					\Article::find($id)->tags()->delete();
 					if (count($tag_array) > 0) {
 						foreach ($tag_array as $tag_value) {
 							$tag_value = trim(\Str::lower($tag_value));
 							$tag_alias = \Str::slug($tag_value,'-');
 							// Kiem if co thi khong them vao
 							$tagCheck = \Tag::where("alias",$tag_alias);
 							if($tagCheck->count() == 0)
 							{
 								$tagGet = \Tag::create(array(
 									"name"  => $tag_value,
 									"alias" => $tag_alias,
 								));
 								$articles->tags()->attach($tagGet->id);
 							}
 						}
 					}
 				}
 			}
 			//
 			return \Redirect::route('admin.articles.index')->with('success','Chỉnh sửa thành công !');
		}else{
			return \Redirect::route('admin.articles.create')->withInput()->with("error",$valid->errors()->first());
		}
	}
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$article = \Article::find($id);
		if ($article) {
			$images = \URL::to('/').'/public/upload/articles/'.$article->images;
			if (\File::exists($images)) {
			    \File::delete($images);
			}
			$article->tags()->detach();
			$article->delete();
			return \Redirect::route('admin.articles.index')->with('success','Xóa thành công !');
		}
	}


}
