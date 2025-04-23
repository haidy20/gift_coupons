<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionTranslation;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;

class PermissionSeederOld extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $routesNamesList = [];
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $index => $value) {
            if ($value->getActionName() != null && str_starts_with($value->getActionName(), 'App\Http\Controllers\Admin')) {
                $routeName = $value->getName();

                if (!str_contains($routeName, 'profile') && !str_contains($routeName, 'permission')) {
                    $routesNamesList[$index] = $this->mapPermissionRoute($routeName);
                    info($routesNamesList);
                }
            }
        }

        foreach ($routesNamesList as $perm) {
            info($perm);
            $permission = Permission::firstOrCreate([
                'front_route_name' => $perm['front_name'],
                'back_route_name'  => $perm['back_name'],
            ]);

            foreach (config('translatable.locales') as $locale) {
                $title = $this->transformRouteName($perm['title']);

                PermissionTranslation::firstOrCreate(
                    [
                        'locale'        => $locale,
                        'permission_id' => $permission->id
                    ],
                    [
                        'title' => ($locale == 'en') ? $title : 'ar',
                    ]
                );
            }
        }

        $permission_ids = Permission::pluck('id')->toArray();
        $role = Role::whereHas('translations', function ($query) {
            $query->where('locale', 'en')->where('name', 'admin');
        })->first();

        if (!$role) {
            $role = new Role();
            $role->save();

            $role->translations()->createMany([
                ['locale' => 'en', 'name' => 'admin'],
                ['locale' => 'ar', 'name' => 'admin'],
            ]);
        }

        $role->permissions()->sync($permission_ids);
    }

    /**
     * إنشاء مصفوفة بيانات لصلاحيات معينة.
     */
    private function mapPermissionRoute($routeName)
    {
        $actions = [
            '.index'   => ['show-all', 'get all '],
            '.store'   => ['add', 'add '],
            '.show'    => ['show', 'show '],
            '.update_' => ['edit', 'update '],
            '.update'  => ['edit', 'update '],
            '.destroy' => ['delete', 'delete '],
            '.get'     => ['show', 'show ']
        ];

        foreach ($actions as $key => [$frontAction, $titlePrefix]) {
            if (str_contains($routeName, $key)) {
                $trimmed = str_replace($key, '', $routeName);
                return [
                    'back_name'  => $routeName,
                    'front_name' => $trimmed . '/' . $frontAction,
                    'title'      => $titlePrefix . $trimmed
                ];
            }
        }

        return [];
    }

    /**
     * تحويل اسم المسار إلى صيغة مقروءة.
     */
    private function transformRouteName($routeName)
    {
        return ucwords(str_replace('.', ' ', $routeName));
    }
}
