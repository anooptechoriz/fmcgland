<?php
namespace App\Http\Controllers\FrontEnd;
use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\SellerProduct;
use App\Models\SellerProductImage;
use App\Models\Category;
use App\Models\TopCategory;
use App\Models\Product;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Contentpage;
use App\Models\CompanyType;
use App\Models\Mynetworks;
use App\Models\Message;
use App\Models\Generalsetting;
use App\Models\Advertisement;
use App\Models\MobileSlider;
use App\Models\MobileSliderimage;
use App\User;
use App\Models\BusinessInsight;
use App\buildTree;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FrontEnd\PublicMiddlewareController;
class PagesController extends Controller{
    protected $PublicMiddlewareController;
    public function __construct(PublicMiddlewareController $PublicMiddlewareController){
        $this->PublicMiddlewareController = $PublicMiddlewareController;
    }
    function isMobile() {
    if(isset($_SERVER['HTTP_USER_AGENT'])) {
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/(tablet|ipad|amazon|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($useragent))) {
        return true ;
    } ;

    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            return true ;
        }
    }
    return 0 ;
}
    public function HomePage(){
        $active_sellers=$this->PublicMiddlewareController->getexpiredsellerslist(); 
		if(Auth::guard('user')->check()){
			if(!$this->PublicMiddlewareController->checkUserlogin()){
				Auth::guard('user')->logout(); 
                return redirect(route('home'))->with('message','Access Denied'); ; 
			}
			if(!$this->PublicMiddlewareController->checkUserValidity() && Auth::guard('user')->user()->seller_type=='Co-Seller' ){
				Auth::guard('user')->logout(); 
				return redirect(route('home')); 
            }
		}
		
			$subquery = BusinessInsight::groupBy('product_id')
    		->select('product_id', DB::raw('COUNT(business_insights.product_id) as count'));

		$products = SellerProduct::joinSub($subquery, 'business_insights', function ($join) {
					    $join->on('seller_products.id', '=', 'business_insights.product_id');
					})
		    ->leftJoin('users', 'users.id', '=', 'seller_products.user_id')
			->leftJoin('currencies', 'currencies.id', '=', 'seller_products.currency_id')
			->select('seller_products.id','seller_products.user_id','seller_products.name','seller_products.price_on_request','seller_products.price_negotiable','seller_products.product_price'
			,'currencies.symbol')
			->where('users.status','Active')
			->where('seller_products.product_visibility','Yes')
			->whereIn('seller_products.user_id',$active_sellers)
			->where('seller_products.status','active')
			->orderBy('business_insights.count', 'desc')
			->take(10)->get();
     	$featured_products = SellerProduct::leftJoin('users', 'users.id', '=', 'seller_products.user_id')
			->select('seller_products.id','seller_products.user_id','seller_products.name','seller_products.price_on_request','seller_products.price_negotiable','seller_products.currency_id','seller_products.product_price')
			->where('users.status','Active')
			->whereIn('seller_products.user_id',$active_sellers)
			->where('seller_products.status','active')
			->where('seller_products.product_visibility','Yes')
			->where('seller_products.featured_product','Yes')
			->latest('seller_products.created_at')->take(8)->get();            
		$newest_company = User::leftjoin('buyer_companies', 'buyer_companies.user_id', '=', 'users.id')
			->where('users.usertype',"seller")
			// ->where('company',"Yes")
		    ->where('users.status','Active')
		    ->where('buyer_companies.company_image','<>','null')->where('buyer_companies.company_image','<>','')
		    ->select('buyer_companies.id','buyer_companies.company_image','buyer_companies.user_id')
		    ->orderBy('users.id','DESC')->take(15)->get(); 
		$slider = Slider::where('show_home',"Yes")->with('Sliderimage')->first();
		
		$welcome_page_adds = Advertisement::leftJoin('contentpages', 'contentpages.id', '=', 'advertisements.page_id')
			->whereRaw('? between start_date and end_date', [date('Y-m-d')])
			->where('page','Welcome Page')->select('media_type','media_file','link_url','advertisements.title','position')->get();
        $TopCategory = TopCategory::pluck('category_id')->all(); 
        $all_categories =  DB::table('categories')->whereIn('id',$TopCategory)->get(); 
        $validity=$this->PublicMiddlewareController->checkUserContactValidity();
		$site_testimonials =  DB::table('testimonials')->get();
		if($this->isMobile()) { 
		    $mobile_slider = MobileSlider::where('show_home',"Yes")->with('MobileSliderimage')->first();
          return view('welcome_mobile',compact('mobile_slider','site_testimonials','products','welcome_page_adds','featured_products','all_categories','newest_company','validity'));
        } else {
          return view('welcome',compact('slider','site_testimonials','products','welcome_page_adds','featured_products','all_categories','newest_company','validity'));
}
		
    }
    public function TermsAndCondition(){
		$terms = Contentpage::where('page',"Terms & Conditions")->first();  
		
		if($this->isMobile()) { 
             return view('frontEnd.pages.TermsAndCondition_mobile',compact('terms'));
            } else {
             return view('frontEnd.pages.TermsAndCondition',compact('terms'));
            }
    }
    public function PrivacyPolicy(){
		$privacy = Contentpage::where('page',"Privacy Policy")->first(); 
		if($this->isMobile())
		        return view('frontEnd.pages.PrivacyPolicy_mobile',compact('privacy'));
		else
				return view('frontEnd.pages.PrivacyPolicy',compact('privacy'));

    }
    public function RefundPolicy(){
		$refund_policy = Contentpage::where('page',"Refund Policy")->first();
		if($this->isMobile())
            	return view('frontEnd.pages.RefundPolicy_mobile',compact('refund_policy'));
        else
	            return view('frontEnd.pages.RefundPolicy',compact('refund_policy'));
    }
    public function CookiePolicy(){
		$cookie_policy = Contentpage::where('page',"Cookie Policy")->first();
		if($this->isMobile())
            	return view('frontEnd.pages.CookiePolicy_mobile',compact('cookie_policy'));
        else
		        return view('frontEnd.pages.CookiePolicy',compact('cookie_policy'));
    }    
    public function AboutUs(){
		$about_us =  Contentpage::where('page',"About Us")->first();
		if($this->isMobile())
                return view('frontEnd.pages.AboutUs_mobile',compact('about_us'));
        else
		        return view('frontEnd.pages.AboutUs',compact('about_us'));
    }
    public function mynetwork(){
		if(Auth::guard('user')->check()){          
			if(Auth::guard('user')->user()->usertype=="guest")
                return redirect(route('home')); 
			if(!$this->PublicMiddlewareController->checkUserlogin()){
				Auth::guard('user')->logout(); 
				return redirect(route('home'))->with('message','Access Denied'); 
			}
			if(!$this->PublicMiddlewareController->checkUserContactValidity()){
				Auth::guard('user')->logout(); 
				return redirect(route('home')); 
			}
		} 
		$active_sellers=$this->PublicMiddlewareController->getexpiredsellerslist(); 
		$user_id  = Auth::guard('user')->user()->id;
		$my_networks=Mynetworks::where('user_id',$user_id)->pluck('mynetwork_id')->first();
		$network_id=explode(',', $my_networks);
		$network_list =User::select('name','id','profile_pic')->whereIn('id',$network_id)
          ->whereIn('id',$active_sellers)->get();
		$chat_data = array();
		if (!$network_list->isEmpty()) {
			foreach($network_list as $key=>$row){
				$unreadCount = Message::where('from_user', $row->id)->where('to_user',$user_id)->where('message_status', 'unread')->count();
				$latestMessage = Message::where(function ($query) use ($user_id, $row) {
					$query->where('from_user', $user_id)
                          ->where('to_user', $row->id);
                })->orWhere(function ($query) use ($user_id, $row) {
					$query->where('from_user', $row->id)
						  ->where('to_user', $user_id);
                })->orderBy('id', 'desc')->take(5)->get();
				$chat_data[$key] = array('contact'=>$row,'latestMessage'=>$latestMessage,'unreadcount'=>$unreadCount);
          
			}
		}
		$allcategorylists=Category::select('name','id')->where('parent_id', null)->orderBy('name','asc')->get();
		$CompanyType=CompanyType::orderBy('company_type','asc')->get();
		
		
		if($this->isMobile()) { 
		   return view('frontEnd.pages.my_networks_mobile',compact('chat_data','allcategorylists','CompanyType'));
        } else {
          return view('frontEnd.pages.my_networks',compact('chat_data','allcategorylists','CompanyType'));
        }
		
	}
    public function revokeFrom_network(Request $request){
        $login_id = Auth::guard('user')->user()->id;
        $revok_userid=$request->get('user_id');
        $networks=Mynetworks::where('user_id',$login_id)->pluck('mynetwork_id')->first();
        $arrayData=explode(',', $networks);
        $network_users='';
        foreach( $arrayData as $value) {
          if($value==$revok_userid|| $value=='')
            continue;
          $network_users.=trim($value).',';
        }
        DB::table('mynetworks')
            ->where('user_id',$login_id)->update(['mynetwork_id'=>$network_users]);
        DB::table('mynetwork_requests')
            ->where('user_id',$login_id)->where('network_id',$revok_userid)->delete();
        $msg="Removed from Networks";        
        echo json_encode($msg);  
    }   
    public function BuyerInstructions(){
		$data =  Contentpage::where('page',"Buyer Instructions")->first();
		if($this->isMobile())
		    return view('frontEnd.pages.BuyerInstructions_mobile',compact('data'));
        else
		    return view('frontEnd.pages.BuyerInstructions',compact('data'));
    }
    public function SellerInstructions(){
		$data =  Contentpage::where('page',"Seller Instructions")->first();
		if($this->isMobile())
            	return view('frontEnd.pages.SellerInstructions_mobile',compact('data'));
        else
		        return view('frontEnd.pages.SellerInstructions',compact('data'));
    }
    public function ContactUs(){
		if($this->isMobile()) { 
           return view('frontEnd.pages.ContactUs_mobile');
        } else {
            return view('frontEnd.pages.ContactUs');
        }
    }    
    public function TwitterSearch(){
		return view('frontEnd.pages.TwitterSearch');
    }
    public function ContactSubmit(Request $request){ 
		request()->validate([
			"name" => ['required','string', 'max:255'],
			"email" =>['required'],
			"phone" =>['required'],
			"message" =>['required'],
		]);
		$name = $request->input('name');
		$email = $request->input('email'); 
		$phone = $request->input('phone');
		$msg = $request->input('message');      
// 		Mail::send('emails.ContactPageMail', ['name' => $name,'email' => $email,'phone' => $phone,'msg' => $msg], function($message) use($request){
// 			$company_email = Generalsetting::where('item','company_email')->first()->value;	   
// 			if(!empty($company_email))
// 				$message->to($company_email);
// 			else
// 				$message->to('info@fmcg.com');
// 			$message->subject('New support request - FMCG');
// 		});  
		return back()->with('message', 'Your Support Request Has Been Sent. Our Team Will be in Touch With You Soon. ');
	}
	public function headsearch(Request $request){
        request()->validate([
			"search" => ['required'],     
		]);
        $query = $request->get('search'); 
        return \Redirect::route('Product.Listing',['search_key'=>$query]);
    }   
    public function TypeaheadSearch(Request $request){
		$active_sellers=$this->PublicMiddlewareController->getexpiredsellerslist(); 
		$validity=$this->PublicMiddlewareController->checkUserContactValidity();
		$query = $request->get('term');
		$productResult = SellerProduct::leftJoin('users', 'users.id', '=', 'seller_products.user_id')
			->where('users.status','Active')
			->where('seller_products.status','active')
			->where('seller_products.product_visibility','Yes')
			->whereIn('seller_products.user_id',$active_sellers)
			->where(DB::raw('lower(seller_products.name)'), 'LIKE', '%'. strtolower($query). '%')
			->select(DB::raw('seller_products.id AS new_id'),'seller_products.*')
			->with('SellerProductImage')
			->take(5)->get();
		$categoryResult = Category::where(DB::raw('lower(slug)'), 'LIKE', '%'. strtolower($query). '%')
		->where('have_product','Yes')
		->select(DB::raw('id AS new_id'),'name','category_pic','slug')->take(5)->get();
		$new_array=$row_set=[];
		foreach($productResult as $data){ 
			$product_image=$data->SellerProductImage ;
			$img_url  = asset('/uploads/defaultImages/no_image.jpg');
			$product_images = SellerProductImage::where('product_id','=',$data->new_id)->get();
			$cnt=count($product_images) ;
			if($cnt>0) {
				if(!empty($product_images)) {
					foreach( $product_images as $productimage){     
						if($productimage->thumbnail == 'yes') 
							$img_url  = asset("/uploads/productImages/").'/'.$productimage->image_path;
					}    
				}
			}    
			if($data->price_on_request != 'Price on request'){
				$curreny_symbol ='$';
				if(!empty($data->Currency->symbol) )
					$curreny_symbol = $data->Currency->symbol;
				$price = $curreny_symbol.'&nbsp;'.$data->product_price;
				if($data->price_negotiable == 'Negotiable')
					$price .='(Price Negotiable)';		   
			}else
				$price = "Price on Request ";          
			if(Auth::guard('user')->check() && $validity==true){
				$user_country_id = $data->User->country_id;
				$countries = Country::where('id',$user_country_id)->pluck('name')->all();
				$Countries_names = implode(",",$countries);              
				if($Countries_names!='')
					$new_row['title']= $data->name." (&nbsp;" .$price.')<br><i class="fa fa-map-marker" aria-hidden="true"></i> '.$Countries_names;
				else
					$new_row['title']= $data->name." ((&nbsp;" .$price.')';
			}else  
				$new_row['title']= $data->name;
			$new_row['image']= $img_url;
			$new_row['search_key']= $query; 
			$new_row['url']= route('view.Sproduct',['productId'=>$data->new_id,'search_key'=>$query] );
			$row_set[] = $new_row;
		}          
        foreach($categoryResult as $data){
            if($data->category_pic=="")
                $img_url =asset('/uploads/defaultImages/no_image.jpg');
             else
                $img_url =asset('/uploads/categoryImages/'.$data->category_pic);          
            $new_array[]=array('id'=>$data->new_id,'name'=>$data->name,'image_path'=>$img_url);
            $new_row['title']= $data->name;
            $new_row['image']= $img_url;
            $new_row['url']= route("Product.Listing", $data->slug);
            $row_set[] = $new_row;
        }
        return response()->json($row_set);
	}      
	public function getsellerslist_search(Request $request){   
		$start_from=$request->input('start_from');
		$per_page=$request->input('per_page');
		$seller_name_search=strtoupper($request->input('seller_name'));
		$category_id= $request->input('category_id');
		$company_type= $request->input('company_type');
		$user_id = Auth::guard('user')->user()->id;
		$my_networks=Mynetworks::where('user_id',$user_id)->pluck('mynetwork_id')->first();
		$network_id=explode(',', $my_networks); 
		$selected_country_id= $request->input('selected_country_id');  
		$selected_country_id=explode(',', $selected_country_id);
		$active_sellers=$this->PublicMiddlewareController->getexpireduserslist(); 
		$user_data = User::leftJoin('buyer_companies', function($join){
			$join->on('buyer_companies.user_id', '=', 'users.id');
			$join->orOn('buyer_companies.user_id', '=', 'users.parent_id');
		})->leftJoin('seller_products', function($join){
			$join->on('seller_products.user_id', '=', 'users.id');
			$join->orOn('seller_products.user_id', '=', 'users.parent_id');
		})
		->leftJoin('countries', 'users.country_id', '=', 'countries.id')
		->leftJoin('company_types', 'buyer_companies.company_type', '=', 'company_types.id')
		->where('users.status','Active')
	//	   ->where('seller_products.product_visibility','Yes')
		   ->where('users.id','<>',$user_id)
		   ->whereIn('users.id',$active_sellers)
		   ->whereNotIn('users.id',$network_id)
		   ->select('users.name as user_name','users.profile_pic','company_types.company_type','users.varification_status','buyer_companies.company_image as company_image','users.id as main_id'
		   ,'buyer_companies.company_street','buyer_companies.company_name','buyer_companies.company_location','countries.name as country_name');
		if($seller_name_search!='')
			$user_data = $user_data->where(DB::raw('UPPER(CONCAT(users.name,COALESCE(buyer_companies.company_name,"")))'), 'LIKE','%'.$seller_name_search.'%');          
		if($company_type!=0)
			$user_data = $user_data->where('buyer_companies.company_type',$company_type); 
		else
					$user_data = $user_data->Where('buyer_companies.company_type','<>',0);
			
		if($request->input('selected_country_id')!='')
            $user_data = $user_data->whereIn('users.country_id',$selected_country_id);
        else
					$user_data = $user_data->Where('users.country_id','<>','');
            
		$category_ids = [];     
		if($category_id!='0'){
			$categorylist=Category::where('parent_id',$category_id)->pluck('id')->all();       // all subcategories                   
			array_push($category_ids,$category_id);  //parent category to array
			foreach ($categorylist as $value){
				if(!in_array($value, $category_ids)) //subctegory id not in $category_id array
					array_push($category_ids,$value); //all category ids as array
				for ($i=0; $i<count($category_ids);$i++){
					$category1=Category::where('parent_id',$category_ids[$i])->get();
					foreach ($category1 as  $value2){   
						if(!in_array($value2->id, $category_ids))
							array_push($category_ids,$value2->id);
					}                             
				}                
			}
			$user_data = $user_data->WhereIn('category_id',$category_ids);
		} 
		else
					$user_data = $user_data->Where('category_id','<>',0);

		$count = $user_data->groupby('main_id')->get()->count();
		$user_data= $user_data->groupby('main_id')
			->orderby('user_name','ASC')
			->skip($start_from)->take($per_page)->get();      
		$list_array=[];
		foreach ($user_data as $data) {
            $class_verified = '';
            if($data->varification_status=='varified') 
            $class_verified = 'active-border';             
            $company_image =    $data->company_image;
            if(($company_image!='')) 
				$img_path = asset('uploads/BuyerCompany/').'/'.$company_image;
			elseif(!empty($data->profile_pic)) 
				$img_path = asset('/uploads/userImages/').'/'.$data->profile_pic;
			else  
				$img_path = asset('uploads/defaultImages/default_avatar.png');        
			$network_exist=false;  
			$list_array[]=array(
				'id'=>$data->main_id,
				'name'=>$data->user_name,
				'class_verified'=>$class_verified,
				'varification_status'=>$data->varification_status,
				'company_type'=>$data->company_type??"",
				'company_street'=>$data->company_street??"",
				'company_name'=>$data->company_name??"",
				'company_location'=>$data->company_location??"",
				'img_path'=>$img_path,
				'network_exist'=>$network_exist,
				'country_name'=>$data->country_name??"",
            );
		}
		$return_array=['count'=>$count,'sellers'=>$list_array];
		return json_encode($return_array);       
	}
	public function getnetwork_users_list(Request $request){
		$active_sellers=$this->PublicMiddlewareController->getexpireduserslist(); 
		$start_from=$request->input('start_from');
		$per_page=$request->input('per_page');
		$seller_name_search=strtoupper($request->input('seller_name'));
		$company_type= $request->input('company_type');
		$category_id= $request->input('category_id');  
		$selected_country_id= $request->input('selected_country_id');  
		$selected_country_id=explode(',', $selected_country_id);
		$login_id = Auth::guard('user')->user()->id;
		$my_networks=Mynetworks::where('user_id',$login_id)->pluck('mynetwork_id')->first();
		$network_id=explode(',', $my_networks);
		$sellers_list =SellerProduct::leftJoin('users', function($join){
			$join->on('seller_products.user_id', '=', 'users.id');
			$join->orOn('seller_products.user_id', '=', 'users.parent_id');
        })
       // ->where('seller_products.product_visibility','Yes')
		->whereIn('users.id',$network_id)
		->whereIn('users.id',$active_sellers);                   
		$category_ids=[];       
        if($category_id!='0'){
             $categorylist=Category::where('parent_id',$category_id)->pluck('id')->all();       // all subcategories                   
             array_push($category_ids,$category_id);  //prent category to array
             foreach ($categorylist as $value){
                if(!in_array($value, $category_ids)) //subctegory id not in $category_id array
                    array_push($category_ids,$value); //all category ids as array
				for ($i=0; $i<count($category_ids);$i++){
					$category1=Category::where('parent_id',$category_ids[$i])->get();
					foreach ($category1 as  $value2){   
						if(!in_array($value2->id, $category_ids))
							array_push($category_ids,$value2->id);
					}                             
				}                    
            }
            $sellers_list = $sellers_list->WhereIn('category_id',$category_ids);
        } 
        else
					$sellers_list = $sellers_list->Where('category_id','<>',0);
		$sellers_list= $sellers_list->groupby('users.id')->pluck('users.id')->all();
		
		$network_list =User::leftJoin('buyer_companies', function($join){
			$join->on('buyer_companies.user_id', '=', 'users.id');
			$join->orOn('buyer_companies.user_id', '=', 'users.parent_id');
		})
		->leftJoin('countries', 'users.country_id', '=', 'countries.id')
		->leftJoin('company_types', 'buyer_companies.company_type', '=', 'company_types.id')
		->select('users.name','users.id','company_types.company_type','users.profile_pic','users.varification_status','buyer_companies.company_street','buyer_companies.company_name','buyer_companies.company_location','buyer_companies.company_image as company_image','countries.name as country_name')
		->whereIn('users.id',$network_id)
		->whereIn('users.id',$active_sellers)
		->when($category_id!=0, function ($query) use ($sellers_list) {
			$query->whereIn('users.id',$sellers_list);
        });
		if($seller_name_search!='')
            $network_list = $network_list->where(DB::raw('UPPER(CONCAT(users.name,COALESCE(buyer_companies.company_name,"")))'), 'LIKE','%'.$seller_name_search.'%');          
		if($company_type!=0)
            $network_list = $network_list->where('buyer_companies.company_type',$company_type); 
        else
			$network_list = $network_list->Where('buyer_companies.company_type','<>',0);    
        if($request->input('selected_country_id')!='')
            $network_list = $network_list->whereIn('users.country_id',$selected_country_id);
         else
			$network_list = $network_list->Where('users.country_id','<>',''); 
			
		$count = $network_list->get()->count();
		$network_list= $network_list->orderby('users.name','ASC')
		->skip($start_from)->take($per_page)->get();
		$mynetwork_list=[];
		foreach ($network_list as $data) {
			$class_verified = '';
			if($data->varification_status=='varified') $class_verified = 'active-border';
			$company_image =    $data->company_image;
            if(($company_image!='')) 
				$img_path = asset('uploads/BuyerCompany/').'/'.$company_image;
			elseif(!empty($data->profile_pic)) 
				$img_path = asset('/uploads/userImages/').'/'.$data->profile_pic;
			else  
				$img_path = asset('uploads/defaultImages/default_avatar.png'); 
				
			
			$mynetwork_list[]=array(
				'id'=>$data->id,
				'name'=>$data->name,
				'company_type'=>$data->company_type??"",
				'class_verified'=>$class_verified,
				'varification_status'=>$data->varification_status,
				'company_street'=>$data->company_street??"",
				'company_name'=>$data->company_name??"",
				'company_location'=>$data->company_location??"",
				'img_path'=>$img_path,
				'country_name'=>$data->country_name??"",
			);
		}
		$return_array=['count'=>$count,'mynetwork_list'=>$mynetwork_list];
		return json_encode($return_array);         
	}
	  public function buildmenustructure(){

          $categories = Category::with('sub')->where('parent_id', null)->where('have_product','Yes')->get(['id','name', 'slug as link','parent_id']);
          $myTree = new buildTree();
          $categoryTree =   $myTree->Tree($categories);
          return json_encode($categoryTree);
       
    } 
}