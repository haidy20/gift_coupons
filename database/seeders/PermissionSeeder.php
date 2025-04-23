<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Permission;
use App\Models\PermissionTranslation;
use App\Models\Role;
use App\Models\RoleTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        
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

        $permission_ids = Permission::pluck('id')->toArray();

        // $role = Role::create(['en' => ['name' => 'admin'],'ar' => ['name' => 'admin']]);

        $role = Role::create();

        // إدخال الترجمات 
        foreach (['en', 'ar'] as $locale) {
            RoleTranslation::create([
                'role_id' => $role->id,
                'locale' => $locale,
                'name' => 'admin',
            ]);
        }

        $role->permissions()->attach($permission_ids);

    }
}
