<?php

namespace App\Http\Controllers\Db;

use Illuminate\Http\Request;
use DB;
use Log;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use QueryException;

class DbController extends Controller
{
    //
    public function listAll($tableName)
    {

        // option - shift - a = block command
        // https://code.visualstudio.com/docs/customization/keybindings

        /*DB::table($tableName)->where('id','>','7')->orderBy('id')->chunk(3, function($rows)
        {
            foreach($rows as $row)
            {
                print_r($row);
            }
            return false;
        });*/

        // where() and get()
        /*$rows = DB::table($tableName)->where('id','>','7')->get();
        foreach($rows as $row)
        {
            echo $row->id.":".$row->name;
        }*/

        // where() and value()
        /*$rows = DB::table($tableName)->where('id','>','2')->value('name');
        echo $rows;*/

        // pluck() 
        /*$rows = DB::table($tableName)->pluck('id');
        foreach($rows as $row)
        {
            echo($row.':');
        }*/

        // pluck() more than one column
        /*$rows = DB::table($tableName)->pluck('id', 'name');
        foreach($rows as $name => $id)
        {
            echo "User: $id, $name \n";
        }*/

        // count()
        /*$count = DB::table($tableName)->sum('id');
        echo $count;*/

        // select()
        $query = DB::table($tableName)->select('name');
        $condition = 1 > 0? true:false;
        if( $condition ){
            $query = $query->addSelect('id');
        }
        $rows = $query->get();

        //$rows  = DB::table($tableName)->select('name')->addSelect('id')->distinct()->get();
        foreach($rows as $row){
            echo "$row->name : ";
            if( $condition ){
                echo $row->id;
            }
        }
    }

    public function show($id)
    {
        //$users = DB::select('select * from user where id > ?',[$id]);
        $users = DB::table('user')->select('select * from user where id > ?',[$id]);
        foreach($users as $user)
        {
            echo $user->id.':'.$user->name;
        }
    }

    public function insert($name)
    {
        // auto rollback if exception is thrown while auto commit if quiry is excecuted successfully
        // add use($variable) to pass outside variable to anonymous function;
        // use &$variable for pass by reference while $variable for pass by value
        DB::transaction(function() use ($name)
        {
            // enable loggling
            DB::connection()->enableQueryLog();
            DB::insert('insert into user values(?,?)',[null, $name]);
            $result = DB::getQueryLog();
            print_r($result);
            $name = 'update'.$name;
            echo $name;
            //$result[0]['query']
            //echo($result[0]['query']);
        });
        echo $name;
    }

    public function insertWfError()
    {
        try{
            DB::beginTransaction();
            Log::info('beginTransaction');
            DB::update('update test set id = 1 where id >1');
            DB::commit;
            Log::info('commit');
        }catch(QueryException $e){
            echo $e;
            Log::info('Error: '.$e);
            DB::rollBack();
        }
    }

    public function update($id, $name)
    {
        // reattempt the quiry after 5 seconds if deadlock occured
        DB::transaction(function() use($name, $id)
        {
            $row = DB::update('update user set name = ? where id = ?',[$name, $id]);
            echo 'Number of row affected: '.$row;
        },5);
    }

    public function delete($id)
    {
        $row = DB::delete('delete from user where id = ?',[$id]);
        echo 'Number of row affected: '.$row;
    }
}
