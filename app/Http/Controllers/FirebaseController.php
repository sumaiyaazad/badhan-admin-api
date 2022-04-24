<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class FirebaseController extends Controller
{
    //

    protected $database;

    private $messages;

    private $rules;

    public function __construct()
    {
        $this->database = app('firebase.database');
        $this->messages = [
            'in' => 'The :attribute must be one of the following values: :values',
            'min' => 'The :attribute does not have minimum length :min',
            'array' => 'The :attribute must be an array'
        ];

        $this->rules = [
            'type' => [
                'required',
                Rule::in(['Active Developers', 'Contributors of Badhan', 'Legacy Developers']),
            ],
            'name' => [
                'required',
                'min:3'
            ],
            'calender' => [
                'required',
                'min:3'
            ],
            'contribution' => [
                'required',
                'array',
                'min:1'
            ],
            "contribution.*" => [
                'min:3',
            ],
            'links' => [
                'required',
                'array',
                'min:1'
            ],
            "links.*.color" => [
                'required',
                'min:3',
            ],
            "links.*.icon" => [
                'required',
                'min:3',
            ],
            "links.*.link" => [
                'required',
                'url',
            ],
        ];
    }

    public function index(){
        $contributors =  $this->database->getReference('data/')->getvalue();
        $activeDevelopers=[];
        $contributorsOfBadhan=[];
        $legacyDevelopers=[];
        $keys = array_keys($contributors);
        foreach ($contributors as $contributor){
            $contributor['id']= array_shift($keys);
            if($contributor['type']=='Active Developers'){
                $activeDevelopers[] = $contributor;
            }else if($contributor['type']=='Contributors of Badhan'){
                $contributorsOfBadhan[]=$contributor;
            }else{
                $legacyDevelopers[]=$contributor;
            };
        }
        return response()->json(['status'=>200, 'message'=>'Contributors fetched successfully','contributors'=>['activeDevelopers'=>$activeDevelopers,'contributorsOfBadhan'=>$contributorsOfBadhan,'legacyDevelopers'=>$legacyDevelopers]]);
    }

    public function store(Request $request){
        $validator = Validator::make( $request->all(), $this->rules , $this->messages);
        $id = Carbon::now()->timestamp;
        if ($validator->fails()) {
            return response()->json(['status'=>400,'data'=>$validator->errors()]);
        }

        $this->database->getReference('data/'.$id)
            ->set($validator->valid());
        $validatedInput = $validator->valid();
        $validatedInput["id"] = $id;
        return response()->json(['status'=>201,'message'=>'Contributor created successfully','contributor'=>$validatedInput]);
    }

    public function update(Request $request,$id){
        $validator = Validator::make( $request->all(), $this->rules , $this->messages);
        if ($validator->fails()) {
            return response()->json(['status'=>400,'data'=>$validator->errors()]);
        }
//        $contributors = $this->database->getReference('data/')->getvalue();
//        $keys = array_keys($contributors);
        $this->database->getReference('data/'.$id)
            ->update($validator->valid());
        $reference = $this->database->getReference('data/'.$id)->getvalue();
        $reference['id']=$id;
        return response()->json(['status'=>200, 'message'=>'Contributor edited successfully','contributor'=>$reference]);
    }

    public function updateImage(){

    }

    public function destroy(Request $input){

        $this->database->getReference('data/'.$input->id)->remove();
        return response()->json(['status'=>200,'message'=>'Contributor deleted successfully']);
    }

    public function indexFrontendSettings(){

    }

    public function updateFrontendSettings(){

    }

}
