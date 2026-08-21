<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('truncate table role_has_permissions');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    	$allPermissions = Permission::pluck('id');

    	//Adminsitrador
    	$role = Role::findByName('Administrador');

    	if ($role) 
    	{
	    	foreach($allPermissions as $perm)
	    	{
	    		\DB::table('role_has_permissions')->insert([
	    			'permission_id' => $perm,
	    			'role_id' => $role->id
	    		]);
	    		//$role->givePermissionTo[$perm];
	    	}
    	}
    }
}
