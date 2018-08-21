<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use Input;
use Redirect;
use Session;
use Validator;

class user_module extends Controller
{
    public function dash_view()
    {
        return view('user.user_dashboard');
    }

    public function login(Request $request)
    {
        $data = Input::except(array('_token'));
        $rule = array('username' => 'required', 'password' => 'required');
        $message = array('username.required' => 'The username cant empty', 'password.required' => 'This filed never empty');
        $validator = Validator::make($data, $rule, $message);
        if ($validator->fails()) {
            return Redirect::to('/')->withErrors($validator);
        } else {
            $userdata = array('username' => Input::get('username'), 'password' => Input::get('password'), 'banned' => 0); //bcrypt(Input::get('password'))
            if (Auth::attempt($userdata)) {
                $username = $request->get('username');
                $role_id = DB::table('users')->join('users_roles', 'users.id', '=', 'users_roles.user_id')->select('users_roles.role_id')->where('users.username', '=', $username)->get();
                //print_r($role_id);
                $permissions = DB::select('select * from permissions where id=?', [DB::select('select permission_id from permission_roles where role_id=?', [$role_id[0]->role_id])[0]->permission_id]);

                $user_id = DB::table('users')->where('username', $username)->pluck('id');

                //print_r($permissions);
                $role = $role_id[0]->role_id;
                //echo $role;

                Session(['username' => $username, 'role' => $role, 'permissions' => $permissions, 'user_id' => $user_id]);
                Session::put('username1', Input::get('username'));
                Session::put('password1', Input::get('password'));
                // ////$userrole=DB::select('select userrole from users where username=? and password=?',[$userdata['username'],$userdata['password']]);
                /* $role_permission=DB::table('permissions')->join('permission_roles','permissions.id','=','permission_roles.permission_id')->select('permissions.*')->where('permission_roles.role_id','=',$role)->get();
                $permission=DB::select('select * from permission_maps');
                $change_password=DB::table('users')->where('username',$username)->pluck('change_password');*/

                //print_r($permission);
                //print_r($role_permission);
                //return view('admin.dashboard',compact('role_permission'))->with('permission',$permission);//->with('role_permission',$role_permission);
                //return Redirect::to('user/dashboard')->with('name', 'amal');
                //  if($change_password[0]==1){
                return Redirect::to('user/dashboard'); //->with('permission',$permission)->with('role_permission',$role_permission);
                // }
                //  else{
                //     return redirect('change_password')->with('username',$username);
                //   }
                //return redirect()->route('user/dashboard', ['permission' => $permission,'role_permission'=>$role_permission]);
                //return Redirect::to('user/dashboard',['role_permission'=>$role_permission,'permission'=>$permission]);
            } else {
                $errors = array('err_msg' => 'Email and/or password invalid.');
                return Redirect::to('/')->withErrors($errors);
                //echo $userdata['password'];

            }

        }
    }

    /* public function dash(){
    // {{ $role=Session::get('role'); }}
    //$role_permission=DB::table('permissions')->join('permission_roles','permissions.id','=','permission_roles.permission_id')->select('permissions.*')->where('permission_roles.role_id','=',$role)->get();
    //  $permission=DB::select('select * from permission_maps');
    return view('user.user_dashboard');//,compact('role_permission'))->with('permission',$permission);

    }*/

    public function doLogout(Request $request)
    {
        Auth::logout(); // log the user out of our application
        Session::flush();
        return Redirect::to('/signin'); // redirect the user to the login screen
    }

    //create user
    public function create_user_page(Request $request)
    {
        return view('user.user_registration');
    }

    public function create_user(Request $request)
    {
        $data = Input::except(array('_token'));
        // print_r($data);
        /*   $rule=array('name'=>'required','city'=>'required','district'=>'required','state'=>'required','address'=>'required','phone_no'=>'required','password'=>'required','username'=>'required','role_id'=>'required','gender'=>'required','age'=>'required');
        // $message=array('stagename.required'=>'The stagename cant empty','description.required'=>'Enter description');

        $validator=Validator::make($data,$rule);
        if($validator->fails()){
        print_r("error");
        // return Redirect::to('user/create_user')->withErrors($validator);
        }*/
       // print_r($validator);
       $new_pass=Hash::make($request->password);
      // $current_user=Session::get('user_id');
     //  if(DB::insert('insert into users(username,password,email,office_id,role_id,report_to, district_id, sub_district_id, edu_district_id, school_code, designation_id,created_by)values(?,?,?,?,?,?,?,?,?,?,?,?)',[$request->username, $new_pass, $request->email, $request->users_office,$request->usergroup,$request->reporting_office, $request->district, $request->sub_district, $request->edu_district, $request->school,$request->designation,$current_user[0]])) {
   
     // DB::insert('insert into user_details (username) values (?)',[$request->username]);
     //      return Redirect::to('user/create_user');


        if(DB::insert('insert into users (name,address,city,district,state,phone_no,password,username,role,gender,age) values(?,?,?,?,?,?,?,?,?,?,?)',[$request->name,$request->address,$request->city,$request->district,$request->state,$request->phone_no,$new_pass,$request->username,$request->role,$request->radios,$request->age])){
         // print_r("inserted");
         $user_id=DB::select('select max(id) as id from users');
   
         DB::insert('insert into users_roles (user_id, role_id) values (?,?)',[$user_id[0]->id,$request->role]);

            \Session::flash('flash_message','New User created successfully.');  
            return Redirect::to('user/dashboard');
        } else {
            $errors = array('err_msg' => 'Email and/or password invalid.');
            \Session::flash('error_message', 'Database encountered some error. Please try again');
            return Redirect::to('user/create_user')->withErrors($errors);
        }
    }

    //delete user
    public function delete_user()
    {
        if (Session::get('permissions')[0]->delete_user) {
            // {{ $role=Session::get('role');  }}
            DB::delete('delete from users where id=?', [$request->id]);
            return Redirect::to('user/dashboard');
        } else {
            return view('error_page');
        }
    }

}
