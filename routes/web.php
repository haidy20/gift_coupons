<?php

use Illuminate\Support\Facades\Route;
use App\Models\Permission;
use App\Models\PermissionTranslation;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    // $routesNamesList = [];
    // $routeCollection = Route::getRoutes();
    // //dd($routeCollection);


    // foreach ($routeCollection as $index => $value) {


    //     if ($value->getActionName() != null && str_starts_with($value->getActionName(), 'App\Http\Controllers\Admin')) {

    //         $routeName = $value->getName();

    //         if (!str_contains($routeName, 'profile') && !str_contains($routeName, 'permission')) {
    //             $routesNamesList[$index] = $this->mapPermissionRoute($routeName);
    //             info($routesNamesList);
    //         }
    //     }
    // }


    // $routesNamesList = array();

    // $routeCollection = Route::getRoutes();

    // echo "<table style='width:100%'>";
    // echo "<tr>";
    // echo "<td width='10%'><h4>HTTP Method</h4></td>";
    // echo "<td width='10%'><h4>Route</h4></td>";
    // echo "<td width='10%'><h4>Name</h4></td>";
    // echo "<td width='70%'><h4>Corresponding Action</h4></td>";
    // echo "</tr>";

    // foreach ($routeCollection as $value) {

    //     $routeName = $value->getName();

    //     if($routeName && str_contains($routeName, "admin.")) {
    //         dd($routeName,$value->getActionName(),str_starts_with($value->getActionName(), 'App\Http\Controllers\Admin'));
    //         $routesNamesList[] = $routeName;
    //     }

    //     echo "<tr>";
    //     echo "<td>" . $value->methods()[0] . "</td>";
    //     echo "<td>" . $value->uri() . "</td>";
    //     echo "<td>" . $value->getName() . "</td>";
    //     //echo "<td>" . $value->getActionName() . "</td>";
    //     echo "</tr>";

    // }

    // echo "</table>";

    // dd($routesNamesList);

    // return view('welcome');

    $routesNamesList = array();

        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $index => $value) {

           // dd($value->getActionName());

            // if(str_starts_with($value->getActionName(),'App\Http\Controllers\Admin')) {
            //     dd('ok',$value->getActionName(),$value->getName());
            // }

            if($value->getActionName() != null && str_starts_with($value->getActionName(),'App\Http\Controllers\Admin') ) {

                $routeName = $value->getName();

                if($routeName != null && ! str_contains($routeName, 'profile') && ! str_contains($routeName, 'role') && ! str_contains($routeName, 'permission') ) {

                    // if($routeName && ! str_starts_with($routeName, "ignition")) { }

                    if(str_contains($routeName, '.index')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.index' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/show-all';

                    } elseif(str_contains($routeName, '.store')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.store' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/add';

                    } elseif(str_contains($routeName, '.show')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.show' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/show';

                    } elseif(str_contains($routeName, '.update_')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.update_' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/edit';

                    } elseif(str_contains($routeName, '.update')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.update' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/edit';

                    } elseif(str_contains($routeName, '.destroy')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.destroy' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/delete';

                    } elseif(str_contains($routeName, '.get')) {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = '.get' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed.'/show';

                    } else {

                        $routesNamesList[$index]['back_name'] = $routeName;

                        $subject = $routeName;
                        $search = 'admin.' ;
                        $trimmed = str_replace($search, '', $subject) ;

                        $routesNamesList[$index]['front_name'] = $trimmed;
                    }
                }

            }
        }

        Permission::where('id','>',0)->delete();
        PermissionTranslation::where('id','>',0)->delete();

        // $routesNamesList = array_unique($routesNamesList);

        // dd($routesNamesList);

        foreach ($routesNamesList as $perm) {

            // dd($perm['front_name'],$perm['back_name']);

            $permission_row = Permission::updateOrCreate(['back_route_name' => $perm['back_name']],[
                'front_route_name' => $perm['front_name'],
                'back_route_name' => $perm['back_name'],
            ]);

            // dd($permission_row,config('translatable.locales'));

            // dd($perm['back_name'],$permission_row->id);

            $languagesArr = ['ar','en'];

            foreach($languagesArr as $locale)
            {
                PermissionTranslation::Create([
                    'title' => ucfirst($perm['back_name']),
                    'locale' => $locale,
                    'permission_id' => $permission_row->id,
                ]);
            }
        }

        dd('success');
});


